<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\LoginRequest;
use App\Http\Resources\Admin\AdminResource;
use App\Models\AdminToken;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class AuthController extends Controller
{
    /**
     * Exchange an email and password for a bearer token. "Keep me signed in"
     * makes the token last 30 days instead of one.
     */
    public function store(LoginRequest $request): JsonResponse
    {
        $admin = $request->authenticate();

        $admin->forceFill(['last_login_at' => now()])->save();

        return response()->json([
            ...AdminToken::issue($admin, $request->boolean('remember')),
            'admin' => AdminResource::make($admin),
        ]);
    }

    /**
     * Sign out this browser only; other devices stay signed in.
     */
    public function destroy(Request $request): Response
    {
        AdminToken::findValid($request->bearerToken())?->delete();

        return response()->noContent();
    }
}
