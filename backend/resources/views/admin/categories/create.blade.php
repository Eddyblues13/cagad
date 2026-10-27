@extends('layouts.admin')

@section('title', 'New category')

@section('content')
    <x-admin.page-header title="New category" :back="route('admin.categories.index')" back-label="All categories" />

    @include('admin.categories.form', [
        'action' => route('admin.categories.store'),
        'method' => 'POST',
    ])
@endsection
