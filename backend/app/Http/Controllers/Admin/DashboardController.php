<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class DashboardController extends Controller
{
    private const ACTIVITY_MONTHS = 12;

    public function __invoke(Request $request): View
    {
        return view('admin.dashboard', [
            'admin' => $request->user(),
            'stats' => $this->stats(),
            'activity' => $this->publishingActivity(),
            'recentPosts' => Post::with('category')->latest('updated_at')->limit(6)->get(),
            'upcomingPosts' => Post::scheduled()->oldest('published_at')->limit(4)->get(),
            'topCategories' => Category::query()
                ->withCount(['posts' => fn (Builder $query) => $query->published()])
                ->orderByDesc('posts_count')
                ->orderBy('name')
                ->limit(5)
                ->get(),
        ]);
    }

    /**
     * @return array{total: int, published: int, scheduled: int, drafts: int, published_this_month: int, categories: int}
     */
    private function stats(): array
    {
        return [
            'total' => Post::count(),
            'published' => Post::published()->count(),
            'scheduled' => Post::scheduled()->count(),
            'drafts' => Post::draft()->count(),
            'published_this_month' => Post::published()->where('published_at', '>=', now()->startOfMonth())->count(),
            'categories' => Category::count(),
        ];
    }

    /**
     * Live posts per month over the last year, oldest month first. Counted in
     * PHP rather than SQL so it works the same on SQLite, MySQL and PostgreSQL.
     *
     * @return Collection<int, array{label: string, month: string, count: int}>
     */
    private function publishingActivity(): Collection
    {
        $firstMonth = now()->startOfMonth()->subMonths(self::ACTIVITY_MONTHS - 1);

        $postsPerMonth = Post::published()
            ->where('published_at', '>=', $firstMonth)
            ->pluck('published_at')
            ->countBy(fn (CarbonInterface $publishedAt): string => $publishedAt->format('Y-m'));

        return collect(range(0, self::ACTIVITY_MONTHS - 1))->map(function (int $offset) use ($firstMonth, $postsPerMonth): array {
            $month = $firstMonth->copy()->addMonths($offset);

            return [
                'label' => $month->format('M'),
                'month' => $month->format('F Y'),
                'count' => $postsPerMonth->get($month->format('Y-m'), 0),
            ];
        });
    }
}
