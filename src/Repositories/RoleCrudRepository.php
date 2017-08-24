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

    /**
     * Get all the roles in the database.
     *
     * @return Collection
     */
    public function getAllRoles(): Collection
    {
        return $this->role::all();
    }

    /**
     * Get a role by name.
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function getRoleByName($name): Role
    {
        return $this->role::where('name', $name)->firstOrFail();
    }

    /**
     * Get a role by id.
     *
     * @param int $id
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function getRoleById($id): Role
    {
        return $this->role::findOrFail($id);
    }

    /**
     * Create a new role with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function createRole(array $attributes): Role
    {
        return $this->role::create([
            'name' => $attributes['name']
        ]);
    }

    /**
     * Update a role with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function updateRole($role, array $attributes): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->update($attributes);

        return $role;
    }

    /**
     * Delete role with $id
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     *
     * @return bool
     */
    public function deleteRole($role): bool
    {
        $role = $this->resolveRoleArgument($role);

        return $role->delete();
    }

    /**
     * Assign one or more permissions to a role.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function assignPermissionsToRole($role, $permissions): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->givePermissionTo($permissions);

        return $role;
    }

    /**
     * Remove permission from role.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function removePermissionsFromRole($role, $permission): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->revokePermissionTo($permission);

        return $role;
    }

    /**
     * Hard delete all current permissions and set the given ones.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function syncRolePermissions($role, $permissions): Role
    {
        $role = $this->resolveRoleArgument($role);
        $role->syncPermissions($permissions);

        return $role;
    }

    /**
     * Return a role based on the type of argument given.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     *
     * @return \Spatie\Permission\Contracts\Role
     */
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