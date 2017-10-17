<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Config;

class Role extends Model
{
    protected $table = 'roles';
    
    // Big block of caching functionality.
    // NOTE: Cache::tags is a temp solution, will use memached in production
    public function cachedPermissions()
    {
        $rolePrimaryKey = $this->primaryKey;
        $cacheKey = 'permissions_for_role_'.$this->$rolePrimaryKey;
        return Cache::tags('permission_role')
            ->remember($cacheKey, Config::get('cache.ttl'), function () {
                return $this->permissions()->get();
            });
    }
    public function save(array $options = [])
    {   //both inserts and updates
        $result = parent::save($options);
        Cache::tags('permission_role')->flush();
        return $result;
    }
    public function delete(array $options = [])
    {   //soft or hard
        $result = parent::delete($options);
        Cache::tags('permission_role')->flush();
        return $result;
    }
    public function restore()
    {   //soft delete undo's
        $result = parent::restore();
        Cache::tags('permission_role')->flush();
        return $result;
    }

    /**
     * Many-to-Many relations with the permission model.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany('App\Models\Permission', 'role_has_permission');
    }

    /**
     * Checks if the role has a permission by its name.
     *
     * @param string|array $name       Permission name or array of permission names.
     * @param bool         $requireAll All permissions in the array are required.
     *
     * @return bool
     */
    public function hasPermission($name, $requireAll = false)
    {
        if (is_array($name)) {
            foreach ($name as $permissionName) {
                $hasPermission = $this->hasPermission($permissionName);

                if ($hasPermission && !$requireAll) {
                    return true;
                } elseif (!$hasPermission && $requireAll) {
                    return false;
                }
            }

            // If we've made it this far and $requireAll is FALSE, then NONE of the permissions were found
            // If we've made it this far and $requireAll is TRUE, then ALL of the permissions were found.
            // Return the value of $requireAll;
            return $requireAll;
        } else {
            foreach ($this->cachedPermissions() as $permission) {
                if ($permission->name == $name) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Save the inputted permissions.
     *
     * @param mixed $inputPermissions
     *
     * @return void
     */
    public function savePermissions($inputPermissions)
    {
        if (!empty($inputPermissions)) {
            $this->permissions()->sync($inputPermissions);
        } else {
            $this->permissions()->detach();
        }
    }

    /**
     * Attach permission to current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function attachPermission($permission)
    {
        if (is_object($permission)) {
            $permission = $permission->getKey();
        }

        if (is_array($permission)) {
            $permission = $permission['id'];
        }

        $this->permissions()->attach($permission);
    }

    /**
     * Detach permission from current role.
     *
     * @param object|array $permission
     *
     * @return void
     */
    public function detachPermission($permission)
    {
        if (is_object($permission))
            $permission = $permission->getKey();

        if (is_array($permission))
            $permission = $permission['id'];

        $this->permissions()->detach($permission);
    }

    /**
     * Attach multiple permissions to current role.
     */
    public function attachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->attachPermission($permission);
        }
    }

    /**
     * Detach multiple permissions from current role
     */
    public function detachPermissions($permissions)
    {
        foreach ($permissions as $permission) {
            $this->detachPermission($permission);
        }
    }
}
