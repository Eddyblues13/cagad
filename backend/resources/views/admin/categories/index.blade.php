@extends('layouts.admin')

@section('title', 'Categories')

@section('content')
    <x-admin.page-header title="Categories" subtitle="Group posts by topic. Readers can filter the blog by category.">
        <x-slot:actions>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-plus" aria-hidden="true"></i> New category
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card">
        @if ($categories->isEmpty())
            <x-admin.empty-state icon="fa-solid fa-layer-group" title="No categories yet">
                Create a few topics so readers can browse related posts.
                <x-slot:actions>
                    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">
                        <i class="fa-solid fa-plus" aria-hidden="true"></i> New category
                    </a>
                </x-slot:actions>
            </x-admin.empty-state>
        @else
            <div class="table-responsive">
                <table class="table admin-table align-middle">
                    <thead>
                        <tr>
                            <th scope="col">Category</th>
                            <th scope="col" class="d-none d-md-table-cell">Description</th>
                            <th scope="col" class="text-end">Posts</th>
                            <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($categories as $category)
                            <tr>
                                <td>
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="table-title">{{ $category->name }}</a>
                                    <div class="table-sub">{{ $category->slug }}</div>
                                </td>
                                <td class="d-none d-md-table-cell table-sub">{{ Str::limit($category->description, 110) ?: '—' }}</td>
                                <td class="text-end">
                                    <a href="{{ route('admin.posts.index', ['category' => $category->id]) }}" class="count-pill text-decoration-none" title="View posts in {{ $category->name }}">
                                        {{ $category->posts_count }}
                                    </a>
                                </td>
                                <td class="text-end">
                                    <div class="row-actions">
                                        <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                              data-confirm="The “{{ $category->name }}” category will be deleted. Its {{ $category->posts_count }} {{ Str::plural('post', $category->posts_count) }} will be kept but become uncategorised."
                                              data-confirm-title="Delete this category?" data-confirm-button="Delete category">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Delete {{ $category->name }}">
                                                <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
@endsection
