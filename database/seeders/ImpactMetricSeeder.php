<?php

namespace Database\Seeders;

use App\Models\ImpactMetric;
use Illuminate\Database\Seeder;

/**
 * Reported results, each carrying the source it came from.
 *
 * {@see ImpactMetric} refuses to publish a figure with no source, so anything
 * seeded here has to name where it was stated. Nothing is estimated: the
 * placement figures are the organisation's own published claims, and the counts
 * of programmes and societies are what this website actually lists.
 */
class ImpactMetricSeeder extends Seeder
{
    private const LEGACY_HOME = 'Previous website home page: "Setup in August 2015, Global Support Foundation has placed over 8,869 volunteers in 14 programs around the world."';

    public function run(): void
    {
        $metrics = [
            [
                'title' => 'Volunteers placed',
                'value' => 8869,
                'unit' => null,
                'geography' => 'Worldwide',
                'source' => self::LEGACY_HOME,
                'position' => 1,
            ],
            [
                'title' => 'Programmes worldwide',
                'value' => 14,
                'unit' => null,
                'geography' => 'Worldwide',
                'source' => self::LEGACY_HOME,
                'position' => 2,
            ],
            [
                'title' => 'Programme areas',
                'value' => 6,
                'unit' => null,
                'geography' => 'Nigeria',
                'source' => 'The programme areas published on this website.',
                'position' => 3,
            ],
            [
                'title' => 'Co-operative societies supported',
                'value' => 5,
                'unit' => null,
                'geography' => 'Ireland, 2008-2010',
                'source' => 'Previous website testimonies page, which profiles five co-operative societies formed with the founder\'s support.',
                'position' => 4,
            ],
        ];

        foreach ($metrics as $metric) {
            ImpactMetric::firstOrCreate(
                ['title' => $metric['title']],
                $metric + ['published' => true],
            );
        }
    }
}
