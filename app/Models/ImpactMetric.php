<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * A reported result. A metric only reaches the public site once an administrator
 * has recorded where the figure came from and marked it published.
 */
class ImpactMetric extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    protected function casts(): array
    {
        return [
            'value' => 'decimal:2',
            'year' => 'integer',
            'published' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::saving(function (self $metric): void {
            if (blank($metric->source)) {
                $metric->published = false;
            }
        });
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('published', true)->whereNotNull('value');
    }

    /** Whole numbers read better as counters; fractional values keep one decimal. */
    public function getDisplayValueAttribute(): string
    {
        $value = (float) $this->value;

        return fmod($value, 1.0) === 0.0
            ? number_format($value)
            : number_format($value, 1);
    }
}
