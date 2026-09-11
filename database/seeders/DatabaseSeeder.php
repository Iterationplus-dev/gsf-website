<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

/**
 * No administrator account is seeded: accounts are created with
 * `php artisan gsf:create-administrator`, so no installation ships with a
 * known password.
 */
class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $this->call([
            SettingsSeeder::class,
            ContentSeeder::class,
            ImpactMetricSeeder::class,
            PostSeeder::class,
            GallerySeeder::class,
            CampaignSeeder::class,
            ProjectSeeder::class,
            RedirectSeeder::class,
        ]);
    }
}
