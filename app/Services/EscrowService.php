<?php

namespace App\Services;

use App\Models\AuditLog;
use App\Models\Escrow;
use App\Models\Order;
use App\Models\User;
use App\Models\Wallet;
use Exception;
use Illuminate\Support\Facades\DB;

class EscrowService
{
    /**
     * Lock seller's USDT in escrow for a new trade.
     *
     * @throws Exception
     */
    public function lock(Order $order): Escrow
    {
        return DB::transaction(function () use ($order) {
            // Find and lock the seller's wallet to prevent concurrent modifications
            $sellerWallet = Wallet::where('user_id', $order->seller_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Calculate fees: we fetch platform config (default 1% fee if setting is empty)
            $feePercentageSetting = DB::table('settings')->where('key', 'fee_percentage')->first();
            $feePercent = $feePercentageSetting ? (float) $feePercentageSetting->value : 1.0;
            $feeUsdt = bcdiv(bcmul($order->amount_usdt, $feePercent, 8), '100', 8);

            $totalRequired = bcadd($order->amount_usdt, $feeUsdt, 8);

            // Check if seller has sufficient available balance
            if (bccomp($sellerWallet->available_balance, $totalRequired, 8) < 0) {
                throw new Exception("Insufficient balance to lock in escrow. Required: {$totalRequired} USDT.");
            }

            // Move funds from available to locked balance
            $sellerWallet->available_balance = bcsub($sellerWallet->available_balance, $totalRequired, 8);
            $sellerWallet->locked_balance = bcadd($sellerWallet->locked_balance, $totalRequired, 8);
            $sellerWallet->save();

            // Create Escrow log
            $escrow = Escrow::create([
                'order_id' => $order->id,
                'seller_wallet_id' => $sellerWallet->id,
                'amount_usdt' => $order->amount_usdt,
                'fee_usdt' => $feeUsdt,
                'status' => 'locked',
            ]);

            // Create Audit Log
            AuditLog::create([
                'user_id' => $order->seller_id,
                'action' => 'ESCROW_LOCK',
                'description' => "Locked {$order->amount_usdt} USDT (Fee: {$feeUsdt} USDT) in escrow for Order {$order->id}",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return $escrow;
        });
    }

    /**
     * Release USDT from escrow to the buyer's wallet.
     *
     * @throws Exception
     */
    public function release(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $escrow = Escrow::where('order_id', $order->id)
                ->where('status', 'locked')
                ->lockForUpdate()
                ->first();

            if (! $escrow) {
                throw new Exception("Active escrow lock not found for Order {$order->id}.");
            }

            // Lock seller's wallet
            $sellerWallet = Wallet::where('id', $escrow->seller_wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            // Lock buyer's wallet
            $buyerWallet = Wallet::where('user_id', $order->buyer_id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalLocked = bcadd($escrow->amount_usdt, $escrow->fee_usdt, 8);

            // Verify seller wallet has enough locked balance
            if (bccomp($sellerWallet->locked_balance, $totalLocked, 8) < 0) {
                throw new Exception('Inconsistent state: Seller wallet does not have locked balance to release.');
            }

            // Deduct from seller's locked balance
            $sellerWallet->locked_balance = bcsub($sellerWallet->locked_balance, $totalLocked, 8);
            $sellerWallet->save();

            // Add amount to buyer's available balance
            $buyerWallet->available_balance = bcadd($buyerWallet->available_balance, $escrow->amount_usdt, 8);
            $buyerWallet->save();

            // Add fee to admin wallet
            $adminUser = User::where('is_admin', true)->first();
            if ($adminUser) {
                $adminWallet = Wallet::where('user_id', $adminUser->id)->lockForUpdate()->first();
                if ($adminWallet) {
                    $adminWallet->available_balance = bcadd($adminWallet->available_balance, $escrow->fee_usdt, 8);
                    $adminWallet->save();
                }
            }

            // Update escrow status
            $escrow->status = 'released';
            $escrow->released_at = now();
            $escrow->save();

            // Create Audit Log
            AuditLog::create([
                'user_id' => $order->seller_id,
                'action' => 'ESCROW_RELEASE',
                'description' => "Released {$escrow->amount_usdt} USDT to Buyer {$order->buyer_id} from Escrow (Fee: {$escrow->fee_usdt} USDT transferred to admin)",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return true;
        });
    }

    /**
     * Refund locked escrow balance back to the seller.
     *
     * @throws Exception
     */
    public function refund(Order $order): bool
    {
        return DB::transaction(function () use ($order) {
            $escrow = Escrow::where('order_id', $order->id)
                ->where('status', 'locked')
                ->lockForUpdate()
                ->first();

            if (! $escrow) {
                throw new Exception("Active escrow lock not found for Order {$order->id}.");
            }

            // Lock seller's wallet
            $sellerWallet = Wallet::where('id', $escrow->seller_wallet_id)
                ->lockForUpdate()
                ->firstOrFail();

            $totalLocked = bcadd($escrow->amount_usdt, $escrow->fee_usdt, 8);

            // Verify seller wallet has enough locked balance
            if (bccomp($sellerWallet->locked_balance, $totalLocked, 8) < 0) {
                throw new Exception('Inconsistent state: Seller wallet does not have locked balance to refund.');
            }

            // Move funds from locked back to available balance
            $sellerWallet->locked_balance = bcsub($sellerWallet->locked_balance, $totalLocked, 8);
            $sellerWallet->available_balance = bcadd($sellerWallet->available_balance, $totalLocked, 8);
            $sellerWallet->save();

            // Update escrow status
            $escrow->status = 'refunded';
            $escrow->save();

            // Create Audit Log
            AuditLog::create([
                'user_id' => $order->seller_id,
                'action' => 'ESCROW_REFUND',
                'description' => "Refunded {$escrow->amount_usdt} USDT (Fee: {$escrow->fee_usdt} USDT) from Escrow to Seller {$order->seller_id}",
                'ip_address' => request()->ip() ?? '127.0.0.1',
            ]);

            return true;
        });
    }
}
