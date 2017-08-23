<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\PermissionRegistrar;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Illuminate\Database\Eloquent\Relations\MorphToMany;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Spatie\Permission\Exceptions\PermissionAlreadyExists;
use Spatie\Permission\Contracts\Permission as PermissionContract;

class Permission extends Model implements PermissionContract
{
    use RefreshesPermissionCache;
    use SoftDeletes;

    public $guarded = ['id'];

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->setTable(config('permission.table_names.permissions'));
    }

    public static function create(array $attributes = [])
    {
        if (static::getPermissions()->where('name', $attributes['name'])->first()) {
            throw PermissionAlreadyExists::create($attributes['name']);
        }

        return static::query()->create($attributes);
    }

    /**
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            config('permission.models.role'),
            config('permission.table_names.role_has_permissions')
        );
    }

    /**
     * A permission belongs to some users of the model.
     */
    public function users(): MorphToMany
    {
        return $this->morphedByMany(
            'App\User',
            'model',
            config('permission.table_names.model_has_permissions'),
            'permission_id',
            'model_id'
        );
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     *
     * @return \Spatie\Permission\Contracts\Permission
     */
    public static function findByName(string $name): PermissionContract
    {
        $permission = static::getPermissions()->where('name', $name)->first();

        if (!$permission) {
            throw PermissionDoesNotExist::create($name);
        }

        return $permission;
    }

    /**
     * Get the current cached permissions.
     */
    protected static function getPermissions(): Collection
    {
        return app(PermissionRegistrar::class)->getPermissions();
    }
}
