<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class Media extends Model
{
    use HasFactory;

    protected $table = 'media';

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'variants' => 'array',
            'approved' => 'boolean',
            'size' => 'integer',
        ];
    }

    /** Curated sets a file can belong to. */
    public const GALLERY = 'gallery';

    /** Only editorially approved media may be rendered on public pages. */
    public function scopeApproved(Builder $query): Builder
    {
        return $query->where('approved', true);
    }

    /**
     * Files an editor has placed in a named set.
     *
     * Approval decides whether a file may be shown; this decides where. The
     * public gallery is a curated selection, not everything that happens to
     * have been approved for use elsewhere on the site.
     */
    public function scopeInCollection(Builder $query, string $collection): Builder
    {
        return $query->where('collection', $collection);
    }

    public function isImage(): bool
    {
        return Str::startsWith($this->mime, 'image/');
    }
}
