<?php

namespace Spatie\Permission\Repositories;

use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\PermissionCrudContract;
use Spatie\Permission\Contracts\Permission;

class PermissionCrudRepository implements PermissionCrudContract
{
    protected $permission;

    public function __construct()
    {
        $this->permission = app()->make(config('permission.models.permission'));
    }

    public function getAllPermissions(): Collection
    {
        return $this->permission::all();
    }

    public function getPermissionByName($name): Permission
    {
        return $this->permission::where('name', $name)->firstOrFail();
    }

    public function getPermissionById($id): Permission
    {
        return $this->permission::findOrFail($id);
    }

    public function createPermission(array $attributes): Permission
    {
        return $this->permission::create([
            'name'       => $attributes['name'],
            'guard_name' => array_key_exists('guard_name', $attributes) ? $attributes['guard_name'] : config('auth.defaults.guard')
        ]);
    }

    public function updatePermission($permission, array $attributes): Permission
    {
        $permission = $this->resolvePermissionArgument($permission);
        $permission->update($attributes);

        return $permission;
    }

    public function deletePermission($permission): bool
    {
        $permission = $this->resolvePermissionArgument($permission);

        return $permission->delete();
    }

    private function resolvePermissionArgument($permission): Permission
    {
        if ($permission instanceof Permission) {
            return $permission;
        }

        if (is_numeric($permission)) {
            return $this->permission::findOrFail($permission);
        }

        if (is_string($permission)) {
            return $this->getPermissionByName($permission);
        }
    }

}