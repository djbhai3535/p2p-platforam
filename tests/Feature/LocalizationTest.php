<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class LocalizationTest extends TestCase
{
    use RefreshDatabase;

    private Country $country;
    private Language $language;
    private User $user;

    protected function setUp(): void
    {
        parent::setUp();

        $this->language = Language::create([
            'name' => 'Spanish',
            'code' => 'es',
            'direction' => 'ltr',
            'is_active' => true,
            'is_default' => false,
        ]);

        $this->country = Country::create([
            'name' => 'Argentina',
            'iso_code' => 'AR',
            'currency_code' => 'ARS',
            'currency_symbol' => '$',
            'phone_code' => '+54',
            'is_active' => true,
        ]);

        $this->user = User::create([
            'name' => 'Juan R',
            'email' => 'juan@test.com',
            'password' => Hash::make('password'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_user_can_set_language_session()
    {
        $response = $this->get(route('locale.set', 'es'));
        $response->assertRedirect();
        
        $this->assertEquals('es', session('locale'));
    }

    public function test_user_can_set_country_session()
    {
        $response = $this->get(route('country.set', $this->country->id));
        $response->assertRedirect();

        $this->assertEquals($this->country->id, session('country_id'));
    }

    public function test_middleware_applies_session_locale()
    {
        // Set session
        $this->withSession(['locale' => 'es']);

        // Request dashboard or homepage
        $response = $this->actingAs($this->user)->get(route('marketplace'));
        
        $this->assertEquals('es', app()->getLocale());
    }
}
