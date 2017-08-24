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

    /**
     * Get all the permissions in the database.
     *
     * @return Collection
     */
    public function getAllPermissions(): Collection
    {
        return $this->permission::all();
    }

    /**
     * Get a permission by name.
     *
     * @param string $name
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function getPermissionByName($name): Permission
    {
        return $this->permission::where('name', $name)->firstOrFail();
    }

    /**
     * Get a permission by id.
     *
     * @param int $id
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function getPermissionById($id): Permission
    {
        return $this->permission::findOrFail($id);
    }

    /**
     * Create a new permission with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function createPermission(array $attributes): Permission
    {
        return $this->permission::create([
            'name' => $attributes['name'],
        ]);
    }

    /**
     * Update a permission with the given attributes.
     *
     * @param array $attributes
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public function updatePermission($permission, array $attributes): Permission
    {
        $permission = $this->resolvePermissionArgument($permission);
        $permission->update($attributes);

        return $permission;
    }

    /**
     * Delete permission with $id
     *
     * @param int|string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function deletePermission($permission): bool
    {
        $permission = $this->resolvePermissionArgument($permission);

        return $permission->delete();
    }

    /**
     * Return a permission based on the type of argument given.
     *
     * @param int|string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
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