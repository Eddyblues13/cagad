@extends('layouts.admin')

@section('title', 'Edit: '.$post->title)

@section('content')
    <x-admin.page-header title="Edit post" :back="route('admin.posts.index')" back-label="All posts">
        <x-slot:badge>
            <x-admin.status-badge :post="$post" />
        </x-slot:badge>
        <x-slot:actions>
            <a href="{{ route('admin.posts.show', $post) }}" class="btn btn-outline-secondary">
                <i class="fa-regular fa-eye" aria-hidden="true"></i> Preview
            </a>
            @if ($post->isLive())
                <a href="{{ $post->frontendUrl() }}" class="btn btn-outline-secondary" target="_blank" rel="noopener">
                    <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View on site
                </a>
            @endif
            <button type="submit" form="post-form" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save changes
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @if ($post->isScheduled())
        <div class="notice notice-gold">
            <i class="fa-regular fa-clock" aria-hidden="true"></i>
            <div>
                Scheduled. This post goes live on <strong>{{ $post->published_at->format('l j F Y \a\t H:i') }}</strong>
                ({{ $post->published_at->diffForHumans() }}).
            </div>
        </div>
    @endif

    @include('admin.posts.form', [
        'action' => route('admin.posts.update', $post),
        'method' => 'PUT',
    ])

    <form method="POST" action="{{ route('admin.posts.destroy', $post) }}" id="delete-post-form"
          data-confirm="“{{ $post->title }}” will be deleted and its cover image removed from Cloudinary. This can’t be undone."
          data-confirm-title="Delete this post?" data-confirm-button="Delete post">
        @csrf
        @method('DELETE')
    </form>
@endsection
