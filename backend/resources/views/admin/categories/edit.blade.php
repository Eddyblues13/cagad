@extends('layouts.admin')

@section('title', 'Edit: '.$category->name)

@section('content')
    <x-admin.page-header title="Edit category" :subtitle="$category->name" :back="route('admin.categories.index')" back-label="All categories" />

    @include('admin.categories.form', [
        'action' => route('admin.categories.update', $category),
        'method' => 'PUT',
    ])
@endsection
