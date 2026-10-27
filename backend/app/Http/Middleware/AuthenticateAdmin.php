<?php

namespace App\Http\Middleware;

use App\Models\Admin;
use Closure;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

/**
 * Lets a request into the admin panel only when an active admin is signed in
 * through the "admin" guard.
 *
 * The guard then becomes the default for the rest of the request, so
 * $request->user(), @auth and rules such as current_password resolve the admin.
 */
class AuthenticateAdmin
{
    public const GUARD = 'admin';

    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     *
     * @throws AuthenticationException
     */
    public function handle(Request $request, Closure $next): Response
    {
        $admin = Auth::guard(self::GUARD)->user();

        if (! $admin instanceof Admin) {
            throw new AuthenticationException(
                guards: [self::GUARD],
                redirectTo: $request->expectsJson() ? null : route('admin.login'),
            );
        }

        if (! $admin->is_active) {
            return $this->signOutDeactivatedAdmin($request);
        }

        Auth::shouldUse(self::GUARD);

        return $next($request);
    }

    /**
     * End the session of an admin whose account was switched off after they signed in.
     */
    private function signOutDeactivatedAdmin(Request $request): Response
    {
        Auth::guard(self::GUARD)->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        $message = 'Your account has been deactivated, so you were signed out.';

        abort_if($request->expectsJson(), Response::HTTP_FORBIDDEN, $message);

        return to_route('admin.login')->withErrors(['email' => $message]);
    }
}
