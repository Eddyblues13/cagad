<?php

namespace Database\Seeders;

use App\Enums\AdminRole;
use App\Enums\PostStatus;
use App\Models\Admin;
use App\Models\Category;
use App\Models\Post;
use Illuminate\Database\Seeder;

class PostSeeder extends Seeder
{
    /**
     * Sample posts: "days" is when each goes live relative to today (negative
     * is the past, positive is scheduled), and null means a draft.
     *
     * @var list<array{title: string, category: string, author: string, days: int|null, excerpt: string|null, body: string|null}>
     */
    private const POSTS = [
        [
            'title' => 'Why Open Budgets Matter for Local Government',
            'category' => 'Accountability',
            'author' => 'CAGAD Research Team',
            'days' => -3,
            'excerpt' => 'When councils publish what they plan to spend and what they actually spent, residents can follow the money, and public services tend to improve.',
            'body' => <<<'HTML'
                <p>Local governments deliver many of the services people rely on every day: primary healthcare, roads, markets and water. Yet budget documents at this level are often hard to find, published late, or written in a way only specialists can follow.</p>
                <h2>What an open budget looks like</h2>
                <p>An open budget is more than a PDF on a website. It means publishing the proposed budget before it is approved, the approved budget once it is signed, and regular reports on what was actually spent, in formats residents can read.</p>
                <ul>
                    <li>A short citizens’ guide that explains priorities in plain language.</li>
                    <li>Quarterly reports that compare planned and actual spending.</li>
                    <li>A clear contact point for questions and feedback.</li>
                </ul>
                <h2>Why it matters</h2>
                <p>Transparency alone does not fix public finance, but it gives communities, journalists and oversight bodies the information they need to ask the right questions. Over time, that scrutiny encourages better planning and more careful spending.</p>
                <blockquote>Budgets are where public promises meet public money.</blockquote>
                <p>Small steps, such as publishing documents on time and in plain language, are a good place to start.</p>
                HTML,
        ],
        [
            'title' => 'Five Habits of Effective Public Institutions',
            'category' => 'Governance',
            'author' => 'CAGAD Editorial Team',
            'days' => -9,
            'excerpt' => 'Strong institutions are built on everyday habits: clear mandates, open records, merit-based hiring, feedback loops and steady leadership.',
            'body' => <<<'HTML'
                <p>Institutions are only as effective as the routines that sustain them. Across very different settings, the public bodies that serve people well tend to share a handful of habits.</p>
                <h2>1. Clear mandates</h2>
                <p>Staff and the public know what the institution is responsible for, and what it is not.</p>
                <h2>2. Open records</h2>
                <p>Decisions, contracts and performance data are documented and accessible.</p>
                <h2>3. Merit-based hiring</h2>
                <p>Appointments and promotions follow transparent criteria.</p>
                <h2>4. Feedback loops</h2>
                <p>Complaints and suggestions are recorded, answered and used to improve services.</p>
                <h2>5. Steady leadership</h2>
                <p>Priorities survive changes in personnel because they are written down and shared.</p>
                <p>None of these habits needs a large budget. They need consistency, and the willingness to measure progress honestly.</p>
                HTML,
        ],
        [
            'title' => 'Putting Community Voices at the Centre of Peacebuilding',
            'category' => 'Peacebuilding',
            'author' => 'CAGAD Research Team',
            'days' => -18,
            'excerpt' => 'Lasting peace is rarely negotiated far away. It is built locally, by people who understand the history and relationships behind a conflict.',
            'body' => <<<'HTML'
                <p>Peace agreements matter, but they are only the beginning. Whether peace holds depends on what happens in markets, schools, places of worship and town halls long after the signatures are dry.</p>
                <h2>Local knowledge</h2>
                <p>Community leaders, women’s groups and young people often understand the triggers of conflict better than outside actors. Involving them early leads to solutions that fit local realities.</p>
                <h2>Trust takes time</h2>
                <p>Dialogue forums, joint projects and early-warning networks all depend on trust, and trust is built through regular, reliable contact rather than one-off events.</p>
                <blockquote>Peace is a process that communities own, not a product delivered to them.</blockquote>
                <p>Supporting these local structures is one of the most cost-effective investments in long-term stability.</p>
                HTML,
        ],
        [
            'title' => 'Strengthening Citizen Participation Between Elections',
            'category' => 'Democracy',
            'author' => 'CAGAD Policy Unit',
            'days' => -33,
            'excerpt' => 'Democracy does not pause between polling days. Town halls, petitions and public hearings keep citizens involved in the decisions that affect them.',
            'body' => <<<'HTML'
                <p>Elections are the most visible part of democracy, but they happen only every few years. What citizens do in between often determines how responsive their representatives are.</p>
                <h2>Ways to stay involved</h2>
                <ul>
                    <li>Attend constituency briefings and town hall meetings.</li>
                    <li>Follow committee hearings and submit memoranda on bills.</li>
                    <li>Use freedom of information requests to understand public decisions.</li>
                    <li>Join or support community associations that engage local officials.</li>
                </ul>
                <p>Participation works best when it is organised and informed. Groups that come prepared with evidence and clear requests are more likely to be heard.</p>
                HTML,
        ],
        [
            'title' => 'The Rule of Law as a Foundation for Lasting Security',
            'category' => 'Security',
            'author' => 'CAGAD Policy Unit',
            'days' => -47,
            'excerpt' => 'Security is strongest where people trust that laws are applied fairly, disputes are settled peacefully and rights are protected.',
            'body' => <<<'HTML'
                <p>Security is often discussed in terms of personnel and equipment. Those matter, but they are not enough. Communities are safest where people believe the law protects them and applies equally to everyone.</p>
                <h2>Fair and accessible justice</h2>
                <p>When courts are slow or out of reach, people turn to other ways of settling disputes, some of them violent. Faster, more accessible justice reduces that pressure.</p>
                <h2>Accountable security services</h2>
                <p>Clear rules of engagement, independent oversight and channels for complaints build public confidence and cooperation.</p>
                <p>Investing in the rule of law is therefore an investment in security itself.</p>
                HTML,
        ],
        [
            'title' => 'Measuring What Matters in Public Service Delivery',
            'category' => 'Sustainable Development',
            'author' => 'CAGAD Research Team',
            'days' => -68,
            'excerpt' => 'Counting projects launched is easy. Measuring whether services reach people, and whether they work, is what drives real progress.',
            'body' => <<<'HTML'
                <p>Public reports often focus on inputs: money allocated, buildings commissioned, staff recruited. These figures are useful, but they say little about whether services are improving for the people who use them.</p>
                <h2>From outputs to outcomes</h2>
                <p>A clinic that has been built is an output. A clinic that is staffed, stocked and used by the community is an outcome. Good measurement looks for the second.</p>
                <h2>Simple indicators, used consistently</h2>
                <ul>
                    <li>Are facilities open when they should be?</li>
                    <li>Do users rate the service as satisfactory?</li>
                    <li>Are waiting times going down?</li>
                </ul>
                <p>Tracking a few meaningful indicators over time is more valuable than publishing long lists of activities.</p>
                HTML,
        ],
        [
            'title' => 'How Freedom of Information Requests Strengthen Accountability',
            'category' => 'Accountability',
            'author' => 'CAGAD Editorial Team',
            'days' => -96,
            'excerpt' => 'Freedom of information laws give citizens a right to ask public bodies for records. Used well, they are one of the most practical accountability tools there is.',
            'body' => <<<'HTML'
                <p>Freedom of information laws give the public a legal right to request records held by public institutions. They turn transparency from a favour into an obligation.</p>
                <h2>Making a good request</h2>
                <ul>
                    <li>Be specific about the records you want and the period they cover.</li>
                    <li>Address the request to the right institution.</li>
                    <li>Keep a copy and note the date it was received.</li>
                </ul>
                <p>Even when responses are slow or incomplete, a clear paper trail helps journalists, researchers and oversight bodies follow up.</p>
                HTML,
        ],
        [
            'title' => 'Why Inclusive Leadership Leads to Better Governance',
            'category' => 'Governance',
            'author' => 'CAGAD Policy Unit',
            'days' => -128,
            'excerpt' => 'Decision-making bodies that reflect the communities they serve tend to make more balanced choices and earn more public trust.',
            'body' => <<<'HTML'
                <p>Who sits at the decision-making table shapes which problems get attention and which solutions are considered. Inclusive leadership, across gender, age and background, widens that view.</p>
                <h2>Broader perspectives</h2>
                <p>Diverse teams are more likely to spot blind spots in policies and to anticipate how decisions will affect different groups.</p>
                <h2>Greater legitimacy</h2>
                <p>When people see themselves represented in public institutions, they are more likely to trust and engage with them.</p>
                <p>Mentoring, transparent appointment processes and support for first-time candidates all help widen the pool of future leaders.</p>
                HTML,
        ],
        [
            'title' => 'Young People and Local Governance: Making Participation Count',
            'category' => 'Democracy',
            'author' => 'CAGAD Editorial Team',
            'days' => 4,
            'excerpt' => 'Young people make up a large share of the population, yet they are often missing from local decision-making. Here is how that can change.',
            'body' => <<<'HTML'
                <p>Young people bring energy, new ideas and a direct stake in the future, yet they are often absent from the forums where local decisions are made.</p>
                <h2>Opening the door</h2>
                <p>Youth advisory councils, internships in local government and consultations held at accessible times all make participation easier.</p>
                <p>The aim is not a single event, but a steady route for young people to contribute and to see their contributions taken seriously.</p>
                HTML,
        ],
        [
            'title' => 'A Practical Guide to Reading Audit Reports',
            'category' => 'Accountability',
            'author' => 'CAGAD Research Team',
            'days' => 11,
            'excerpt' => 'Audit reports are among the most valuable public documents, and among the least read. A few pointers make them far easier to use.',
            'body' => <<<'HTML'
                <p>Audit reports show whether public funds were spent as intended. They are detailed and technical, but a few pointers make them far more approachable.</p>
                <h2>Where to start</h2>
                <ul>
                    <li>Read the executive summary and the key findings first.</li>
                    <li>Look for findings that repeat from previous years.</li>
                    <li>Check the management responses to see what was promised.</li>
                </ul>
                <p>Following up on whether recommendations are carried out is where much of the value lies.</p>
                HTML,
        ],
        [
            'title' => 'Notes on Electoral Reform Priorities',
            'category' => 'Democracy',
            'author' => 'CAGAD Policy Unit',
            'days' => null,
            'excerpt' => null,
            'body' => '<p>Working notes on reform priorities: voter education, accessible polling units and transparent results. To be expanded before publishing.</p>',
        ],
        [
            'title' => 'Building Early-Warning Networks for Conflict Prevention',
            'category' => 'Peacebuilding',
            'author' => 'CAGAD Research Team',
            'days' => null,
            'excerpt' => null,
            'body' => null,
        ],
    ];

    /**
     * Sample posts for local development, so the admin panel and the React blog
     * have something to show. DatabaseSeeder only runs this locally, and posts
     * that already exist (matched by title) are skipped.
     */
    public function run(): void
    {
        $admin = Admin::where('role', AdminRole::SuperAdmin)->oldest('id')->first();
        $categoryIds = Category::pluck('id', 'name');

        foreach (self::POSTS as $index => $sample) {
            $post = Post::firstOrNew(['title' => $sample['title']]);

            if ($post->exists) {
                continue;
            }

            $publishedAt = $sample['days'] === null ? null : now()->addDays($sample['days'])->setTime(9, 0);
            $writtenAt = $publishedAt?->isPast() ? $publishedAt : now()->subHours(5 * ($index + 1));

            $post->fill([
                'category_id' => $categoryIds->get($sample['category']),
                'excerpt' => $sample['excerpt'],
                'body' => $sample['body'],
                'author_name' => $sample['author'],
                'status' => $publishedAt ? PostStatus::Published : PostStatus::Draft,
                'published_at' => $publishedAt,
            ]);
            $post->admin()->associate($admin);
            $post->created_at = $writtenAt;
            $post->updated_at = $writtenAt;
            $post->save();
        }
    }
}
