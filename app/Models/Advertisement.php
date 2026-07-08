<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Advertisement extends Model
{
    use HasFactory, HasUuids;

    protected $fillable = [
        'user_id',
        'country_id',
        'type',
        'price_type',
        'rate',
        'amount',
        'min_limit',
        'max_limit',
        'payment_method_ids',
        'terms',
        'status',
    ];

    protected $casts = [
        'rate' => 'decimal:2',
        'amount' => 'decimal:8',
        'min_limit' => 'decimal:2',
        'max_limit' => 'decimal:2',
        'payment_method_ids' => 'array',
    ];

    /**
     * Get the user who created this advertisement.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country where this ad is targetting.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get orders placed against this advertisement.
     */
    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    /**
     * Get the resolved payment method models collection.
     */
    public function getPaymentMethodsAttribute()
    {
        return PaymentMethod::whereIn('id', $this->payment_method_ids ?? [])->get();
    }
}
