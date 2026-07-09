<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\Setting;
use App\Models\User;
use App\Services\WalletService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class WithdrawalTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Country $country;

    private Language $language;

    private WalletService $walletService;

    protected function setUp(): void
    {
        parent::setUp();

        $this->walletService = new WalletService;

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
            'name' => 'Withdraw User',
            'email' => 'withdraw@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_user_can_request_withdrawal_with_sufficient_balance()
    {
        // Load wallet balance: 100 USDT
        $this->user->wallet->update(['available_balance' => 100.00000000]);

        // Request withdrawal of 50 USDT (Fee: 2 USDT, total: 52 USDT)
        $transaction = $this->walletService->requestWithdrawal($this->user, 50.00000000, 'TAddressXYZ');

        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'amount' => '50.00000000',
            'fee' => '2.00000000',
            'status' => 'pending',
            'address' => 'TAddressXYZ',
            'type' => 'withdrawal',
        ]);

        $this->user->wallet->refresh();
        // 100 - 52 = 48 USDT available balance
        $this->assertEquals('48.00000000', $this->user->wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'WALLET_WITHDRAWAL_REQUEST',
        ]);
    }

    public function test_withdrawal_fails_if_insufficient_balance()
    {
        $this->user->wallet->update(['available_balance' => 10.00000000]);

        $this->expectException(\Exception::class);
        $this->expectExceptionMessage('Insufficient balance to request withdrawal');

        // Needs 50 + 2 = 52 USDT
        $this->walletService->requestWithdrawal($this->user, 50.00000000, 'TAddressXYZ');
    }

    public function test_admin_can_approve_withdrawal()
    {
        $this->user->wallet->update(['available_balance' => 100.00000000]);
        $transaction = $this->walletService->requestWithdrawal($this->user, 50.00000000, 'TAddressXYZ');

        // Approve
        $result = $this->walletService->approveWithdrawal($transaction);

        $this->assertTrue($result);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'completed',
        ]);

        $this->user->wallet->refresh();
        // Available remains 48 USDT (already deducted on request)
        $this->assertEquals('48.00000000', $this->user->wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'WALLET_WITHDRAWAL_APPROVE',
        ]);
    }

    public function test_admin_can_reject_withdrawal_and_refunds_balance()
    {
        $this->user->wallet->update(['available_balance' => 100.00000000]);
        $transaction = $this->walletService->requestWithdrawal($this->user, 50.00000000, 'TAddressXYZ');

        // Reject
        $result = $this->walletService->rejectWithdrawal($transaction);

        $this->assertTrue($result);
        $this->assertDatabaseHas('transactions', [
            'id' => $transaction->id,
            'status' => 'rejected',
        ]);

        $this->user->wallet->refresh();
        // Refunded 50 + 2 = 52 USDT -> back to 100 USDT available balance
        $this->assertEquals('100.00000000', $this->user->wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'WALLET_WITHDRAWAL_REJECT',
        ]);
    }
}
