@extends('layouts.admin')

@section('title', 'Posts')

@section('content')
    @php
        $activeStatus = (string) request('status', '');
        $statusTabs = ['' => 'All', 'published' => 'Published', 'scheduled' => 'Scheduled', 'draft' => 'Drafts'];
        $isFiltered = request()->filled('q') || request()->filled('category') || $activeStatus !== '';
    @endphp

    <x-admin.page-header title="Posts" subtitle="Write, schedule and publish articles for the CAGAD blog.">
        <x-slot:actions>
            <a href="{{ route('admin.posts.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> New post
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card">
        <div class="card-toolbar">
            <nav class="status-tabs" aria-label="Filter posts by status">
                @foreach ($statusTabs as $value => $label)
                    @php($isCurrent = $activeStatus === (string) $value)
                    <a href="{{ route('admin.posts.index', array_filter([...request()->only('q', 'category'), 'status' => $value])) }}"
                       @class(['status-tab', 'active' => $isCurrent]) @if ($isCurrent) aria-current="page" @endif>
                        {{ $label }} <span class="count">{{ $statusCounts[$value] }}</span>
                    </a>
                @endforeach
            </nav>

            <form method="GET" action="{{ route('admin.posts.index') }}" class="table-filters" role="search">
                @if ($activeStatus !== '')
                    <input type="hidden" name="status" value="{{ $activeStatus }}">
                @endif
                <div class="input-icon filter-search">
                    <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                    <label for="q" class="visually-hidden">Search posts by title</label>
                    <input type="search" id="q" name="q" value="{{ request('q') }}" class="form-control" placeholder="Search by title…">
                </div>
                <label for="category" class="visually-hidden">Category</label>
                <select id="category" name="category" class="form-select filter-select" data-autosubmit>
                    <option value="">All categories</option>
                    @foreach ($categories as $category)
                        <option value="{{ $category->id }}" @selected((string) request('category') === (string) $category->id)>{{ $category->name }}</option>
                    @endforeach
                </select>
                <noscript><button type="submit" class="btn btn-outline-secondary">Filter</button></noscript>
            </form>
        </div>

        @if ($posts->isEmpty())
            @if ($isFiltered)
                <x-admin.empty-state icon="fa-solid fa-magnifying-glass" title="No posts match">
                    Try another search term, category or status.
                    <x-slot:actions>
                        <a href="{{ route('admin.posts.index') }}" class="btn btn-outline-secondary btn-sm">Clear filters</a>
                    </x-slot:actions>
                </x-admin.empty-state>
            @else
                <x-admin.empty-state icon="fa-regular fa-newspaper" title="No posts yet">
                    Write the first one. It stays a private draft until you publish it.
                    <x-slot:actions>
                        <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm">
                            <i class="fa-solid fa-plus" aria-hidden="true"></i> New post
                        </a>
                    </x-slot:actions>
                </x-admin.empty-state>
            @endif
        @else
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Post</th>
                            <th scope="col" class="d-none d-lg-table-cell">Category</th>
                            <th scope="col" class="d-none d-md-table-cell">Status</th>
                            <th scope="col" class="d-none d-xl-table-cell">Publish date</th>
                            <th scope="col" class="d-none d-lg-table-cell">Updated</th>
                            <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($posts as $post)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-3">
                                        @if ($post->cover_image_url)
                                            <img src="{{ $post->coverUrl('c_fill,g_auto,w_128,h_88') }}" alt="" class="cover-thumb" width="64" height="44" loading="lazy">
                                        @else
                                            <span class="cover-thumb" aria-hidden="true"><i class="fa-regular fa-image"></i></span>
                                        @endif
                                        <div class="min-w-0">
                                            <a href="{{ route('admin.posts.edit', $post) }}" class="table-title">{{ $post->title }}</a>
                                            <div class="table-meta">
                                                <span class="d-md-none"><x-admin.status-badge :post="$post" /></span>
                                                <span class="slug-text d-none d-sm-inline-block">/blog/{{ $post->slug }}</span>
                                                <span class="d-lg-none">{{ $post->category?->name ?? 'Uncategorised' }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                                <td class="d-none d-lg-table-cell">{{ $post->category?->name ?? '—' }}</td>
                                <td class="d-none d-md-table-cell"><x-admin.status-badge :post="$post" /></td>
                                <td class="d-none d-xl-table-cell table-sub text-nowrap">{{ $post->published_at?->format('j M Y, H:i') ?? '—' }}</td>
                                <td class="d-none d-lg-table-cell table-sub text-nowrap">{{ $post->updated_at->diffForHumans() }}</td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-sm btn-outline-secondary d-none d-sm-inline-flex">Edit</a>
                                        <div class="dropdown">
                                            <button class="btn btn-sm btn-outline-secondary btn-icon" type="button" data-bs-toggle="dropdown" data-bs-popper-config='{"strategy":"fixed"}' aria-expanded="false" aria-label="More actions for {{ $post->title }}">
                                                <i class="fa-solid fa-ellipsis" aria-hidden="true"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end">
                                                <li class="d-sm-none">
                                                    <a class="dropdown-item" href="{{ route('admin.posts.edit', $post) }}"><i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Edit</a>
                                                </li>
                                                <li>
                                                    <a class="dropdown-item" href="{{ route('admin.posts.show', $post) }}"><i class="fa-regular fa-eye" aria-hidden="true"></i> Preview</a>
                                                </li>
                                                @if ($post->isLive())
                                                    <li>
                                                        <a class="dropdown-item" href="{{ $post->frontendUrl() }}" target="_blank" rel="noopener"><i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View on site</a>
                                                    </li>
                                                @endif
                                                <li><hr class="dropdown-divider"></li>
                                                <li>
                                                    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}"
                                                          data-confirm="“{{ $post->title }}” will be deleted and its cover image removed from Cloudinary. This can’t be undone."
                                                          data-confirm-title="Delete this post?" data-confirm-button="Delete post">
                                                        @csrf
                                                        @method('DELETE')
                                                        <button type="submit" class="dropdown-item text-danger"><i class="fa-regular fa-trash-can" aria-hidden="true"></i> Delete</button>
                                                    </form>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if ($posts->hasPages())
                <div class="table-footer">
                    {{ $posts->links() }}
                </div>
            @endif
        @endif
    </div>
@endsection
