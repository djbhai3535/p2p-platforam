<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\Setting;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class NOWPaymentsWebhookTest extends TestCase
{
    use RefreshDatabase;

    private User $user;

    private Country $country;

    private Language $language;

    private Transaction $transaction;

    protected function setUp(): void
    {
        parent::setUp();

        // Seed settings
        Setting::create(['key' => 'nowpayments_ipn_secret', 'value' => 'my-secret-key', 'group' => 'nowpayments']);

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
            'name' => 'Deposit User',
            'email' => 'deposit@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->transaction = Transaction::create([
            'wallet_id' => $this->user->wallet->id,
            'type' => 'deposit',
            'amount' => 100.00000000,
            'status' => 'pending',
            'payment_provider' => 'nowpayments',
            'payment_id' => '123456789',
        ]);
    }

    public function test_webhook_fails_with_invalid_signature()
    {
        $payload = [
            'payment_status' => 'finished',
            'order_id' => $this->transaction->id,
            'payment_id' => '123456789',
            'pay_amount' => 100.00000000,
        ];

        $response = $this->postJson('/api/payments/nowpayments/webhook', $payload, [
            'x-nowpayments-sig' => 'invalid-signature',
        ]);

        $response->assertStatus(400);
        $response->assertJsonStructure(['error']);

        $this->transaction->refresh();
        $this->assertEquals('pending', $this->transaction->status);
        $this->assertEquals('0.00000000', $this->user->wallet->available_balance);
    }

    public function test_webhook_succeeds_with_valid_signature_and_credits_wallet()
    {
        $payload = [
            'order_id' => $this->transaction->id,
            'pay_amount' => 100.00000000,
            'payment_id' => '123456789',
            'payment_status' => 'finished',
        ];

        // Generate dynamic signature
        ksort($payload);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha512', $json, 'my-secret-key');

        $response = $this->postJson('/api/payments/nowpayments/webhook', $payload, [
            'x-nowpayments-sig' => $signature,
        ]);

        $response->assertStatus(200);
        $response->assertJson(['success' => true]);

        $this->transaction->refresh();
        $this->user->wallet->refresh();

        $this->assertEquals('completed', $this->transaction->status);
        $this->assertEquals('100.00000000', $this->user->wallet->available_balance);

        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'WALLET_DEPOSIT',
        ]);
    }

    public function test_webhook_sets_status_to_rejected_on_failure()
    {
        $payload = [
            'order_id' => $this->transaction->id,
            'pay_amount' => 100.00000000,
            'payment_id' => '123456789',
            'payment_status' => 'failed',
        ];

        ksort($payload);
        $json = json_encode($payload, JSON_UNESCAPED_SLASHES);
        $signature = hash_hmac('sha512', $json, 'my-secret-key');

        $response = $this->postJson('/api/payments/nowpayments/webhook', $payload, [
            'x-nowpayments-sig' => $signature,
        ]);

        $response->assertStatus(200);

        $this->transaction->refresh();
        $this->user->wallet->refresh();

        $this->assertEquals('rejected', $this->transaction->status);
        $this->assertEquals('0.00000000', $this->user->wallet->available_balance);
    }
}
