<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Str;

/**
 * The shared editorial record behind every published entity on the site.
 *
 * Keeping publishing, provenance, SEO and review on one table means a draft award
 * and a draft project are excluded from public queries by exactly the same rule.
 */
class Content extends Model
{
    use HasFactory, SoftDeletes;

    protected $guarded = ['id'];

    /** @var array<string, string> */
    public const TYPES = [
        'page' => 'Pages',
        'program' => 'Programs',
        'project' => 'Projects',
        'post' => 'News',
        'story' => 'Success stories',
        'award' => 'Awards',
        'person' => 'Leadership',
        'board' => 'Board',
        'partner' => 'Partners',
        'publication' => 'Publications',
        'policy' => 'Policies',
    ];

    /**
     * Public URL prefix for each type. Pages sit at the site root.
     *
     * @var array<string, string>
     */
    private const PREFIXES = [
        'page' => '',
        'program' => 'programs/',
        'project' => 'projects/',
        'post' => 'news/',
        'story' => 'stories/',
        'award' => 'awards/',
        'person' => 'leadership/',
        'board' => 'governance/',
        'partner' => 'partners/',
        'publication' => 'resources/',
        'policy' => 'policies/',
    ];

    protected function casts(): array
    {
        return [
            'details' => 'array',
            'tags' => 'array',
            'published_at' => 'datetime',
            'featured' => 'boolean',
            'is_demo' => 'boolean',
            'position' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $content): void {
            if (blank($content->slug)) {
                $content->slug = Str::slug($content->title).'-'.Str::lower(Str::random(5));
            }

            // Unverified sample content can never reach the public site by accident.
            if ($content->is_demo) {
                $content->status = 'draft';
            }

            // An editor without publishing rights may prepare a draft but not release it.
            if (auth()->check() && ! auth()->user()->hasPermission('content.publish')
                && ($content->isDirty('status') || $content->isDirty('published_at'))) {
                $content->status = 'draft';
                $content->published_at = null;
            }
        });
    }

    /**
     * Public visibility: released, not demo content, and past its publication time.
     * Scheduled records become visible on their own without a deployment.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query
            ->whereIn('status', ['published', 'scheduled'])
            ->where('is_demo', false)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    public function scopeOfType(Builder $query, string $type): Builder
    {
        return $query->where('type', $type);
    }

    public function project(): HasOne
    {
        return $this->hasOne(Project::class);
    }

    public function image(): BelongsTo
    {
        return $this->belongsTo(Media::class, 'image_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function getUrlAttribute(): string
    {
        return url('/'.(self::PREFIXES[$this->type] ?? '').$this->slug);
    }

    /**
     * A specific value from the free-form `details` payload, which holds the fields
     * that only some content types use (award issuer, publication year, role title).
     */
    public function detail(string $key, mixed $default = null): mixed
    {
        return data_get($this->details, $key, $default);
    }
}
