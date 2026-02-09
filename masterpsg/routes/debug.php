<?php

use Illuminate\Support\Facades\Route;

// Debug route to check user permissions
Route::get('/debug/permissions', function() {
    $userId = session('user_id');
    $permissions = session('permissions', []);
    $isSuperadmin = session('is_superadmin', false);
    
    if (!$userId) {
        return 'Not logged in';
    }
    
    $user = \App\Models\User::find($userId);
    
    if (!$user) {
        return 'User not found in database';
    }
    
    $dbPermissions = $user->getPermissionSlugs();
    
    return [
        'user_id' => $userId,
        'is_superadmin' => $isSuperadmin,
        'session_permissions' => $permissions,
        'db_permissions' => $dbPermissions,
        'has_bahanbaku_view' => in_array('master.bahanbaku.view', $permissions),
        'userCan_result' => userCan('master.bahanbaku.view'),
    ];
});
