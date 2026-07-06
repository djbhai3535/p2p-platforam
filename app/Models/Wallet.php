<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'available_balance',
        'locked_balance',
    ];

    protected $casts = [
        'available_balance' => 'decimal:8',
        'locked_balance' => 'decimal:8',
    ];

    /**
     * Get the total balance (available + locked).
     */
    public function getTotalBalanceAttribute(): string
    {
        return bcadd($this->available_balance, $this->locked_balance, 8);
    }

    /**
     * Get the user who owns the wallet.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the transactions for the wallet.
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }
}
