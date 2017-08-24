<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;

interface RoleCrudContract
{

    /**
     * Get all the roles in the database.
     *
     * @return Collection
     */
    public function getAllRoles(): Collection;

    /**
     * Get a role by name.
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function getRoleByName($name): Role;

    /**
     * Get a role by id.
     *
     * @param int $id
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function getRoleById($id): Role;

    /**
     * Create a new role with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function createRole(array $attributes): Role;

    /**
     * Update a role with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function updateRole($role, array $attributes): Role;

    /**
     * Delete role with $id
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     *
     * @return bool
     */
    public function deleteRole($role): bool;

    /**
     * Assign one or more permissions to a role.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function assignPermissionsToRole($role, $permissions): Role;

    /**
     * Remove permission from role.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function removePermissionsFromRole($role, $permission): Role;


    /**
     * Hard delete all current permissions and set the given ones.
     *
     * @param int|string|\Spatie\Permission\Contracts\Role $role
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Role
     */
    public function syncRolePermissions($role, $permissions): Role;
}