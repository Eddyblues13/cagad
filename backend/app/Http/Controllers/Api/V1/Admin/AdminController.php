<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AdminRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\Admin;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

/**
 * Super admins manage the other admin accounts here.
 *
 * Nobody can change their own role, deactivate themselves or delete their own
 * account, so there is always at least one active super admin. Admins edit
 * their own name, email and password through the account endpoint instead.
 */
class AdminController extends Controller
{
    public function index(): AnonymousResourceCollection
    {
        return AdminResource::collection(Admin::withCount('posts')->orderBy('name')->get());
    }

    public function store(AdminRequest $request): AdminResource
    {
        $admin = Admin::create($request->validated());

        return AdminResource::make($admin)
            ->additional(['message' => "{$admin->name} was added as {$admin->role->label()}."]);
    }

    public function show(Admin $admin): AdminResource
    {
        return AdminResource::make($admin->loadCount('posts'));
    }

    public function update(AdminRequest $request, Admin $admin): AdminResource
    {
        $this->abortIfOwnAccount($request, $admin);

        $admin->fill($request->safe()->except('password'));

        if ($request->filled('password')) {
            $admin->password = $request->validated('password');
        }

        $admin->save();

        // A deactivated admin, or one whose password was reset, is signed out everywhere.
        if (! $admin->is_active || $request->filled('password')) {
            $admin->tokens()->delete();
        }

        return AdminResource::make($admin->loadCount('posts'))
            ->additional(['message' => "{$admin->name}’s account was updated."]);
    }

    public function destroy(Request $request, Admin $admin): JsonResponse
    {
        $this->abortIfOwnAccount($request, $admin);

        $admin->delete();

        return response()->json(['message' => "{$admin->name} was removed. Their posts are still on the blog."]);
    }

    private function abortIfOwnAccount(Request $request, Admin $admin): void
    {
        abort_if(
            $admin->is($request->user()),
            403,
            'You can’t change your own role or status, or delete your own account. Use My account for your details.',
        );
    }
}
