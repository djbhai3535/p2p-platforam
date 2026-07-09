<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Country;
use App\Models\Dispute;
use App\Models\KycVerification;
use App\Models\Language;
use App\Models\Order;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class P2POrderTest extends TestCase
{
    use RefreshDatabase;

    private User $merchant;

    private User $buyer;

    private Country $country;

    private Language $language;

    private PaymentMethod $paymentMethod;

    private Advertisement $advertisement;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $this->language = Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);

        $this->country = Country::create([
            'name' => 'Pakistan',
            'iso_code' => 'PK',
            'currency_code' => 'PKR',
            'currency_symbol' => '₨',
            'phone_code' => '+92',
            'is_active' => true,
        ]);

        $this->merchant = User::create([
            'name' => 'Ad Merchant',
            'email' => 'merchant@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'USDT Buyer',
            'email' => 'buyer@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // Approve KYC for merchant
        KycVerification::create([
            'user_id' => $this->merchant->id,
            'full_name' => 'Merchant Name',
            'dob' => '1990-01-01',
            'country_id' => $this->country->id,
            'document_type' => 'id_card',
            'document_number' => '12345',
            'front_image_path' => 'private/kyc/front1.jpg',
            'selfie_image_path' => 'private/kyc/selfie1.jpg',
            'status' => 'approved',
        ]);

        // Approve KYC for buyer
        KycVerification::create([
            'user_id' => $this->buyer->id,
            'full_name' => 'Buyer Full Name',
            'dob' => '1992-05-15',
            'country_id' => $this->country->id,
            'document_type' => 'id_card',
            'document_number' => '54321',
            'front_image_path' => 'private/kyc/front2.jpg',
            'selfie_image_path' => 'private/kyc/selfie2.jpg',
            'status' => 'approved',
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'country_id' => $this->country->id,
            'name' => 'EasyPaisa',
            'slug' => 'easypaisa',
            'fields' => [['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true]],
            'is_active' => true,
        ]);

        // Fund merchant's wallet to post and back the Sell Ad
        $this->merchant->wallet->update(['available_balance' => 200.00000000]);

        $this->advertisement = Advertisement::create([
            'user_id' => $this->merchant->id,
            'country_id' => $this->country->id,
            'type' => 'sell',
            'price_type' => 'fixed',
            'rate' => 280.00,
            'amount' => 100.00000000,
            'min_limit' => 1000,
            'max_limit' => 30000,
            'payment_method_ids' => [$this->paymentMethod->id],
            'status' => 'active',
        ]);
    }

    public function test_verified_buyer_can_place_order_and_escrow_locks()
    {
        // Place Order of 50 USDT (Fiat: 14000 PKR, limits: 1000-30000 PKR)
        $response = $this->actingAs($this->buyer)->post(route('orders.store', $this->advertisement->id), [
            'amount_usdt' => 50.00000000,
        ]);

        $this->assertDatabaseHas('orders', [
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->merchant->id,
            'amount_usdt' => '50.00000000',
            'amount_fiat' => '14000.00',
            'status' => 'pending',
        ]);

        $order = Order::where('buyer_id', $this->buyer->id)->first();
        $response->assertRedirect(route('orders.show', $order->id));

        // Verify Escrow Lock (merchant's locked balance: 50 + 1% fee = 50.5 USDT)
        $this->merchant->wallet->refresh();
        $this->assertEquals('149.50000000', $this->merchant->wallet->available_balance);
        $this->assertEquals('50.50000000', $this->merchant->wallet->locked_balance);

        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'status' => 'locked',
        ]);
    }

    public function test_buyer_can_mark_order_as_paid_with_receipt()
    {
        // Place Order
        $this->actingAs($this->buyer)->post(route('orders.store', $this->advertisement->id), [
            'amount_usdt' => 50.00000000,
        ]);
        $order = Order::where('buyer_id', $this->buyer->id)->first();

        // Mark as Paid
        $receipt = UploadedFile::fake()->image('receipt.png');
        $response = $this->actingAs($this->buyer)->post(route('orders.paid', $order->id), [
            'payment_screenshot' => $receipt,
        ]);

        $response->assertRedirect(route('orders.show', $order->id));

        $order->refresh();
        $this->assertEquals('paid', $order->status);
        Storage::disk('public')->assertExists($order->payment_screenshot);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->buyer->id,
            'action' => 'ORDER_MARK_PAID',
        ]);
    }

    public function test_seller_can_release_order_after_payment()
    {
        // Place Order
        $this->actingAs($this->buyer)->post(route('orders.store', $this->advertisement->id), [
            'amount_usdt' => 50.00000000,
        ]);
        $order = Order::where('buyer_id', $this->buyer->id)->first();

        // Mark as Paid
        $receipt = UploadedFile::fake()->image('receipt.png');
        $this->actingAs($this->buyer)->post(route('orders.paid', $order->id), [
            'payment_screenshot' => $receipt,
        ]);

        // Release as merchant (seller)
        $response = $this->actingAs($this->merchant)->post(route('orders.release', $order->id));

        $response->assertRedirect(route('orders.show', $order->id));
        $response->assertSessionHas('status', 'USDT released successfully! Trade complete.');

        $order->refresh();
        $this->assertEquals('completed', $order->status);

        // Verify wallets update
        $this->merchant->wallet->refresh();
        $this->buyer->wallet->refresh();

        // Merchant locked balance is now 0 (50.5 USDT released)
        $this->assertEquals('0.00000000', $this->merchant->wallet->locked_balance);
        // Buyer available balance increased by 50 USDT
        $this->assertEquals('50.00000000', $this->buyer->wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->merchant->id,
            'action' => 'ORDER_RELEASE',
        ]);
    }

    public function test_user_can_open_dispute()
    {
        // Place Order
        $this->actingAs($this->buyer)->post(route('orders.store', $this->advertisement->id), [
            'amount_usdt' => 50.00000000,
        ]);
        $order = Order::where('buyer_id', $this->buyer->id)->first();

        // Open dispute
        $response = $this->actingAs($this->buyer)->post(route('orders.dispute', $order->id), [
            'reason' => 'I made payment but merchant went offline.',
        ]);

        $response->assertRedirect(route('orders.show', $order->id));

        $order->refresh();
        $this->assertEquals('disputed', $order->status);

        $this->assertDatabaseHas('disputes', [
            'order_id' => $order->id,
            'disputed_by' => $this->buyer->id,
            'reason' => 'I made payment but merchant went offline.',
        ]);
    }
}
