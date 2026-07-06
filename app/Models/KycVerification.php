<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class KycVerification extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'full_name',
        'dob',
        'country_id',
        'document_type',
        'document_number',
        'front_image_path',
        'back_image_path',
        'selfie_image_path',
        'status',
        'rejection_reason',
        'reviewed_by',
        'reviewed_at',
    ];

    protected $casts = [
        'dob' => 'date',
        'reviewed_at' => 'datetime',
    ];

    /**
     * Get the user who owns the KYC.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the country of the document.
     */
    public function country(): BelongsTo
    {
        return $this->belongsTo(Country::class);
    }

    /**
     * Get the admin reviewer.
     */
    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
