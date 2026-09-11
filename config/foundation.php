<?php

/*
|--------------------------------------------------------------------------
| Foundation platform configuration
|--------------------------------------------------------------------------
|
| Integration and infrastructure settings only. Organisational content that
| administrators are expected to change — addresses, phone numbers, social
| profiles, statistics — lives in the `settings` table and the CMS, never here.
|
*/

return [

    'paystack_secret' => env('PAYSTACK_SECRET_KEY'),

    'currencies' => array_values(array_filter(array_map('trim', explode(',', (string) env('PAYSTACK_CURRENCIES', 'NGN'))))),

    'donations_enabled' => (bool) env('DONATIONS_ENABLED', false),

    'admin_email' => env('ADMIN_NOTIFICATION_EMAIL'),

    'newsletter_double_opt_in' => (bool) env('NEWSLETTER_DOUBLE_OPT_IN', true),

    'maps_enabled' => (bool) env('GOOGLE_MAPS_ENABLED', false),

    'maps_key' => env('GOOGLE_MAPS_API_KEY'),

    'analytics_domain' => env('PLAUSIBLE_DOMAIN'),

    'analytics_script' => env('PLAUSIBLE_SCRIPT_URL', 'https://plausible.io/js/script.js'),

    /*
    |--------------------------------------------------------------------------
    | Media storage
    |--------------------------------------------------------------------------
    |
    | `driver` selects where uploads are stored. Use `cloudinary` in production for
    | responsive delivery, automatic AVIF/WebP negotiation and CDN caching; any
    | filesystem disk name (for example `public`) is accepted for local development.
    | Existing records keep the driver they were uploaded with, so changing this
    | value never orphans previously stored files.
    |
    */

    'media' => [

        'driver' => env('MEDIA_DRIVER', 'public'),

        'cloudinary' => [
            'cloud_name' => env('CLOUDINARY_CLOUD_NAME'),
            'api_key' => env('CLOUDINARY_API_KEY'),
            'api_secret' => env('CLOUDINARY_API_SECRET'),
            'folder' => env('CLOUDINARY_FOLDER', 'gsf'),
            'signature_algorithm' => env('CLOUDINARY_SIGNATURE_ALGORITHM', 'sha1'),
        ],

    ],

];
