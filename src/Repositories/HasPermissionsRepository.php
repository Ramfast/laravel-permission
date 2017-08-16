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
        /* TODO: make sure you can not create duplicates. */
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


    public function hasRole($model, $role)
    {
        return $model->hasRole($role);
    }

    public function hasPermissionTo($model, $permission)
    {
        return $model->hasPermissionTo($permission);
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

    /*
     *  By "direct permissions" we refer to permissions that are directly attached to the model
     *  via model_has_permissions table. As we encourage the use of permissions either through a role or a relation,
     *  these methods are all moved down to the bottom of this file. Use with caution.
     */
    public function getAllDirectPermissions($model)
    {
        return $model->permissions()->get();
    }

    public function getAllActiveDirectPermissions($model)
    {
        $now = Carbon::now();

        return $model->permissions()
            ->where('start', '<=', $now->toDateTimeString())
            ->where(function ($query) use ($now) {
                $query->where('end', '>=', $now->toDateTimeString());
                $query->orWhereNull('end');
            })->get();
    }

    public function assignDirectPermission($model, $permission, $start = null, $end = null)
    {
        /* TODO: make sure you can not create duplicates. */
        return $model->givePermissionTo($start, $end, $permission);
    }

    public function endDirectPermission($model, $permission, $end = null)
    {
        return $model->endPermissionTo($permission, $end);
    }

    public function deleteDirectPermission($model, $permission)
    {
        return $model->revokePermissionTo($permission);
    }

}
