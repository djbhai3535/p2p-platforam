<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\AuditLog;
use App\Models\Dispute;
use App\Models\Order;
use App\Services\EscrowService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class OrderController extends Controller
{
    protected EscrowService $escrowService;

    public function __construct(EscrowService $escrowService)
    {
        $this->escrowService = $escrowService;
    }

    /**
     * Display order creation placement page.
     */
    public function create(Advertisement $advertisement, Request $request): \Illuminate\Contracts\View\View|\Illuminate\Http\RedirectResponse
    {
        // Block self trading
        if ($advertisement->user_id === $request->user()->id) {
            return redirect()->route('marketplace')->withErrors(['message' => 'You cannot trade with your own advertisement.']);
        }

        return view('p2p.place-order', compact('advertisement'));
    }

    /**
     * Store new trade order and lock escrow.
     */
    public function store(Advertisement $advertisement, Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // Enforce KYC
        if (!$user->isKycVerified()) {
            return redirect()->route('profile.kyc')->withErrors(['message' => 'KYC verification is required to open a trade.']);
        }

        // Block self trading
        if ($advertisement->user_id === $user->id) {
            return redirect()->route('marketplace')->withErrors(['message' => 'You cannot trade with your own advertisement.']);
        }

        $request->validate([
            'amount_usdt' => ['required', 'numeric', 'min:1'],
        ]);

        $amountUsdt = (float) $request->amount_usdt;
        $rate = (float) $advertisement->rate;
        $amountFiat = bcmul($amountUsdt, $rate, 2);

        // Check if amount is within ad limit boundaries
        if (bccomp($amountFiat, $advertisement->min_limit, 2) < 0 || bccomp($amountFiat, $advertisement->max_limit, 2) > 0) {
            return back()->withErrors(['amount_usdt' => "The fiat value ({$amountFiat} {$advertisement->country->currency_code}) must be between the advertisement limits."])->withInput();
        }

        // Resolve Buyer & Seller roles
        if ($advertisement->type === 'buy') {
            // Ad creator wants to BUY, so user placing trade is the SELLER
            $sellerId = $user->id;
            $buyerId = $advertisement->user_id;
        } else {
            // Ad creator wants to SELL, so user placing trade is the BUYER
            $sellerId = $advertisement->user_id;
            $buyerId = $user->id;
        }

        try {
            $order = DB::transaction(function () use ($advertisement, $sellerId, $buyerId, $amountUsdt, $amountFiat, $rate) {
                // Create Order record
                $order = Order::create([
                    'advertisement_id' => $advertisement->id,
                    'buyer_id' => $buyerId,
                    'seller_id' => $sellerId,
                    'amount_usdt' => $amountUsdt,
                    'amount_fiat' => $amountFiat,
                    'rate' => $rate,
                    'status' => 'pending',
                    'expiry_at' => now()->addMinutes(15), // 15 mins payment window
                ]);

                // Call Escrow Engine to lock funds
                $this->escrowService->lock($order);

                return $order;
            });

            return redirect()->route('orders.show', $order->id)->with('status', 'Trade order opened and escrow locked.');
        } catch (Exception $e) {
            return back()->withErrors(['amount_usdt' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Show trade order page (Escrow trade room).
     */
    public function show(Order $order, Request $request): \Illuminate\Contracts\View\View
    {
        $user = $request->user();

        // Enforce membership authentication
        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            abort(403, 'Unauthorized trade access.');
        }

        return view('p2p.trade', compact('order'));
    }

    /**
     * Buyer marks trade order as paid.
     */
    public function markAsPaid(Order $order, Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($order->buyer_id !== $user->id) {
            abort(403);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['message' => 'This trade cannot be marked as paid.']);
        }

        $request->validate([
            'payment_screenshot' => ['required', 'image', 'max:5120'], // max 5MB
        ]);

        $path = $request->file('payment_screenshot')->store('payment_screenshots', 'public');

        $order->update([
            'status' => 'paid',
            'payment_screenshot' => $path,
            'paid_at' => now(),
        ]);

        AuditLog::create([
            'user_id' => $user->id,
            'action' => 'ORDER_MARK_PAID',
            'description' => "Buyer marked Order ID {$order->id} as paid. Screenshot uploaded.",
            'ip_address' => $request->ip() ?? '127.0.0.1',
        ]);

        return redirect()->route('orders.show', $order->id)->with('status', 'Trade marked as paid. Awaiting seller release.');
    }

    /**
     * Seller releases USDT from escrow.
     */
    public function release(Order $order, Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($order->seller_id !== $user->id) {
            abort(403);
        }

        if (!in_array($order->status, ['pending', 'paid', 'disputed'])) {
            return back()->withErrors(['message' => 'This trade cannot be released.']);
        }

        try {
            $this->escrowService->release($order);

            $order->update([
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'ORDER_RELEASE',
                'description' => "Seller released escrow USDT for Order ID {$order->id} to buyer.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return redirect()->route('orders.show', $order->id)->with('status', 'USDT released successfully! Trade complete.');
        } catch (Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Buyer cancels trade or seller cancels if expired.
     */
    public function cancel(Order $order, Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            abort(403);
        }

        // Sellers can only cancel if payment countdown has expired
        if ($order->seller_id === $user->id && now()->lessThan($order->expiry_at)) {
            return back()->withErrors(['message' => 'You cannot cancel yet. Buyer still has time to pay.']);
        }

        if ($order->status !== 'pending') {
            return back()->withErrors(['message' => 'Only pending trades can be cancelled.']);
        }

        try {
            $this->escrowService->refund($order);

            $order->update([
                'status' => 'cancelled',
                'cancelled_at' => now(),
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'ORDER_CANCEL',
                'description' => "Order ID {$order->id} cancelled. Escrow refunded to seller.",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);

            return redirect()->route('orders.show', $order->id)->with('status', 'Trade order cancelled.');
        } catch (Exception $e) {
            return back()->withErrors(['message' => $e->getMessage()]);
        }
    }

    /**
     * Open a dispute on a trade order.
     */
    public function openDispute(Order $order, Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        if ($order->buyer_id !== $user->id && $order->seller_id !== $user->id) {
            abort(403);
        }

        if (!in_array($order->status, ['paid', 'pending'])) {
            return back()->withErrors(['message' => 'You can only dispute paid or pending orders.']);
        }

        $request->validate([
            'reason' => ['required', 'string', 'min:10', 'max:1000'],
        ]);

        DB::transaction(function () use ($order, $user, $request) {
            $order->update(['status' => 'disputed']);

            Dispute::create([
                'order_id' => $order->id,
                'disputed_by' => $user->id,
                'reason' => $request->reason,
            ]);

            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'ORDER_DISPUTE',
                'description' => "Dispute opened by {$user->name} on Order ID {$order->id}. Reason: {$request->reason}",
                'ip_address' => $request->ip() ?? '127.0.0.1',
            ]);
        });

        return redirect()->route('orders.show', $order->id)->with('status', 'Trade dispute opened. Admin will review soon.');
    }

    /**
     * List user trades (buyer or seller).
     */
    public function myTrades(Request $request): \Illuminate\Contracts\View\View
    {
        $user = $request->user();
        $trades = Order::with(['buyer', 'seller', 'advertisement'])
            ->where(function ($q) use ($user) {
                $q->where('buyer_id', $user->id)
                  ->orWhere('seller_id', $user->id);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('p2p.my-trades', compact('trades'));
    }
}
