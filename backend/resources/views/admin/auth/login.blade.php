@extends('layouts.base')

@section('title', 'Sign in')

@section('body')
    <div class="auth-shell">
        <section class="auth-aside">
            <div class="auth-aside-inner">
                <div class="d-flex align-items-center gap-3">
                    <span class="brand-chip"><img src="{{ asset('images/logo.png') }}" alt="" width="38" height="32"></span>
                    <span>
                        <span class="brand-name">CAGAD</span>
                        <span class="brand-sub">Blog Admin</span>
                    </span>
                </div>

                <div>
                    <h2 class="auth-headline">Publish research, insight and news for the people CAGAD serves.</h2>
                    <p class="auth-lead">One place to write, schedule and manage every post on the CAGAD blog.</p>
                    <ul class="auth-features">
                        <li>
                            <i class="fa-solid fa-pen-nib" aria-hidden="true"></i>
                            <div><strong>Write with a rich editor</strong><span>Headings, images, tables and quotes, ready for the site.</span></div>
                        </li>
                        <li>
                            <i class="fa-regular fa-calendar-check" aria-hidden="true"></i>
                            <div><strong>Schedule ahead</strong><span>Pick a date and posts go live on their own.</span></div>
                        </li>
                        <li>
                            <i class="fa-solid fa-bolt" aria-hidden="true"></i>
                            <div><strong>Fast by default</strong><span>Images are resized and optimised automatically.</span></div>
                        </li>
                    </ul>
                </div>

                <p class="auth-copyright mb-0">© {{ now()->year }} Centre for Advancement of Governance and Development</p>
            </div>
        </section>

        <main class="auth-main">
            <div class="auth-card">
                <span class="auth-logo"><img src="{{ asset('images/logo.png') }}" alt="CAGAD" width="52" height="43"></span>
                <h1 class="auth-title">Welcome back</h1>
                <p class="auth-subtitle">Sign in to the CAGAD Blog Admin.</p>

                @if (session('status'))
                    <div class="notice notice-info" role="status">
                        <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                        <div>{{ session('status') }}</div>
                    </div>
                @endif

                <form method="POST" action="{{ route('admin.login.store') }}" novalidate>
                    @csrf

                    <div class="mb-3">
                        <label for="email" class="form-label">Email</label>
                        <div class="input-icon">
                            <i class="fa-regular fa-envelope" aria-hidden="true"></i>
                            <input type="email" id="email" name="email" value="{{ old('email') }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('email')])
                                   placeholder="you@cagad.org" required autofocus
                                   autocomplete="username" autocapitalize="none" spellcheck="false"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror>
                        </div>
                        @error('email')
                            <div class="field-error" id="email-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="mb-3">
                        <label for="password" class="form-label">Password</label>
                        <div class="input-icon">
                            <i class="fa-solid fa-lock" aria-hidden="true"></i>
                            <input type="password" id="password" name="password"
                                   @class(['form-control has-toggle', 'is-invalid' => $errors->has('password')])
                                   required autocomplete="current-password"
                                   @error('password') aria-invalid="true" aria-describedby="password-error" @enderror>
                            <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                            </button>
                        </div>
                        @error('password')
                            <div class="field-error" id="password-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="form-check mb-4">
                        <input class="form-check-input" type="checkbox" name="remember" id="remember" value="1" @checked(old('remember'))>
                        <label class="form-check-label" for="remember">Keep me signed in on this device</label>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100">
                        Sign in <i class="fa-solid fa-arrow-right" aria-hidden="true"></i>
                    </button>
                </form>

                <p class="auth-help">
                    <i class="fa-solid fa-shield-halved me-1" aria-hidden="true"></i>
                    Access is limited to CAGAD staff. Forgotten your password? Ask a super admin to reset it.
                </p>
            </div>
        </main>
    </div>
@endsection
