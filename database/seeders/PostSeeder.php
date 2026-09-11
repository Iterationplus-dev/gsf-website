<?php

namespace Database\Seeders;

use App\Models\Content;
use Illuminate\Database\Seeder;

/**
 * Opening articles for the blog.
 *
 * These are explanatory pieces about co-operative enterprise, not reports of
 * work GSF has done. That distinction is deliberate: an article claiming a
 * training course, a beneficiary count or a result would be an unverified claim
 * on a donor-facing site, which is exactly what the demo flag exists to prevent.
 * Everything here is general guidance that stands on its own, so it can be
 * published safely and stays true whatever the programme calendar looks like.
 *
 * Anything describing a specific GSF activity should be written by the
 * foundation and published from the CMS.
 */
class PostSeeder extends Seeder
{
    private const SOURCE = 'Written for this website as general guidance on co-operative enterprise. Not a report of GSF activity.';

    public function run(): void
    {
        foreach ($this->articles() as $index => $article) {
            Content::withTrashed()->firstOrCreate(
                ['type' => 'post', 'slug' => $article['slug']],
                [
                    'title' => $article['title'],
                    'excerpt' => $article['excerpt'],
                    'category' => $article['category'],
                    'body' => $article['body'],
                    'source' => self::SOURCE,
                    'status' => 'published',
                    'published_at' => now()->subDays(($index + 1) * 9),
                    'position' => $index + 1,
                ],
            );
        }
    }

    /**
     * @return list<array{slug: string, title: string, category: string, excerpt: string, body: string}>
     */
    private function articles(): array
    {
        return [
            [
                'slug' => 'the-seven-co-operative-principles',
                'title' => 'The seven co-operative principles, and what each one asks of you',
                'category' => 'Co-operative basics',
                'excerpt' => 'The principles are not a mission statement. Each one is a rule about how decisions get made and money gets shared, and each one has teeth.',
                'body' => <<<'HTML'
                    <p>Most people meet the co-operative principles as a list on a wall. They are more useful read as seven commitments a society makes to its own members — each with a practical consequence when things get difficult.</p>

                    <h2>1. Voluntary and open membership</h2>
                    <p>Membership is open to anyone who can use the society's services and accept the responsibilities of belonging. No one is excluded on grounds of gender, social status, race, politics or religion. The consequence: you cannot quietly close the society once it becomes profitable.</p>

                    <h2>2. Democratic member control</h2>
                    <p>Members control the society and set its policy. In a primary co-operative each member has one vote, regardless of how much capital they put in. The consequence: the largest contributor does not get to decide alone, and this is the principle most often broken first.</p>

                    <h2>3. Member economic participation</h2>
                    <p>Members contribute equitably to the capital of the society and control it democratically. Surplus is allocated by members — to reserves, to developing the business, or returned in proportion to their trade with the society, not in proportion to their shareholding.</p>

                    <h2>4. Autonomy and independence</h2>
                    <p>A co-operative that takes capital from outside must do so on terms that keep member control intact. The consequence: read the conditions on any facility carefully. A loan that comes with a seat and a veto is not the same as a loan.</p>

                    <h2>5. Education, training and information</h2>
                    <p>Societies provide education and training for members, elected representatives and staff, so they can contribute effectively. A committee that does not understand the accounts cannot govern.</p>

                    <h2>6. Co-operation among co-operatives</h2>
                    <p>Societies serve members best by working together — locally, nationally and internationally. Buying together, selling together and lending to each other is where much of the practical advantage lies.</p>

                    <h2>7. Concern for community</h2>
                    <p>Societies work for the sustainable development of their communities, through policies their members approve.</p>

                    <p>These principles are maintained by the International Co-operative Alliance and are shared by co-operatives worldwide. They are the reason a co-operative is a different instrument from a company with several shareholders.</p>
                    HTML,
            ],
            [
                'slug' => 'registering-a-co-operative-what-to-have-ready',
                'title' => 'Registering a co-operative society: what to have ready first',
                'category' => 'Co-operative basics',
                'excerpt' => 'Most applications stall on the same few things. Assembling them before you approach the registrar turns months into weeks.',
                'body' => <<<'HTML'
                    <p>Registration is rarely refused outright. It stalls — over an incomplete member list, a set of bye-laws that contradicts itself, or an address nobody can visit. The work of preparing well is mostly done before any form is filled in.</p>

                    <h2>Your members</h2>
                    <p>A co-operative society needs a minimum of seven members. Have a written list with full names, addresses and signatures, and be sure every person on it knows they are on it. A list padded with names to reach seven is the fastest way to a society that cannot hold a quorum six months later.</p>

                    <h2>What the society is actually for</h2>
                    <p>Be able to state in one sentence what the society does and how members will use it. "We buy inputs together and sell as one supplier" is a purpose. "Empowerment" is not — and a registrar will ask.</p>

                    <h2>Bye-laws</h2>
                    <p>These are the society's own rules: who may join, how officers are elected and removed, how meetings are called, how surplus is dealt with, and how disputes are settled. Model bye-laws are usually available from the registering authority and are worth starting from rather than drafting fresh.</p>

                    <h2>Officers and a committee</h2>
                    <p>You will need elected officers and a management committee, along with minutes of the meeting at which they were elected. Hold that meeting properly and record it properly — it becomes evidence.</p>

                    <h2>Records and an address</h2>
                    <p>Expect to show a register of members, minutes, and a basic set of books. A verifiable address for the society matters more than people expect.</p>

                    <p>Requirements and fees vary between states and change over time. Confirm the current position with the co-operative registrar in your state before you submit. If you would like help preparing an application, that is part of what this foundation does.</p>
                    HTML,
            ],
            [
                'slug' => 'shared-equipment-the-clearest-case-for-co-operating',
                'title' => 'Shared equipment: the clearest case for working together',
                'category' => 'Running an enterprise',
                'excerpt' => 'One machine none of you could buy alone changes what work you are able to accept. It also creates arguments, unless you agree the rules first.',
                'body' => <<<'HTML'
                    <p>Ask a group of young artisans what stops them growing and the answer is usually a specific object: a milling machine, a delivery van, a cold room, an industrial sewing machine. Not capital in the abstract — one thing, with a price.</p>

                    <h2>Why it is the strongest argument for a co-operative</h2>
                    <p>Equipment is the easiest case to make because the arithmetic is plain. A machine that costs more than any one member earns in a year is within reach of eight members contributing together. Once it exists, each of them can accept work they previously had to turn down.</p>

                    <h2>Agree the boring things in advance</h2>
                    <p>Shared assets fail on operational detail, not on goodwill. Before the purchase, write down:</p>
                    <ul>
                        <li><strong>Who may operate it</strong>, and what training is required first.</li>
                        <li><strong>How time is allocated</strong> when two members need it the same day.</li>
                        <li><strong>What members pay to use it</strong> — even a nominal hourly rate, because it funds the next item.</li>
                        <li><strong>Who maintains it</strong>, on what schedule, and from which fund.</li>
                        <li><strong>What happens when it breaks</strong>, and who decides on repair or replacement.</li>
                        <li><strong>Who owns it</strong> — the society, not any individual member.</li>
                    </ul>

                    <h2>Charge for use from the first day</h2>
                    <p>The most common mistake is treating a shared asset as free at the point of use. It is not free: it wears out. A usage charge that builds a maintenance and replacement reserve is what separates a society that still has working equipment in year five from one explaining why it does not.</p>

                    <p>The same logic applies to premises, transport and cold storage. The asset is the visible part; the agreement is what makes it last.</p>
                    HTML,
            ],
            [
                'slug' => 'bookkeeping-for-a-new-co-operative',
                'title' => 'Bookkeeping for a new co-operative: the minimum that actually works',
                'category' => 'Running an enterprise',
                'excerpt' => 'You do not need accounting software to start. You do need four records, kept without fail, from the first day of trading.',
                'body' => <<<'HTML'
                    <p>Young societies rarely fail because the books were imperfect. They fail because there were no books, so nobody could answer a simple question: did we make anything last month, and where is it?</p>

                    <h2>Four records, kept from day one</h2>
                    <ol>
                        <li><strong>Cash book.</strong> Every naira in and out, dated, with what it was for. Nothing else works without this.</li>
                        <li><strong>Member register.</strong> Who has joined, when, what they contributed, and what they have withdrawn.</li>
                        <li><strong>Sales and purchases.</strong> What the society bought and sold, to whom, and whether it has been paid.</li>
                        <li><strong>Minutes.</strong> What was decided, by whom, and on what date. This is a financial record too — it is where spending authority comes from.</li>
                    </ol>

                    <h2>Separate the society's money from your own</h2>
                    <p>This is the single most important habit, and the one most often broken early. The society needs its own account, and members need to be reimbursed through it rather than paying for things personally and remembering later. Mixed funds are how honest people end up under suspicion.</p>

                    <h2>Two signatures</h2>
                    <p>Require two officers to authorise any payment above a threshold the members set. This protects the officers as much as the society.</p>

                    <h2>Report monthly, in plain language</h2>
                    <p>A one-page summary each month — money in, money out, what we hold, what we are owed, what we owe — read aloud at a members' meeting. Members who can see the position do not invent one.</p>

                    <p>Move to software when the volume demands it, not before. A disciplined cash book beats an abandoned accounting package every time.</p>
                    HTML,
            ],
            [
                'slug' => 'what-a-business-plan-has-to-answer',
                'title' => 'What a business plan actually has to answer',
                'category' => 'Running an enterprise',
                'excerpt' => 'Most rejected plans are not too short. They are long in the wrong places, and silent on the four questions a reader is actually asking.',
                'body' => <<<'HTML'
                    <p>A business plan is not an essay about ambition. It is an argument that a specific amount of money, spent in a specific way, will produce more money than it consumed — and a reader is checking that argument, not your enthusiasm.</p>

                    <h2>Four questions</h2>
                    <h3>1. Who buys this, and how do you know?</h3>
                    <p>Name the customer. If you have already sold to them, say how many, how often and at what price. Ten real customers described precisely is worth more than an estimate of the national market.</p>

                    <h3>2. What does one unit cost you, and what do you sell it for?</h3>
                    <p>Materials, labour, transport, packaging, the share of rent and power it consumes. If you cannot produce this figure, nothing else in the plan can be trusted.</p>

                    <h3>3. What exactly will the money buy?</h3>
                    <p>An itemised list with prices and quotes. "Working capital" is not a use of funds; "sixty bags of input at ₦X, quoted by named supplier" is.</p>

                    <h3>4. What happens if you are wrong?</h3>
                    <p>Show what the business looks like at half the sales you expect. A plan that only works at the optimistic figure tells the reader you have not thought about the risk — and they will.</p>

                    <h2>What to keep short</h2>
                    <p>Background, vision and history. A reader will give you a page for those and would rather spend the rest on your numbers.</p>

                    <p>Write the plan for the person deciding, in the order they will ask. If you would like a review before you submit one, that is work this foundation does.</p>
                    HTML,
            ],
            [
                'slug' => 'governance-mistakes-that-break-young-co-operatives',
                'title' => 'Five governance mistakes that break young co-operatives',
                'category' => 'Governance',
                'excerpt' => 'Most societies that collapse do so for reasons that were visible early, and that cost nothing to prevent.',
                'body' => <<<'HTML'
                    <p>A society rarely fails because the market turned. It fails because a small governance problem was left alone until it became a dispute nobody could settle. These are the five most common.</p>

                    <h2>1. The founder who is also the treasurer, the secretary and the signatory</h2>
                    <p>Concentration of roles is convenient at the start and dangerous by the second year. Separate the person who authorises payments from the person who makes them, from the person who records them. This protects the founder too.</p>

                    <h2>2. Meetings that are never called</h2>
                    <p>Bye-laws specify how often members meet. Societies that stop meeting stop being co-operatives in practice long before they stop on paper — and members who are not consulted eventually stop contributing.</p>

                    <h2>3. Decisions with no record</h2>
                    <p>If it is not in the minutes, it did not happen. Six months later nobody agrees what was decided, and the person who benefited is assumed to have arranged it.</p>

                    <h2>4. Surplus distributed before reserves are built</h2>
                    <p>Sharing out everything the society earns in a good year feels like success. It leaves nothing for the bad year, the broken machine, or the buyer who pays late. Agree the reserve percentage before there is anything to argue over.</p>

                    <h2>5. No way to remove an officer</h2>
                    <p>Every set of bye-laws should say how an officer is removed and by what majority. Societies without that clause discover they need it at the worst possible moment, and the dispute goes outside the society to be settled.</p>

                    <p>None of these require money to fix. They require agreeing the rules while everyone is still on good terms — which is the only time it is easy.</p>
                    HTML,
            ],
        ];
    }
}
