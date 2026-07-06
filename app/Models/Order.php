<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'advertisement_id',
        'buyer_id',
        'seller_id',
        'amount_usdt',
        'amount_fiat',
        'rate',
        'status',
        'payment_screenshot',
        'paid_at',
        'completed_at',
        'cancelled_at',
        'expiry_at',
    ];

    protected $casts = [
        'amount_usdt' => 'decimal:8',
        'amount_fiat' => 'decimal:2',
        'rate' => 'decimal:2',
        'paid_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'expiry_at' => 'datetime',
    ];

    /**
     * Get the advertisement that this order was created from.
     */
    public function advertisement(): BelongsTo
    {
        return $this->belongsTo(Advertisement::class);
    }

    /**
     * Get the buyer of the trade.
     */
    public function buyer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'buyer_id');
    }

    /**
     * Get the seller of the trade.
     */
    public function seller(): BelongsTo
    {
        return $this->belongsTo(User::class, 'seller_id');
    }

    /**
     * Get the escrow lock for this order.
     */
    public function escrow(): HasOne
    {
        return $this->hasOne(Escrow::class);
    }

    /**
     * Get the dispute associated with this order.
     */
    public function dispute(): HasOne
    {
        return $this->hasOne(Dispute::class);
    }

    /**
     * Get the chat messages inside the order.
     */
    public function messages(): HasMany
    {
        return $this->hasMany(OrderMessage::class);
    }
}
