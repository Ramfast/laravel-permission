<?php

namespace Spatie\Permission\Traits;

use Carbon\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use ReflectionClass;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Contracts\Permission;

trait HasPermissions
{
    /**
     * Grant the given permission(s) to a role.
     *
     * @param string|Carbon $start
     * @param string|Carbon $end
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function givePermissionTo($start = null, $end = null, ...$permissions)
    {
        if (!$start) {
            $start = Carbon::now();
        }
        if (!$start instanceof Carbon) {
            $start = Carbon::parse($start);
        }
        if (is_string($end)) {
            $end = Carbon::parse($end);
        }

        $permissions = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->all();

        foreach ($permissions as $permission) {
            $this->permissions()->attach($permission, [
                'start'      => $start->toDateTimeString(),
                'end'        => $end,
                'created_at' => Carbon::now()->toDateTimeString(),
                'updated_at' => Carbon::now()->toDateTimeString()
            ]);
        }

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Revoke the given permission from the model.
     *
     * @param string|\Spatie\Permission\Contracts\Permission $permission
     *
     * @return bool
     */
    public function endPermissionTo($permission, $end = null)
    {
        if (!$end) {
            $end = Carbon::now();
        }
        if (is_string($end)) {
            $end = Carbon::parse($end);
        }
        if (!$permission instanceof Permission) {
            $permission = $this->getStoredPermission($permission);
        }

        $now = Carbon::now();
        $this->permissions()
            ->where('name', $permission->name)
            ->where('start', '<=', $now->toDateTimeString())
            ->where(function ($query) use ($now) {
                $query->where('end', '>=', $now->toDateTimeString());
                $query->orWhereNull('end');
            })->updateExistingPivot($permission->id, ['end' => $end]);

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * Remove all current permissions and set the given ones.
     *
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return $this
     */
    public function syncPermissions(...$permissions)
    {
        $this->permissions()->detach();

        return $this->givePermissionTo($permissions);
    }

    /**
     * Revoke the given permission.
     *
     * @param \Spatie\Permission\Contracts\Permission|string $permission
     *
     * @return $this
     */
    public function revokePermissionTo($permission)
    {
        $permission = $this->getStoredPermission($permission);
        $reflection = new ReflectionClass($this);
        DB::table('model_has_permissions')
            ->where('model_id', $this->id)
            ->where('model_type', $reflection->getName())
            ->where('permission_id', $permission->id)
            ->whereNull('deleted_at')
            ->update(array('deleted_at' => DB::raw('NOW()')));

        $this->forgetCachedPermissions();

        return $this;
    }

    /**
     * @param string|array|\Spatie\Permission\Contracts\Permission|\Illuminate\Support\Collection $permissions
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    protected function getStoredPermission($permissions): Permission
    {
        if (is_string($permissions)) {
            return app(Permission::class)->findByName($permissions);
        }

        if (is_array($permissions)) {
            return app(Permission::class)
                ->whereIn('name', $permissions)
                ->get();
        }

        return $permissions;
    }

    /**
     * Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
