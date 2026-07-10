<?php

namespace App\Http\Controllers;

use App\Models\Transaction;
use App\Services\WalletService;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WalletController extends Controller
{
    protected $walletService;

    public function __construct(WalletService $walletService)
    {
        $this->walletService = $walletService;
    }

    /**
     * Display the wallet page with balances and transactions history.
     */
    public function index()
    {
        $user = Auth::user();
        $user->wallet->refresh();

        $transactions = Transaction::where('wallet_id', $user->wallet->id)
            ->latest()
            ->paginate(10);

        return view('wallet.index', compact('user', 'transactions'));
    }

    /**
     * Handle USDT deposit request.
     */
    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1|max:1000000',
        ]);

        try {
            $user = Auth::user();
            $result = $this->walletService->createDeposit($user, (float) $request->amount);

            return back()->with('deposit_result', $result);
        } catch (Exception $e) {
            return back()->withErrors(['deposit' => $e->getMessage()]);
        }
    }

    /**
     * Handle USDT withdrawal request.
     */
    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:5|max:1000000',
            'address' => 'required|string|regex:/^T[A-Za-z0-9]{33}$/', // TRON USDT (TRC-20) address format validation
        ], [
            'address.regex' => 'The withdrawal address must be a valid TRC-20 USDT address starting with T.',
        ]);

        try {
            $user = Auth::user();
            $this->walletService->requestWithdrawal($user, (float) $request->amount, $request->address);

            return redirect()->route('wallet')->with('status', 'Withdrawal request submitted successfully! Awaiting administrator approval.');
        } catch (Exception $e) {
            return back()->withErrors(['withdraw' => $e->getMessage()]);
        }
    }
}
