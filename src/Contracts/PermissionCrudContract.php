<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;

interface PermissionCrudContract
{

    /**
     * Get all the permissions in the database.
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection;

    /**
     * Get a permission by name.
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function getPermissionByName($name): Permission;

    /**
     * Get a permission by id.
     *
     * @param int $id
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function getPermissionById($id): Permission;

    /**
     * Create a new permission with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function createPermission(array $attributes): Permission;

    /**
     * Update a permission with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function updatePermission($permission, array $attributes): Permission;

    /**
     * Delete permission with $id
     *
     * @param int|string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function deletePermission($permission): bool;
}
