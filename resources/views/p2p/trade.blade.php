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
                    <h4 class="fw-bold text-warning mb-0">{{ number_format($order->rate, 2) }} <span class="fs-6 text-muted-custom">{{ $order->advertisement->country->currency_code }}/USDT</span></h4>
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
                        <h6 class="fw-bold text-warning mb-3">Seller's Payment Details:</h6>
                        @if($paymentAccounts->isEmpty())
                            <p class="text-muted-custom small">Contact seller in chat for payment details.</p>
                        @else
                            <div class="d-flex flex-column gap-2">
                                @foreach($paymentAccounts as $account)
                                    <div class="p-3 rounded border border-secondary" style="background-color: rgba(255,255,255,0.01);">
                                        <h6 class="fw-bold text-warning mb-1">{{ $account->paymentMethod->name }}</h6>
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
    
    <!-- Right Column: Chat Room -->
    <div class="col-lg-4">
        <div class="glass-card d-flex flex-column h-100" style="min-height: 500px;">
            <div class="d-flex justify-content-between align-items-center border-bottom border-secondary pb-2 mb-3">
                <h5 class="fw-bold mb-0 text-warning">Trade Chat</h5>
                <span id="chat-peer-status" class="badge bg-secondary">Offline</span>
            </div>
            
            <div id="chat-messages-container" class="chat-messages-container flex-grow-1 border border-secondary rounded p-3 mb-3" style="background-color: rgba(0,0,0,0.15); height: 350px; overflow-y: auto; display: flex; flex-direction: column; gap: 10px;">
                <div class="text-center text-muted-custom py-4 small">System: Escrow locked. Chat communication is active. Keep all deals here.</div>
            </div>

            <!-- Form -->
            <form id="chat-send-form" enctype="multipart/form-data">
                @csrf
                <div class="input-group">
                    <input type="text" id="chat-message-input" class="form-control" placeholder="Type a message..." style="background-color: rgba(0,0,0,0.2); border-color: rgba(255,255,255,0.1); color: #fff;">
                    <label class="btn btn-outline-secondary mb-0 d-flex align-items-center justify-content-center" style="cursor: pointer;">
                        📎
                        <input type="file" id="chat-attachment-input" name="attachment" style="display: none;" accept="image/*">
                    </label>
                    <button class="btn btn-primary px-4" type="submit" id="chat-send-btn">Send</button>
                </div>
                <div id="attachment-preview" class="small text-muted-custom mt-1" style="display: none;">
                    Selected attachment: <span id="attachment-filename"></span> 
                    <button type="button" class="btn btn-link btn-sm text-danger p-0 ms-2" id="clear-attachment-btn">Remove</button>
                </div>
            </form>
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
<script>
document.addEventListener('DOMContentLoaded', function () {
    // ----------------------------------------------------
    // 1. Countdown Timer Logic
    // ----------------------------------------------------
    const timerLabel = document.getElementById('countdown-timer');
    if (timerLabel) {
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
    }

    // ----------------------------------------------------
    // 2. Real-time P2P Trade Chat Room Logic
    // ----------------------------------------------------
    const orderId = '{{ $order->id }}';
    const currentUserId = '{{ Auth::id() }}';
    const fetchUrl = '{{ route("orders.chat.messages", $order->id) }}';
    const sendUrl = '{{ route("orders.chat.send", $order->id) }}';
    
    const messagesContainer = document.getElementById('chat-messages-container');
    const sendForm = document.getElementById('chat-send-form');
    const messageInput = document.getElementById('chat-message-input');
    const attachmentInput = document.getElementById('chat-attachment-input');
    const attachmentPreview = document.getElementById('attachment-preview');
    const attachmentFilename = document.getElementById('attachment-filename');
    const clearAttachmentBtn = document.getElementById('clear-attachment-btn');
    const sendBtn = document.getElementById('chat-send-btn');
    const peerStatusBadge = document.getElementById('chat-peer-status');

    let fallbackInterval = null;
    let isWebsocketConnected = false;

    // Scroll to bottom
    function scrollToBottom() {
        messagesContainer.scrollTop = messagesContainer.scrollHeight;
    }

    // File selection preview
    attachmentInput.addEventListener('change', function() {
        if (this.files && this.files.length > 0) {
            attachmentFilename.textContent = this.files[0].name;
            attachmentPreview.style.display = 'block';
        } else {
            attachmentPreview.style.display = 'none';
        }
    });

    clearAttachmentBtn.addEventListener('click', function() {
        attachmentInput.value = '';
        attachmentPreview.style.display = 'none';
    });

    // Render message bubble
    function renderMessage(msg) {
        if (document.getElementById(`msg-${msg.id}`)) {
            return;
        }

        const msgDiv = document.createElement('div');
        msgDiv.id = `msg-${msg.id}`;
        
        const isSelf = String(msg.sender_id) === String(currentUserId);
        
        msgDiv.style.display = 'flex';
        msgDiv.style.flexDirection = 'column';
        msgDiv.style.alignItems = isSelf ? 'flex-end' : 'flex-start';
        msgDiv.style.width = '100%';
        msgDiv.style.marginBottom = '12px';

        const bubble = document.createElement('div');
        bubble.className = 'p-2 rounded px-3 small';
        bubble.style.maxWidth = '85%';
        bubble.style.wordBreak = 'break-word';

        if (isSelf) {
            bubble.style.backgroundColor = 'rgba(59, 130, 246, 0.2)';
            bubble.style.border = '1px solid rgba(59, 130, 246, 0.4)';
            bubble.style.color = '#fff';
            bubble.style.borderRadius = '12px 12px 2px 12px';
        } else {
            bubble.style.backgroundColor = 'rgba(255, 255, 255, 0.05)';
            bubble.style.border = '1px solid rgba(255, 255, 255, 0.1)';
            bubble.style.color = '#e2e8f0';
            bubble.style.borderRadius = '12px 12px 12px 2px';
        }

        if (msg.message) {
            const textNode = document.createElement('p');
            textNode.className = 'mb-1';
            textNode.textContent = msg.message;
            bubble.appendChild(textNode);
        }

        if (msg.attachment_url) {
            const imgLink = document.createElement('a');
            imgLink.href = msg.attachment_url;
            imgLink.target = '_blank';
            
            const img = document.createElement('img');
            img.src = msg.attachment_url;
            img.style.maxWidth = '200px';
            img.style.maxHeight = '200px';
            img.style.borderRadius = '6px';
            img.style.marginTop = '6px';
            img.style.border = '1px solid rgba(255,255,255,0.1)';
            img.style.display = 'block';
            
            imgLink.appendChild(img);
            bubble.appendChild(imgLink);
        }

        const footer = document.createElement('span');
        footer.className = 'text-muted small mt-1';
        footer.style.fontSize = '0.7rem';
        
        const time = new Date(msg.created_at).toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
        footer.textContent = `${msg.sender_name} • ${time}`;

        msgDiv.appendChild(bubble);
        msgDiv.appendChild(footer);
        messagesContainer.appendChild(msgDiv);
        scrollToBottom();
    }

    // Fetch messages AJAX
    function fetchMessages() {
        fetch(fetchUrl)
            .then(res => res.json())
            .then(data => {
                // Clear default placeholder on first fetch if messages exist
                if (data.length > 0) {
                    const placeholder = messagesContainer.querySelector('.text-center');
                    if (placeholder) placeholder.remove();
                }
                data.forEach(msg => {
                    renderMessage(msg);
                });
            })
            .catch(err => console.error("Error fetching messages:", err));
    }

    // Initial load
    fetchMessages();

    // 3. WebSocket / Laravel Echo Configuration
    if (window.Echo) {
        window.Echo.private(`order.${orderId}`)
            .listen('.App.Events.MessageSent', (e) => {
                const placeholder = messagesContainer.querySelector('.text-center');
                if (placeholder) placeholder.remove();
                renderMessage(e);
            })
            .listen('MessageSent', (e) => {
                const placeholder = messagesContainer.querySelector('.text-center');
                if (placeholder) placeholder.remove();
                renderMessage(e);
            });
        
        isWebsocketConnected = true;
        peerStatusBadge.textContent = 'Active (Real-time)';
        peerStatusBadge.className = 'badge bg-success';
        console.log("WebSocket connected to trade channel.");
    }

    // 4. Polling Fallback (polls every 3 seconds if Echo is offline or not installed)
    if (!isWebsocketConnected) {
        console.warn("WebSocket not available. Falling back to HTTP polling.");
        peerStatusBadge.textContent = 'Active (Polling)';
        peerStatusBadge.className = 'badge bg-warning text-dark';
        fallbackInterval = setInterval(fetchMessages, 3000);
    }

    // Form Submit handling
    sendForm.addEventListener('submit', function (e) {
        e.preventDefault();

        const messageText = messageInput.value.trim();
        const hasFile = attachmentInput.files.length > 0;

        if (!messageText && !hasFile) {
            return;
        }

        sendBtn.disabled = true;
        sendBtn.textContent = 'Sending...';

        const formData = new FormData();
        formData.append('_token', '{{ csrf_token() }}');
        if (messageText) formData.append('message', messageText);
        if (hasFile) formData.append('attachment', attachmentInput.files[0]);

        fetch(sendUrl, {
            method: 'POST',
            body: formData,
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
        .then(res => res.json())
        .then(data => {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
            if (data.success) {
                const placeholder = messagesContainer.querySelector('.text-center');
                if (placeholder) placeholder.remove();
                
                messageInput.value = '';
                attachmentInput.value = '';
                attachmentPreview.style.display = 'none';
                renderMessage(data.message);
            } else {
                alert(data.error || 'Failed to send message.');
            }
        })
        .catch(err => {
            sendBtn.disabled = false;
            sendBtn.textContent = 'Send';
            console.error("Error sending message:", err);
            alert('Failed to send message.');
        });
    });
});
</script>
@endsection
