<?php

namespace Database\Seeders;

use App\Models\Redirect;
use Illuminate\Database\Seeder;

/**
 * Permanent redirects preserving inbound links to the previous website.
 *
 * Legacy paths taken from backup/application/config/routes.php. Paths whose slug
 * is unchanged (`/about-us`, `/contact-us`, `/gallery`) need no entry - and must
 * not have one, because this map is consulted before the router and would
 * redirect away from a page that now exists.
 */
class RedirectSeeder extends Seeder
{
    public function run(): void
    {
        $redirects = [
            '/support-us' => '/get-involved',
            '/our-team' => '/leadership',
            '/partnership' => '/partner-with-us',
            '/testimonies' => '/stories',
            '/form' => '/membership',
            '/sendcontact' => '/contact-us',

            // The demonstration record this replaced was publicly reachable.
            '/projects/demo-rivers-youth-co-operative-formation' => '/projects/rivers-youth-co-operative-formation',
        ];

        foreach ($redirects as $from => $to) {
            Redirect::firstOrCreate(['from_path' => $from], ['to_path' => $to]);
        }
    }
}
