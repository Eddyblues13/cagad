@extends('layouts.admin')

@section('title', 'My account')

@section('content')
    <x-admin.page-header title="My account" subtitle="Update your name, sign-in email and password." />

    <div class="row g-4">
        <div class="col-lg-4">
            <div class="admin-card profile-card">
                <x-admin.avatar :admin="$admin" size="xl" />
                <h2 class="profile-name">{{ $admin->name }}</h2>
                <p class="profile-email">{{ $admin->email }}</p>
                <span @class(['pill', 'pill-navy' => $admin->isSuperAdmin(), 'pill-gray' => ! $admin->isSuperAdmin()])>
                    @if ($admin->isSuperAdmin())
                        <i class="fa-solid fa-crown" aria-hidden="true"></i>
                    @endif
                    {{ $admin->role->label() }}
                </span>
                <dl class="meta-list text-start mt-4 pt-4 border-top">
                    <div><dt>Posts created</dt><dd>{{ $admin->posts_count }}</dd></div>
                    <div><dt>Signed in</dt><dd>{{ $admin->last_login_at?->diffForHumans() ?? '—' }}</dd></div>
                    <div><dt>Member since</dt><dd>{{ $admin->created_at->format('M Y') }}</dd></div>
                </dl>
            </div>
        </div>

        <div class="col-lg-8">
            <form method="POST" action="{{ route('admin.account.update') }}" class="admin-card" novalidate>
                @csrf
                @method('PUT')

                <div class="admin-card-body">
                    <section class="form-section">
                        <h2 class="form-section-title">Profile</h2>
                        <p class="form-section-text">Your name is the default byline on new posts.</p>
                        <div class="row g-3">
                            <div class="col-md-6">
                                <label for="name" class="form-label">Full name</label>
                                <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}"
                                       @class(['form-control', 'is-invalid' => $errors->has('name')]) required autocomplete="name">
                                @error('name')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="email" class="form-label">Email</label>
                                <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}"
                                       @class(['form-control', 'is-invalid' => $errors->has('email')])
                                       required autocomplete="username" autocapitalize="none" spellcheck="false">
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>
                    </section>

                    <section class="form-section">
                        <h2 class="form-section-title">Change password</h2>
                        <p class="form-section-text">Leave these empty to keep your current password.</p>
                        <div class="row g-3">
                            <div class="col-12">
                                <label for="current_password" class="form-label">Current password</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                    <input type="password" id="current_password" name="current_password"
                                           @class(['form-control has-toggle', 'is-invalid' => $errors->has('current_password')])
                                           autocomplete="current-password">
                                    <button type="button" class="password-toggle" data-password-toggle="current_password" aria-label="Show password" aria-pressed="false">
                                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('current_password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password" class="form-label">New password</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-key" aria-hidden="true"></i>
                                    <input type="password" id="password" name="password"
                                           @class(['form-control has-toggle', 'is-invalid' => $errors->has('password')])
                                           autocomplete="new-password">
                                    <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                                        <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                    </button>
                                </div>
                                @error('password')
                                    <div class="field-error">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-6">
                                <label for="password_confirmation" class="form-label">Confirm new password</label>
                                <div class="input-icon">
                                    <i class="fa-solid fa-key" aria-hidden="true"></i>
                                    <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                                </div>
                            </div>
                        </div>
                    </section>
                </div>

                <div class="admin-card-footer d-flex justify-content-end">
                    <button type="submit" class="btn btn-primary">
                        <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> Save changes
                    </button>
                </div>
            </form>
        </div>
    </div>
@endsection
