<?php

namespace Database\Seeders;

use App\Models\Setting;
use Illuminate\Database\Seeder;

/**
 * Organizational details administrators edit without a deployment.
 *
 * Values transcribed from the previous website's contact page are seeded as-is.
 * Anything the backup did not evidence — registration numbers, organizational
 * social accounts, office hours — is seeded empty rather than invented, so the
 * administration panel shows an obvious gap for staff to complete.
 */
class SettingsSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            'org.name' => 'Global Support Foundation',
            'org.legal_name' => 'Global Support Foundation for Grassroot Entrepreneurship',
            'org.short_name' => 'GSF',
            'org.tagline' => 'Grassroot entrepreneurship for African communities',
            'org.founded' => 'August 2015',
            'org.description' => 'Global Support Foundation for Grassroot Entrepreneurship helps young people in Africa build co-operative enterprises through training, business advice, consultancy and advocacy.',

            // Served from Cloudinary so the mark can be replaced without a
            // deployment. The bundled file in public/images is the fallback.
            'org.logo' => 'https://res.cloudinary.com/dt6xndtv/image/upload/v1789025382/logo.jpg',

            // Transcribed from the previous site's contact page. Address spelling preserved.
            'contact.address' => 'No 1 Ohiamini Street, Rumoula Road',
            'contact.city' => 'Port Harcourt',
            'contact.state' => 'Rivers State',
            'contact.country' => 'Nigeria',
            'contact.email' => 'info@globalsupportfoundation.org',
            'contact.phone_primary' => '+234 812 613 8041',
            'contact.phone_secondary' => '+234 703 335 1303',
            'contact.phone_international' => '+353 85 786 4995',
            'contact.hours' => '',

            // Only the founder's LinkedIn profile is confirmed. Organizational
            // accounts must be supplied by GSF before they appear in the footer.
            'social.linkedin' => '',
            'social.facebook' => '',
            'social.instagram' => '',
            'social.x' => '',
            'social.youtube' => '',
            'founder.linkedin' => 'https://ie.linkedin.com/in/golden-anikwe-83451a18',

            // Awaiting documentation from the organization.
            'registration.number' => '',
            'registration.authority' => '',

            'seo.description' => 'Global Support Foundation for Grassroot Entrepreneurship supports co-operative enterprise, training and economic inclusion for young people in Nigeria and across Africa.',
        ];

        foreach ($settings as $key => $value) {
            Setting::firstOrCreate(['key' => $key], ['value' => $value]);
        }
    }
}
