<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Auth\Events\Login;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;
    private Language $language;

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
    }

    public function test_user_can_view_login_page()
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Welcome Back');
    }

    public function test_user_can_register_successfully()
    {
        $response = $this->post('/register', [
            'name' => 'Test User',
            'email' => 'test@user.com',
            'country_id' => $this->country->id,
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ]);

        $response->assertRedirect('/email/verify');
        $this->assertDatabaseHas('users', ['email' => 'test@user.com']);

        $user = User::where('email', 'test@user.com')->first();
        
        // Assert wallet was auto-created via booting
        $this->assertNotNull($user->wallet);
        $this->assertEquals('0.00000000', $user->wallet->available_balance);

        // Assert OTP was cached
        $this->assertTrue(Cache::has("otp.{$user->id}"));
    }

    public function test_user_can_authenticate_with_valid_credentials()
    {
        $user = User::create([
            'name' => 'John Doe',
            'email' => 'john@doe.com',
            'password' => Hash::make('password123'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'john@doe.com',
            'password' => 'password123',
        ]);

        $response->assertRedirect('/dashboard');
        $this->assertAuthenticatedAs($user);

        // Assert device login & audit log were recorded by the listener
        $this->assertDatabaseHas('device_logins', ['user_id' => $user->id]);
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $user->id,
            'action' => 'LOGIN'
        ]);
    }

    public function test_user_is_redirected_to_2fa_challenge_if_enabled()
    {
        $user = User::create([
            'name' => 'Secure User',
            'email' => 'secure@user.com',
            'password' => Hash::make('password123'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'two_factor_secret' => 'B3BCDEFGHIJKLMNO', // Mock secret key
            'two_factor_confirmed_at' => now(),
            'is_active' => true,
        ]);

        $response = $this->post('/login', [
            'email' => 'secure@user.com',
            'password' => 'password123',
        ]);

        // Should redirect to 2fa challenge, not directly logged in yet
        $response->assertRedirect('/login/two-factor');
        $this->assertGuest();
    }

    public function test_user_can_verify_email_via_otp()
    {
        $user = User::create([
            'name' => 'Verify User',
            'email' => 'verify@user.com',
            'password' => Hash::make('password123'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => null, // Unverified
            'is_active' => true,
        ]);

        $this->actingAs($user);

        // Setup OTP
        Cache::put("otp.{$user->id}", '123456', now()->addMinutes(15));

        $response = $this->post('/email/verify/otp', [
            'otp' => '123456',
        ]);

        $response->assertRedirect('/dashboard');
        
        $user->refresh();
        $this->assertNotNull($user->email_verified_at);
        $this->assertFalse(Cache::has("otp.{$user->id}"));
    }
}
