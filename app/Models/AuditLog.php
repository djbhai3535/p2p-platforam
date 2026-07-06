<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AuditLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'action',
        'description',
        'ip_address',
    ];

    /**
     * Get the user linked to this audit action, if any.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
