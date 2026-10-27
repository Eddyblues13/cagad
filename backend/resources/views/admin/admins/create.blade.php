@extends('layouts.admin')

@section('title', 'Add admin')

@section('content')
    <x-admin.page-header title="Add admin" subtitle="Give a colleague access to the blog admin." :back="route('admin.admins.index')" back-label="All admins" />

    @include('admin.admins.form', [
        'action' => route('admin.admins.store'),
        'method' => 'POST',
    ])
@endsection
