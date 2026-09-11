<?php

namespace Database\Seeders;

use App\Models\Content;
use App\Models\Media;
use App\Services\MediaService;
use Illuminate\Database\Seeder;

/**
 * Editorial content migrated from the previous website and the project backup.
 *
 * Records carry a `source` naming the file the material came from. Anything the
 * backup did not evidence is seeded as a draft with a review note rather than
 * published, so nothing unverified can reach donors or partners.
 */
class ContentSeeder extends Seeder
{
    private const BACKUP = 'Migrated from backup/application/views/pages/';

    public function run(): void
    {
        $this->pages();
        $this->membershipPage();
        $this->stories();
        $this->programs();
        $this->people();
        $this->currentTeam();
        $this->awards();
        $this->policies();
        $this->partners();

        // Last, so that every record it attaches an image to already exists.
        $this->contentImages();
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function upsert(string $type, string $slug, array $attributes): Content
    {
        return Content::withTrashed()->firstOrCreate(
            ['type' => $type, 'slug' => $slug],
            $attributes,
        );
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function published(string $type, string $slug, array $attributes): Content
    {
        return $this->upsert($type, $slug, $attributes + [
            'status' => 'published',
            'published_at' => now(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $attributes
     */
    private function draft(string $type, string $slug, string $reviewNote, array $attributes): Content
    {
        return $this->upsert($type, $slug, $attributes + [
            'status' => 'draft',
            'review_notes' => $reviewNote,
        ]);
    }

    private function pages(): void
    {
        $this->published('page', 'about-us', [
            'title' => 'About Us',
            'excerpt' => 'Global Support Foundation for Grassroot Entrepreneurship was established in August 2015 to help young people across Africa build co-operative enterprises.',
            'source' => self::BACKUP.'view_about.php',
            'position' => 1,
            'body' => <<<'HTML'
                <p>Global Support Foundation for Grassroot Entrepreneurship (GSF) was established in August 2015. It works to raise the level of participation of young people in economic life by helping them form and run co-operative business enterprises.</p>
                <p>The organisation is led by its Executive Director, Golden Anikwe, supported by board members and a group of professionals with long experience in co-operative development. Its approach draws directly on Co-operative Support Services for Ethnic Minority Entrepreneurs Limited, an organisation the founder ran in Ireland between 2006 and 2014, which provided nationwide business support to co-operatives.</p>
                <h2>What we do</h2>
                <p>GSF concentrates on four functions:</p>
                <ul>
                    <li>Providing business and entrepreneurship training.</li>
                    <li>Providing consultancy and advisory services to co-operative enterprises run by young people.</li>
                    <li>Acting as a network for the exchange of information, ideas and experience.</li>
                    <li>Encouraging advocacy by co-operative members.</li>
                </ul>
                <p>The organisation raises awareness among young people of what co-operative enterprise makes possible, and supports the formation of co-operatives that in turn strengthen the individual businesses of their members.</p>
                <h2>How we work</h2>
                <p>GSF works in collaboration with governmental and non-governmental organisations that can provide practical support to start-up and established businesses. It positions itself as a partner to state, national and international co-operative movements, development partners, financial institutions and other relevant organisations.</p>
                <h2>Our services</h2>
                <p>GSF offers advice free of charge in most cases, and assists with feasibility studies, business analysis and management reviews. Its services include:</p>
                <ul>
                    <li>General advice on co-operative, democratic and social enterprise models of running a business.</li>
                    <li>Development support for groups and individuals setting up co-operative enterprises.</li>
                    <li>Start-up training, from short introductory sessions to comprehensive programmes leading to internationally recognised business management and administration qualifications.</li>
                    <li>Sector training, from one-day sessions to full courses on the different aspects of managing a co-operative enterprise.</li>
                    <li>Consultancy covering feasibility studies, business analysis and management reviews.</li>
                    <li>Guidance on preparing business plans.</li>
                    <li>Advice on government grants available to such ventures.</li>
                </ul>
                <p>Training is delivered in co-operative skills, technical skills and business management.</p>
                HTML,
        ]);

        $this->published('page', 'mission-vision-values', [
            'title' => 'Mission, Vision & Values',
            'excerpt' => 'What Global Support Foundation exists to do, the future it works towards, and the principles that govern how it operates.',
            'source' => self::BACKUP.'view_about.php',
            'position' => 2,
            'body' => <<<'HTML'
                <h2>Our mission</h2>
                <p>GSF exists to encourage and help young people unlock their full potential by pooling their skills and resources to develop co-operative businesses in their chosen sectors. It works to achieve this by developing a best-practice model for creating new co-operative businesses, and by offering business advice, training and development, and publications tailored to the needs of its members.</p>
                <h2>Our vision</h2>
                <p>GSF holds a vision of Africa as a multi-cultural continent where young people can realise their ambitions and work together to build a more successful future.</p>
                <p>For itself, GSF intends to become the reference organisation for youth co-operatives in Africa and a recognised participant in the continent's business life — including as an advocacy organisation representing the views of its members in relevant forums.</p>
                <h2>Our goals</h2>
                <ul>
                    <li>Build a sustainable organisation over the long term by promoting the development of youth co-operatives in Africa.</li>
                    <li>Create awareness of the contribution youth co-operatives make to African life.</li>
                    <li>Become the reference organisation for youth co-operatives for government, media, academics and business organisations.</li>
                    <li>Bring governmental and non-governmental organisations together with young Africans in a lasting and mutually beneficial partnership.</li>
                </ul>
                <h2>Our objectives</h2>
                <ul>
                    <li>Develop best-practice processes for setting up new youth co-operative businesses.</li>
                    <li>Develop support services that co-operatives and individuals recognise as valuable to the success of their own businesses.</li>
                    <li>Create awareness of GSF and its activities among communities, relevant government ministries, departments and agencies, and wider society.</li>
                    <li>Lift young Africans out of poverty by strengthening their economic activity through effective co-operatives.</li>
                    <li>Raise awareness among young Africans of their responsibilities in nation-building.</li>
                    <li>Raise the level of economic participation of Africa's female population.</li>
                    <li>Create conditions for fuller economic and social inclusion of those affected by past conflict and instability.</li>
                </ul>
                <h2>Our values</h2>
                <p>The co-operative principles that GSF promotes also govern how it works: voluntary and open membership, democratic member control, member economic participation, autonomy and independence, education and training, co-operation among co-operatives, and concern for community.</p>
                HTML,
        ]);

        $this->published('page', 'our-history', [
            'title' => 'Our History',
            'excerpt' => 'From co-operative support work in Ireland to the founding of Global Support Foundation in Nigeria in August 2015.',
            'source' => self::BACKUP.'view_about.php, view_team.php',
            'position' => 3,
            'body' => <<<'HTML'
                <h2>Origins in Ireland, 2006–2014</h2>
                <p>The organisation's approach was shaped by Co-operative Support Services Limited (CSS), founded in Ireland by Golden Anikwe. CSS promoted the establishment of co-operatives among ethnic minority communities, trained their members and staff, and guided those co-operatives in their operations. It provided nationwide business support, promoted socially sustainable business practice, raised awareness of the co-operative movement and delivered community education on social enterprise. CSS was for a period a member of the International Co-operative Alliance.</p>
                <h2>Founding in Nigeria, August 2015</h2>
                <p>During a visit to Nigeria in 2015, the founder established Global Support Foundation for Grassroot Entrepreneurship. The intention was to apply what had been learned in Ireland to a context where the co-operative model is not yet widely used or understood as a development instrument — despite clear demand from young people for a viable route into enterprise.</p>
                <h2>Why the co-operative model</h2>
                <p>The co-operative model has been used successfully in many countries to create sustainable employment and increase household wealth, and a number of United Nations bodies treat co-operatives as instruments of employment creation and sustainable development. GSF was founded on the view that the same model, adapted to local conditions, can serve young people across Africa.</p>
                HTML,
        ]);

        $this->published('page', 'our-founder', [
            'title' => 'Our Founder',
            'excerpt' => 'Golden Chudi Anikwe, Executive Director and founder of Global Support Foundation for Grassroot Entrepreneurship.',
            'source' => self::BACKUP.'view_team.php',
            'position' => 4,
            'review_notes' => 'The organisational record in the backup gives the title "Executive Director". Confirm with GSF whether "CEO" is also used before displaying an additional title. Founder portrait: backup/assets/images/t1.jpg.',
        ]);

        $this->published('page', 'leadership', [
            'title' => 'Leadership',
            'excerpt' => 'The management team responsible for delivering the foundation\'s programmes.',
            'source' => self::BACKUP.'view_team.php',
            'position' => 5,
        ]);

        $this->published('page', 'governance', [
            'title' => 'Governance',
            'excerpt' => 'How Global Support Foundation is structured, who takes decisions, and what remains to be published.',
            'position' => 12,
            'source' => 'Organisational structure as recorded on this website. Board register still to be supplied.',
            'review_notes' => 'Published describing the management structure only. The board register, terms of appointment and committee structure are still to be supplied by GSF and must be added here once confirmed.',
            'body' => <<<'HTML'
                <p>Global Support Foundation for Grassroot Entrepreneurship was established in <strong>August 2015</strong> and operates from Port Harcourt, Rivers State. This page sets out how the foundation is run and how decisions are taken. Where something has not yet been settled or published, it says so.</p>

                <h2>Day-to-day management</h2>
                <p>The foundation is led by its Executive Director, supported by a management team responsible for programme delivery, technical work, general management and administration. Each member of that team is named, with their background, on the <a href="/leadership">leadership page</a>.</p>
                <p>Responsibility is separated rather than concentrated: the person who authorises a payment is not the person who records it. The same discipline the foundation teaches co-operatives applies to the foundation itself.</p>

                <h2>How decisions are taken</h2>
                <p>The foundation works through the co-operative model and applies its principles internally. In practice that means decisions affecting members and beneficiaries are taken openly, recorded in writing, and reported back to the people they affect.</p>
                <p>Programme decisions — which societies to support, what training to run, where to work — are taken by the management team against the programme areas published on this site.</p>

                <h2>Working with young people and vulnerable adults</h2>
                <p>The foundation works with unemployed young people, people living with disabilities and retirees. Volunteers working with young people or in community settings are subject to the foundation's safeguarding requirements.</p>

                <h2>What is not yet published</h2>
                <p>The foundation is being straightforward about the gaps rather than leaving them implied:</p>
                <ul>
                    <li><strong>Board register.</strong> The composition of the board, terms of appointment and committee structure are being finalised and will be published here.</li>
                    <li><strong>Matters reserved to the board.</strong> The schedule of decisions that require board approval will be published alongside the register.</li>
                    <li><strong>Formal policy documents.</strong> The policies the foundation is adopting are listed on the <a href="/policies">policies page</a>, with their current status.</li>
                </ul>

                <p>If you need governance information that is not published here, please <a href="/contact-us">ask us directly</a> and we will tell you what exists and what does not.</p>
                HTML,
        ]);

        $this->published('page', 'transparency', [
            'title' => 'Transparency & Accountability',
            'excerpt' => 'What the foundation publishes, how money and personal data are handled, and what is not yet available.',
            'position' => 13,
            'source' => 'Describes the handling of donations, data and editorial sourcing as implemented on this website.',
            'review_notes' => 'Published describing what the website itself does. Registration number, annual report and audited accounts are still to be supplied by GSF and must be added here once available.',
            'body' => <<<'HTML'
                <p>An organisation asking for money should be able to say where it goes and who is accountable for it. This page records what the foundation publishes today, how it handles money and personal information, and — just as importantly — what it has not yet published.</p>

                <h2>How donations are handled</h2>
                <ul>
                    <li>Card details never reach this website. Payment is taken entirely by the payment provider.</li>
                    <li>A donation is only ever recorded as paid after the provider confirms it <strong>server-side</strong>. A browser returning from the payment page is not treated as proof of payment.</li>
                    <li>Every donation has a receipt, reachable through a signed, time-limited link, so a reference cannot be guessed or enumerated.</li>
                    <li>Amounts are held as whole minor units, so no rounding error can accumulate between what a donor gave and what is recorded.</li>
                </ul>

                <h2>How personal information is handled</h2>
                <ul>
                    <li>Consent is recorded, with a timestamp, every time someone submits a form.</li>
                    <li>Newsletter subscription is confirmed by email, and every message carries an unsubscribe link.</li>
                    <li>Donor details are not passed to analytics services.</li>
                    <li>The full position is set out in the <a href="/privacy-policy">privacy notice</a>.</li>
                </ul>

                <h2>How content on this site is sourced</h2>
                <p>Every published record on this website carries the source it came from, and material the foundation cannot evidence is held back rather than published. Photographs of identifiable people are reviewed before they appear. Where a figure is quoted — on the <a href="/impact">impact page</a> and elsewhere — the source of that figure is recorded with it.</p>

                <h2>What is not yet published</h2>
                <p>These are genuine gaps, not oversights, and the foundation would rather name them than imply they are covered:</p>
                <ul>
                    <li><strong>Registration details.</strong> The registering authority and reference number are not yet published on this site.</li>
                    <li><strong>Annual report.</strong> No annual narrative report has been published here.</li>
                    <li><strong>Financial statements.</strong> The foundation has not yet stated its position on publishing audited accounts.</li>
                    <li><strong>Board register.</strong> See the <a href="/governance">governance page</a>.</li>
                </ul>

                <h2>Complaints and questions</h2>
                <p>If you have a concern about the foundation's conduct, or you want information this page does not give you, <a href="/contact-us">contact us</a> and say what you need. A complaints and feedback procedure is among the policies being adopted.</p>
                HTML,
        ]);

        $this->published('page', 'get-involved', [
            'title' => 'Get Involved',
            'excerpt' => 'Ways to support grassroots enterprise in Nigeria — as a donor, volunteer, partner or subscriber.',
            'position' => 6,
            'body' => <<<'HTML'
                <p>Global Support Foundation depends on people and organisations who share its conviction that young people build their own way out of poverty when they are given the training, structure and support to do it together.</p>
                <p>There are four routes into the work, and each one matters.</p>
                <h2>Give</h2>
                <p>Donations fund training, business advice and the practical costs of helping a co-operative get from a group of interested people to a registered, trading enterprise.</p>
                <h2>Volunteer</h2>
                <p>The foundation's work depends on skills — in co-operative development, business planning, finance, training delivery, agriculture, technology and monitoring. If you have them, we would like to hear from you.</p>
                <h2>Partner</h2>
                <p>Development agencies, foundations, corporations, government bodies, universities and diaspora organisations can work with GSF on programme funding, project sponsorship, technical assistance and research.</p>
                <h2>Stay informed</h2>
                <p>Our newsletter carries news, project updates and opportunities to take part. It is straightforward to unsubscribe at any time.</p>
                HTML,
        ]);

        $this->published('page', 'partner-with-us', [
            'title' => 'Partner With Us',
            'excerpt' => 'Partnership opportunities for development agencies, foundations, corporations, government and academic institutions.',
            'source' => self::BACKUP.'view_partnership.php (rewritten: the source describes partnerships GSF seeks, not partnerships already in place)',
            'position' => 7,
            'review_notes' => 'The legacy partnership page named CBN, the World Bank, the UN and the AU. The source text describes these as partnerships GSF is eligible for or aspires to, not existing agreements. This page has been rewritten to describe the partnerships GSF seeks. Do not present any named institution as a current partner without a signed agreement.',
            'body' => <<<'HTML'
                <p>Partnership is central to how Global Support Foundation intends to grow its work. The organisation is looking for partners whose contribution goes beyond funding — institutions that bring technical depth, reach, or access to markets and finance that co-operatives cannot secure alone.</p>
                <h2>Who we are looking to work with</h2>
                <ul>
                    <li>Development agencies and multilateral institutions.</li>
                    <li>Grant-making foundations and philanthropists.</li>
                    <li>Corporations pursuing social investment and CSR objectives.</li>
                    <li>Government ministries, departments and agencies.</li>
                    <li>International NGOs and civil society organisations.</li>
                    <li>Universities and research institutions.</li>
                    <li>Diaspora organisations.</li>
                </ul>
                <h2>Forms of partnership</h2>
                <ul>
                    <li><strong>Programme funding</strong> — multi-year support for a defined area of work.</li>
                    <li><strong>Project sponsorship</strong> — funding a single project with agreed outcomes and reporting.</li>
                    <li><strong>Technical assistance</strong> — expertise in co-operative governance, agribusiness, finance or monitoring and evaluation.</li>
                    <li><strong>Corporate social responsibility</strong> — employee skills, in-kind support and supply chain access.</li>
                    <li><strong>Research partnership</strong> — evidence on what works in grassroots co-operative development.</li>
                    <li><strong>Capacity development</strong> — strengthening GSF's own systems and staff.</li>
                    <li><strong>Technology partnership</strong> — digital tools, connectivity and training for the enterprises we support.</li>
                </ul>
                <h2>Financial inclusion</h2>
                <p>Nigeria's Micro, Small and Medium Enterprises Development Fund makes provision for NGO microfinance institutions and financial co-operatives with a demonstrated record to access facilities through participating financial institutions. GSF sees routes of this kind as a way to move co-operative members from training into working capital, and welcomes conversations with financial institutions about how that pathway can be built.</p>
                <h2>Talk to us</h2>
                <p>Use the enquiry form on this page and the foundation will respond with a description of current work, its governance position, and what a partnership could look like in practice.</p>
                HTML,
        ]);

        $this->published('page', 'volunteer', [
            'title' => 'Volunteer',
            'excerpt' => 'Contribute your skills to co-operative development and grassroots enterprise.',
            'position' => 8,
            'body' => <<<'HTML'
                <p>Global Support Foundation works with volunteers who bring practical skills to the enterprises it supports — in co-operative governance, business planning, bookkeeping, training delivery, agriculture, digital skills and monitoring and evaluation.</p>
                <p>Tell us what you can offer and how much time you have. The foundation will respond with the areas where that contribution would make the most difference, and what a placement would involve.</p>
                <p>All volunteers working with young people or in community settings are subject to the foundation's safeguarding requirements.</p>
                HTML,
        ]);

        $this->published('page', 'contact-us', [
            'title' => 'Contact Us',
            'excerpt' => 'Reach the Global Support Foundation office in Port Harcourt, Rivers State, Nigeria.',
            'source' => self::BACKUP.'view_contacts.php',
            'position' => 9,
        ]);

        $this->published('page', 'privacy-policy', [
            'title' => 'Privacy Notice',
            'excerpt' => 'How Global Support Foundation collects, uses and protects personal information.',
            'position' => 10,
            'review_notes' => 'Reviewed structure covering the data the website actually collects. GSF should confirm its data controller details, retention periods and the contact point for data subject requests before relying on this notice.',
            'body' => <<<'HTML'
                <h2>What this notice covers</h2>
                <p>This notice explains what personal information the Global Support Foundation website collects, why it is collected, and what you can ask us to do with it.</p>
                <h2>Information we collect</h2>
                <ul>
                    <li><strong>Enquiries.</strong> When you use a contact, partnership or volunteer form we collect your name, email address, and any phone number, organisation and message you provide.</li>
                    <li><strong>Donations.</strong> When you give, we record your name, email address, any phone number and message, the amount, and the payment reference. Card details are handled entirely by our payment provider and never reach this website.</li>
                    <li><strong>Newsletter.</strong> We record your email address and the time you consented to receive email from us.</li>
                </ul>
                <h2>Why we collect it</h2>
                <p>To respond to your enquiry, to process and acknowledge your donation, to meet our record-keeping obligations, and — where you have asked for it — to send you news about the foundation's work.</p>
                <h2>Who we share it with</h2>
                <p>We share donation details with our payment provider to the extent needed to take the payment, and with our email provider to send you an acknowledgement. We do not sell personal information, and we do not send donor details to analytics services.</p>
                <h2>Your choices</h2>
                <p>Every newsletter email carries an unsubscribe link. You may ask us for a copy of the information we hold about you, ask us to correct it, or ask us to erase it, by writing to the contact address on this website.</p>
                <h2>Retention</h2>
                <p>Donation records are kept for as long as financial record-keeping requires. Enquiries are kept while they are being handled and for a reasonable period afterwards. Newsletter records are kept until you unsubscribe.</p>
                HTML,
        ]);
    }

    /**
     * Co-operative societies formed with the founder's support during the
     * Ireland years, carried over from the previous site's testimonies page.
     *
     * These predate the Nigerian foundation, so each one says plainly where and
     * when it happened rather than implying it is current GSF programme work.
     */
    private function stories(): void
    {
        $source = 'Migrated from https://globalsupportfoundation.org/testimonies';

        $studies = [
            [
                'slug' => 'baffles-entertainment-co-operative',
                'title' => 'Baffles Entertainment and Event Management Co-operative Society',
                'excerpt' => 'Dublin, December 2008. Media and entertainment professionals formed a co-operative to serve a market the mainstream industry was not reaching.',
                'category' => 'Ireland, 2008',
                'body' => <<<'HTML'
                    <p>Baffles Entertainment and Event Management Co-operative Society Limited was established in Dublin in December 2008. Its aim was to search out and provide a complete entertainment and event management package — a solution to ethnic community entertainment in Ireland.</p>
                    <p>The society operates as a social enterprise, primarily targeted at the African community but also drawing interest from other ethnic and Irish communities. Its members are media and entertainment professionals whose goal is built on the original aim: to provide solutions to problems relating to immigrant and ethnic entertainment.</p>
                    <p>Baffles shows the pattern the foundation still works to. A group of people already had the skill. What they did not have was a legal structure that let them win work as one business instead of competing as individuals.</p>
                    HTML,
            ],
            [
                'slug' => 'his-and-hers-recreational-co-operative',
                'title' => 'His and Hers Recreational Co-operative Society',
                'excerpt' => 'Galway, 2009. Eight members pooled competencies in event management, music and dance to run a recreational centre.',
                'category' => 'Ireland, 2009',
                'body' => <<<'HTML'
                    <p>His and Hers Recreational Centre was established in Galway in 2009 and formally launched on 1 May 2010.</p>
                    <p>The society has eight members with competencies across event organisation and management, music and dance. It organises dance classes for students, teaches people to use musical instruments, rents its space out for functions, and provides a leisure and relaxing environment for people in the community.</p>
                    <p>A single member could not have carried the cost of a venue. Eight members sharing it could, and the space then earned in several ways at once.</p>
                    HTML,
            ],
            [
                'slug' => 'little-angels-childcare-co-operative',
                'title' => 'Little Angels Childcare Co-operative Society',
                'excerpt' => 'Dundalk, 2009. Over 72 members of a community union answered their own shortage of affordable childcare.',
                'category' => 'Ireland, 2009',
                'body' => <<<'HTML'
                    <p>Little Angels Childcare Co-operative Society Limited was established in Dundalk in 2009 as a crèche and day-care facility for children aged six months to fourteen years.</p>
                    <p>It came out of the more than 72 members of Nwannedinamba, the Dundalk chapter of the Igbo Union Ireland, as an initiative to promote the socio-cultural integration of the Igbo people in Dundalk and to meet their need for affordable childcare in the area.</p>
                    <p>The members were the customers. That is what made the numbers work from the first day, and it is why a co-operative was the right structure rather than a company with outside shareholders.</p>
                    HTML,
            ],
            [
                'slug' => 'meh-hair-and-beauty-co-operative',
                'title' => 'MEH Hair and Beauty Co-operative Society',
                'excerpt' => 'Cork, 2009. Five African women with years of experience stopped competing separately and registered as one society.',
                'category' => 'Ireland, 2009',
                'body' => <<<'HTML'
                    <p>MEH Hair and Beauty Co-operative Society Limited was established in Cork in 2009. It is made up of five African women who each had several years of experience in hair and beauty before migrating to Ireland.</p>
                    <p>They had been conducting business in an individual capacity. After learning that support was available to help them formalise, they sought assistance to form a co-operative society and traded together from then on.</p>
                    <p>Nothing changed about their skill. What changed was that one shared premises, one set of registration costs and one name in the market replaced five of each.</p>
                    HTML,
            ],
            [
                'slug' => 'amazing-ore-ofe-co-operative',
                'title' => 'Amazing (Ore Ofe) Co-operative Society',
                'excerpt' => 'Dublin, 2008. Six immigrants from Africa built a fashion business mixing African and European fabrics.',
                'category' => 'Ireland, 2008',
                'body' => <<<'HTML'
                    <p>Amazing (Ore Ofe) Co-operative Society was established in Dublin in 2008 and comprises six immigrants from Africa.</p>
                    <p>The society engages in promoting and supplying Afro-European outfits through a fashion design business that mixes African and European fabrics to create clothing for both markets.</p>
                    <p>The society found a product neither market was making on its own, and had the combined capacity to produce it at a scale worth selling.</p>
                    HTML,
            ],
        ];

        foreach ($studies as $position => $study) {
            $this->published('story', $study['slug'], [
                'title' => $study['title'],
                'excerpt' => $study['excerpt'],
                'category' => $study['category'],
                'body' => $study['body'],
                'source' => $source,
                'position' => $position + 1,
            ]);
        }
    }

    private function membershipPage(): void
    {
        $this->published('page', 'membership', [
            'title' => 'Membership Application',
            'excerpt' => 'Affiliate your co-operative society with Global Support Foundation.',
            'source' => 'https://globalsupportfoundation.org/form',
            'position' => 11,
            'seo_title' => 'Apply for co-operative membership',
            'seo_description' => 'Apply to affiliate your co-operative society with Global Support Foundation for Grassroot Entrepreneurship.',
            'body' => <<<'HTML'
                <p>Global Support Foundation works through co-operative societies rather than with individuals alone. Affiliating brings your society into the foundation's training, advisory and advocacy work, and into a network of enterprises facing the same practical problems.</p>
                <h2>What affiliation involves</h2>
                <p>Affiliated societies can take part in the foundation's business and co-operative training, ask for advisory support on governance, bookkeeping and business planning, and be considered when the foundation is matching enterprises to funding and market opportunities.</p>
                <h2>Who can apply</h2>
                <p>Any co-operative society with at least seven members may apply. Seven is the minimum membership at which a co-operative society can be registered, so the form will not accept a smaller number. You do not need to be registered already — the foundation advises societies that are still working towards registration.</p>
                <h2>What happens next</h2>
                <p>The membership team reviews each application and contacts the named person using the details supplied. You will be asked for a written list of members at that point, so it is worth having one ready.</p>
                HTML,
        ]);
    }

    private function programs(): void
    {
        $source = self::BACKUP.'view_about.php, view_support.php';

        $this->published('program', 'grassroots-entrepreneurship', [
            'title' => 'Grassroots Entrepreneurship',
            'excerpt' => 'Training, business advice and mentorship that take a small enterprise from an idea to a trading business.',
            'source' => $source,
            'position' => 1,
            'body' => <<<'HTML'
                <p>Most of the enterprises GSF works with begin small, informal and undercapitalised. This programme provides what those businesses need in order to become durable: a plan, a set of skills, and someone experienced to consult when a decision matters.</p>
                <h2>What the programme provides</h2>
                <ul>
                    <li>Entrepreneurship and business management training, from short introductory sessions to comprehensive programmes leading to recognised qualifications.</li>
                    <li>Guidance with the preparation of business plans.</li>
                    <li>Feasibility studies, business analysis and management reviews — in most cases free of charge.</li>
                    <li>Advice on government grants and finance available to small ventures.</li>
                    <li>Continuing mentorship as an enterprise grows.</li>
                </ul>
                <h2>Who it is for</h2>
                <p>Young people entering self-employment, existing microenterprises seeking to formalise and grow, and groups intending to trade together.</p>
                HTML,
        ]);

        $this->published('program', 'cooperative-development', [
            'title' => 'Co-operative Development',
            'excerpt' => 'Forming, registering and governing co-operative enterprises so that members can achieve together what none could alone.',
            'source' => $source,
            'position' => 2,
            'body' => <<<'HTML'
                <p>The co-operative is the organisational form at the centre of the foundation's work. A co-operative lets members pool capital, share equipment they could not individually afford, buy and sell at better terms, and hold each other accountable through a democratic structure.</p>
                <h2>What the programme provides</h2>
                <ul>
                    <li>General advice on co-operative, democratic and social enterprise models.</li>
                    <li>Development support for groups and individuals setting up a co-operative.</li>
                    <li>Training in co-operative skills, governance and the duties of officers and members.</li>
                    <li>Support with registration and compliance.</li>
                    <li>Help with market access and trading arrangements.</li>
                </ul>
                <h2>Why it matters</h2>
                <p>The co-operative model is not yet widely used or understood in Africa as a development instrument, despite having been adopted successfully elsewhere to create sustainable employment and increase household wealth. Closing that gap is the foundation's central purpose.</p>
                HTML,
        ]);

        $this->published('program', 'human-capacity-development', [
            'title' => 'Human Capacity Development',
            'excerpt' => 'Skills, leadership and vocational training that give young people a foundation qualification and a route to self-employment.',
            'source' => $source,
            'position' => 3,
            'body' => <<<'HTML'
                <p>GSF trains young people and provides the practical experience needed to obtain a foundation qualification in co-operative business, improving their prospects for gainful self-employment and further progression.</p>
                <h2>Areas of training</h2>
                <ul>
                    <li><strong>Co-operative skills</strong> — membership, governance, and the responsibilities of holding office.</li>
                    <li><strong>Technical skills</strong> — the trade or sector skills an enterprise depends on.</li>
                    <li><strong>Business management</strong> — planning, records, costing and administration.</li>
                    <li><strong>Leadership</strong> — running meetings, representing members and managing people.</li>
                </ul>
                <h2>Sectors</h2>
                <p>Training is delivered across the sectors where young people can realistically create work for themselves, including industrial and processing production — furniture, mats, bags, footwear, clothing, bedding and leather goods — and professional services, where graduates in engineering, architecture, technology, medicine and pharmacy form co-operatives in order to establish themselves.</p>
                HTML,
        ]);

        $this->published('program', 'agriculture-and-livelihoods', [
            'title' => 'Agriculture & Livelihoods',
            'excerpt' => 'Making agricultural production a credible livelihood for young people through co-operative farming and agribusiness.',
            'source' => $source,
            'position' => 4,
            'body' => <<<'HTML'
                <p>Young people commonly avoid agricultural production, and the reasons are practical rather than attitudinal: no land, no capital, no skills, no encouragement, and a widespread tendency to look down on smallholder farming.</p>
                <p>The co-operative form addresses several of those constraints at once. Members can access land and equipment collectively that none could obtain individually, buy inputs and sell produce at better terms, and share the risk of a poor season.</p>
                <h2>What the programme addresses</h2>
                <ul>
                    <li>Farmer support and co-operative farming arrangements.</li>
                    <li>Agribusiness and value-chain development, moving members beyond raw production.</li>
                    <li>Food security at household and community level.</li>
                    <li>Agricultural entrepreneurship as a deliberate career choice.</li>
                </ul>
                <p>The same logic applies in mining, where young people organised into co-operatives can acquire equipment that no individual operator could afford.</p>
                HTML,
        ]);

        $this->published('program', 'industrial-and-processing', [
            'title' => 'Industrial & Processing',
            'excerpt' => 'Manufacturing and processing co-operatives in furniture, textiles and leather goods, where shared plant makes production viable.',
            'source' => 'https://globalsupportfoundation.org/support-us',
            'position' => 5,
            'body' => <<<'HTML'
                <p>Manufacturing is one of the clearest cases for the co-operative form. The equipment needed to produce furniture, textiles or leather goods at a saleable standard is beyond what a young person starting out can buy, but well within reach of a group buying together.</p>
                <h2>What the programme provides</h2>
                <ul>
                    <li>Support for groups forming production and processing co-operatives.</li>
                    <li>Advice on shared premises, plant and equipment, and on the arrangements that govern their use.</li>
                    <li>Training in production planning, costing and quality standards.</li>
                    <li>Help with market access, so that output reaches buyers at a workable price.</li>
                </ul>
                <h2>Who it is for</h2>
                <p>Young people with a trade or craft skill who cannot individually finance the equipment their work requires, and existing artisan groups seeking to formalise and produce at scale.</p>
                HTML,
        ]);

        $this->published('program', 'mining-and-extraction', [
            'title' => 'Mining & Extraction',
            'excerpt' => 'Co-operative access to extraction equipment and markets, so that small-scale miners can work safely and sell on fair terms.',
            'source' => 'https://globalsupportfoundation.org/support-us',
            'position' => 7,
            'body' => <<<'HTML'
                <p>Small-scale extraction is among the hardest sectors for an individual to enter legitimately. The equipment is expensive, the licensing is complex, and a lone operator selling to a single buyer has no leverage over the price.</p>
                <h2>What the programme provides</h2>
                <ul>
                    <li>Support for groups forming extraction and processing co-operatives.</li>
                    <li>Advice on shared equipment, and on the arrangements that govern its use and maintenance.</li>
                    <li>Guidance on licensing, compliance and the safety obligations that come with the work.</li>
                    <li>Help with market access, so that output is sold on terms members have some say over.</li>
                </ul>
                <h2>Who it is for</h2>
                <p>Young people already working in small-scale extraction without a legal structure, and groups seeking to formalise, work more safely and trade together.</p>
                HTML,
        ]);

        $this->published('program', 'professional-services', [
            'title' => 'Professional Services',
            'excerpt' => 'Practice co-operatives and professional networks for young graduates entering engineering, health, law and the other professions.',
            'source' => 'https://globalsupportfoundation.org/support-us',
            'position' => 6,
            'body' => <<<'HTML'
                <p>A newly qualified engineer, doctor or lawyer faces the same problem as any other person starting out: the cost of establishing a practice, and no network to bring the first clients. Graduates leave training qualified and then wait years for work that uses it.</p>
                <h2>What the programme provides</h2>
                <ul>
                    <li>Support for groups of graduates forming a shared practice or service co-operative.</li>
                    <li>Networking between members, so that referrals and joint work become possible.</li>
                    <li>Business and management training for people trained in a profession rather than in running a firm.</li>
                    <li>Advocacy on the terms under which young professionals enter their fields.</li>
                </ul>
                <h2>Who it is for</h2>
                <p>Graduates and newly qualified professionals who are unemployed or underemployed, and small practices seeking the strength of a larger group.</p>
                HTML,
        ]);

        $this->published('program', 'technology-and-digital-inclusion', [
            'review_notes' => 'Published at GSF\'s instruction. Describes the intended area of work; add delivered activity, locations and numbers once they are confirmed.',
            'title' => 'Technology & Digital Inclusion',
            'excerpt' => 'Digital literacy and ICT skills for young entrepreneurs and the co-operatives they run.',
            'position' => 7,
            'body' => <<<'HTML'
                <p>Digital capability increasingly determines whether a small enterprise can reach customers, keep usable records and access finance. A society that cannot issue an invoice, hold a reliable set of books or be found by a buyer is competing with one hand behind its back.</p>
                <p>This programme covers digital literacy for members and officers, the practical ICT skills a trading co-operative needs, and support in choosing tools that suit the way a society actually works.</p>
                HTML,
        ]);

        $this->published('program', 'community-interventions', [
            'review_notes' => 'Published at GSF\'s instruction. Describes the groups the foundation has identified; add delivered interventions, locations and results once they are confirmed.',
            'title' => 'Community Interventions',
            'excerpt' => 'Support for groups at the margins of economic life, including people living with disabilities and retirees.',
            'position' => 8,
            'body' => <<<'HTML'
                <p>Some groups face barriers to economic participation that general enterprise support does not remove on its own. The foundation has identified people living with disabilities, retirees and children as groups requiring specific attention.</p>
                <p>For people living with disabilities, the barrier is often access rather than ability — to premises, to equipment, to a group willing to include them. For retirees, it is that a working life ends without a pension that can carry it. For children, it is that a household without income is the first thing that interrupts an education.</p>
                <p>This programme exists so that enterprise support reaches those groups deliberately, rather than assuming a general offer will find them.</p>
                HTML,
        ]);
    }

    /**
     * Editorial photography hosted on Cloudinary.
     *
     * Registered as media rather than referenced as bare URLs so delivery goes
     * through {@see MediaService}: several originals are 1-2MB PNGs, and the
     * transformation pipeline serves them as responsive variants of around
     * 70KB in whatever format the requesting browser supports.
     *
     * An image is attached only where the record does not already have one, so
     * a picture chosen later in the CMS is never overwritten by a reseed.
     */
    private function contentImages(): void
    {
        $images = [
            'program' => [
                'grassroots-entrepreneurship' => [
                    'path' => 'image/upload/v1789035238/Grassroots-entrepreneurship.png',
                    'title' => 'Grassroots entrepreneurship',
                    'alt' => 'Young entrepreneurs at work in a grassroots enterprise.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'cooperative-development' => [
                    'path' => 'image/upload/v1789035233/Co-operative-development-1.jpg',
                    'title' => 'Co-operative development',
                    'alt' => 'Members of a co-operative society meeting together.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 976, 'height' => 608],
                ],
                'human-capacity-development' => [
                    'path' => 'image/upload/v1789035238/Human-capacity-development.png',
                    'title' => 'Human capacity development',
                    'alt' => 'A training session building practical skills.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'mining-and-extraction' => [
                    'path' => 'image/upload/v1789096456/Heroes-of-the-Quarry-Worksite.png',
                    'title' => 'Heroes of the quarry worksite',
                    'alt' => 'Workers at a small-scale quarry site.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1448, 'height' => 1086],
                ],
                'community-interventions' => [
                    // Already in the gallery collection; firstOrCreate reuses that
                    // record, so the photograph serves both without duplication.
                    'path' => 'image/upload/v1789056031/g13.jpg',
                    'title' => 'Community intervention',
                    'alt' => 'Community members taking part in a foundation activity.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 258, 'height' => 145],
                ],
                'technology-and-digital-inclusion' => [
                    // The same file already illustrates the digital skills project;
                    // firstOrCreate reuses that media record rather than duplicating it.
                    'path' => 'image/upload/v1789064599/Digital-Skills_for-Small-Enterprises-Workshop.png',
                    'title' => 'Digital skills workshop for small enterprises',
                    'alt' => 'Enterprise owners learning digital skills.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'agriculture-and-livelihoods' => [
                    'path' => 'image/upload/v1789035565/agriculture_Livelihoods.png',
                    'title' => 'Agriculture and livelihoods',
                    'alt' => 'Agricultural work supporting rural livelihoods.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'professional-services' => [
                    'path' => 'image/upload/v1789039103/professional-services.png',
                    'title' => 'Professional services',
                    'alt' => 'Young professionals working together in a shared practice.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'industrial-and-processing' => [
                    'path' => 'image/upload/v1789039111/Industrial-processing.png',
                    'title' => 'Industrial and processing',
                    'alt' => 'Manufacturing and processing work in a co-operative workshop.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
            ],

            'person' => [
                'seth-yaw-berko' => [
                    'path' => 'image/upload/v1789050974/Seth-Berko.jpg',
                    'title' => 'Prof. Seth Yaw Berko',
                    'alt' => 'Portrait of Prof. Seth Yaw Berko, Project Coordinator.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 908, 'height' => 1008],
                ],
                'nwobodo-nkiruka-angela' => [
                    'path' => 'image/upload/v1789050972/Nwobodo-Angela.jpg',
                    'title' => 'Mrs. Nwobodo Nkiruka Angela',
                    'alt' => 'Portrait of Mrs. Nwobodo Nkiruka Angela, Senior Technical Officer.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 637, 'height' => 1080],
                ],
                'geoffrey-usir-okpo' => [
                    'path' => 'image/upload/v1789050972/Geoffrey-Usir.jpg',
                    'title' => 'Mr. Geoffrey Usir Okpo',
                    'alt' => 'Portrait of Mr. Geoffrey Usir Okpo, General Manager.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 904, 'height' => 1012],
                ],
                'akilo-franca' => [
                    'path' => 'image/upload/v1789050971/Akilo-Franca.jpg',
                    'title' => 'Ms. Akilo Franca N.',
                    'alt' => 'Portrait of Ms. Akilo Franca N., Admin Manager.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 908, 'height' => 1004],
                ],
                'golden-chudi-anikwe' => [
                    'path' => 'image/upload/v1789050448/gsf-founder.jpg',
                    'title' => 'Golden Chudi Anikwe',
                    'alt' => 'Portrait of Golden Chudi Anikwe, Executive Director.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 908, 'height' => 1004],
                ],
            ],

            'project' => [
                'rivers-youth-co-operative-formation' => [
                    'path' => 'image/upload/v1789064607/Rivers-Youth_Co-Operative-Workshop.png',
                    'title' => 'Rivers youth co-operative workshop',
                    'alt' => 'Young people at a co-operative formation workshop.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'business-management-foundation-training' => [
                    'path' => 'image/upload/v1789064608/Business-mgt-Training_Workshop.png',
                    'title' => 'Business management training workshop',
                    'alt' => 'A business management training session in progress.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'co-operative-farming-and-input-access' => [
                    'path' => 'image/upload/v1789064605/Hands-On-Agricultural-Training-Workshop.png',
                    'title' => 'Hands-on agricultural training workshop',
                    'alt' => 'Farmers taking part in hands-on agricultural training.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'digital-skills-for-small-enterprises' => [
                    'path' => 'image/upload/v1789064599/Digital-Skills_for-Small-Enterprises-Workshop.png',
                    'title' => 'Digital skills workshop for small enterprises',
                    'alt' => 'Enterprise owners learning digital skills.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'shared-production-workshop' => [
                    'path' => 'image/upload/v1789064606/Shared-Production-Workshop.png',
                    'title' => 'Shared production workshop',
                    'alt' => 'Producers working with shared machinery in a co-operative workshop.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
                'inclusive-enterprise-for-people-living-with-disabilities' => [
                    'path' => 'image/upload/v1789064599/Inclusive-Artisan-Workshop-Collaboration.png',
                    'title' => 'Inclusive artisan workshop collaboration',
                    'alt' => 'Artisans collaborating in an accessible workshop.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1448, 'height' => 1086],
                ],
            ],

            'post' => [
                'the-seven-co-operative-principles' => [
                    'path' => 'image/upload/v1789055642/The-Seven-Co-operative-Principles-Together.png',
                    'title' => 'The seven co-operative principles',
                    'alt' => 'Members of a co-operative society meeting together.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'registering-a-co-operative-what-to-have-ready' => [
                    'path' => 'image/upload/v1789055647/Cooperative-Registration.png',
                    'title' => 'Co-operative registration',
                    'alt' => 'Paperwork being prepared for a co-operative society registration.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'shared-equipment-the-clearest-case-for-co-operating' => [
                    'path' => 'image/upload/v1789055640/Farmers-Cooperative-Machinery-Day.png',
                    'title' => 'Farmers co-operative machinery day',
                    'alt' => 'Farmers gathered around shared agricultural machinery.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'bookkeeping-for-a-new-co-operative' => [
                    'path' => 'image/upload/v1789055644/Community-Cooperative-Bookkeeping-Workshop.png',
                    'title' => 'Community co-operative bookkeeping workshop',
                    'alt' => 'A bookkeeping workshop for co-operative members.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'what-a-business-plan-has-to-answer' => [
                    'path' => 'image/upload/v1789055643/Cooperative-Business-Planning-Workshop.png',
                    'title' => 'Co-operative business planning workshop',
                    'alt' => 'A business planning session in progress.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1536, 'height' => 1024],
                ],
                'governance-mistakes-that-break-young-co-operatives' => [
                    'path' => 'image/upload/v1789055631/Governance-Workshop_for-Stronger_Co-operatives.png',
                    'title' => 'Governance workshop for stronger co-operatives',
                    'alt' => 'A governance workshop for co-operative officers.',
                    'mime' => 'image/png',
                    'variants' => ['width' => 1672, 'height' => 941],
                ],
            ],

            'award' => [
                'entrepreneurs-ireland-2007' => [
                    'path' => 'image/upload/v1789051890/2007.jpg',
                    'title' => 'Entrepreneurs Ireland Awards 2007 certificate',
                    'alt' => 'Certificate: Entrepreneurs Ireland Awards 2007, presented to Golden Anikwe.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 600, 'height' => 592],
                ],
                'social-entrepreneurs-ireland-2008' => [
                    'path' => 'image/upload/v1789051891/2008.jpg',
                    'title' => 'Social Entrepreneurs Ireland 2008 Awards certificate',
                    'alt' => 'Certificate: Social Entrepreneurs Ireland 2008 Awards, Level 2, presented to Golden Anikwe.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 600, 'height' => 600],
                ],
                'cecop-cicopa-associate-membership' => [
                    'path' => 'image/upload/v1789051890/2006.jpg',
                    'title' => 'CECOP-CICOPA Europe associate membership letter',
                    'alt' => 'Letter from CECOP-CICOPA Europe confirming associate membership, dated 6 June 2012.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 600, 'height' => 600],
                ],
            ],

            'story' => [
                'baffles-entertainment-co-operative' => [
                    'path' => 'image/upload/v1789038172/Baffles-cop.jpg',
                    'title' => 'Baffles Entertainment and Event Management Co-operative Society',
                    'alt' => 'Members of Baffles Entertainment and Event Management Co-operative Society.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 350, 'height' => 233],
                ],
                'his-and-hers-recreational-co-operative' => [
                    'path' => 'image/upload/v1789041240/His-and-Hers_Recreational-Co-operative-1.jpg',
                    'title' => 'His and Hers Recreational Co-operative Society',
                    'alt' => 'The His and Hers recreational centre in Galway.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 1697, 'height' => 972],
                ],
                'little-angels-childcare-co-operative' => [
                    'path' => 'image/upload/v1789044716/Little-Angels-Childcare-Co-operativ2.jpg',
                    'title' => 'Little Angels Childcare Co-operative Society',
                    'alt' => 'Members of the Nwannedinamba community behind the Little Angels childcare co-operative.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 1697, 'height' => 1057],
                ],
                'amazing-ore-ofe-co-operative' => [
                    'path' => 'image/upload/v1789040254/Amazing-_Ore_Ofe_-Co-operative.jpg',
                    'title' => 'Amazing (Ore Ofe) Co-operative Society',
                    'alt' => 'Fashion produced by the Amazing (Ore Ofe) Co-operative Society.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 1600, 'height' => 900],
                ],
                'meh-hair-and-beauty-co-operative' => [
                    'path' => 'image/upload/v1789038172/MEH-Hair-and-cop.jpg',
                    'title' => 'MEH Hair and Beauty Co-operative Society',
                    'alt' => 'Members of MEH Hair and Beauty Co-operative Society.',
                    'mime' => 'image/jpeg',
                    'variants' => ['width' => 840, 'height' => 560],
                ],
            ],
        ];

        // Not attached to any content record: these are rendered directly by
        // their templates, which look them up by path.
        $standalone = [
            [
                'path' => 'image/upload/v1789044981/stronger-together-brighter-futures-posible.png',
                'title' => 'Stronger together, brighter futures',
                'mime' => 'image/png',
                'alt' => 'Members of a co-operative working together towards a shared enterprise.',
                'variants' => ['width' => 1672, 'height' => 941],
            ],
            [
                'path' => 'image/upload/v1789044992/build-support.jpg',
                'title' => 'Building support together',
                'mime' => 'image/jpeg',
                'alt' => '',
                'variants' => ['width' => 4716, 'height' => 3144],
            ],
            [
                'path' => 'image/upload/v1789047718/unemployed-Youths-1.png',
                'title' => 'Unemployed youths',
                'mime' => 'image/png',
                'alt' => 'Young men and women seeking work.',
                'variants' => ['width' => 1536, 'height' => 1024],
            ],
            [
                'path' => 'image/upload/v1789047715/disabilities-1.png',
                'title' => 'People living with disabilities',
                'mime' => 'image/png',
                'alt' => 'A person living with a disability at work.',
                'variants' => ['width' => 1448, 'height' => 1086],
            ],
            [
                'path' => 'image/upload/v1789047715/Retirees_Senior_Citizens.png',
                'title' => 'Retirees and senior citizens',
                'mime' => 'image/png',
                'alt' => 'Older people continuing in economic life.',
                'variants' => ['width' => 1448, 'height' => 1086],
            ],
            [
                'path' => 'image/upload/v1789049558/bb2.jpg',
                'title' => 'Aiming at the target',
                'mime' => 'image/jpeg',
                'alt' => '',
                'variants' => ['width' => 266, 'height' => 339],
            ],
            [
                'path' => 'image/upload/v1789047705/ngo.jpg',
                'title' => 'Reaching communities at the grassroots',
                'mime' => 'image/jpeg',
                'alt' => '',
                'variants' => ['width' => 3674, 'height' => 2450],
            ],
            [
                'path' => 'image/upload/v1789045974/Established-Businesses_Startups.png',
                'title' => 'Established businesses and startups',
                'mime' => 'image/png',
                'alt' => 'Owners of an established small business at work.',
                'variants' => ['width' => 1536, 'height' => 1024],
            ],
        ];

        foreach ($standalone as $attributes) {
            Media::firstOrCreate(
                ['path' => $attributes['path']],
                [
                    'title' => $attributes['title'],
                    'disk' => 'cloudinary',
                    'mime' => $attributes['mime'],
                    'size' => 0,
                    'alt' => $attributes['alt'],
                    'variants' => $attributes['variants'],
                    'approved' => true,
                ],
            );
        }

        foreach ($images as $type => $entries) {
            foreach ($entries as $slug => $attributes) {
                $media = Media::firstOrCreate(
                    ['path' => $attributes['path']],
                    [
                        'title' => $attributes['title'],
                        'disk' => 'cloudinary',
                        'mime' => $attributes['mime'],
                        'size' => 0,
                        'alt' => $attributes['alt'],
                        'variants' => $attributes['variants'],
                        'approved' => true,
                    ],
                );

                Content::query()
                    ->where('type', $type)
                    ->where('slug', $slug)
                    ->whereNull('image_id')
                    ->update(['image_id' => $media->id]);
            }
        }
    }

    private function people(): void
    {
        $this->published('person', 'golden-chudi-anikwe', [
            'title' => 'Golden Chudi Anikwe',
            'category' => 'Executive Director',
            'excerpt' => 'Founder and Executive Director. A co-operative practitioner with international experience in Nigeria and Ireland.',
            'source' => self::BACKUP.'view_team.php',
            'featured' => true,
            'position' => 1,
            'details' => [
                'role' => 'Executive Director',
                'linkedin' => 'https://ie.linkedin.com/in/golden-anikwe-83451a18',
                'portrait_source' => 'backup/assets/images/t1.jpg',
            ],
            'review_notes' => 'Title shown as recorded in the organisational source: Executive Director. Confirm before adding any additional title. His doctoral research is described in the source as an interest and work in progress — it must not be presented as a completed award.',
            'body' => <<<'HTML'
                <p>Golden Anikwe's interest in education and social enterprise began at home. His father, Chief Robert Anikwe, was a retired Deputy Director of Co-operatives in Enugu State; his mother, Mrs Idah Nwamaka Anikwe, a retired school headmistress.</p>
                <p>After completing secondary school in 1984 he taught English Language and Economics at Allins Institute of Stenography in Agbani, Enugu State. He then studied co-operatives at the Federal Co-operative College, Oji River, and at Enugu State University of Science and Technology, where he obtained a B.Sc. in Co-operatives and Rural Development.</p>
                <p>He worked as a supervisor with the former Anambra Co-operative Federation (ACFED Limited) between 1988 and 1989, and later in the Internal Audit Department of the Federal Ministry of Police Affairs between 1990 and 1996.</p>
                <p>In 1997 he moved to Ireland, where he continued to develop his understanding of social enterprise: an M.Sc. in Co-operatives and Social Enterprise at the National University of Ireland, Cork; a Professional Diploma in Legal Studies; and three years of study towards a Bachelor of Laws at Griffith College Dublin — a reflection of a sustained interest in equality and social justice.</p>
                <h2>Leadership philosophy</h2>
                <p>With a background as both a co-operative promoter and practitioner, and as an advocate for equality and social justice, he came to regard co-operatives as a genuine platform through which cultural, social, economic and political emancipation can be realised. Giving community members the opportunity to contribute directly to the economic development of their own locality, he argues, is what actually enlarges their capacity to participate in society.</p>
                <p>That conviction — a multicultural Ireland in which ethnic communities could unlock their potential by pooling skills and resources into community-led businesses — led to the creation of Co-operative Support Services Limited in Ireland. CSS provided nationwide business support to co-operatives, promoted socially sustainable business practice, raised awareness of the co-operative movement and delivered community education on social enterprise.</p>
                <h2>Founding Global Support Foundation</h2>
                <p>During a visit to Nigeria in 2015 he founded Global Support Foundation for Grassroots Entrepreneurship. The foundation's mandate also shaped his doctoral research interest, on self-help groups and international development agencies in poverty alleviation for marginalised and less-privileged people in Nigeria between 2010 and 2018.</p>
                <p>A former committee member of the Society for Co-operative Studies in Ireland and a member of the Irish Institute of Legal Executives, he has received awards and recognition in Ireland for his work in effecting social change.</p>
                HTML,
        ]);

        $this->published('person', 'seth-yaw-berko', [
            'title' => 'Prof. Seth Yaw Berko',
            'category' => 'Project Coordinator',
            'excerpt' => 'Project Coordinator. An academic and consultant in co-operative economics and management with more than four decades of experience.',
            'source' => self::BACKUP.'view_team.php',
            'position' => 2,
            'details' => ['role' => 'Project Coordinator'],
            'review_notes' => 'Confirm current appointment and preferred short biography with GSF. Achievements listed here were earned at other institutions and must not be attributed to GSF.',
            'body' => <<<'HTML'
                <p>Professor Seth Yaw Berko studied at Philipps University, Marburg, Germany, at the Institute for Co-operation in Developing Countries and the Department of Sociology. His academic field is development economics, specialising in co-operative economics and management, and ethno-sociology.</p>
                <p>His particular interests include co-operative law, co-operative development policy, producer and marketing co-operatives, housing co-operatives, savings and credit co-operatives, co-operatives and entrepreneurship, and the design of academic programmes in co-operative science.</p>
                <h2>Academic career</h2>
                <p>He has lectured at the Institute of Management and Technology, Enugu (1977–1988); Anambra State University of Technology, Enugu (1988–1991); Enugu State University of Science and Technology (1991–2020); and the University of Lagos. He has headed departments of Co-operative Economics and Management and of Co-operatives and Rural Development at IMT, ASUTECH and ESUT, and has served as external examiner at universities and polytechnics across Nigeria between 1982 and 2019.</p>
                <h2>Policy and consultancy</h2>
                <p>He served the National Board for Technical Education in Kaduna on committees preparing and reviewing programmes in co-operative economics and management, and on accreditation panels, from 1982 to 2018. As a consultant to Nigeria's Federal Department of Co-operatives between 1998 and 2012 he sat on the panel that prepared the national Co-operative Development Policy, the panel on the revitalisation of Nigerian co-operatives, and the panel that prepared a database of the Nigerian co-operative movement.</p>
                <p>Outside Nigeria he prepared the B.Sc. and M.Sc. programmes in Co-operative Economics and Organisation for the African University for Co-operative Development in Cotonou, Benin, served on its Ph.D. programme panel, and supervised M.Sc. dissertations there. He has published textbooks, monographs, journal articles, book chapters and seminar papers.</p>
                HTML,
        ]);

        $this->published('person', 'nwobodo-nkiruka-angela', [
            'title' => 'Mrs. Nwobodo Nkiruka Angela',
            'category' => 'Senior Technical Officer',
            'excerpt' => 'Senior Technical Officer. A programme and operations coordinator with over a decade of experience in multi-site programme delivery and safeguarding compliance.',
            'source' => self::BACKUP.'view_team.php',
            'position' => 3,
            'details' => ['role' => 'Senior Technical Officer'],
            'review_notes' => 'Confirm current appointment with GSF. The donor-funded programmes listed were delivered at CENGOS and must not be presented as GSF projects.',
            'body' => <<<'HTML'
                <p>Mrs Nwobodo Nkiruka Angela is a programme and operations coordinator with more than a decade of professional experience spanning operations management, multi-site programme delivery, youth development, educational programming, safeguarding compliance and stakeholder engagement.</p>
                <p>Her competencies include operational planning, monitoring and evaluation, event logistics, team supervision, financial documentation and donor reporting. She has coordinated school- and community-based programmes across geographically dispersed locations, with a consistent focus on quality assurance, data management and adherence to organisational policy and regulatory requirements.</p>
                <h2>Professional experience</h2>
                <p><strong>State Project Coordinator, CENGOS (Coalition of Eastern NGOs), December 2015 – September 2022.</strong> She coordinated the daily delivery of community and youth-focused programmes across multiple locations; developed work plans, operational trackers, activity calendars and reporting templates; and supervised project officers, finance officers and community educators.</p>
                <p>She conducted routine monitoring visits and quality assurance checks, ensured safeguarding and child protection procedures were applied across all programme activity, and escalated safeguarding and compliance concerns through the proper reporting channels. She prepared weekly, monthly and quarterly operational reports and maintained documentation systems covering attendance registers, training reports and partner records.</p>
                <p>She coordinated logistics for training, workshops and stakeholder forums — venues, transport, vendors and participant management — and worked with government ministries, departments and agencies, schools, youth stakeholders and community leaders. She managed donor-funded projects for OSIWA, the World Bank, Global Fund and Amplify Change, meeting reporting timelines and financial documentation standards.</p>
                HTML,
        ]);
    }

    /**
     * Team members recorded on the organisation's current website rather than in
     * the backup, so their source is the live page they were taken from.
     */
    private function currentTeam(): void
    {
        $source = 'https://globalsupportfoundation.org/our-team';

        $this->published('person', 'geoffrey-usir-okpo', [
            'title' => 'Mr. Geoffrey Usir Okpo',
            'category' => 'General Manager',
            'excerpt' => 'General Manager. A computer scientist who has built enterprise systems for private and public sector clients since 2008.',
            'source' => $source,
            'position' => 4,
            'details' => ['role' => 'General Manager'],
            'body' => <<<'HTML'
                <p>Geoffrey Usir Okpo holds a B.Sc. in Computer Science from the University of Port Harcourt, together with professional certifications in database administration (OCA and OCP) and systems administration (MCITP).</p>
                <p>His work covers systems analysis and the development of web and mobile applications. Since 2008 he has built enterprise solutions for clients in both the private sector and government.</p>
                <p>He served as Head Technical Instructor under the NDDC Graduate Skill Acquisition Programme, where he trained developers for regional competition, before taking up the position of General Manager at the foundation.</p>
                HTML,
        ]);

        $this->published('person', 'akilo-franca', [
            'title' => 'Ms. Akilo Franca N.',
            'category' => 'Admin Manager',
            'excerpt' => 'Administrative Manager. An educator by training, with teaching experience across secondary and continuing education.',
            'source' => $source,
            'position' => 5,
            'details' => ['role' => 'Admin Manager'],
            'body' => <<<'HTML'
                <p>Akilo Franca N. holds a Bachelor of Education in English Language and Literature from the University of Nigeria, Nsukka. She completed the National Youth Service Corps scheme and holds a Diploma in Secretarial Studies and Information Technology.</p>
                <p>Her teaching experience spans Rock Foundation College, Federal Government College Onitsha, and the Delta State Institute of Continuing Education.</p>
                <p>She was appointed Administrative Manager at the foundation, where she is responsible for the day-to-day running of the office.</p>
                HTML,
        ]);
    }

    /**
     * Awards and recognition, transcribed from the certificate scans themselves.
     *
     * All three predate the Nigerian foundation and two were made to the founder
     * personally rather than to an organisation, so each record says plainly who
     * received what, from whom, and when.
     */
    private function awards(): void
    {
        $this->published('award', 'entrepreneurs-ireland-2007', [
            'title' => 'Entrepreneurs Ireland Awards 2007',
            'category' => 'Ireland',
            'excerpt' => 'Level One Awardee, presented to Golden Anikwe in recognition of his visionary, innovative and entrepreneurial approach to effecting social change in Ireland.',
            'source' => 'Certificate scan: Entrepreneurs Ireland Awards 2007, signed Sean Coughlan, Managing Director.',
            'position' => 1,
            'details' => [
                'year' => '2007',
                'awarding_institution' => 'Entrepreneurs Ireland',
                'recipient' => 'Golden Anikwe',
                'level' => 'Level One Awardee',
            ],
            'body' => <<<'HTML'
                <p>Presented to Golden Anikwe as a <strong>Level One Awardee</strong> of the Entrepreneurs Ireland Awards 2007.</p>
                <blockquote>In recognition of his visionary, innovative and entrepreneurial approach to effecting social change in Ireland.</blockquote>
                <p>Signed by Sean Coughlan, Managing Director. The award was made to the founder personally, during the years he worked in Ireland and before Global Support Foundation was established in Nigeria in 2015.</p>
                HTML,
        ]);

        $this->published('award', 'social-entrepreneurs-ireland-2008', [
            'title' => 'Social Entrepreneurs Ireland Awards 2008',
            'category' => 'Ireland',
            'excerpt' => 'A 2008 Level 2 award presented to Golden Anikwe in recognition of his outstanding contribution to igniting social change in Ireland.',
            'source' => 'Certificate scan: Social Entrepreneurs Ireland 2008 Awards, signed Sean Coughlan, Chief Executive.',
            'position' => 2,
            'details' => [
                'year' => '2008',
                'awarding_institution' => 'Social Entrepreneurs Ireland',
                'recipient' => 'Golden Anikwe',
                'level' => 'Level 2',
            ],
            'body' => <<<'HTML'
                <p>Social Entrepreneurs Ireland presented a <strong>2008 Level 2 award</strong> to Golden Anikwe.</p>
                <blockquote>In recognition of his outstanding contribution to igniting social change in Ireland.</blockquote>
                <p>Signed by Sean Coughlan, Chief Executive. As with the 2007 award, this recognition was made to the founder personally rather than to the foundation.</p>
                HTML,
        ]);

        $this->published('award', 'cecop-cicopa-associate-membership', [
            'title' => 'CECOP-CICOPA Europe associate membership',
            'category' => 'Europe',
            'excerpt' => 'Co-operative Support Services for Ethnic Minority Entrepreneurs Limited was approved as an Associate Member of CECOP-CICOPA Europe in May 2012.',
            'source' => 'Letter from Rossana Rimelli, President, CECOP-CICOPA Europe, Brussels, 6 June 2012.',
            'position' => 3,
            'details' => [
                'year' => '2012',
                'awarding_institution' => 'CECOP-CICOPA Europe',
                'recipient' => 'Co-operative Support Services for Ethnic Minority Entrepreneurs Limited (CSS)',
            ],
            'body' => <<<'HTML'
                <p>On 9 May 2012 the CECOP Executive Committee approved the affiliation of <strong>Co-operative Support Services for Ethnic Minority Entrepreneurs Limited (CSS)</strong>, Dublin, as an Associate Member of CECOP-CICOPA Europe — the European Confederation of Worker Co-operatives, Social Co-operatives and Social and Participative Enterprises.</p>
                <p>The confirmation was sent from Brussels on 6 June 2012 by Rossana Rimelli, President.</p>
                <p>CSS was the founder's Ireland-based organisation, and the affiliation belongs to that period of the work rather than to the Nigerian foundation.</p>
                HTML,
        ]);
    }

    private function policies(): void
    {
        $policies = [
            'safeguarding-policy' => 'Safeguarding Policy',
            'child-protection-policy' => 'Child Protection Policy',
            'anti-fraud-and-anti-corruption-policy' => 'Anti-Fraud & Anti-Corruption Policy',
            'conflict-of-interest-policy' => 'Conflict of Interest Policy',
            'whistleblowing-policy' => 'Whistleblowing Policy',
            'procurement-policy' => 'Procurement Policy',
            'data-protection-policy' => 'Data Protection Policy',
            'code-of-conduct' => 'Code of Conduct',
            'complaints-and-feedback-policy' => 'Complaints & Feedback Policy',
            'gender-and-inclusion-policy' => 'Gender & Inclusion Policy',
        ];

        // Published at GSF's instruction. Each page states on its face that the
        // document is not yet adopted, so the listing shows the commitment and
        // its status rather than implying an approved policy exists.
        $note = 'Published as a status page, not as a policy. The body must be replaced with the approved text and approval date once the board has adopted the document.';

        $position = 0;

        foreach ($policies as $slug => $title) {
            $this->published('policy', $slug, [
                'title' => $title,
                'excerpt' => 'Adoption in progress — this document has not yet been approved by the board.',
                'position' => $position++,
                'review_notes' => $note,
                'details' => ['approved_on' => null, 'review_due' => null],
                'body' => '<p><strong>Not yet adopted.</strong> Global Support Foundation intends to publish an approved '.$title.' here. This page exists so that the document has a permanent home once the board has approved it; it does not represent organisational policy, and nothing on it should be relied upon as such.</p><p>If you need to know the foundation\'s current position on this subject, please <a href="/contact-us">ask us directly</a>.</p>',
            ]);
        }
    }

    private function partners(): void
    {
        $this->draft('partner', 'partner-placeholder', 'No partnership agreement was evidenced in the backup. The legacy site named CBN, the World Bank, the UN and the AU as institutions GSF hoped to work with, not as partners. Create a partner record only where a signed agreement exists.', [
            'title' => 'Partner record template',
            'excerpt' => 'Structure for recording a confirmed partner organisation.',
            'details' => ['website' => null, 'partnership_type' => null, 'since' => null],
            'body' => '<p>This template exists so that administrators can add partners once agreements are in place. Each partner record should carry the organisation name, logo, website, the nature of the partnership and the date it began.</p>',
        ]);
    }
}
