<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WalletControllerTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Country $country;

    private Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        Setting::create(['key' => 'withdrawal_fee', 'value' => '2.0', 'group' => 'fees']);

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

        $this->user = User::create([
            'name' => 'User One',
            'email' => 'userone@example.com',
            'password' => bcrypt('password123'),
            'email_verified_at' => now(),
            'google2fa_secret' => null,
            'google2fa_enabled' => false,
        ]);

        // Create user wallet safely
        if (! $this->user->wallet) {
            $this->user->wallet()->create([
                'available_balance' => 100.0,
                'locked_balance' => 0.0,
            ]);
        } else {
            $this->user->wallet->update([
                'available_balance' => 100.0,
                'locked_balance' => 0.0,
            ]);
        }
    }

    /**
     * Test user can access wallet page.
     */
    public function test_user_can_access_wallet_page()
    {
        $response = $this->actingAs($this->user)->get(route('wallet'));

        $response->assertStatus(200);
        $response->assertViewIs('wallet.index');
        $response->assertViewHas('user');
        $response->assertViewHas('transactions');
    }

    /**
     * Test user can request deposit link simulation.
     */
    public function test_user_can_request_deposit()
    {
        $response = $this->actingAs($this->user)->post(route('wallet.deposit'), [
            'amount' => 50.0,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHas('deposit_result');

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->user->wallet->id,
            'type' => 'deposit',
            'amount' => 50.0,
            'status' => 'pending',
        ]);
    }

    /**
     * Test user can request withdrawal.
     */
    public function test_user_can_request_withdrawal()
    {
        $trc20Address = 'T123456789012345678901234567890123'; // valid TRC-20 layout starting with T

        $response = $this->actingAs($this->user)->post(route('wallet.withdraw'), [
            'amount' => 10.0,
            'address' => $trc20Address,
        ]);

        $response->assertStatus(302);
        $response->assertRedirect(route('wallet'));
        $response->assertSessionHas('status');

        $this->user->wallet->refresh();
        // 100.0 - (10.0 amount + 2.0 fee) = 88.0 available balance
        $this->assertEquals(88.0, (float) $this->user->wallet->available_balance);

        $this->assertDatabaseHas('transactions', [
            'wallet_id' => $this->user->wallet->id,
            'type' => 'withdrawal',
            'amount' => 10.0,
            'fee' => 2.0,
            'status' => 'pending',
            'address' => $trc20Address,
        ]);
    }

    /**
     * Test user cannot request withdrawal with insufficient balance.
     */
    public function test_user_cannot_withdraw_insufficient_balance()
    {
        $trc20Address = 'T123456789012345678901234567890123';

        $response = $this->actingAs($this->user)->post(route('wallet.withdraw'), [
            'amount' => 200.0, // greater than 100.0 available
            'address' => $trc20Address,
        ]);

        $response->assertStatus(302);
        $response->assertSessionHasErrors(['withdraw']);
    }
}
