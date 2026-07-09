<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\PaymentMethod;
use App\Models\User;
use App\Models\UserPaymentMethod;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserPaymentMethodTest extends TestCase
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
            'name' => 'John Seller',
            'email' => 'seller@test.com',
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
            'fields' => [
                ['name' => 'account_title', 'label' => 'Account Title', 'type' => 'text', 'required' => true],
                ['name' => 'mobile_number', 'label' => 'Mobile Number', 'type' => 'text', 'required' => true],
            ],
            'is_active' => true,
        ]);
    }

    public function test_user_can_view_linked_payment_methods_page()
    {
        $response = $this->actingAs($this->user)->get('/profile/payment-methods');
        $response->assertStatus(200);
        $response->assertSee('Linked Payment Accounts');
    }

    public function test_user_can_link_payment_method_successfully()
    {
        $response = $this->actingAs($this->user)->post('/profile/payment-methods', [
            'payment_method_id' => $this->paymentMethod->id,
            'account_title' => 'John Seller Account',
            'details' => [
                'account_title' => 'John Seller Account',
                'mobile_number' => '03001234567',
            ],
        ]);

        $response->assertRedirect('/profile/payment-methods');
        $response->assertSessionHas('status', 'Payment method linked successfully.');

        $this->assertDatabaseHas('user_payment_methods', [
            'user_id' => $this->user->id,
            'payment_method_id' => $this->paymentMethod->id,
            'account_title' => 'John Seller Account',
        ]);

        $linked = UserPaymentMethod::where('user_id', $this->user->id)->first();
        $this->assertEquals('03001234567', $linked->account_details['mobile_number']);
    }

    public function test_user_cannot_link_payment_method_with_missing_required_fields()
    {
        $response = $this->actingAs($this->user)->post('/profile/payment-methods', [
            'payment_method_id' => $this->paymentMethod->id,
            'account_title' => 'John Seller Account',
            'details' => [
                'account_title' => 'John Seller Account',
                'mobile_number' => '', // Missing required field!
            ],
        ]);

        $response->assertSessionHasErrors(['details.mobile_number']);
        $this->assertDatabaseMissing('user_payment_methods', [
            'user_id' => $this->user->id,
        ]);
    }
}
