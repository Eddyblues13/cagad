@extends('layouts.admin')

@section('title', 'Dashboard')

@section('content')
    @php
        $hour = now()->hour;
        $greeting = match (true) {
            $hour < 12 => 'Good morning',
            $hour < 17 => 'Good afternoon',
            default => 'Good evening',
        };

        // Clean, whole-number gridlines: 0, half and the top of the scale.
        $peak = $activity->max('count');
        $peakIndex = $activity->search(fn (array $point): bool => $point['count'] === $peak);
        $axisMax = max(2, $peak + $peak % 2);

        $nextPost = $upcomingPosts->first();
        $tiles = [
            [
                'label' => 'Published',
                'value' => $stats['published'],
                'icon' => 'fa-solid fa-circle-check',
                'tone' => 'tone-green',
                'meta' => $stats['published_this_month'] > 0 ? "{$stats['published_this_month']} this month" : 'None yet this month',
                'url' => route('admin.posts.index', ['status' => 'published']),
            ],
            [
                'label' => 'Scheduled',
                'value' => $stats['scheduled'],
                'icon' => 'fa-regular fa-clock',
                'tone' => 'tone-gold',
                'meta' => $nextPost ? 'Next on '.$nextPost->published_at->format('j M') : 'Nothing queued',
                'url' => route('admin.posts.index', ['status' => 'scheduled']),
            ],
            [
                'label' => 'Drafts',
                'value' => $stats['drafts'],
                'icon' => 'fa-regular fa-file-lines',
                'tone' => 'tone-slate',
                'meta' => 'Not visible on the site',
                'url' => route('admin.posts.index', ['status' => 'draft']),
            ],
            [
                'label' => 'Categories',
                'value' => $stats['categories'],
                'icon' => 'fa-solid fa-layer-group',
                'tone' => 'tone-navy',
                'meta' => Str::plural('topic', $stats['categories']).' on the blog',
                'url' => route('admin.categories.index'),
            ],
        ];
    @endphp

    <x-admin.page-header
        :title="$greeting.', '.Str::before($admin->name, ' ')"
        :subtitle="'Here’s how the CAGAD blog is doing this '.now()->format('l, j F').'.'">
        <x-slot:actions>
            <a href="{{ config('app.frontend_url') }}/blog" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View blog
            </a>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> New post
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="row g-3 g-xl-4 mb-3 mb-xl-4">
        @foreach ($tiles as $tile)
            <div class="col-6 col-xl-3">
                <a href="{{ $tile['url'] }}" class="admin-card stat-card">
                    <span class="stat-icon {{ $tile['tone'] }}"><i class="{{ $tile['icon'] }}" aria-hidden="true"></i></span>
                    <span class="min-w-0">
                        <span class="stat-label d-block">{{ $tile['label'] }}</span>
                        <span class="stat-value d-block">{{ number_format($tile['value']) }}</span>
                        <span class="stat-meta d-block">{{ $tile['meta'] }}</span>
                    </span>
                </a>
            </div>
        @endforeach
    </div>

    <div class="row g-3 g-xl-4 mb-3 mb-xl-4">
        <div class="col-xl-8">
            <section class="admin-card h-100" aria-labelledby="activity-title">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title" id="activity-title">Publishing activity</h2>
                        <p class="admin-card-subtitle">Posts that went live each month over the last 12 months</p>
                    </div>
                    <ul class="nav nav-pills view-toggle" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="activity-chart-tab" data-bs-toggle="tab" data-bs-target="#activity-chart-pane" type="button" role="tab" aria-controls="activity-chart-pane" aria-selected="true">
                                <i class="fa-solid fa-chart-column me-1" aria-hidden="true"></i>Chart
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="activity-table-tab" data-bs-toggle="tab" data-bs-target="#activity-table-pane" type="button" role="tab" aria-controls="activity-table-pane" aria-selected="false">
                                <i class="fa-solid fa-table-list me-1" aria-hidden="true"></i>Table
                            </button>
                        </li>
                    </ul>
                </div>
                <div class="admin-card-body">
                    <div class="tab-content">
                        <div class="tab-pane fade show active" id="activity-chart-pane" role="tabpanel" aria-labelledby="activity-chart-tab" tabindex="0">
                            <div class="activity-chart">
                                <div class="activity-axis" aria-hidden="true">
                                    <span style="--at: 100%">{{ $axisMax }}</span>
                                    <span style="--at: 50%">{{ $axisMax / 2 }}</span>
                                    <span style="--at: 0%">0</span>
                                </div>
                                <div class="activity-plot">
                                    @foreach ($activity as $point)
                                        @php($postsLabel = $point['count'].' '.Str::plural('post', $point['count']))
                                        <div class="activity-col" style="--h: {{ round($point['count'] / $axisMax * 100, 2) }}%" tabindex="0" role="img" aria-label="{{ $point['month'] }}: {{ $postsLabel }} published">
                                            @if ($peak > 0 && $loop->index === $peakIndex)
                                                <span class="activity-value position-absolute" style="bottom: calc(var(--h) + 4px)" aria-hidden="true">{{ $point['count'] }}</span>
                                            @endif
                                            <span class="activity-bar"></span>
                                            <span class="activity-tip" aria-hidden="true"><strong>{{ $postsLabel }}</strong>{{ $point['month'] }}</span>
                                        </div>
                                    @endforeach
                                    @if ($peak === 0)
                                        <p class="activity-empty mb-0">Nothing has been published in the last 12 months. Published posts will show up here.</p>
                                    @endif
                                </div>
                                <div class="activity-months" aria-hidden="true">
                                    @foreach ($activity as $point)
                                        <span>{{ $point['label'] }}</span>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                        <div class="tab-pane fade" id="activity-table-pane" role="tabpanel" aria-labelledby="activity-table-tab" tabindex="0">
                            <div class="table-responsive">
                                <table class="table table-sm data-table mb-0">
                                    <caption class="visually-hidden">Posts published per month</caption>
                                    <thead>
                                        <tr>
                                            <th scope="col">Month</th>
                                            <th scope="col" class="text-end">Posts published</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($activity->reverse() as $point)
                                            <tr>
                                                <td>{{ $point['month'] }}</td>
                                                <td class="text-end">{{ $point['count'] }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <div class="col-xl-4">
            <section class="admin-card h-100" aria-labelledby="upcoming-title">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title" id="upcoming-title">Up next</h2>
                        <p class="admin-card-subtitle">Scheduled posts go live on their own</p>
                    </div>
                    @if ($upcomingPosts->isNotEmpty())
                        <a href="{{ route('admin.posts.index', ['status' => 'scheduled']) }}" class="card-link">View all</a>
                    @endif
                </div>
                @forelse ($upcomingPosts as $post)
                    <a href="{{ route('admin.posts.edit', $post) }}" class="list-row">
                        <span class="date-chip" aria-hidden="true">
                            <span class="date-chip-month">{{ $post->published_at->format('M') }}</span>
                            <span class="date-chip-day">{{ $post->published_at->format('j') }}</span>
                        </span>
                        <span class="min-w-0">
                            <span class="table-title text-truncate">{{ $post->title }}</span>
                            <span class="table-sub d-block">
                                {{ $post->published_at->format('D j M, H:i') }} · {{ $post->published_at->diffForHumans() }}
                            </span>
                        </span>
                    </a>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-calendar" title="Nothing scheduled" compact>
                        Publish a post with a future date and it will appear here.
                    </x-admin.empty-state>
                @endforelse
            </section>
        </div>
    </div>

    <div class="row g-3 g-xl-4">
        <div class="col-xl-8">
            <section class="admin-card h-100" aria-labelledby="recent-title">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title" id="recent-title">Recently updated</h2>
                        <p class="admin-card-subtitle">The latest changes across all posts</p>
                    </div>
                    <a href="{{ route('admin.posts.index') }}" class="card-link">All posts</a>
                </div>
                @if ($recentPosts->isEmpty())
                    <x-admin.empty-state icon="fa-regular fa-newspaper" title="No posts yet">
                        Write the first post. It stays a private draft until you publish it.
                        <x-slot:actions>
                            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
                                <i class="fa-solid fa-plus" aria-hidden="true"></i> New post
                            </a>
                        </x-slot:actions>
                    </x-admin.empty-state>
                @else
                    <div class="table-responsive">
                        <table class="table admin-table align-middle">
                            <thead>
                                <tr>
                                    <th scope="col">Post</th>
                                    <th scope="col" class="d-none d-sm-table-cell">Status</th>
                                    <th scope="col" class="d-none d-md-table-cell">Updated</th>
                                    <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($recentPosts as $post)
                                    <tr>
                                        <td>
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="table-title">{{ $post->title }}</a>
                                            <div class="table-meta">
                                                <span>{{ $post->category?->name ?? 'Uncategorised' }}</span>
                                                <span class="d-sm-none"><x-admin.status-badge :post="$post" /></span>
                                            </div>
                                        </td>
                                        <td class="d-none d-sm-table-cell"><x-admin.status-badge :post="$post" /></td>
                                        <td class="d-none d-md-table-cell table-sub text-nowrap">{{ $post->updated_at->diffForHumans() }}</td>
                                        <td class="text-end">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </section>
        </div>

        <div class="col-xl-4 d-flex flex-column gap-3 gap-xl-4">
            <section class="admin-card" aria-labelledby="categories-title">
                <div class="admin-card-header">
                    <div>
                        <h2 class="admin-card-title" id="categories-title">Posts by category</h2>
                        <p class="admin-card-subtitle">Published posts in the top five</p>
                    </div>
                    <a href="{{ route('admin.categories.index') }}" class="card-link">Manage</a>
                </div>
                <div class="admin-card-body">
                    @php($categoryPeak = max(1, $topCategories->max('posts_count')))
                    @if ($topCategories->isEmpty())
                        <p class="text-secondary small mb-0">No categories yet.</p>
                    @else
                        <ul class="meter-list">
                            @foreach ($topCategories as $category)
                                <li>
                                    <div class="meter-row">
                                        <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="meter-name text-decoration-none">{{ $category->name }}</a>
                                        <span class="meter-value">{{ $category->posts_count }}</span>
                                    </div>
                                    <div class="meter-track" aria-hidden="true">
                                        <span class="meter-fill" style="width: {{ round($category->posts_count / $categoryPeak * 100, 2) }}%"></span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>
            </section>

            <section class="admin-card" aria-labelledby="actions-title">
                <div class="admin-card-header">
                    <h2 class="admin-card-title" id="actions-title">Quick actions</h2>
                </div>
                <div class="admin-card-body">
                    <div class="quick-actions">
                        <a href="{{ route('admin.posts.create') }}" class="quick-action">
                            <i class="fa-solid fa-pen-nib tone-green" aria-hidden="true"></i> Write a post
                        </a>
                        <a href="{{ route('admin.categories.create') }}" class="quick-action">
                            <i class="fa-solid fa-folder-plus tone-navy" aria-hidden="true"></i> Add a category
                        </a>
                        @if ($admin->isSuperAdmin())
                            <a href="{{ route('admin.admins.create') }}" class="quick-action">
                                <i class="fa-solid fa-user-plus tone-gold" aria-hidden="true"></i> Add an admin
                            </a>
                        @else
                            <a href="{{ config('app.frontend_url') }}/blog" class="quick-action" target="_blank" rel="noopener">
                                <i class="fa-solid fa-globe tone-gold" aria-hidden="true"></i> View live blog
                            </a>
                        @endif
                        <a href="{{ route('admin.account.edit') }}" class="quick-action">
                            <i class="fa-solid fa-key tone-slate" aria-hidden="true"></i> Change password
                        </a>
                    </div>
                </div>
            </section>
        </div>
    </div>
@endsection
