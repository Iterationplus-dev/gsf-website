<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * An application from a co-operative society seeking affiliation.
 *
 * Held apart from {@see Enquiry} because an application has an eligibility
 * threshold, a review outcome and a named society behind it, none of which a
 * general enquiry carries.
 */
class MembershipApplication extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    /**
     * The sectors the foundation organises co-operatives around, matching the
     * programme areas the public site already publishes.
     *
     * @var array<string, string>
     */
    public const TYPES = [
        'agriculture' => 'Agriculture & livelihoods',
        'industrial' => 'Industrial & processing',
        'mining' => 'Mining & extraction',
        'professional' => 'Professional services',
        'trading' => 'Trading & retail',
        'technology' => 'Technology & digital services',
        'multipurpose' => 'Multipurpose',
    ];

    /** @var array<string, string> */
    public const STATUSES = [
        'new' => 'New',
        'reviewing' => 'Under review',
        'approved' => 'Approved',
        'declined' => 'Declined',
    ];

    /** A co-operative society may not be registered with fewer members than this. */
    public const MINIMUM_MEMBERS = 7;

    protected function casts(): array
    {
        return [
            'consented_at' => 'datetime',
            'member_count' => 'integer',
        ];
    }

    public function scopeAwaitingReview(Builder $query): Builder
    {
        return $query->whereIn('status', ['new', 'reviewing']);
    }
}
