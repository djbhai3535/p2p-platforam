<?php

namespace App\Http\Controllers;

use App\Models\Advertisement;
use App\Models\Country;
use App\Models\PaymentMethod;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class P2PController extends Controller
{
    /**
     * Display the P2P Marketplace ads list with filters.
     */
    public function marketplace(Request $request): \Illuminate\Contracts\View\View
    {
        $countries = Country::where('is_active', true)->get();
        $userCountry = $request->user() ? $request->user()->country : Country::where('iso_code', 'PK')->first();
        
        $selectedCountryId = $request->input('country_id', $userCountry->id ?? null);
        $type = $request->input('type', 'buy'); // buy or sell ads
        $amount = $request->input('amount');
        $paymentMethodId = $request->input('payment_method_id');

        // Dynamic Payment Methods list based on selected Country
        $paymentMethods = PaymentMethod::where('country_id', $selectedCountryId)
            ->where('is_active', true)
            ->get();

        $query = Advertisement::with(['user', 'country'])
            ->where('country_id', $selectedCountryId)
            ->where('type', $type)
            ->where('status', 'active');

        // Filter by dynamic payment methods
        if ($paymentMethodId) {
            $query->whereJsonContains('payment_method_ids', $paymentMethodId);
        }

        // Filter by transaction size limits
        if ($amount) {
            $query->where('min_limit', '<=', $amount)
                  ->where('max_limit', '>=', $amount);
        }

        $ads = $query->orderBy('rate', $type === 'buy' ? 'asc' : 'desc')->paginate(10);

        return view('p2p.marketplace', compact(
            'countries',
            'paymentMethods',
            'ads',
            'selectedCountryId',
            'type',
            'amount',
            'paymentMethodId'
        ));
    }

    /**
     * Display current user's advertisements.
     */
    public function myAdvertisements(Request $request): \Illuminate\Contracts\View\View
    {
        $ads = $request->user()->advertisements()->with(['country', 'paymentMethods'])->get();
        return view('p2p.my-ads', compact('ads'));
    }

    /**
     * Show form to create a new P2P advertisement.
     */
    public function createAdvertisement(Request $request): \Illuminate\Contracts\View\View
    {
        $countries = Country::where('is_active', true)->get();
        // User payment options linked
        $linkedMethods = $request->user()->userPaymentMethods()->with('paymentMethod')->get();

        return view('p2p.create-ad', compact('countries', 'linkedMethods'));
    }

    /**
     * Store new P2P advertisement.
     */
    public function storeAdvertisement(Request $request): \Illuminate\Http\RedirectResponse
    {
        $user = $request->user();

        // KYC check middleware is also active, but double safeguard
        if (!$user->isKycVerified()) {
            return redirect()->route('profile.kyc')->withErrors(['message' => 'KYC verification is required.']);
        }

        $request->validate([
            'country_id' => ['required', 'exists:countries,id'],
            'type' => ['required', 'string', 'in:buy,sell'],
            'price_type' => ['required', 'string', 'in:fixed,margin'],
            'rate' => ['required', 'numeric', 'min:0.01'],
            'amount' => ['required', 'numeric', 'min:1'],
            'min_limit' => ['required', 'numeric', 'min:1'],
            'max_limit' => ['required', 'numeric', 'gte:min_limit'],
            'payment_methods' => ['required', 'array', 'min:1'],
            'payment_methods.*' => ['exists:payment_methods,id'],
            'terms' => ['nullable', 'string', 'max:2000'],
        ]);

        // If it's a SELL ad, the seller MUST have enough available USDT in their wallet to back the trade
        if ($request->type === 'sell') {
            $user->wallet->refresh();
            if (bccomp($user->wallet->available_balance, $request->amount, 8) < 0) {
                return back()->withErrors(['amount' => "Insufficient wallet balance to place this Sell Ad. Available: {$user->wallet->available_balance} USDT."])->withInput();
            }
        }

        DB::transaction(function () use ($request, $user) {
            Advertisement::create([
                'user_id' => $user->id,
                'country_id' => $request->country_id,
                'type' => $request->type,
                'price_type' => $request->price_type,
                'rate' => $request->rate,
                'amount' => $request->amount,
                'min_limit' => $request->min_limit,
                'max_limit' => $request->max_limit,
                'payment_method_ids' => $request->payment_methods,
                'terms' => $request->terms,
                'status' => 'active',
            ]);
        });

        return redirect()->route('advertisements.my')->with('status', 'P2P Advertisement created successfully.');
    }

    /**
     * Pause / Activate advertisement.
     */
    public function toggleAdvertisement(Advertisement $advertisement, Request $request): \Illuminate\Http\RedirectResponse
    {
        if ($advertisement->user_id !== $request->user()->id) {
            abort(403);
        }

        $newStatus = $advertisement->status === 'active' ? 'paused' : 'active';
        $advertisement->update(['status' => $newStatus]);

        return redirect()->route('advertisements.my')->with('status', "Advertisement marked as {$newStatus}.");
    }
}
