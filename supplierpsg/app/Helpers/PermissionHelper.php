<?php

if (!function_exists('userCan')) {
    /**
     * Check if the current user has a specific permission.
     *
     * @param string $permission
     * @return bool
     */
    function userCan(string $permission): bool
    {
        // Check if user is logged in
        if (!session()->has('user_id') && !\Auth::check()) {
            return false;
        }
 
        // Superadmin has all permissions
        if (session('is_superadmin') == true || (\Auth::check() && \Auth::user()->is_superadmin)) {
            return true;
        }

        // Check if permission exists in session
        $permissions = session('permissions', []);
        
        // If permissions array contains '*', user has all permissions
        if (in_array('*', $permissions)) {
            return true;
        }

        return in_array($permission, $permissions);
    }
}

if (!function_exists('userCanAny')) {
    /**
     * Check if the current user has any of the specified permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function userCanAny(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (userCan($permission)) {
                return true;
            }
        }
        return false;
    }
}

if (!function_exists('userCanAll')) {
    /**
     * Check if the current user has all of the specified permissions.
     *
     * @param array $permissions
     * @return bool
     */
    function userCanAll(array $permissions): bool
    {
        foreach ($permissions as $permission) {
            if (!userCan($permission)) {
                return false;
            }
        }
        return true;
    }
}

if (!function_exists('countAccessiblePermissions')) {
    /**
     * Count how many permissions from the list the user has access to.
     *
     * @param array $permissions
     * @return int
     */
    function countAccessiblePermissions(array $permissions): int
    {
        $count = 0;
        foreach ($permissions as $permission) {
            if (userCan($permission)) {
                $count++;
            }
        }
        return $count;
    }
}

if (!function_exists('isSuperadmin')) {
    /**
     * Check if the current user is superadmin.
     *
     * @return bool
     */
    function isSuperadmin(): bool
    {
        return session('is_superadmin', false);
    }
}

if (!function_exists('currentUser')) {
    /**
     * Get the current user's information.
     *
     * @return array|null
     */
    function currentUser(): ?array
    {
        if (!session()->has('user_id')) {
            return null;
        }

        return [
            'user_id' => session('user_id'),
            'is_superadmin' => session('is_superadmin', false),
            'mp_nama' => session('mp_nama'),
            'permissions' => session('permissions', []),
        ];
    }
}

if (!function_exists('isLoggedIn')) {
    /**
     * Check if user is logged in.
     *
     * @return bool
     */
    function isLoggedIn(): bool
    {
        return session()->has('user_id');
    }
}
