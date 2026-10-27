<?php

namespace App\Http\Middleware;

use App\Enums\AdminRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Restricts a route to admins with one of the given roles, for example
 * ->middleware('admin.role:super_admin'). Runs after the "admin" middleware.
 */
class EnsureAdminHasRole
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $allowedRoles = array_map(AdminRole::from(...), $roles);

        abort_unless(
            in_array($request->user(AuthenticateAdmin::GUARD)?->role, $allowedRoles, true),
            Response::HTTP_FORBIDDEN,
            'Your role doesn’t give you access to this page.',
        );

        return $next($request);
    }
}
