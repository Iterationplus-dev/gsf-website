<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Project;
use Illuminate\Database\Seeder;

/**
 * Project records for the five programme areas the foundation delivers through.
 *
 * These describe the shape of each intervention — objectives, activities and who
 * it is for — and deliberately carry no dates, beneficiary counts, budgets or
 * outcomes. Those are reported figures: they belong to a specific delivery and
 * must be entered by GSF against work that actually happened. A project record
 * asserting a completion date or a beneficiary count that nobody can evidence is
 * the one thing this site should never do.
 *
 * Each is published as `planned` for the same reason. Status, dates and results
 * are set in the administration panel as delivery progresses.
 */
class ProjectSeeder extends Seeder
{
    private const NOTE = 'Describes the intended shape of the project. Set the real status, dates, beneficiary numbers and outcomes in the administration panel as delivery progresses.';

    public function run(): void
    {
        $programs = Content::where('type', 'program')->pluck('id', 'slug');

        foreach ($this->projects() as $position => $definition) {
            $content = Content::withTrashed()->firstOrCreate(
                ['type' => 'project', 'slug' => $definition['slug']],
                [
                    'title' => $definition['title'],
                    'excerpt' => $definition['excerpt'],
                    'body' => $definition['body'],
                    'source' => 'Programme area published on this website.',
                    'status' => 'published',
                    'published_at' => now(),
                    'position' => $position + 1,
                    'review_notes' => self::NOTE,
                ],
            );

            Project::firstOrCreate(
                ['content_id' => $content->id],
                [
                    'program_id' => $programs[$definition['program']] ?? null,
                    'status' => 'planned',
                    'location' => $definition['location'],
                    'state' => 'Rivers State',
                    'country' => 'Nigeria',
                    'beneficiary_category' => $definition['beneficiary_category'],
                    'objectives' => $definition['objectives'],
                    'activities' => $definition['activities'],
                    'sdgs' => $definition['sdgs'],
                ],
            );
        }
    }

    /**
     * @return list<array<string, mixed>>
     */
    private function projects(): array
    {
        return [
            [
                'slug' => 'rivers-youth-co-operative-formation',
                'title' => 'Rivers Youth Co-operative Formation',
                'program' => 'cooperative-development',
                'location' => 'Port Harcourt',
                'beneficiary_category' => 'Unemployed young people, 18-35',
                'excerpt' => 'Taking a group of young people from an informal working arrangement to a registered co-operative that can trade, borrow and hold assets in its own name.',
                'body' => '<p>Young people who already work together informally are often closer to a trading business than they realise. What they lack is the structure: a legal identity, a management committee, and records that satisfy a registrar and a lender.</p><p>This project takes a group from that starting point through to a registered society with a functioning committee, bye-laws its members understand, and books that stand up to inspection.</p>',
                'objectives' => "Register a trading co-operative with a functioning management committee.\nTrain members in co-operative governance and member responsibilities.\nEstablish records and financial controls that satisfy registration requirements.",
                'activities' => "Community mobilisation and interest meetings.\nGovernance and co-operative skills training.\nSupport with registration documentation.\nQuarterly mentoring visits.",
                'sdgs' => [1, 8],
            ],
            [
                'slug' => 'business-management-foundation-training',
                'title' => 'Business Management Foundation Training',
                'program' => 'human-capacity-development',
                'location' => 'Rumuola, Port Harcourt',
                'beneficiary_category' => 'School leavers and graduates',
                'excerpt' => 'A foundation course in co-operative business management, with a practical placement, for school leavers and graduates entering self-employment.',
                'body' => '<p>A qualification on its own does not make someone employable in a market with few jobs. This course is built around what a person actually needs to start and hold a small business together: costing, records, and the discipline of running something that other people depend on.</p><p>Classroom modules are paired with a placement in an operating co-operative, so participants see the work before they are asked to do it alone.</p>',
                'objectives' => "Deliver a foundation qualification in co-operative business.\nMove participants into self-employment or further training.",
                'activities' => "Classroom modules in business management, records and costing.\nPractical placement with an operating co-operative.\nEnd-of-course assessment.",
                'sdgs' => [4, 8],
            ],
            [
                'slug' => 'co-operative-farming-and-input-access',
                'title' => 'Co-operative Farming and Input Access',
                'program' => 'agriculture-and-livelihoods',
                'location' => 'Rivers State',
                'beneficiary_category' => 'Young farmers',
                'excerpt' => 'Collective access to land, equipment and inputs for young farmers, and a route beyond raw production into processing.',
                'body' => '<p>A young farmer buying inputs alone pays the worst price available and sells at the worst point in the season. Buying and selling as a group changes both ends of that.</p><p>This project establishes collective access to land and equipment, improves purchasing terms through group buying, and supports members to move beyond selling raw produce into processing, where the margin is.</p>',
                'objectives' => "Establish collective access to land and equipment.\nImprove input purchasing terms through group buying.\nMove members beyond raw production into processing.",
                'activities' => "Group formation and land agreements.\nInput purchasing and equipment sharing.\nAgronomy and post-harvest training.",
                'sdgs' => [2, 8, 12],
            ],
            [
                'slug' => 'digital-skills-for-small-enterprises',
                'title' => 'Digital Skills for Small Enterprises',
                'program' => 'technology-and-digital-inclusion',
                'location' => 'Port Harcourt',
                'beneficiary_category' => 'Micro and small enterprise owners',
                'excerpt' => 'Practical digital skills for enterprise owners: keeping records, taking payment, and being findable by customers.',
                'body' => '<p>Digital capability decides who a small business can reach and whether anyone can verify its accounts. The gap is rarely interest — it is that nobody has sat down with the owner and shown them the specific tools their trade needs.</p><p>This project covers digital record-keeping, mobile money, and the basics of reaching customers online, taught against the way each participant actually trades.</p>',
                'objectives' => "Enable participants to keep digital business records.\nEnable participants to reach customers through online channels.",
                'activities' => "Digital literacy modules.\nRecord-keeping and mobile money practice.\nOnline marketing basics.",
                'sdgs' => [4, 8, 9],
            ],
            [
                'slug' => 'shared-production-workshop',
                'title' => 'Shared Production Workshop',
                'program' => 'industrial-and-processing',
                'location' => 'Port Harcourt',
                'beneficiary_category' => 'Artisans and small-scale producers',
                'excerpt' => 'A shared workshop and equipment pool for furniture, textile and leather producers who cannot individually finance the machinery their trade requires.',
                'body' => '<p>Ask a group of young artisans what limits them and the answer is usually one object with a price: a milling machine, an industrial sewing machine, a press. Each is beyond what any one of them earns in a year, and comfortably within reach of eight of them buying together.</p><p>This project establishes a shared workshop with pooled equipment, along with the agreements that decide who may operate it, how time is allocated, what members pay to use it and who maintains it. The usage charge builds the reserve that replaces the machine — which is what separates a society still producing in year five from one explaining why it is not.</p>',
                'objectives' => 'Establish a shared workshop with equipment no member could finance alone.
Agree operating, maintenance and replacement arrangements before purchase.
Enable members to accept production work they currently turn down.',
                'activities' => 'Group formation and needs assessment.
Equipment specification and joint purchase.
Operator training and safety certification.
Maintenance scheduling and reserve management.',
                'sdgs' => [8, 9, 12],
            ],
            [
                'slug' => 'inclusive-enterprise-for-people-living-with-disabilities',
                'title' => 'Inclusive Enterprise for People Living with Disabilities',
                'program' => 'community-interventions',
                'location' => 'Rivers State',
                'beneficiary_category' => 'People living with disabilities',
                'excerpt' => 'Removing the practical barriers that keep people living with disabilities out of co-operative membership, and adapting enterprises to their circumstances.',
                'body' => '<p>The barrier is usually access rather than ability — to premises, to equipment, to a group that has thought about how someone will take part. General enterprise support tends to assume that away.</p><p>This project starts with an accessibility assessment, adapts how training is delivered, and supports the establishment of enterprises built around the circumstances of their members rather than in spite of them.</p>',
                'objectives' => "Remove practical barriers to co-operative membership.\nEstablish enterprises adapted to members' circumstances.",
                'activities' => "Accessibility assessment.\nAdapted training delivery.\nEnterprise establishment support.",
                'sdgs' => [8, 10],
            ],
        ];
    }
}
