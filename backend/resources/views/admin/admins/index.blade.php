@extends('layouts.admin')

@section('title', 'Admins')

@section('content')
    <x-admin.page-header title="Admins" subtitle="Everyone who can sign in to this panel. Editors run the blog; super admins also manage accounts.">
        <x-slot:actions>
            <a href="{{ route('admin.admins.create') }}" class="btn btn-primary">
                <i class="fa-solid fa-user-plus" aria-hidden="true"></i> Add admin
            </a>
        </x-slot:actions>
    </x-admin.page-header>

    <div class="admin-card">
        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th scope="col">Admin</th>
                        <th scope="col" class="d-none d-md-table-cell">Role</th>
                        <th scope="col" class="d-none d-sm-table-cell">Status</th>
                        <th scope="col" class="d-none d-lg-table-cell">Last sign-in</th>
                        <th scope="col" class="d-none d-lg-table-cell text-end">Posts</th>
                        <th scope="col" class="text-end"><span class="visually-hidden">Actions</span></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($admins as $account)
                        @php($isYou = $account->is(auth()->user()))
                        <tr>
                            <td>
                                <div class="d-flex align-items-center gap-3">
                                    <x-admin.avatar :admin="$account" />
                                    <div class="min-w-0">
                                        <div class="table-title">
                                            {{ $account->name }}
                                            @if ($isYou)
                                                <span class="pill pill-outline ms-1">You</span>
                                            @endif
                                        </div>
                                        <div class="table-sub text-truncate">{{ $account->email }}</div>
                                        <div class="table-meta d-md-none">
                                            <span>{{ $account->role->label() }}</span>
                                            @unless ($account->is_active)
                                                <span class="pill pill-red d-sm-none">Deactivated</span>
                                            @endunless
                                        </div>
                                    </div>
                                </div>
                            </td>
                            <td class="d-none d-md-table-cell">
                                <span @class(['pill', 'pill-navy' => $account->isSuperAdmin(), 'pill-gray' => ! $account->isSuperAdmin()])>
                                    @if ($account->isSuperAdmin())
                                        <i class="fa-solid fa-crown" aria-hidden="true"></i>
                                    @endif
                                    {{ $account->role->label() }}
                                </span>
                            </td>
                            <td class="d-none d-sm-table-cell">
                                @if ($account->is_active)
                                    <span class="pill pill-green"><i class="fa-solid fa-circle-check" aria-hidden="true"></i> Active</span>
                                @else
                                    <span class="pill pill-red"><i class="fa-solid fa-ban" aria-hidden="true"></i> Deactivated</span>
                                @endif
                            </td>
                            <td class="d-none d-lg-table-cell table-sub text-nowrap">{{ $account->last_login_at?->diffForHumans() ?? 'Never' }}</td>
                            <td class="d-none d-lg-table-cell text-end"><span class="count-pill">{{ $account->posts_count }}</span></td>
                            <td class="text-end">
                                @if ($isYou)
                                    <a href="{{ route('admin.account.edit') }}" class="btn btn-sm btn-outline-secondary">My account</a>
                                @else
                                    <div class="row-actions">
                                        <a href="{{ route('admin.admins.edit', $account) }}" class="btn btn-sm btn-outline-secondary">Edit</a>
                                        <form method="POST" action="{{ route('admin.admins.destroy', $account) }}"
                                              data-confirm="{{ $account->name }} will no longer be able to sign in. Posts they created stay on the blog."
                                              data-confirm-title="Remove this admin?" data-confirm-button="Remove admin">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger btn-icon" aria-label="Remove {{ $account->name }}">
                                                <i class="fa-regular fa-trash-can" aria-hidden="true"></i>
                                            </button>
                                        </form>
                                    </div>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
@endsection
