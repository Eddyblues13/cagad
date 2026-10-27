<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AdminRole;
use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Models\Admin;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Super admins manage the other admin accounts here.
 *
 * Nobody can change their own role, deactivate themselves or delete their own
 * account, so there is always at least one active super admin. Admins edit
 * their own name, email and password on the Account page instead.
 */
class AdminController extends Controller
{
    public function index(): View
    {
        return view('admin.admins.index', [
            'admins' => Admin::withCount('posts')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.admins.create', [
            'admin' => new Admin(['role' => AdminRole::Editor, 'is_active' => true]),
        ]);
    }

    public function store(AdminRequest $request): RedirectResponse
    {
        $admin = Admin::create($request->validated());

        return to_route('admin.admins.index')
            ->with('status', "{$admin->name} was added as {$admin->role->label()}.");
    }

    public function edit(Request $request, Admin $admin): View|RedirectResponse
    {
        if ($admin->is($request->user())) {
            return to_route('admin.account.edit');
        }

        return view('admin.admins.edit', ['admin' => $admin->loadCount('posts')]);
    }

    public function update(AdminRequest $request, Admin $admin): RedirectResponse
    {
        $this->abortIfOwnAccount($request, $admin);

        $admin->fill($request->safe()->except('password'));

        if ($request->filled('password')) {
            $admin->password = $request->validated('password');
        }

        $admin->save();

        return to_route('admin.admins.index')->with('status', "{$admin->name}’s account was updated.");
    }

    public function destroy(Request $request, Admin $admin): RedirectResponse
    {
        $this->abortIfOwnAccount($request, $admin);

        $admin->delete();

        return to_route('admin.admins.index')
            ->with('status', "{$admin->name} was removed. Their posts are still on the blog.");
    }

    private function abortIfOwnAccount(Request $request, Admin $admin): void
    {
        abort_if(
            $admin->is($request->user()),
            403,
            'You can’t change your own role or status, or delete your own account. Use the Account page for your details.',
        );
    }
}
