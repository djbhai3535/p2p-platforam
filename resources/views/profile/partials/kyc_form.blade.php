<form method="POST" action="{{ route('profile.kyc.submit') }}" enctype="multipart/form-data">
    @csrf

    <div class="row g-3">
        <!-- Full Name -->
        <div class="col-md-6">
            <label for="full_name" class="form-label">Full Name (As shown on ID)</label>
            <input id="full_name" type="text" class="form-control" name="full_name" value="{{ old('full_name') }}" required placeholder="John Doe">
        </div>

        <!-- Date of Birth -->
        <div class="col-md-6">
            <label for="dob" class="form-label">Date of Birth</label>
            <input id="dob" type="date" class="form-control" name="dob" value="{{ old('dob') }}" required>
        </div>

        <!-- Document Type -->
        <div class="col-md-6">
            <label for="document_type" class="form-label">Document Type</label>
            <select id="document_type" name="document_type" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                <option value="id_card" {{ old('document_type') === 'id_card' ? 'selected' : '' }}>National Identity Card (CNIC / ID)</option>
                <option value="passport" {{ old('document_type') === 'passport' ? 'selected' : '' }}>International Passport</option>
            </select>
        </div>

        <!-- Document Number -->
        <div class="col-md-6">
            <label for="document_number" class="form-label">Document Number</label>
            <input id="document_number" type="text" class="form-control" name="document_number" value="{{ old('document_number') }}" required placeholder="Enter ID/Passport Number">
        </div>

        <hr class="my-4 border-secondary">

        <!-- Document Images Uploads -->
        <div class="col-md-6">
            <div class="mb-4">
                <label for="front_image" class="form-label">Front of ID Document</label>
                <input id="front_image" type="file" class="form-control" name="front_image" required accept="image/*">
                <div class="form-text text-muted-custom small">Upload a clear photo of the front side of your ID.</div>
            </div>
        </div>

        <div class="col-md-6" id="back-image-wrapper">
            <div class="mb-4">
                <label for="back_image" class="form-label">Back of ID Document</label>
                <input id="back_image" type="file" class="form-control" name="back_image" required accept="image/*">
                <div class="form-text text-muted-custom small">Upload a clear photo of the back side of your ID (unnecessary for Passports).</div>
            </div>
        </div>

        <div class="col-md-6">
            <div class="mb-4">
                <label for="selfie_image" class="form-label">Selfie with Document</label>
                <input id="selfie_image" type="file" class="form-control" name="selfie_image" required accept="image/*">
                <div class="form-text text-muted-custom small">Take a selfie holding your ID card next to your face clearly.</div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="col-12 mt-4 text-end">
            <button type="submit" class="btn btn-premium px-4 py-2">Submit Verification</button>
        </div>
    </div>
</form>

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const docTypeSelect = document.getElementById('document_type');
        const backImageWrapper = document.getElementById('back-image-wrapper');
        const backImageInput = document.getElementById('back_image');

        function toggleBackImage() {
            if (docTypeSelect.value === 'passport') {
                backImageWrapper.style.display = 'none';
                backImageInput.removeAttribute('required');
            } else {
                backImageWrapper.style.display = 'block';
                backImageInput.setAttribute('required', 'required');
            }
        }

        docTypeSelect.addEventListener('change', toggleBackImage);
        toggleBackImage(); // Run once on load
    });
</script>
@endsection
