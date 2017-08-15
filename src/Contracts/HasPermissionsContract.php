<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;

interface HasPermissonsContract
{
    public function getRoles($model) : Collection;

    public function getActiveRoles($model) : Collection;

    public function assignRole($model, $role, $start = null, $end = null);

    public function endRole($model, $role, $end = null);

    public function deleteRole($model, $role);


    /* Getters, Assigners and Removers for Permissions */
    public function getAllModelPermissions($model);

    public function getAllActiveModelPermissions($model);

    public function assignDirectPermissionToModel($permission, $start = null, $end = null);

    public function endDirectPermissionOnModel($permission, $end = null);

    public function deleteDirectPermissionFromModel($permission_id);


    /* Checkers */
    public function hasRole($model, $role);

    public function hasPermissionTo($model, $permission);

    public function hasAnyRole($model, $roles);

    public function hasAllRoles($model, $roles);

    public function hasAnyPermission(...$permissions): bool;
}