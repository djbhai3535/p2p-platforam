<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeviceLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'ip_address',
        'user_agent',
        'location',
    ];

    /**
     * Get the user linked to this device login log.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
