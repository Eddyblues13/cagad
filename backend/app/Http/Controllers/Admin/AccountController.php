<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\AccountRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function edit(Request $request): View
    {
        return view('admin.account.edit', [
            'admin' => $request->user()->loadCount('posts'),
        ]);
    }

    public function update(AccountRequest $request): RedirectResponse
    {
        $admin = $request->user();

        $admin->fill($request->safe()->only(['name', 'email']));

        if ($request->filled('password')) {
            $admin->password = $request->validated('password');
        }

        $admin->save();

        return to_route('admin.account.edit')->with('status', 'Your account was updated.');
    }
}
