<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRequest;
use App\Http\Resources\Admin\AdminResource;
use Illuminate\Http\Request;

class AccountController extends Controller
{
    /**
     * The signed-in admin. The React admin loads this on start-up.
     */
    public function show(Request $request): AdminResource
    {
        return AdminResource::make($request->user()->loadCount('posts'));
    }

    public function update(AccountRequest $request): AdminResource
    {
        $admin = $request->user();

        $admin->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $admin->password = $request->validated('password');
        }

        $admin->save();

        return AdminResource::make($admin->loadCount('posts'))
            ->additional(['message' => 'Your account was updated.']);
    }
}
