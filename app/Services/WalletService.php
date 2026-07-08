<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Transaction;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;

class WalletService
{
    /**
     * Create a pending deposit transaction and call NOWPayments API.
     *
     * @param User $user
     * @param float $amount
     * @return array
     * @throws Exception
     */
    public function createDeposit(User $user, float $amount): array
    {
        $apiKey = SettingsService::get('nowpayments_api_key');
        $sandbox = SettingsService::get('nowpayments_sandbox', 'true') === 'true';

        // Unique transaction record
        $transaction = Transaction::create([
            'wallet_id' => $user->wallet->id,
            'type' => 'deposit',
            'amount' => $amount,
            'status' => 'pending',
            'payment_provider' => 'nowpayments',
        ]);

        if (empty($apiKey)) {
            // For local development sandbox/simulation if no key is configured
            $mockAddress = 'T' . \Illuminate\Support\Str::random(33);
            $transaction->update([
                'address' => $mockAddress,
                'payment_id' => 'mock-' . \Illuminate\Support\Str::random(10),
            ]);

            return [
                'payment_id' => $transaction->payment_id,
                'pay_address' => $mockAddress,
                'pay_amount' => $amount,
                'pay_currency' => 'usdttrc20',
                'simulated' => true,
            ];
        }

        // Call NOWPayments API
        $url = $sandbox 
            ? 'https://api-sandbox.nowpayments.io/v1/payment'
            : 'https://api.nowpayments.io/v1/payment';

        $response = Http::withHeaders([
            'x-api-key' => $apiKey,
            'Content-Type' => 'application/json',
        ])->post($url, [
            'price_amount' => $amount,
            'price_currency' => 'usd',
            'pay_amount' => $amount,
            'pay_currency' => 'usdttrc20', // USDT on TRC20 network (standard low-fee option)
            'ipn_callback_url' => route('api.nowpayments.webhook'),
            'order_id' => $transaction->id,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            
            $transaction->update([
                'address' => $data['pay_address'],
                'payment_id' => $data['payment_id'],
                'meta' => $data,
            ]);

            return [
                'payment_id' => $data['payment_id'],
                'pay_address' => $data['pay_address'],
                'pay_amount' => $data['pay_amount'],
                'pay_currency' => $data['pay_currency'],
                'simulated' => false,
            ];
        }

        $transaction->update(['status' => 'rejected']);
        throw new Exception("NOWPayments API Error: " . $response->body());
    }

    /**
     * Process deposit webhook callback.
     *
     * @param array $payload
     * @param string $signatureHeader
     * @return bool
     * @throws Exception
     */
    public function processDepositWebhook(array $payload, string $signatureHeader): bool
    {
        $ipnSecret = SettingsService::get('nowpayments_ipn_secret');

        // Signature validation if secret is set
        if (!empty($ipnSecret)) {
            // Sort keys alphabetically
            ksort($payload);
            $sortedPayloadJson = json_encode($payload, JSON_UNESCAPED_SLASHES);
            $calculatedSignature = hash_hmac('sha512', $sortedPayloadJson, $ipnSecret);

            if ($calculatedSignature !== $signatureHeader) {
                throw new Exception("NOWPayments webhook signature verification failed.");
            }
        }

        $paymentStatus = $payload['payment_status'] ?? null;
        $orderId = $payload['order_id'] ?? null;
        $paymentId = $payload['payment_id'] ?? null;
        $payAmount = $payload['pay_amount'] ?? 0;

        if (!$orderId) {
            return false;
        }

        return DB::transaction(function () use ($orderId, $paymentStatus, $paymentId, $payAmount) {
            $transaction = Transaction::where('id', $orderId)->lockForUpdate()->firstOrFail();

            // Only process pending transactions
            if ($transaction->status !== 'pending') {
                return true;
            }

            if (in_array($paymentStatus, ['finished', 'confirmed'])) {
                // Update transaction status
                $transaction->status = 'completed';
                $transaction->payment_id = $paymentId;
                $transaction->save();

                // Lock and credit wallet
                $wallet = Wallet::where('id', $transaction->wallet_id)->lockForUpdate()->firstOrFail();
                $wallet->available_balance = bcadd($wallet->available_balance, $payAmount, 8);
                $wallet->save();

                // Audit Log
                AuditLog::create([
                    'user_id' => $wallet->user_id,
                    'action' => 'WALLET_DEPOSIT',
                    'description' => "Received deposit of {$payAmount} USDT via NOWPayments (Payment ID: {$paymentId})",
                    'ip_address' => request()->ip() ?? '127.0.0.1',
                ]);
            } elseif (in_array($paymentStatus, ['failed', 'expired'])) {
                $transaction->status = 'rejected';
                $transaction->save();
            }

            return true;
        });
    }

    /**
     * Create a withdrawal request.
     *
     * @param User $user
     * @param float $amount
     * @param string $address
     * @return Transaction
     * @throws Exception
     */
    public function requestWithdrawal(User $user, float $amount, string $address): Transaction
    {
        return DB::transaction(function () use ($user, $amount, $address) {
            $wallet = Wallet::where('user_id', $user->id)->lockForUpdate()->firstOrFail();

            // Calculate fees: dynamic settings (e.g. flat withdrawal fee of 2 USDT)
            $withdrawalFeeSetting = DB::table('settings')->where('key', 'withdrawal_fee')->first();
            $fee = $withdrawalFeeSetting ? (float)$withdrawalFeeSetting->value : 2.0;

            $totalRequired = bcadd($amount, $fee, 8);

            if (bccomp($wallet->available_balance, $totalRequired, 8) < 0) {
                throw new Exception("Insufficient balance to request withdrawal. Required: {$totalRequired} USDT.");
            }

            // Deduct from available balance immediately to prevent double spending
            $wallet->available_balance = bcsub($wallet->available_balance, $totalRequired, 8);
            $wallet->save();

            // Create Transaction record in pending state
            $transaction = Transaction::create([
                'wallet_id' => $wallet->id,
                'type' => 'withdrawal',
                'amount' => $amount,
                'fee' => $fee,
                'status' => 'pending',
                'address' => $address,
                'payment_provider' => 'manual',
            ]);

            // Audit Trail
            AuditLog::create([
                'user_id' => $user->id,
                'action' => 'WALLET_WITHDRAWAL_REQUEST',
                'description' => "Requested withdrawal of {$amount} USDT to address {$address} (Fee: {$fee} USDT)",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $transaction;
        });
    }

    /**
     * Approve a withdrawal.
     *
     * @param Transaction $transaction
     * @return bool
     * @throws Exception
     */
    public function approveWithdrawal(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $transaction = Transaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->status !== 'pending' || $transaction->type !== 'withdrawal') {
                throw new Exception("Transaction is not a pending withdrawal.");
            }

            $transaction->status = 'completed';
            $transaction->save();

            // Audit Trail
            AuditLog::create([
                'user_id' => $transaction->wallet->user_id,
                'action' => 'WALLET_WITHDRAWAL_APPROVE',
                'description' => "Approved withdrawal of {$transaction->amount} USDT to {$transaction->address}.",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return true;
        });
    }

    /**
     * Reject and refund a withdrawal.
     *
     * @param Transaction $transaction
     * @return bool
     * @throws Exception
     */
    public function rejectWithdrawal(Transaction $transaction): bool
    {
        return DB::transaction(function () use ($transaction) {
            $transaction = Transaction::where('id', $transaction->id)->lockForUpdate()->firstOrFail();

            if ($transaction->status !== 'pending' || $transaction->type !== 'withdrawal') {
                throw new Exception("Transaction is not a pending withdrawal.");
            }

            $transaction->status = 'rejected';
            $transaction->save();

            // Lock and refund wallet balance
            $wallet = Wallet::where('id', $transaction->wallet_id)->lockForUpdate()->firstOrFail();
            $refundAmount = bcadd($transaction->amount, $transaction->fee, 8);
            $wallet->available_balance = bcadd($wallet->available_balance, $refundAmount, 8);
            $wallet->save();

            // Audit Trail
            AuditLog::create([
                'user_id' => $wallet->user_id,
                'action' => 'WALLET_WITHDRAWAL_REJECT',
                'description' => "Rejected withdrawal of {$transaction->amount} USDT. Refunded {$refundAmount} USDT to wallet.",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return true;
        });
    }
}
