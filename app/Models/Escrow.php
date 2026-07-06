<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escrow extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'seller_wallet_id',
        'amount_usdt',
        'fee_usdt',
        'status',
        'released_at',
    ];

    protected $casts = [
        'amount_usdt' => 'decimal:8',
        'fee_usdt' => 'decimal:8',
        'released_at' => 'datetime',
    ];

    /**
     * Get the order associated with this escrow lock.
     */
    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    /**
     * Get the seller's wallet.
     */
    public function sellerWallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'seller_wallet_id');
    }
}
