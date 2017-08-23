<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class PermissionMiddleware
{
    public function handle($request, Closure $next, $permission)
    {
        if (Auth::guest()) {
            throw new AccessDeniedException("You do not have access to this resource.");
        }

        $permission = is_array($permission) ? $permission : explode('|', $permission);

        if (! Auth::user()->hasAnyPermission(...$permission)) {
            throw new AccessDeniedException("You do not have access to this resource.");
        }

        return $next($request);
    }
}
