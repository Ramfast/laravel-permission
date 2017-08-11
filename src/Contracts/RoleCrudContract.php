<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;
use Spatie\Permission\Contracts\Role;

interface RoleCrudContract
{

    public function getAllRoles(): Collection;

    public function getRoleByName($name): Role;

    public function getRoleById($id): Role;

    public function createRole(array $attributes): Role;

    public function updateRole($role, array $attributes): Role;

    public function deleteRole($id): bool;

    /* Separate contract? */
    public function assignPermissionsToRole($role, $permissions): Role;

    public function removePermissionsFromRole($role, $permissions): Role;

    public function syncRolePermissions($role, $permissions): Role;
}