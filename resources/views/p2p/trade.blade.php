@extends('layouts.dashboard')

@section('title', 'Trade Room #' . substr($order->id, 0, 8))

@section('content')
<div class="row g-4">
    <!-- Left Column: Trade Status & Steps -->
    <div class="col-lg-8">
        <div class="glass-card mb-4">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 border-bottom border-secondary pb-3 mb-4">
                <div>
                    <h3 class="card-title-custom mb-0">Order #{{ substr($order->id, 0, 8) }}</h3>
                    <p class="text-muted-custom small mb-0">Status: 
                        <span class="badge @if($order->status === 'completed') bg-success @elseif($order->status === 'paid') bg-info @elseif($order->status === 'disputed') bg-danger @elseif($order->status === 'cancelled') bg-secondary @else bg-warning @endif">
                            {{ ucfirst($order->status) }}
                        </span>
                    </p>
                </div>
                <!-- Countdown Timer -->
                @if($order->status === 'pending')
                    <div class="text-end">
                        <span class="text-muted-custom small d-block">Payment Countdown</span>
                        <h4 class="fw-bold text-warning mb-0" id="countdown-timer">15:00</h4>
                    </div>
                @endif
            </div>

            <!-- Trade Details Summary -->
            <div class="row g-3 mb-4">
                <div class="col-sm-4 col-6">
                    <span class="text-muted-custom small">Amount to Receive</span>
                    <h4 class="fw-bold text-success mb-0">{{ number_format($order->amount_usdt, 8) }} <span class="fs-6 text-muted-custom">USDT</span></h4>
                </div>
                <div class="col-sm-4 col-6">
                    <span class="text-muted-custom small">Total Fiat Payment</span>
                    <h4 class="fw-bold text-light mb-0">{{ number_format($order->amount_fiat, 2) }} <span class="fs-6 text-muted-custom">{{ $order->advertisement->country->currency_code }}</span></h4>
                </div>
                <div class="col-sm-4 col-12">
                    <span class="text-muted-custom small">Rate</span>
                    <h4 class="fw-bold text-primary mb-0">{{ number_format($order->rate, 2) }} <span class="fs-6 text-muted-custom">{{ $order->advertisement->country->currency_code }}/USDT</span></h4>
                </div>
            </div>

            <!-- Role Instructions -->
            @if(Auth::id() === $order->buyer_id)
                <!-- I am the BUYER -->
                @if($order->status === 'pending')
                    <div class="alert alert-warning border-0" style="background-color: rgba(245, 158, 11, 0.05);">
                        <h6 class="fw-bold text-warning mb-1">Instructions for Buyer:</h6>
                        <p class="mb-0 small text-muted-custom">Please transfer exactly <strong>{{ number_format($order->amount_fiat, 2) }} {{ $order->advertisement->country->currency_code }}</strong> using one of the seller's payment methods below. After sending, upload the payment transfer screenshot and click "Mark as Paid" before the countdown expires.</p>
                    </div>

                    <!-- Payment methods of the seller -->
                    @php
                        $paymentAccounts = $order->seller->userPaymentMethods()
                            ->whereIn('payment_method_id', $order->advertisement->payment_method_ids ?? [])
                            ->get();
                    @endphp

                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">Seller's Payment Details:</h6>
                        @if($paymentAccounts->isEmpty())
                            <p class="text-muted-custom small">Contact seller in chat for payment details.</p>
                        @else
                            <div class="d-flex flex-column gap-2">
                                @foreach($paymentAccounts as $account)
                                    <div class="p-3 rounded border border-secondary" style="background-color: rgba(255,255,255,0.01);">
                                        <h6 class="fw-bold text-primary mb-1">{{ $account->paymentMethod->name }}</h6>
                                        <p class="mb-1 small"><strong>Account Title:</strong> {{ $account->account_title }}</p>
                                        @foreach($account->account_details as $k => $v)
                                            <p class="mb-0 small text-muted-custom"><strong>{{ ucfirst(str_replace('_', ' ', $k)) }}:</strong> {{ $v }}</p>
                                        @endforeach
                                    </div>
                                @endforeach
                            </div>
                        @endif
                    </div>

                    <!-- Action form -->
                    <div class="border-top border-secondary pt-3 mt-4 d-flex justify-content-between flex-wrap gap-2">
                        <form method="POST" action="{{ route('orders.cancel', $order->id) }}">
                            @csrf
                            <button type="submit" class="btn btn-outline-danger px-4" onclick="return confirm('Are you sure you want to cancel this order?')">Cancel Trade</button>
                        </form>
                        
                        <button type="button" class="btn btn-premium px-5" data-bs-toggle="modal" data-bs-target="#paidModal">Mark as Paid</button>
                    </div>
                @elseif($order->status === 'paid')
                    <div class="alert alert-info border-0" style="background-color: rgba(59, 130, 246, 0.05);">
                        <h6 class="fw-bold text-info mb-1">Awaiting Release:</h6>
                        <p class="mb-0 small text-muted-custom">You have marked this trade as paid. The seller is currently verifying their account. Once verified, they will release the USDT to your wallet available balance.</p>
                    </div>
                @endif
            @else
                <!-- I am the SELLER -->
                @if($order->status === 'pending')
                    <div class="alert alert-info border-0" style="background-color: rgba(59, 130, 246, 0.05);">
                        <h6 class="fw-bold text-info mb-1">Awaiting Buyer Payment:</h6>
                        <p class="mb-0 small text-muted-custom">The buyer is transferring <strong>{{ number_format($order->amount_fiat, 2) }} {{ $order->advertisement->country->currency_code }}</strong> to your linked accounts. Do NOT release the escrow until you have received and confirmed the full payment in your bank account.</p>
                    </div>
                @elseif($order->status === 'paid')
                    <div class="alert alert-warning border-0" style="background-color: rgba(245, 158, 11, 0.05);">
                        <h6 class="fw-bold text-warning mb-1">Action Required: Release Escrow</h6>
                        <p class="mb-3 small text-muted-custom">The buyer has marked this trade as paid. Please verify the payment in your bank account. If received, click "Release Escrow" to credit the USDT. If not received, you may open a dispute.</p>
                        @if($order->payment_screenshot)
                            <a href="{{ asset('storage/' . $order->payment_screenshot) }}" target="_blank" class="btn btn-sm btn-outline-warning">View Payment Screenshot</a>
                        @endif
                    </div>

                    <div class="border-top border-secondary pt-3 mt-4 text-end">
                        <form method="POST" action="{{ route('orders.release', $order->id) }}" class="d-inline">
                            @csrf
                            <button type="submit" class="btn btn-success px-5" onclick="return confirm('WARNING: Have you confirmed the payment inside your bank account? Do NOT release if payment is not received.')">Release Escrow</button>
                        </form>
                    </div>
                @endif
            @endif

            <!-- Dispute Button Gating -->
            @if(in_array($order->status, ['paid', 'pending']))
                <div class="mt-4 pt-3 border-top border-secondary">
                    <button type="button" class="btn btn-sm btn-outline-danger" data-bs-toggle="modal" data-bs-target="#disputeModal">
                        ⚠️ Open Dispute
                    </button>
                </div>
            @endif

            <!-- Dispute Status Box -->
            @if($order->status === 'disputed')
                <div class="p-3 rounded border border-danger mb-4" style="background-color: rgba(239, 68, 68, 0.05);">
                    <h6 class="fw-bold text-danger mb-1">Dispute Under Review:</h6>
                    <p class="mb-0 small text-muted-custom">A dispute was opened. Our support compliance team is reviewing the chat logs and bank screenshot proof. Escrow funds will remain locked until resolved by the admin.</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Right Column: Chat Room Placeholder (Real-time features in Stage 7) -->
    <div class="col-lg-4">
        <div class="glass-card d-flex flex-column h-100" style="min-height: 450px;">
            <h5 class="fw-bold mb-3 text-primary">Trade Chat</h5>
            <div class="chat-messages-container flex-grow-1 border border-secondary rounded p-3 mb-3 text-muted-custom small" style="background-color: rgba(0,0,0,0.1); max-height: 350px; overflow-y: auto;">
                <div class="text-center text-muted-custom py-4">System: Escrow locked. Communication chat is open. Stay inside this chat for secure records.</div>
            </div>
            
            <div class="input-group">
                <input type="text" class="form-control" placeholder="Type a message..." disabled>
                <button class="btn btn-primary" type="button" disabled>Send</button>
            </div>
        </div>
    </div>
</div>

<!-- Mark as Paid Modal -->
<div class="modal fade" id="paidModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">Mark Trade as Paid</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('orders.paid', $order->id) }}" enctype="multipart/form-data">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="payment_screenshot" class="form-label">Upload Payment Screenshot / Receipt</label>
                        <input id="payment_screenshot" type="file" class="form-control" name="payment_screenshot" required accept="image/*">
                        <div class="form-text text-muted-custom small">Please upload a clear screenshot of the transaction receipt showing the reference ID.</div>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-premium">Submit Payment Confirmation</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Dispute Modal -->
<div class="modal fade" id="disputeModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content bg-dark text-light border-secondary">
            <div class="modal-header border-secondary">
                <h5 class="modal-title fw-bold">File a Dispute</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="{{ route('orders.dispute', $order->id) }}">
                @csrf
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="reason" class="form-label">Reason for Dispute</label>
                        <textarea id="reason" class="form-control" name="reason" rows="4" required minlength="10" placeholder="Provide a detailed explanation. E.g. 'Paid the buyer but they did not release' or 'Buyer marked paid but funds not received'"></textarea>
                    </div>
                </div>
                <div class="modal-footer border-secondary">
                    <button type="button" class="btn btn-outline-secondary" data-bs-toggle="modal" data-bs-dismiss="modal">Close</button>
                    <button type="submit" class="btn btn-danger">File Dispute</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
@if($order->status === 'pending')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        const timerLabel = document.getElementById('countdown-timer');
        const expiryTime = new Date('{{ $order->expiry_at->toIso8601String() }}').getTime();

        const interval = setInterval(function () {
            const now = new Date().getTime();
            const distance = expiryTime - now;

            if (distance < 0) {
                clearInterval(interval);
                timerLabel.innerText = "EXPIRED";
                timerLabel.classList.remove('text-warning');
                timerLabel.classList.add('text-danger');
                return;
            }

            const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
            const seconds = Math.floor((distance % (1000 * 60)) / 1000);

            timerLabel.innerText = (minutes < 10 ? "0" + minutes : minutes) + ":" + (seconds < 10 ? "0" + seconds : seconds);
        }, 1000);
    });
</script>
@endif
@endsection
