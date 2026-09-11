<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Donation extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /** Donations are addressed by their unpredictable reference, never by id. */
    public function getRouteKeyName(): string
    {
        return 'reference';
    }

    protected function casts(): array
    {
        return [
            'anonymous' => 'boolean',
            'amount_minor' => 'integer',
            'paid_at' => 'datetime',
            'consented_at' => 'datetime',
            'receipt_sent_at' => 'datetime',
        ];
    }

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(Campaign::class);
    }

    public function transactions(): HasMany
    {
        return $this->hasMany(PaymentTransaction::class);
    }

    public function getAmountAttribute(): string
    {
        return number_format($this->amount_minor / 100, 2);
    }

    public function getDisplayNameAttribute(): string
    {
        return $this->anonymous ? 'Anonymous supporter' : $this->name;
    }
}
