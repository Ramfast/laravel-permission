<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Support\Collection;

interface PermissionCrudContract
{
    public function getAllPermissions(): Collection;

    public function getPermissionByName($name): Permission;

    public function getPermissionById($id): Permission;

    public function createPermission(array $attributes): Permission;

    public function updatePermission($permission, array $attributes): Permission;

    public function deletePermission($permission): bool;
}