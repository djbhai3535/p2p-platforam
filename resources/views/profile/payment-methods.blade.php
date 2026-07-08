@extends('layouts.dashboard')

@section('title', 'Payment Methods')

@section('content')
<div class="row g-4">
    <!-- Linked Accounts List -->
    <div class="col-md-7">
        <div class="glass-card h-100">
            <h3 class="card-title-custom">Linked Payment Accounts</h3>
            <p class="text-muted-custom small mb-4">These payment accounts will be visible to buyers when they open a sell trade with you.</p>

            @if($linkedMethods->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted-custom">No payment methods linked yet.</p>
                </div>
            @else
                <div class="d-flex flex-column gap-3">
                    @foreach($linkedMethods as $method)
                        <div class="p-3 rounded-3 border d-flex justify-content-between align-items-center" style="background-color: rgba(255,255,255,0.02); border-color: var(--border-color) !important;">
                            <div>
                                <h5 class="fw-bold mb-1 text-primary">{{ $method->paymentMethod->name }}</h5>
                                <p class="mb-1 small"><strong>Title:</strong> {{ $method->account_title }}</p>
                                @foreach($method->account_details as $key => $val)
                                    <p class="mb-0 small text-muted-custom">
                                        <strong>{{ ucfirst(str_replace('_', ' ', $key)) }}:</strong> {{ $val }}
                                    </p>
                                @endforeach
                            </div>
                            <div>
                                <form method="POST" action="{{ route('profile.payment-methods.destroy', $method->id) }}">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger px-3" onclick="return confirm('Are you sure you want to remove this account?')">Remove</button>
                                </form>
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>

    <!-- Add Payment Method Form -->
    <div class="col-md-5">
        <div class="glass-card">
            <h3 class="card-title-custom">Link New Account</h3>
            <p class="text-muted-custom small mb-4">Choose a dynamic payment option available in your country.</p>

            <form method="POST" action="{{ route('profile.payment-methods.store') }}">
                @csrf

                <!-- Method Select -->
                <div class="mb-3">
                    <label for="payment_method_id" class="form-label">Payment Method</label>
                    <select id="payment_method_id" name="payment_method_id" class="form-select form-control" required style="background-image: url(&quot;data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='m2 5 6 6 6-6'/%3e%3c/svg%3e&quot;);">
                        <option value="" disabled selected>Select method</option>
                        @foreach($availableMethods as $method)
                            <option value="{{ $method->id }}" data-fields="{{ json_encode($method->fields) }}">
                                {{ $method->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <!-- Account Title -->
                <div class="mb-3">
                    <label for="account_title" class="form-label">Account Title (Must match KYC Name)</label>
                    <input id="account_title" type="text" class="form-control" name="account_title" required value="{{ Auth::user()->name }}" placeholder="e.g. John Doe">
                </div>

                <!-- Dynamic Fields Container -->
                <div id="dynamic-fields-container"></div>

                <div class="d-grid mt-4">
                    <button type="submit" class="btn btn-premium">Link Account</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const methodSelect = document.getElementById('payment_method_id');
        const fieldsContainer = document.getElementById('dynamic-fields-container');

        methodSelect.addEventListener('change', function () {
            // Clear current inputs
            fieldsContainer.innerHTML = '';
            
            const selectedOption = methodSelect.options[methodSelect.selectedIndex];
            const fieldsJson = selectedOption.getAttribute('data-fields');
            
            if (fieldsJson) {
                const fields = JSON.parse(fieldsJson);
                
                fields.forEach(field => {
                    const div = document.createElement('div');
                    div.className = 'mb-3';
                    
                    const label = document.createElement('label');
                    label.className = 'form-label';
                    label.innerText = field.label;
                    label.setAttribute('for', 'field-' + field.name);
                    
                    const input = document.createElement('input');
                    input.className = 'form-control';
                    input.id = 'field-' + field.name;
                    input.name = 'details[' + field.name + ']';
                    input.type = field.type === 'number' ? 'text' : 'text'; // Handle type
                    if (field.required) {
                        input.setAttribute('required', 'required');
                    }
                    input.placeholder = 'Enter ' + field.label;

                    div.appendChild(label);
                    div.appendChild(input);
                    fieldsContainer.appendChild(div);
                });
            }
        });
    });
</script>
@endsection
