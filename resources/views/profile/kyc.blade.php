@extends('layouts.dashboard')

@section('title', 'KYC Verification')

@section('content')
<div class="row justify-content-center">
    <div class="col-lg-8 col-md-10">
        <div class="glass-card">
            <h3 class="card-title-custom">KYC Identity Verification</h3>
            <p class="text-muted mb-4">To comply with financial regulations and secure your P2P trading limits, please complete your identity verification. Your documents will be reviewed securely by our team.</p>

            @if($kyc)
                @if($kyc->status === 'approved')
                    <div class="p-4 mb-4 rounded-3 border border-success" style="background-color: rgba(16, 185, 129, 0.05); border-color: rgba(16, 185, 129, 0.2) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-success fs-2">✓</div>
                            <div>
                                <h5 class="mb-1 text-success fw-bold">Verification Approved</h5>
                                <p class="mb-0 text-muted-custom small">Your account is fully verified. You now have access to P2P advertisements creation and escrow trading.</p>
                            </div>
                        </div>
                    </div>
                    
                    <div class="mt-4">
                        <h5 class="fw-bold mb-3">Submitted Details</h5>
                        <ul class="list-group list-group-flush bg-transparent border-0">
                            <li class="list-group-item bg-transparent text-light border-secondary ps-0"><strong>Full Name:</strong> {{ $kyc->full_name }}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary ps-0"><strong>Document Type:</strong> {{ strtoupper(str_replace('_', ' ', $kyc->document_type)) }}</li>
                            <li class="list-group-item bg-transparent text-light border-secondary ps-0"><strong>Document Number:</strong> *******{{ substr($kyc->document_number, -4) }}</li>
                            <li class="list-group-item bg-transparent text-light border-0 ps-0"><strong>Verified At:</strong> {{ $kyc->reviewed_at->format('Y-m-d H:i') }}</li>
                        </ul>
                    </div>
                @elseif($kyc->status === 'pending')
                    <div class="p-4 mb-4 rounded-3 border border-warning" style="background-color: rgba(245, 158, 11, 0.05); border-color: rgba(245, 158, 11, 0.2) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="spinner-border text-warning" role="status"></div>
                            <div>
                                <h5 class="mb-1 text-warning fw-bold">Verification Pending</h5>
                                <p class="mb-0 text-muted-custom small">Your documents have been submitted and are currently undergoing manual review by our compliance team. Typically takes 12-24 hours.</p>
                            </div>
                        </div>
                    </div>
                @elseif($kyc->status === 'rejected')
                    <div class="p-4 mb-4 rounded-3 border border-danger" style="background-color: rgba(239, 68, 68, 0.05); border-color: rgba(239, 68, 68, 0.2) !important;">
                        <div class="d-flex align-items-center gap-3">
                            <div class="text-danger fs-2">✗</div>
                            <div>
                                <h5 class="mb-1 text-danger fw-bold">Verification Rejected</h5>
                                <p class="mb-0 text-muted-custom small"><strong>Reason:</strong> {{ $kyc->rejection_reason ?? 'Your document images were unclear.' }}</p>
                            </div>
                        </div>
                    </div>
                    
                    <p class="text-muted-custom small mb-4">Please verify the instructions and resubmit clear copies of your documents below.</p>
                    @include('profile.partials.kyc_form')
                @endif
            @else
                @include('profile.partials.kyc_form')
            @endif
        </div>
    </div>
</div>
@endsection
