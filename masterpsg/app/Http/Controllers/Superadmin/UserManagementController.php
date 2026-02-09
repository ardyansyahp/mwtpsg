<?php

namespace App\Http\Controllers\Superadmin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use App\Models\Permission;
use App\Models\MManpower;

class UserManagementController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('permissions', 'manpower');

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('user_id', 'like', "%{$search}%")
                  ->orWhereHas('manpower', function($q2) use ($search) {
                      $q2->where('nama', 'like', "%{$search}%");
                  });
            });
        }

        $perPage = $request->input('per_page', 10);
        $users = $query->paginate($perPage)->withQueryString();
        return view('superadmin.users.index', compact('users'));
    }

    /**
     * Bulk update permissions for selected users.
     */
    /**
     * Show the form for bulk updating permissions.
     */
    public function bulkPermissions(Request $request)
    {
        $userIds = explode(',', $request->ids);
        $users = User::whereIn('id', $userIds)->with('manpower')->get();
        
        if ($users->isEmpty()) {
            return redirect()->route('superadmin.users.index')->with('error', 'No users selected');
        }

        $orderedCategories = [
            'management',
            'dashboard',
            'master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan',
            'master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart',
            'controlsupplier', 'bahanbaku_receiving',
            'finishgood_in', 'spk', 'finishgood_out',
            'shipping_controltruck', 'shipping_dispatch', 'shipping_delivery',
        ];

        $permissions = Permission::all()->reject(function($perm) {
            return in_array($perm->category, ['superadmin', 'dashboard']);
        });

        $allPermissions = $permissions->groupBy('category')
            ->sortBy(function($items, $key) use ($orderedCategories) {
                $index = array_search($key, $orderedCategories);
                return $index === false ? 999 : $index;
            });

        return view('superadmin.users.bulk_permissions', compact('users', 'allPermissions'));
    }

    /**
     * Bulk update permissions for selected users.
     */
    public function bulkUpdatePermissions(Request $request)
    {
        $request->validate([
            'user_ids' => 'required|array',
            'user_ids.*' => 'exists:users,id',
            'permissions' => 'required|array',
            'permissions.*' => 'exists:permissions,id'
        ]);

        $users = User::whereIn('id', $request->user_ids)->get();
        $count = 0;

        foreach ($users as $user) {
            $user->permissions()->syncWithoutDetaching($request->permissions);
            $count++;
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', "Permissions successfully added to {$count} users.");
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        // Load all manpower for autocomplete
        $manpowers = MManpower::select('mp_id', 'nama')->get();
        return view('superadmin.users.create', compact('manpowers'));
    }

    /**
     * Store a newly created user.
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|unique:users,user_id',
            'password' => 'nullable|min:4',
            'role' => 'nullable|in:0,1,2',
        ]);

        $role = $request->input('role', 0); // Default 0
        
        $user = User::create([
            'user_id' => $request->user_id,
            'password' => $request->password ? bcrypt($request->password) : null,
            'role' => $role,
        ]);

        // LOGIC FOR MANAGEMENT ROLE (2): Auto-assign Default Permissions
        if ($role == 2) {
            $managementPerms = Permission::whereIn('slug', [
                'superadmin.users.index',
                'superadmin.users.create',
                'superadmin.users.destroy',
                'superadmin.users.permissions.edit',
                'superadmin.users.bulk_permissions',
            ])->pluck('id');
            
            if ($managementPerms->isNotEmpty()) {
                $user->permissions()->sync($managementPerms);
            }
        }

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully. Role: ' . ($role == 2 ? 'Management' : ($role == 1 ? 'Superadmin' : 'Operator')));
    }

    /**
     * Display the specified user.
     */
    /**
     * Display the specified user.
     */
    public function show(User $user)
    {
        $user->load('permissions', 'manpower');
        
        $orderedCategories = [
            // Management
            'management',

            // Dashboard
            'dashboard',
            
            // Master Data (Master PSG)
            'master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan',
            
            // Sub Master (Master PSG)
            'master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart',
            
            // Bahan Baku (Supplier PSG)
            'controlsupplier', 'bahanbaku_receiving',
            
            // Finish Good (Shipping PSG)
            'finishgood_in', 'spk', 'finishgood_out',
            
            // Shipping (Shipping PSG)
            'shipping_controltruck', 'shipping_dispatch', 'shipping_delivery',
        ];

        // Filter out superadmin and old dashboard category
        $permissions = Permission::all()->reject(function($perm) {
            return in_array($perm->category, ['superadmin', 'dashboard']);
        });

        $allPermissions = $permissions->groupBy('category')
            ->sortBy(function($items, $key) use ($orderedCategories) {
                $index = array_search($key, $orderedCategories);
                return $index === false ? 999 : $index;
            });
        
        return view('superadmin.users.show', compact('user', 'allPermissions'));
    }

    /**
     * Show the form for editing user permissions.
     */
    public function editPermissions(User $user)
    {
        $user->load('permissions');
        
        $orderedCategories = [
            // Management
            'management',

            // Dashboard
            'dashboard',
            
            // Master Data (Master PSG)
            'master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan',
            
            // Sub Master (Master PSG)
            'master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart',
            
            // Bahan Baku (Supplier PSG)
            'controlsupplier', 'bahanbaku_receiving',
            
            // Finish Good (Shipping PSG)
            'finishgood_in', 'spk', 'finishgood_out',
            
            // Shipping (Shipping PSG)
            'shipping_controltruck', 'shipping_dispatch', 'shipping_delivery',
        ];

        // Filter out superadmin category
        $permissions = Permission::all()->reject(function($perm) {
            return in_array($perm->category, ['superadmin']);
        });

        $allPermissions = $permissions->groupBy('category')
            ->sortBy(function($items, $key) use ($orderedCategories) {
                $index = array_search($key, $orderedCategories);
                return $index === false ? 999 : $index;
            });
        
        return view('superadmin.users.permissions', compact('user', 'allPermissions'));
    }

    /**
     * Update user permissions.
     */
    public function updatePermissions(Request $request, User $user)
    {
        $permissionIds = $request->input('permissions', []);
        
        // Sync permissions (this will add new ones and remove unchecked ones)
        $user->permissions()->sync($permissionIds);

        return redirect()->route('superadmin.users.show', $user)
            ->with('success', 'Permissions updated successfully');
    }

    /**
     * Remove the specified user.
     */
    public function destroy(User $user)
    {
        // Prevent deleting superadmin
        if ($user->is_superadmin) {
            return redirect()->route('superadmin.users.index')
                ->with('error', 'Cannot delete superadmin user');
        }

        $user->delete();

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User deleted successfully');
    }
}
