<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Append-only log of provider events. The unique `event_key` is what makes
 * settlement idempotent across webhook retries and callback replays.
 */
class PaymentTransaction extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function donation(): BelongsTo
    {
        return $this->belongsTo(Donation::class);
    }
}
