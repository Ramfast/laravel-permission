<?php

namespace Spatie\Permission\Repositories;

use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Contracts\RoleCrudContract;

class RoleCrudRepository implements RoleCrudContract
{
    protected $role;
    protected $permission;

    public function __construct()
    {
        $this->role = app()->make(config('permission.models.role'));
        $this->permission = app()->make(config('permission.models.permission'));
    }


    public function getAllRoles(): Collection
    {
        return $this->role::all();
    }

    public function getRoleByName($name): Role
    {
        return $this->role::where('name', $name)->firstOrFail();
    }

    public function getRoleById($id): Role
    {
        return $this->role::findOrFail($id);
    }

    public function createRole(array $attributes): Role
    {
        return $this->role::create([
            'name' => $attributes['name']
        ]);
    }


    public function updateRole($role, array $attributes): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->update($attributes);

        return $role;
    }

    public function deleteRole($role): bool
    {
        $role = $this->resolveRoleArgument($role);

        return $role->delete();
    }

    public function assignPermissionsToRole($role, $permissions): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->givePermissionTo($permissions);

        return $role;
    }

    public function removePermissionsFromRole($role, $permissions): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->revokePermissionTo($permissions);

        return $role;
    }

    public function syncRolePermissions($role, $permissions): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->syncPermissions($permissions);

        return $role;
    }

    private function resolveRoleArgument($role): Role
    {
        if ($role instanceof Role) {
            return $role;
        }

        if (is_numeric($role)) {
            return $this->role::findOrFail($role);
        }

        if (is_string($role)) {
            return $this->getRoleByName($role);
        }
    }

}