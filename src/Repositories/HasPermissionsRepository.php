<?php

namespace Spatie\Permission\Repositories;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\HasPermissionsContract;

class HasPermissionsRepository implements HasPermissionsContract
{

    public function getRoles($model): Collection
    {
        return $model->roles()->get();
    }

    public function getActiveRoles($model): Collection
    {
        $now = Carbon::now();

        return $model->roles()
            ->where('start', '<=', $now->toDateTimeString())
            ->where(function ($query) use ($now) {
                $query->where('end', '>=', $now->toDateTimeString());
                $query->orWhereNull('end');
            })->get();
    }

    public function assignRole($model, $role, $start = null, $end = null)
    {
        $model->assignRole($start, $end, $role);
    }

    public function endRole($model, $role, $end = null)
    {
        $model->endRole($role, $end);
    }

    public function deleteRole($model, $role)
    {
        return $model->removeRole($role);
    }


    /* Getters, Assigners and Removers for Permissions */
    public function getAllModelPermissions($model)
    {
    }

    public function getAllActiveModelPermissions($model)
    {
    }

    public function assignDirectPermissionToModel($permission, $start = null, $end = null)
    {
    }

    public function endDirectPermissionOnModel($permission, $end = null)
    {
    }

    public function deleteDirectPermissionFromModel($permission_id)
    {
    }


    /* Checkers */
    public function hasRole($model, $role)
    {
    }

    public function hasPermissionTo($model, $permission)
    {
    }

    public function hasAnyRole($model, $roles)
    {
    }

    public function hasAllRoles($model, $roles)
    {
    }

    public function hasAnyPermission(...$permissions): bool
    {
        return false;
    }
}