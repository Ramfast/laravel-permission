<?php

namespace Spatie\Permission\Middlewares;

use Closure;
use Illuminate\Support\Facades\Auth;
use Psr\Log\InvalidArgumentException;
use Symfony\Component\Finder\Exception\AccessDeniedException;

class AssertAllPermissions
{
    public function handle($request, Closure $next, $permissions = null, $conditions = null)
    {
        if (!$permissions && !$conditions) {
            throw new InvalidArgumentException('Neither permissions nor conditions were provided to middleware.');
        }

        if (Auth::guest()) {
            throw new AccessDeniedException("You do not have access to this resource.");
        }

        if ($this->allPermissionsAreMet($permissions) && $this->allConditionsAreMet($conditions)) {
            return $next($request);
        }

        throw new AccessDeniedException("You do not have access to this resource.");

    }

    protected function allPermissionsAreMet($permissions = null)
    {
        if ('null' == $permissions) {
            return true;
        }

        $permissions = is_array($permissions) ? $permissions : explode('|', $permissions);
        foreach ($permissions as $permission) {
            if (!Auth::user()->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    protected function allConditionsAreMet($conditions = null)
    {
        $conditions = $this->resolveConditions($conditions);
        foreach ($conditions as $condition) {
            if (!$condition->validate()) {
                return false;
            }
        }

        return true;
    }

    protected function resolveConditions($conditions)
    {
        if (!$conditions) {
            return [];
        }

        $conditions = is_array($conditions) ? $conditions : explode('|', $conditions);

        foreach ($conditions as $key => $condition) {
            // if not complete namespace of condition is provided, we assume its in the App\Conditions namespace.
            $condition = class_exists($condition) ? $condition : "App\\Conditions\\" . $condition;

            $conditions[$key] = app()->make($condition);
        }

        return $conditions;
    }
}
