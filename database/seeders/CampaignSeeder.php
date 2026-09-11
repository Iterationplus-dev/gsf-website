<?php

namespace Database\Seeders;

use App\Models\Campaign;
use Illuminate\Database\Seeder;

/**
 * Destinations a donor can choose on the Donate page.
 *
 * Only general giving is active by default. The themed campaigns are seeded
 * inactive so that GSF activates a campaign once it has agreed what the money
 * funds and what it will report back — a campaign is a commitment to a donor,
 * not a form field.
 */
class CampaignSeeder extends Seeder
{
    public function run(): void
    {
        $campaigns = [
            'general-donation' => [
                'title' => 'General Donation',
                'active' => true,
                'description' => 'Support the foundation\'s work where it is needed most — training, business advice and the running costs of getting co-operatives established.',
            ],
            'entrepreneurship-support' => [
                'title' => 'Entrepreneurship Support',
                'description' => 'Business training, planning support and mentorship for young people starting or growing an enterprise.',
            ],
            'youth-skills-development' => [
                'title' => 'Youth Skills Development',
                'description' => 'Vocational and business skills training leading to a foundation qualification in co-operative business.',
            ],
            'women-economic-empowerment' => [
                'title' => 'Women\'s Economic Empowerment',
                'description' => 'Raising the economic participation of women through co-operative enterprise and access to training.',
            ],
            'agricultural-development' => [
                'title' => 'Agricultural Development',
                'description' => 'Co-operative farming, agribusiness and value-chain support for young farmers.',
            ],
            'technology-training' => [
                'title' => 'Technology Training',
                'description' => 'Digital literacy and ICT skills for young entrepreneurs and the co-operatives they run.',
            ],
        ];

        foreach ($campaigns as $slug => $attributes) {
            Campaign::firstOrCreate(['slug' => $slug], $attributes + [
                'active' => false,
                'currency' => 'NGN',
            ]);
        }
    }
}
