{{-- Shared by create and edit. Expects $admin, $action and $method. --}}
@php($selectedRole = old('role', $admin->role?->value))

<div class="row g-4">
    <div class="col-lg-8">
        <form method="POST" action="{{ $action }}" class="admin-card" novalidate>
            @csrf
            @method($method)

            <div class="admin-card-body">
                <section class="form-section">
                    <h2 class="form-section-title">Account details</h2>
                    <p class="form-section-text">The name shown in the panel and the email used to sign in.</p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="name" class="form-label">Full name</label>
                            <input type="text" id="name" name="name" value="{{ old('name', $admin->name) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('name')])
                                   required autocomplete="off" @unless ($admin->exists) autofocus @endunless>
                            @error('name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="email" class="form-label">Email</label>
                            <input type="email" id="email" name="email" value="{{ old('email', $admin->email) }}"
                                   @class(['form-control', 'is-invalid' => $errors->has('email')])
                                   required autocomplete="off" autocapitalize="none" spellcheck="false">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                </section>

                <section class="form-section">
                    <h2 class="form-section-title">Role</h2>
                    <p class="form-section-text">What this person is allowed to do in the panel.</p>
                    <div class="row g-3" role="radiogroup" aria-label="Role">
                        @foreach (\App\Enums\AdminRole::cases() as $role)
                            <div class="col-md-6">
                                <label class="choice-card">
                                    <input class="form-check-input" type="radio" name="role" value="{{ $role->value }}" @checked($selectedRole === $role->value)>
                                    <span>
                                        <span class="choice-title">{{ $role->label() }}</span>
                                        <span class="choice-text">{{ $role->description() }}</span>
                                    </span>
                                </label>
                            </div>
                        @endforeach
                    </div>
                    @error('role')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </section>

                <section class="form-section">
                    <h2 class="form-section-title">Access</h2>
                    <div class="switch-row form-switch">
                        <label for="is_active" class="mb-0">
                            <span class="choice-title">Active</span>
                            <span class="choice-text">Deactivated admins can’t sign in, and are signed out the next time they load a page.</span>
                        </label>
                        <input type="hidden" name="is_active" value="0">
                        <input class="form-check-input" type="checkbox" role="switch" id="is_active" name="is_active" value="1" @checked(old('is_active', $admin->is_active))>
                    </div>
                    @error('is_active')
                        <div class="field-error">{{ $message }}</div>
                    @enderror
                </section>

                <section class="form-section">
                    <h2 class="form-section-title">{{ $admin->exists ? 'Reset password' : 'Password' }}</h2>
                    <p class="form-section-text">
                        {{ $admin->exists
                            ? 'Leave both fields empty to keep their current password.'
                            : 'At least 8 characters. Share it with them privately; they can change it on their Account page.' }}
                    </p>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label for="password" class="form-label">{{ $admin->exists ? 'New password' : 'Password' }}</label>
                            <div class="input-icon">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                <input type="password" id="password" name="password"
                                       @class(['form-control has-toggle', 'is-invalid' => $errors->has('password')])
                                       autocomplete="new-password" @unless ($admin->exists) required @endunless>
                                <button type="button" class="password-toggle" data-password-toggle="password" aria-label="Show password" aria-pressed="false">
                                    <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                </button>
                            </div>
                            @error('password')
                                <div class="field-error">{{ $message }}</div>
                            @enderror
                        </div>
                        <div class="col-md-6">
                            <label for="password_confirmation" class="form-label">Confirm password</label>
                            <div class="input-icon">
                                <i class="fa-solid fa-lock" aria-hidden="true"></i>
                                <input type="password" id="password_confirmation" name="password_confirmation" class="form-control" autocomplete="new-password">
                            </div>
                        </div>
                    </div>
                </section>
            </div>

            <div class="admin-card-footer d-flex justify-content-end gap-2">
                <a href="{{ route('admin.admins.index') }}" class="btn btn-outline-secondary">Cancel</a>
                <button type="submit" class="btn btn-primary">
                    <i class="fa-solid fa-floppy-disk" aria-hidden="true"></i> {{ $admin->exists ? 'Save changes' : 'Add admin' }}
                </button>
            </div>
        </form>
    </div>

    <div class="col-lg-4">
        @if ($admin->exists)
            <div class="admin-card profile-card">
                <x-admin.avatar :admin="$admin" size="lg" />
                <h2 class="profile-name">{{ $admin->name }}</h2>
                <p class="profile-email">{{ $admin->email }}</p>
                <dl class="meta-list text-start mt-4 pt-4 border-top">
                    <div><dt>Posts created</dt><dd>{{ $admin->posts_count }}</dd></div>
                    <div><dt>Last sign-in</dt><dd>{{ $admin->last_login_at?->diffForHumans() ?? 'Never' }}</dd></div>
                    <div><dt>Added</dt><dd>{{ $admin->created_at->format('j M Y') }}</dd></div>
                </dl>
            </div>
        @else
            <div class="notice notice-info mb-0">
                <i class="fa-solid fa-circle-info" aria-hidden="true"></i>
                <div>No invitation email is sent. Give the new admin their email and password yourself; they can sign in straight away.</div>
            </div>
        @endif
    </div>
</div>
