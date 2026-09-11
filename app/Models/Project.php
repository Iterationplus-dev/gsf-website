<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Project extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public const STATUSES = [
        'planned' => 'Planned',
        'active' => 'Active',
        'completed' => 'Completed',
        'suspended' => 'Suspended',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'beneficiary_count' => 'integer',
            'public_budget_minor' => 'integer',
            'sdgs' => 'array',
            'partners' => 'array',
            'donors' => 'array',
            'gallery' => 'array',
            'documents' => 'array',
            'related_posts' => 'array',
            'related_testimonials' => 'array',
        ];
    }

    public function content(): BelongsTo
    {
        return $this->belongsTo(Content::class);
    }

    public function program(): BelongsTo
    {
        return $this->belongsTo(Content::class, 'program_id');
    }

    public function manager(): BelongsTo
    {
        return $this->belongsTo(User::class, 'manager_id');
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUSES[$this->status] ?? 'Planned';
    }

    /** Where a project sits in place and time, formatted for display. */
    public function getPeriodAttribute(): ?string
    {
        if (! $this->start_date) {
            return null;
        }

        return $this->start_date->format('M Y').' – '.($this->end_date?->format('M Y') ?? 'present');
    }
}
