{{-- The signed-in shell: sidebar, top bar, flash toasts and the delete confirmation dialog. --}}
@extends('layouts.base')

@section('body')
    @php
        $currentAdmin = auth()->user();
        $blogUrl = config('app.frontend_url').'/blog';
        $navLinks = [
            'Overview' => [
                ['route' => 'admin.dashboard', 'active' => 'admin.dashboard', 'icon' => 'fa-solid fa-chart-simple', 'label' => 'Dashboard'],
            ],
            'Content' => [
                ['route' => 'admin.posts.index', 'active' => 'admin.posts.*', 'icon' => 'fa-regular fa-newspaper', 'label' => 'Posts'],
                ['route' => 'admin.categories.index', 'active' => 'admin.categories.*', 'icon' => 'fa-solid fa-layer-group', 'label' => 'Categories'],
            ],
            'Administration' => $currentAdmin->isSuperAdmin() ? [
                ['route' => 'admin.admins.index', 'active' => 'admin.admins.*', 'icon' => 'fa-solid fa-user-shield', 'label' => 'Admins'],
            ] : [],
            'You' => [
                ['route' => 'admin.account.edit', 'active' => 'admin.account.*', 'icon' => 'fa-regular fa-circle-user', 'label' => 'My account'],
            ],
        ];
    @endphp

    <a href="#main" class="visually-hidden-focusable position-absolute top-0 start-0 m-2 btn btn-primary" style="z-index: 2000">Skip to content</a>

    <aside class="admin-sidebar">
        <div class="offcanvas-lg offcanvas-start sidebar-panel" tabindex="-1" id="admin-sidebar" aria-label="Admin navigation">
            <div class="d-flex align-items-center">
                <a href="{{ route('admin.dashboard') }}" class="sidebar-brand">
                    <span class="brand-chip"><img src="{{ asset('images/logo.png') }}" alt="" width="38" height="32"></span>
                    <span>
                        <span class="brand-name">CAGAD</span>
                        <span class="brand-sub">Blog Admin</span>
                    </span>
                </a>
                <button type="button" class="btn-close btn-close-white sidebar-close me-3 d-lg-none" data-bs-dismiss="offcanvas" data-bs-target="#admin-sidebar" aria-label="Close menu"></button>
            </div>

            <nav class="sidebar-nav">
                @foreach ($navLinks as $section => $links)
                    @continue(empty($links))
                    <p class="sidebar-label">{{ $section }}</p>
                    @foreach ($links as $link)
                        @php($isActive = request()->routeIs($link['active']))
                        <a href="{{ route($link['route']) }}" @class(['sidebar-link', 'active' => $isActive]) @if ($isActive) aria-current="page" @endif>
                            <i class="{{ $link['icon'] }}" aria-hidden="true"></i> {{ $link['label'] }}
                        </a>
                    @endforeach
                @endforeach

                <a href="{{ $blogUrl }}" class="sidebar-link" target="_blank" rel="noopener">
                    <i class="fa-solid fa-globe" aria-hidden="true"></i> View live blog
                    <i class="fa-solid fa-arrow-up-right-from-square external" aria-hidden="true"></i>
                </a>
            </nav>

            <div class="sidebar-footer">
                <div class="sidebar-user">
                    <x-admin.avatar :admin="$currentAdmin" />
                    <div class="min-w-0">
                        <div class="sidebar-user-name text-truncate">{{ $currentAdmin->name }}</div>
                        <div class="sidebar-user-role">{{ $currentAdmin->role->label() }}</div>
                    </div>
                </div>
                <form method="POST" action="{{ route('admin.logout') }}">
                    @csrf
                    <button type="submit" class="sidebar-signout">
                        <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out
                    </button>
                </form>
            </div>
        </div>
    </aside>

    <div class="admin-main">
        <header class="admin-topbar">
            <button type="button" class="icon-btn d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#admin-sidebar" aria-controls="admin-sidebar" aria-label="Open menu">
                <i class="fa-solid fa-bars" aria-hidden="true"></i>
            </button>

            <a href="{{ route('admin.dashboard') }}" class="topbar-brand d-md-none">
                <img src="{{ asset('images/logo.png') }}" alt="" width="34" height="28"> CAGAD
            </a>

            <form method="GET" action="{{ route('admin.posts.index') }}" class="topbar-search d-none d-md-block" role="search">
                <label for="topbar-search" class="visually-hidden">Search posts</label>
                <i class="fa-solid fa-magnifying-glass" aria-hidden="true"></i>
                <input type="search" id="topbar-search" name="q" class="form-control" placeholder="Search posts…"
                       value="{{ request()->routeIs('admin.posts.index') ? request('q') : '' }}" autocomplete="off">
            </form>

            <div class="topbar-actions">
                <a href="{{ route('admin.posts.create') }}" class="btn btn-primary btn-sm d-none d-sm-inline-flex">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i> New post
                </a>
                <a href="{{ route('admin.posts.create') }}" class="icon-btn d-sm-none" aria-label="New post">
                    <i class="fa-solid fa-plus" aria-hidden="true"></i>
                </a>

                <div class="dropdown">
                    <button class="user-menu-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" aria-label="Account menu for {{ $currentAdmin->name }}">
                        <x-admin.avatar :admin="$currentAdmin" size="sm" />
                        <span class="d-none d-md-inline">{{ Str::before($currentAdmin->name, ' ') }}</span>
                        <i class="fa-solid fa-chevron-down small text-secondary d-none d-md-inline" aria-hidden="true"></i>
                    </button>
                    <div class="dropdown-menu dropdown-menu-end">
                        <div class="user-menu-header">
                            <div class="fw-semibold text-ink">{{ $currentAdmin->name }}</div>
                            <div class="small text-secondary text-truncate" style="max-width: 220px">{{ $currentAdmin->email }}</div>
                        </div>
                        <a class="dropdown-item" href="{{ route('admin.account.edit') }}">
                            <i class="fa-regular fa-circle-user" aria-hidden="true"></i> My account
                        </a>
                        <a class="dropdown-item" href="{{ $blogUrl }}" target="_blank" rel="noopener">
                            <i class="fa-solid fa-arrow-up-right-from-square" aria-hidden="true"></i> View live blog
                        </a>
                        <div class="dropdown-divider"></div>
                        <form method="POST" action="{{ route('admin.logout') }}">
                            @csrf
                            <button type="submit" class="dropdown-item text-danger">
                                <i class="fa-solid fa-arrow-right-from-bracket" aria-hidden="true"></i> Sign out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <main class="admin-content" id="main" tabindex="-1">
            @yield('content')
        </main>

        <footer class="admin-footer">
            © {{ now()->year }} Centre for Advancement of Governance and Development
        </footer>
    </div>

    <div class="toast-stack" aria-live="polite" aria-atomic="true">
        @if (session('status'))
            <div class="toast admin-toast fade show" role="status" data-autoshow>
                <div class="toast-body">
                    <span class="toast-icon"><i class="fa-solid fa-check" aria-hidden="true"></i></span>
                    <div class="flex-grow-1 pt-1">{{ session('status') }}</div>
                    <button type="button" class="btn-close btn-sm mt-1" data-bs-dismiss="toast" aria-label="Close"></button>
                </div>
            </div>
        @endif
    </div>

    <div class="modal fade" id="confirm-modal" tabindex="-1" aria-labelledby="confirm-modal-title" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content">
                <div class="modal-body p-4">
                    <div class="d-flex gap-3">
                        <span class="confirm-icon"><i class="fa-solid fa-triangle-exclamation" aria-hidden="true"></i></span>
                        <div>
                            <h2 class="h5 mb-2" id="confirm-modal-title" data-confirm-title>Are you sure?</h2>
                            <p class="mb-0 text-secondary" data-confirm-message></p>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 px-4 pt-0 pb-4">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-danger" data-confirm-accept>Delete</button>
                </div>
            </div>
        </div>
    </div>
@endsection
