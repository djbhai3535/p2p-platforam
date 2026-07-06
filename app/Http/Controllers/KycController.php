<?php

namespace App\Http\Controllers;

use App\Models\AuditLog;
use App\Models\KycVerification;
use Illuminate\Http\Request;

class KycController extends Controller
{
    /**
     * Display the KYC verification status or form.
     */
    public function index(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $request->user();
        $kyc = $user->kycVerification;

        return view('profile.kyc', compact('kyc'));
    }

    /**
     * Handle KYC documents submission.
     */
    public function submit(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Prevent double submission if pending or approved
        if ($user->kycVerification && in_array($user->kycVerification->status, ['pending', 'approved'])) {
            return back()->withErrors(['message' => 'You already have a pending or approved verification request.']);
        }

        $request->validate([
            'full_name' => ['required', 'string', 'max:255'],
            'dob' => ['required', 'date', 'before:today'],
            'document_type' => ['required', 'string', 'in:id_card,passport'],
            'document_number' => ['required', 'string', 'max:100'],
            'front_image' => ['required', 'image', 'max:5120'], // Max 5MB
            'back_image' => ['required_if:document_type,id_card', 'nullable', 'image', 'max:5120'],
            'selfie_image' => ['required', 'image', 'max:5120'],
        ]);

        // Secure file uploads inside the 'local' private storage
        $frontPath = $request->file('front_image')->store('private/kyc');
        
        $backPath = null;
        if ($request->hasFile('back_image')) {
            $backPath = $request->file('back_image')->store('private/kyc');
        }

        $selfiePath = $request->file('selfie_image')->store('private/kyc');

        // Create or update record
        KycVerification::updateOrCreate(
            ['user_id' => $user->id],
            [
                'full_name' => $request->full_name,
                'dob' => $request->dob,
                'country_id' => $user->country_id,
                'document_type' => $request->document_type,
                'document_number' => $request->document_number,
                'front_image_path' => $frontPath,
                'back_image_path' => $backPath,
                'selfie_image_path' => $selfiePath,
                'status' => 'pending',
                'rejection_reason' => null,
            ]
        );

        // Audit Trail logging
        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'KYC_SUBMIT',
            'description' => "Submitted KYC verification documents ({$request->document_type}).",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('profile.kyc')->with('status', 'Your identity verification documents have been submitted successfully.');
    }
}
