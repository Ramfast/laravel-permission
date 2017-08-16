<?php

namespace Spatie\Permission\Traits;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Spatie\Permission\Contracts\Role;
use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Contracts\Permission;
use Illuminate\Database\Eloquent\Relations\MorphToMany;

trait HasRoles
{
    use HasPermissions;

    public static function bootHasRoles()
    {
        static::deleting(function ($model) {
            $model->roles()->detach();
            $model->permissions()->detach();
        });
    }

    /**
     * A model may have multiple roles.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role'),
            'model',
            config('permission.table_names.model_has_roles'),
            'model_id',
            'role_id'
        )->whereNull('model_has_roles.deleted_at');
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.permission'),
            'model',
            config('permission.table_names.model_has_permissions'),
            'model_id',
            'permission_id'
        )->whereNull('model_has_permissions.deleted_at');
    }

    /**
     * Scope the model query to certain roles only.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string|array|Role|\Illuminate\Support\Collection $roles
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeRole(Builder $query, $roles): Builder
    {
        if ($roles instanceof Collection) {
            $roles = $roles->toArray();
        }

        if (!is_array($roles)) {
            $roles = [$roles];
        }

        $roles = array_map(function ($role) {
            if ($role instanceof Role) {
                return $role;
            }

            return app(Role::class)->findByName($role, $this->getDefaultGuardName());
        }, $roles);

        return $query->whereHas('roles', function ($query) use ($roles) {
            $query->where(function ($query) use ($roles) {
                foreach ($roles as $role) {
                    $query->orWhere(config('permission.table_names.roles') . '.id', $role->id);
                }
            });
        });
    }

    /**
     * Assign the given role to the model.
     *
     * @param string|Carbon $start
     * @param string|Carbon $end
     * @param array|string|\Spatie\Permission\Contracts\Role ...$roles
     *
     * @return $this
     */
    public function assignRole($start = null, $end = null, ...$roles)
    {
        if (!$start) {
            $start = Carbon::now();
        }
        if (!$start instanceof Carbon) {
            $start = Carbon::parse($start);
        }
        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                return $this->getStoredRole($role);
            })
            ->each(function ($role) {
                $this->ensureModelSharesGuard($role);
            })
            ->all();

        foreach ($roles as $role) {
            $this->roles()->attach($role, [
                'start'      => $start->toDateTimeString(),
                'end'        => $end,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
        }

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Revoke the given role from the model.
     *
     * @param string|\Spatie\Permission\Contracts\Role $role
     *
     * @return bool
     */
    public function endRole($role, $end = null)
    {
        if (!$end) {
            $end = Carbon::now();
        }
        if (is_string($end)) {
            $end = Carbon::parse($end);
        }
        if (!$role instanceof Role) {
            $role = $this->getStoredRole($role);
        }

        $now = Carbon::now();

        $this->roles()
            ->where('name', $role->name)
            ->where('guard_name', $role->guard_name)
            ->where('start', '<=', $now->toDateTimeString())
            ->where(function ($query) use ($now) {
                $query->where('end', '>=', $now->toDateTimeString());
                $query->orWhereNull('end');
            })->updateExistingPivot($role->id, ['end' => $end]);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Delete the given role from the model.
     *
     * @param string|\Spatie\Permission\Contracts\Role $role
     *
     * @return $this
     */
    public function removeRole($role)
    {
        $role = $this->getStoredRole($role);
        $reflection = new ReflectionClass($this);
        DB::table('model_has_roles')
            ->where('model_id', $this->id)
            ->where('model_type', $reflection->getName())
            ->where('role_id', $role->id)
            ->whereNull('deleted_at')
            ->update(array('deleted_at' => DB::raw('NOW()')));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current roles and set the given ones. NB: Does hard delete.
     *
     * @param array ...$roles
     *
     * @return $this
     */
    public function syncRoles(...$roles)
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    /**
     * Determine if the model has (one of) the given role(s).
     *
     * @param string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            $current_roles = $this->getCurrentRoles();
            return $current_roles->contains('name', $roles);
        }
        if ($roles instanceof Role) {
            $current_roles = $this->getCurrentRoles();
            return $current_roles->contains('id', $roles->id);
        }
        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        return $roles->intersect($this->getCurrentRoles())->isNotEmpty();
    }

    public function getCurrentRoles()
    {
        $now = Carbon::now();
        return $this->roles()
            ->where('guard_name', $this->getDefaultGuardName())
            ->where('start', '<=', $now->toDateTimeString())
            ->where(function ($query) use ($now) {
                $query->where('end', '>=', $now->toDateTimeString());
                $query->orWhereNull('end');
            })

            ->get();
    }

    /**
     * Determine if the model has any of the given role(s).
     *
     * @param string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param string|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles) && false !== strpos($roles, '|')) {
            $roles = $this->convertPipeToArray($roles);
        }

        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains('id', $roles->id);
        }

        $roles = collect()->make($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        return $roles->intersect($this->roles->pluck('name')) == $roles;
    }

    /**
     * Determine if the model may perform the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     * @param string|null $guardName
     *
     * @return bool
     */
    public function hasPermissionTo($permission, $guardName = null): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName(
                $permission,
                $guardName ?? $this->getDefaultGuardName()
            );
        }

        return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
    }

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param array ...$permissions
     *
     * @return bool
     */
    public function hasAnyPermission(...$permissions): bool
    {
        if (is_array($permissions[0])) {
            $permissions = $permissions[0];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has, via roles, the given permission.
     *
     * @param \Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    protected function hasPermissionViaRole(Permission $permission): bool
    {
        $now = Carbon::now();
        $roles =
            $this->roles()
                ->where('start', '<=', $now->toDateTimeString())
                ->where(function ($query) use ($now) {
                    $query->where('end', '>=', $now->toDateTimeString());
                    $query->orWhereNull('end');
                })->get();

        return $roles->contains('id', $permission->id);

        return $this->hasRole($permission->roles);
    }

    /**
     * Determine if the model has the given permission.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function hasDirectPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = app(Permission::class)->findByName($permission, $this->getDefaultGuardName());

            if (!$permission) {
                return false;
            }
        }

        $now = Carbon::now();
        $permissions =
            $this->permissions()
                ->where('start', '<=', $now->toDateTimeString())
                ->where(function ($query) use ($now) {
                    $query->where('end', '>=', $now->toDateTimeString());
                    $query->orWhereNull('end');
                })->get();

        return $permissions->contains('id', $permission->id);
    }

    /**
     * Return all permissions the directory coupled to the model.
     */
    public function getDirectPermissions(): Collection
    {
        return $this->permissions;
    }

    /**
     * Return all the permissions the model has via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->load('roles', 'roles.permissions')
            ->roles->flatMap(function ($role) {
                return $role->permissions;
            })->sort()->values();
    }

    /**
     * Return all the permissions the model has, both directly and via roles.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions
            ->merge($this->getPermissionsViaRoles())
            ->sort()
            ->values();
    }

    protected function getStoredRole($role): Role
    {
        if (is_string($role)) {
            return app(Role::class)->findByName($role, $this->getDefaultGuardName());
        }

        return $role;
    }

    protected function convertPipeToArray(string $pipeString)
    {
        $pipeString = trim($pipeString);

        if (strlen($pipeString) <= 2) {
            return $pipeString;
        }

        $quoteCharacter = substr($pipeString, 0, 1);
        $endCharacter = substr($quoteCharacter, -1, 1);

        if ($quoteCharacter !== $endCharacter) {
            return explode('|', $pipeString);
        }

        if (!in_array($quoteCharacter, ["'", '"'])) {
            return explode('|', $pipeString);
        }

        return explode('|', trim($pipeString, $quoteCharacter));
    }
}
