<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;

interface HasPermissionsContract
{

    /**
     * Get all roles assigned to model through history.
     *
     * @param \Illuminate\Database\Eloquent $model
     *
     * @return Collection
     */
    public function getRoles($model): Collection;

    /**
     * Get all roles currently assigned to model.
     *
     * @param \Illuminate\Database\Eloquent $model
     *
     * @return Collection
     */
    public function getCurrentRoles($model): Collection;

    /**
     * Assign a role to the model. If no start is provided, now() will be set. If no end is given, role will last indefinitely.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param array|string|\Spatie\Permission\Contracts\Role $role
     * @param null|string|\Carbon\Carbon $start
     * @param null|string|\Carbon\Carbon $end
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function assignRole($model, $role, $start = null, $end = null);

    /**
     * End a current role on model. If no end is provided, now() will be set.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param array|string|\Spatie\Permission\Contracts\Role $role
     * @param null|string|\Carbon\Carbon $end
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function endRole($model, $role, $end = null);

    /**
     * Delete the role from the model.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param array|string|\Spatie\Permission\Contracts\Role $role
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function deleteRole($model, $role);


    /**
     * Get all direct permissions assigned to model through history.
     *
     * @param \Illuminate\Database\Eloquent $model
     *
     * @return Collection
     */
    public function getDirectPermissions($model);

    /**
     * Get all direct permissions currently assigned to model.
     *
     * @param \Illuminate\Database\Eloquent $model
     *
     * @return Collection
     */
    public function getCurrentDirectPermissions($model);

    /**
     * Assign a direct permission to a model
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permission
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function assignDirectPermission($model, $permission, $start = null, $end = null);

    /**
     * End a model's direct permission
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param string|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permission
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function endDirectPermission($model, $permission, $end = null);

    /**
     * Delete a model's direct permission
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param \Spatie\Permission\Contracts\Permission|string $permission
     *
     * @return \Illuminate\Database\Eloquent $model
     */
    public function deleteDirectPermission($model, $permission);


    /**
     * Determine if the model has one of the given roles.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param string|array|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $role
     *
     * @return bool
     */
    public function hasRole($model, $role);

    /**
     * Determine if the model has all of the given role(s).
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param string|\Spatie\Permission\Contracts\Role|\Illuminate\Support\Collection $roles
     *
     * @return bool
     */
    public function hasAllRoles($model, $roles);

    /**
     * Determine if the model has the given permission, either directly or through role.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($model, $permission);

    /**
     * Determine if the model has any of the given permissions.
     *
     * @param \Illuminate\Database\Eloquent $model
     * @param array|\Illuminate\Support\Collection $permissions
     *
     * @return bool
     */
    public function hasAnyPermission($model, $permissions): bool;
}