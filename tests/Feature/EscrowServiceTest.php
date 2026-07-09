<?php

namespace Tests\Feature;

use App\Models\Advertisement;
use App\Models\Country;
use App\Models\Order;
use App\Models\Setting;
use App\Models\User;
use App\Services\EscrowService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class EscrowServiceTest extends TestCase
{
    use RefreshDatabase;

    private EscrowService $escrowService;

    private User $admin;

    private User $seller;

    private User $buyer;

    private Country $country;

    private Advertisement $ad;

    protected function setUp(): void
    {
        parent::setUp();

        $this->escrowService = new EscrowService;

        // Seed settings
        Setting::create(['key' => 'fee_percentage', 'value' => '1.5', 'group' => 'fees']);

        // Create country
        $this->country = Country::create([
            'name' => 'Pakistan',
            'iso_code' => 'PK',
            'currency_code' => 'PKR',
            'currency_symbol' => '₨',
            'phone_code' => '+92',
            'is_active' => true,
        ]);

        // Create admin (needs a wallet created automatically via boot)
        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => Hash::make('password'),
            'is_admin' => true,
            'is_active' => true,
        ]);

        // Create seller
        $this->seller = User::create([
            'name' => 'Seller User',
            'email' => 'seller@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
        ]);

        // Create buyer
        $this->buyer = User::create([
            'name' => 'Buyer User',
            'email' => 'buyer@test.com',
            'password' => Hash::make('password'),
            'is_admin' => false,
            'is_active' => true,
        ]);

        // Create advertisement
        $this->ad = Advertisement::create([
            'user_id' => $this->seller->id,
            'country_id' => $this->country->id,
            'type' => 'sell',
            'price_type' => 'fixed',
            'rate' => 280.00,
            'amount' => 1000.00000000,
            'min_limit' => 5000.00,
            'max_limit' => 50000.00,
            'payment_method_ids' => [],
            'status' => 'active',
        ]);
    }

    public function test_it_fails_to_lock_escrow_if_seller_has_insufficient_funds()
    {
        $order = Order::create([
            'advertisement_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount_usdt' => 100.00000000, // Requires 100 + 1.5% fee = 101.5 USDT
            'amount_fiat' => 28000.00,
            'rate' => 280.00,
            'status' => 'pending',
            'expiry_at' => now()->addMinutes(15),
        ]);

        // Seller currently has 0 available balance
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('Insufficient balance to lock in escrow');

        $this->escrowService->lock($order);
    }

    public function test_it_locks_escrow_successfully_when_funds_are_sufficient()
    {
        // Give seller 200 USDT
        $this->seller->wallet->update(['available_balance' => 200.00000000]);

        $order = Order::create([
            'advertisement_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount_usdt' => 100.00000000, // Requires 100 + 1.5% fee = 101.5 USDT
            'amount_fiat' => 28000.00,
            'rate' => 280.00,
            'status' => 'pending',
            'expiry_at' => now()->addMinutes(15),
        ]);

        $escrow = $this->escrowService->lock($order);

        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'amount_usdt' => '100.00000000',
            'fee_usdt' => '1.50000000',
            'status' => 'locked',
        ]);

        $this->seller->wallet->refresh();
        // 200 - 101.5 = 98.5 USDT
        $this->assertEquals('98.50000000', $this->seller->wallet->available_balance);
        $this->assertEquals('101.50000000', $this->seller->wallet->locked_balance);
    }

    public function test_it_releases_escrowed_funds_to_buyer_and_sends_fees_to_admin()
    {
        // Give seller 200 USDT
        $this->seller->wallet->update(['available_balance' => 200.00000000]);

        $order = Order::create([
            'advertisement_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount_usdt' => 100.00000000, // Requires 101.5 USDT locked
            'amount_fiat' => 28000.00,
            'rate' => 280.00,
            'status' => 'pending',
            'expiry_at' => now()->addMinutes(15),
        ]);

        // Lock funds first
        $this->escrowService->lock($order);

        // Verify pre-conditions
        $this->assertEquals('0.00000000', $this->buyer->wallet->available_balance);
        $this->assertEquals('0.00000000', $this->admin->wallet->available_balance);

        // Release escrow
        $result = $this->escrowService->release($order);

        $this->assertTrue($result);
        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'status' => 'released',
        ]);

        $this->seller->wallet->refresh();
        $this->buyer->wallet->refresh();
        $this->admin->wallet->refresh();

        // Seller locked balance becomes 0, available balance remains 98.5
        $this->assertEquals('98.50000000', $this->seller->wallet->available_balance);
        $this->assertEquals('0.00000000', $this->seller->wallet->locked_balance);

        // Buyer available balance receives 100 USDT
        $this->assertEquals('100.00000000', $this->buyer->wallet->available_balance);

        // Admin available balance receives 1.5 USDT
        $this->assertEquals('1.50000000', $this->admin->wallet->available_balance);
    }

    public function test_it_refunds_escrowed_funds_back_to_seller_on_cancellation()
    {
        $this->seller->wallet->update(['available_balance' => 200.00000000]);

        $order = Order::create([
            'advertisement_id' => $this->ad->id,
            'buyer_id' => $this->buyer->id,
            'seller_id' => $this->seller->id,
            'amount_usdt' => 100.00000000,
            'amount_fiat' => 28000.00,
            'rate' => 280.00,
            'status' => 'pending',
            'expiry_at' => now()->addMinutes(15),
        ]);

        $this->escrowService->lock($order);

        // Refund escrow
        $result = $this->escrowService->refund($order);

        $this->assertTrue($result);
        $this->assertDatabaseHas('escrows', [
            'order_id' => $order->id,
            'status' => 'refunded',
        ]);

        $this->seller->wallet->refresh();
        // Seller gets all 200 USDT back in available balance
        $this->assertEquals('200.00000000', $this->seller->wallet->available_balance);
        $this->assertEquals('0.00000000', $this->seller->wallet->locked_balance);
    }
}
