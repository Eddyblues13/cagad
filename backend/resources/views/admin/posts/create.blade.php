@extends('layouts.admin')

@section('title', 'New post')

@section('content')
    <x-admin.page-header title="New post" subtitle="Drafts stay private until you publish them." :back="route('admin.posts.index')" back-label="All posts">
        <x-slot:actions>
            <button type="submit" form="post-form" class="btn btn-primary">
                <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save post
            </button>
        </x-slot:actions>
    </x-admin.page-header>

    @include('admin.posts.form', [
        'action' => route('admin.posts.store'),
        'method' => 'POST',
    ])
@endsection
