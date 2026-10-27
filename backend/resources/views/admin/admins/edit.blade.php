@extends('layouts.admin')

@section('title', 'Edit: '.$admin->name)

@section('content')
    <x-admin.page-header title="Edit admin" :subtitle="$admin->name" :back="route('admin.admins.index')" back-label="All admins" />

    @include('admin.admins.form', [
        'action' => route('admin.admins.update', $admin),
        'method' => 'PUT',
    ])
@endsection
