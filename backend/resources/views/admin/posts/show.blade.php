@extends('layouts.admin')

@section('title', 'Preview: '.$post->title)

@section('content')
    <x-admin.page-header title="Preview" :back="route('admin.posts.edit', $post)" back-label="Back to editing">
        <x-slot:badge>
            <x-admin.status-badge :post="$post" />
        </x-slot:badge>
        <x-slot:actions>
            @if ($post->isLive())
                <a href="{{ $post->frontendUrl() }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View on site
                </a>
            @endif
            <a href="{{ route('admin.posts.edit', $post) }}" class="btn btn-primary">
                <i class="fa-regular fa-pen-to-square" aria-hidden="true"></i> Edit
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    @unless ($post->isLive())
        <div class="notice notice-gold">
            <i class="fa-regular fa-eye" aria-hidden="true"></i>
            <div>
                This is a preview of a {{ Str::lower($post->statusLabel()) }} post. Visitors can’t see it yet.
                @if ($post->isScheduled())
                    It goes live {{ $post->published_at->format('l j F \a\t H:i') }}.
                @endif
            </div>
        </div>
    @endunless

    <article class="admin-card p-4 p-md-5">
        <div class="post-preview">
            @if ($post->category)
                <p class="eyebrow">{{ $post->category->name }}</p>
            @endif
            <h1 class="mb-3">{{ $post->title }}</h1>
            <p class="text-secondary mb-4">
                @if ($post->author_name)
                    By {{ $post->author_name }} ·
                @endif
                {{ $post->published_at?->format('j F Y') ?? 'Not yet published' }} ·
                {{ $post->readingTime() }} min read
            </p>

            @if ($post->cover_image_url)
                <img src="{{ $post->coverUrl('c_limit,w_1600') }}" alt="{{ $post->cover_image_alt ?: $post->title }}" class="cover">
            @endif

            @if (filled($post->body))
                <div class="prose">
                    {!! $post->body !!}
                </div>
            @else
                <x-admin.empty-state icon="fa-regular fa-file-lines" title="This post has no content yet" compact>
                    Add the body in the editor before publishing.
                </x-admin.empty-state>
            @endif
        </div>
    </article>
@endsection
