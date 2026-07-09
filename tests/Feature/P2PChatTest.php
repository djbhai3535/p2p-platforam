<?php

namespace Tests\Feature;

use App\Events\MessageSent;
use App\Models\Advertisement;
use App\Models\Country;
use App\Models\KycVerification;
use App\Models\Language;
use App\Models\Order;
use App\Models\OrderMessage;
use App\Models\PaymentMethod;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class P2PChatTest extends TestCase
{
    use RefreshDatabase;

    private User $buyer;

    private User $seller;

    private User $unrelatedUser;

    private Order $order;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');

        $language = Language::create([
            'name' => 'English',
            'code' => 'en',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => true,
        ]);

        $country = Country::create([
            'name' => 'Pakistan',
            'iso_code' => 'PK',
            'currency_code' => 'PKR',
            'currency_symbol' => '₨',
            'phone_code' => '+92',
            'is_active' => true,
        ]);

        $this->seller = User::create([
            'name' => 'Seller Merchant',
            'email' => 'seller@test.com',
            'password' => Hash::make('password'),
            'country_id' => $country->id,
            'language_id' => $language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->buyer = User::create([
            'name' => 'Buyer Client',
            'email' => 'buyer@test.com',
            'password' => Hash::make('password'),
            'country_id' => $country->id,
            'language_id' => $language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->unrelatedUser = User::create([
            'name' => 'Stranger User',
            'email' => 'stranger@test.com',
            'password' => Hash::make('password'),
            'country_id' => $country->id,
            'language_id' => $language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        // KYC Verification approved for buyer & seller
        KycVerification::create([
            'user_id' => $this->seller->id,
            'full_name' => 'Seller Name',
            'dob' => '1985-01-01',
            'country_id' => $country->id,
            'document_type' => 'passport',
            'document_number' => 'AA12345',
            'front_image_path' => 'private/kyc/front_s.jpg',
            'selfie_image_path' => 'private/kyc/selfie_s.jpg',
            'status' => 'approved',
        ]);

        KycVerification::create([
            'user_id' => $this->buyer->id,
            'full_name' => 'Buyer Name',
            'dob' => '1990-05-05',
            'country_id' => $country->id,
            'document_type' => 'id_card',
            'document_number' => 'BB54321',
            'front_image_path' => 'private/kyc/front_b.jpg',
            'selfie_image_path' => 'private/kyc/selfie_b.jpg',
            'status' => 'approved',
        ]);

        $paymentMethod = PaymentMethod::create([
            'country_id' => $country->id,
            'name' => 'EasyPaisa',
            'slug' => 'easypaisa',
            'fields' => [['name' => 'phone', 'label' => 'Phone', 'type' => 'text', 'required' => true]],
            'is_active' => true,
        ]);

        $this->seller->wallet->update(['available_balance' => 100.0]);

        $advertisement = Advertisement::create([
            'user_id' => $this->seller->id,
            'country_id' => $country->id,
            'type' => 'sell',
            'price_type' => 'fixed',
            'rate' => 280.00,
            'amount' => 50.00000000,
            'min_limit' => 1000,
            'max_limit' => 30000,
            'payment_method_ids' => [$paymentMethod->id],
            'status' => 'active',
        ]);

        $this->order = Order::create([
            'advertisement_id' => $advertisement->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount_usdt' => 10.00000000,
            'amount_fiat' => 2800.00,
            'rate' => 280.00,
            'status' => 'pending',
            'expiry_at' => now()->addMinutes(15),
        ]);
    }

    public function test_trade_members_can_fetch_messages()
    {
        OrderMessage::create([
            'order_id' => $this->order->id,
            'sender_id' => $this->seller->id,
            'message' => 'Hello buyer, please pay.',
        ]);

        // Seller fetches
        $response1 = $this->actingAs($this->seller)->getJson(route('orders.chat.messages', $this->order->id));
        $response1->assertOk();
        $response1->assertJsonCount(1);
        $response1->assertJsonFragment(['message' => 'Hello buyer, please pay.']);

        // Buyer fetches
        $response2 = $this->actingAs($this->buyer)->getJson(route('orders.chat.messages', $this->order->id));
        $response2->assertOk();
        $response2->assertJsonCount(1);
    }

    public function test_unrelated_user_cannot_fetch_messages()
    {
        $response = $this->actingAs($this->unrelatedUser)->getJson(route('orders.chat.messages', $this->order->id));
        $response->assertStatus(403);
    }

    public function test_trade_members_can_send_text_message_and_trigger_broadcast()
    {
        Event::fake();

        $response = $this->actingAs($this->buyer)->postJson(route('orders.chat.send', $this->order->id), [
            'message' => 'I have transferred the amount.',
        ]);

        $response->assertOk();
        $response->assertJson([
            'success' => true,
        ]);

        $this->assertDatabaseHas('order_messages', [
            'order_id' => $this->order->id,
            'sender_id' => $this->buyer->id,
            'message' => 'I have transferred the amount.',
        ]);

        Event::assertDispatched(MessageSent::class, function ($event) {
            return $event->message->message === 'I have transferred the amount.';
        });
    }

    public function test_trade_members_can_send_attachment_message()
    {
        Event::fake();

        $file = UploadedFile::fake()->image('payment_receipt.jpg');

        $response = $this->actingAs($this->buyer)->postJson(route('orders.chat.send', $this->order->id), [
            'attachment' => $file,
        ]);

        $response->assertOk();
        $response->assertJson(['success' => true]);

        $message = OrderMessage::where('order_id', $this->order->id)->first();
        $this->assertNotNull($message->attachment_path);
        Storage::disk('public')->assertExists($message->attachment_path);

        Event::assertDispatched(MessageSent::class);
    }

    public function test_empty_message_cannot_be_sent()
    {
        $response = $this->actingAs($this->buyer)->postJson(route('orders.chat.send', $this->order->id), [
            'message' => '',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => 'Cannot send an empty message.']);
    }

    public function test_cannot_send_messages_to_archived_trades()
    {
        $this->order->update(['status' => 'completed']);

        $response = $this->actingAs($this->buyer)->postJson(route('orders.chat.send', $this->order->id), [
            'message' => 'Hello',
        ]);

        $response->assertStatus(400);
        $response->assertJsonFragment(['error' => 'Trade order is completed or cancelled.']);
    }
}
