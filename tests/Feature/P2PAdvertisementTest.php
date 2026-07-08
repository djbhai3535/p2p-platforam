<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\KycVerification;
use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\Advertisement;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class P2PAdvertisementTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Country $country;
    private Language $language;
    private PaymentMethod $paymentMethod;

    protected function setUp(): void
    {
        parent::setUp();

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
            'name' => 'Ad Merchant',
            'email' => 'merchant@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $this->paymentMethod = PaymentMethod::create([
            'country_id' => $this->country->id,
            'name' => 'JazzCash',
            'slug' => 'jazzcash',
            'fields' => [['name' => 'account_no', 'label' => 'Account No', 'type' => 'text', 'required' => true]],
            'is_active' => true,
        ]);
    }

    public function test_unverified_user_cannot_access_create_ad_page()
    {
        // User has no KYC
        $response = $this->actingAs($this->user)->get('/advertisements/create');

        $response->assertRedirect('/profile/kyc');
        $response->assertSessionHasErrors(['message']);
    }

    public function test_verified_user_can_access_create_ad_page()
    {
        // Approve KYC for the user
        KycVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Merchant Name',
            'dob' => '1990-01-01',
            'country_id' => $this->country->id,
            'document_type' => 'id_card',
            'document_number' => '12345',
            'front_image_path' => 'private/kyc/front.jpg',
            'selfie_image_path' => 'private/kyc/selfie.jpg',
            'status' => 'approved',
        ]);

        $response = $this->actingAs($this->user)->get('/advertisements/create');
        $response->assertStatus(200);
        $response->assertSee('Post P2P Advertisement');
    }

    public function test_verified_user_cannot_post_sell_ad_with_insufficient_balance()
    {
        // Set KYC to approved
        KycVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Merchant Name',
            'dob' => '1990-01-01',
            'country_id' => $this->country->id,
            'document_type' => 'id_card',
            'document_number' => '12345',
            'front_image_path' => 'private/kyc/front.jpg',
            'selfie_image_path' => 'private/kyc/selfie.jpg',
            'status' => 'approved',
        ]);

        // Wallet is empty (0 USDT)
        $response = $this->actingAs($this->user)->post('/advertisements', [
            'country_id' => $this->country->id,
            'type' => 'sell',
            'price_type' => 'fixed',
            'rate' => 280.00,
            'amount' => 100.00000000, // Wants to sell 100 USDT
            'min_limit' => 500,
            'max_limit' => 28000,
            'payment_methods' => [$this->paymentMethod->id],
        ]);

        $response->assertSessionHasErrors(['amount']);
        $this->assertDatabaseMissing('advertisements', ['user_id' => $this->user->id]);
    }

    public function test_verified_user_can_post_sell_ad_with_sufficient_balance()
    {
        // Set KYC to approved
        KycVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Merchant Name',
            'dob' => '1990-01-01',
            'country_id' => $this->country->id,
            'document_type' => 'id_card',
            'document_number' => '12345',
            'front_image_path' => 'private/kyc/front.jpg',
            'selfie_image_path' => 'private/kyc/selfie.jpg',
            'status' => 'approved',
        ]);

        // Fund user's wallet available balance with 200 USDT
        $this->user->wallet->update(['available_balance' => 200.00000000]);

        $response = $this->actingAs($this->user)->post('/advertisements', [
            'country_id' => $this->country->id,
            'type' => 'sell',
            'price_type' => 'fixed',
            'rate' => 280.00,
            'amount' => 100.00000000, // Wants to sell 100 USDT
            'min_limit' => 500,
            'max_limit' => 28000,
            'payment_methods' => [$this->paymentMethod->id],
        ]);

        $response->assertRedirect('/my-ads');
        $response->assertSessionHas('status', 'P2P Advertisement created successfully.');

        $this->assertDatabaseHas('advertisements', [
            'user_id' => $this->user->id,
            'type' => 'sell',
            'amount' => '100.00000000',
            'status' => 'active',
        ]);

        $ad = Advertisement::where('user_id', $this->user->id)->first();
        $this->assertCount(1, $ad->paymentMethods);
    }
}
