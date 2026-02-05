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
    public function index()
    {
        $users = User::with('permissions', 'manpower')->get();
        return view('superadmin.users.index', compact('users'));
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
        ]);

        User::create([
            'user_id' => $request->user_id,
            'password' => $request->password ? bcrypt($request->password) : null,
            'is_superadmin' => false,
        ]);

        return redirect()->route('superadmin.users.index')
            ->with('success', 'User created successfully');
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
            'dashboard_rinci',
            'master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan',
            'master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart',
            'controlsupplier', 'bahanbaku_receiving', 'planning', 'planning_matriks', 'bahanbaku_supply',
            'produksi_inject_in', 'produksi_inject_out',
            'produksi_wip_in', 'produksi_wip_out',
            'produksi_assy_in', 'produksi_assy_out',
            'finishgood_in', 'spk', 'finishgood_out',
            'shipping_controltruck', 'shipping_dispatch', 'shipping_delivery',
            'tracer'
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
            'dashboard_rinci',
            'master_perusahaan', 'master_mesin', 'master_manpower', 'master_plantgate', 'master_kendaraan',
            'master_bahanbaku', 'submaster_part', 'master_mold', 'submaster_plantgatepart',
            'controlsupplier', 'bahanbaku_receiving', 'planning', 'planning_matriks', 'bahanbaku_supply',
            'produksi_inject_in', 'produksi_inject_out',
            'produksi_wip_in', 'produksi_wip_out',
            'produksi_assy_in', 'produksi_assy_out',
            'finishgood_in', 'spk', 'finishgood_out',
            'shipping_controltruck', 'shipping_dispatch', 'shipping_delivery',
            'tracer'
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
