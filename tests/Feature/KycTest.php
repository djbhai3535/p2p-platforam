<?php

namespace Tests\Feature;

use App\Models\Country;
use App\Models\KycVerification;
use App\Models\Language;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class KycTest extends TestCase
{
    use RefreshDatabase;

    private User $user;
    private Country $country;
    private Language $language;

    protected function setUp(): void
    {
        parent::setUp();

        // Fake local storage disk to prevent writing actual files during tests
        Storage::fake('local');

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
            'name' => 'Test User',
            'email' => 'test@user.com',
            'password' => Hash::make('password123'),
            'country_id' => $this->country->id,
            'language_id' => $this->language->id,
            'email_verified_at' => now(),
            'is_active' => true,
        ]);
    }

    public function test_authenticated_user_can_view_kyc_page()
    {
        $response = $this->actingAs($this->user)->get('/profile/kyc');

        $response->assertStatus(200);
        $response->assertSee('KYC Identity Verification');
    }

    public function test_user_can_submit_kyc_documents_successfully()
    {
        $front = UploadedFile::fake()->image('front.jpg');
        $back = UploadedFile::fake()->image('back.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->actingAs($this->user)->post('/profile/kyc', [
            'full_name' => 'Test KYC User',
            'dob' => '1995-05-15',
            'document_type' => 'id_card',
            'document_number' => '12345-6789012-3',
            'front_image' => $front,
            'back_image' => $back,
            'selfie_image' => $selfie,
        ]);

        $response->assertRedirect('/profile/kyc');
        $response->assertSessionHas('status', 'Your identity verification documents have been submitted successfully.');

        // Verify database state
        $this->assertDatabaseHas('kyc_verifications', [
            'user_id' => $this->user->id,
            'full_name' => 'Test KYC User',
            'document_type' => 'id_card',
            'status' => 'pending',
        ]);

        $kyc = KycVerification::where('user_id', $this->user->id)->first();

        // Verify secure file storage (private local folder)
        Storage::disk('local')->assertExists($kyc->front_image_path);
        Storage::disk('local')->assertExists($kyc->back_image_path);
        Storage::disk('local')->assertExists($kyc->selfie_image_path);

        // Verify audit log creation
        $this->assertDatabaseHas('audit_logs', [
            'user_id' => $this->user->id,
            'action' => 'KYC_SUBMIT'
        ]);
    }

    public function test_user_cannot_double_submit_kyc_if_pending()
    {
        // Setup existing pending KYC
        KycVerification::create([
            'user_id' => $this->user->id,
            'full_name' => 'Existing Name',
            'dob' => '1995-05-15',
            'country_id' => $this->country->id,
            'document_type' => 'passport',
            'document_number' => 'EP123456',
            'front_image_path' => 'private/kyc/fake_front.jpg',
            'selfie_image_path' => 'private/kyc/fake_selfie.jpg',
            'status' => 'pending',
        ]);

        $front = UploadedFile::fake()->image('front.jpg');
        $selfie = UploadedFile::fake()->image('selfie.jpg');

        $response = $this->actingAs($this->user)->post('/profile/kyc', [
            'full_name' => 'New Name',
            'dob' => '1995-05-15',
            'document_type' => 'passport',
            'document_number' => 'EP123456',
            'front_image' => $front,
            'selfie_image' => $selfie,
        ]);

        $response->assertSessionHasErrors(['message']);
        
        // Assert old record remains unchanged
        $this->assertDatabaseHas('kyc_verifications', [
            'user_id' => $this->user->id,
            'full_name' => 'Existing Name',
        ]);
    }
}
