<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MManpower;
use Illuminate\Http\Request;

class ManpowerController extends Controller
{
    private function columnToIndex($col)
    {
        if (empty($col)) return -1;
        $col = strtoupper(trim($col));
        $len = strlen($col);
        $num = 0;
        for ($i = 0; $i < $len; $i++) {
            $num = $num * 26 + ord($col[$i]) - 0x40;
        }
        return $num - 1;
    }

    private function buildManpowerQrcode(MManpower $m): string
    {
        $baseSeed = strtoupper(trim($m->mp_id ?: ($m->nik ?: (string) $m->id)));
        $seed = preg_replace('/[^A-Z0-9]/', '', $baseSeed) ?: (string) $m->id;
        $base = sprintf('MP-%s', $seed);
        $candidate = substr($base, 0, 255);
        $suffix = 1;
        while (MManpower::where('qrcode', $candidate)->exists()) {
            $suffix++;
            $candidate = substr($base, 0, 245) . '-' . $suffix;
        }
        return $candidate;
    }

    public function index(Request $request)
    {
        $query = MManpower::query();

        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%")
                  ->orWhere('departemen', 'like', "%{$search}%")
                  ->orWhere('qrcode', 'like', "%{$search}%");
            });
        }

        // Filter Departemen
        if ($request->has('departemen_filter') && $request->departemen_filter) {
             $query->where('departemen', $request->departemen_filter);
        }

        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        
        $allowedSorts = ['nik', 'nama', 'departemen', 'bagian', 'status', 'created_at'];
        if (!in_array($sortColumn, $allowedSorts)) {
            $sortColumn = 'created_at';
        }
        
        $sortDirection = strtolower($sortDirection) === 'asc' ? 'asc' : 'desc';

        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $manpowers = $query->paginate($perPage)->onEachSide(1);
        $manpowers->appends($request->all());

        return view('master.manpower.manpower', compact('manpowers'));
    }

    public function create()
    {
        if (!userCan('master.manpower.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.manpower.create');
    }

    public function store(Request $request)
    {
        if (!userCan('master.manpower.create')) {
            abort(403, 'Unauthorized action.');
        }

        // Check trash Logic (by NIK if exists, or Name)
        $query = MManpower::onlyTrashed();
        if ($request->filled('nik')) {
            $query->where('nik', $request->nik);
        } else {
            $query->where('nama', $request->nama); // Fallback uniqueness
        }
        $existingTrash = $query->first();

        if ($existingTrash) {
            return response()->json([
                'success' => false,
                'message' => 'Data Manpower ini sudah ada di SAMPAH (Trash). Silahkan restore data tersebut.'
            ], 422);
        }

        $validated = $request->validate([
            'nik' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'departemen' => 'nullable|string|max:100',
            'bagian' => 'nullable|string|max:100',
            'role' => 'nullable|in:0,1,2',
            'password' => 'nullable|string|min:4'
        ]);

        // Validate Password Requirement for High Roles
        $role = (int) ($request->role ?? 0);
        if (in_array($role, [1, 2]) && empty($request->password)) {
             return response()->json([
                'success' => false,
                'message' => 'Password wajib diisi untuk Role Superadmin atau Management'
            ], 422);
        }

        // Generate ID: Nama|NIK
        $generatedId = trim($validated['nama']) . '|' . trim($validated['nik'] ?? '');
        
        $validated['mp_id'] = $generatedId;
        $validated['qrcode'] = $generatedId;
        $validated['status'] = true;

        $manpower = MManpower::create($validated);

        // Sync with User Account
        $user = \App\Models\User::firstOrCreate(
            ['user_id' => $manpower->mp_id],
            ['role' => 0]
        );
        
        $user->role = $role;
        if ($request->filled('password')) {
            $user->password = bcrypt($request->password);
        }
        $user->save();
        
        // Auto-assign Management Permissions if Role 2
        if ($role === 2) {
             $managementPerms = \App\Models\Permission::whereIn('slug', [
                'superadmin.users.index', 'superadmin.users.create', 'superadmin.users.destroy',
                'superadmin.users.permissions.edit', 'superadmin.users.bulk_permissions'
            ])->pluck('id');
            $user->permissions()->sync($managementPerms);
        }

        return response()->json([
            'success' => true,
            'message' => 'Data manpower & user berhasil ditambahkan'
        ]);
    }

    public function show($id)
    {
        if (!userCan('master.manpower.index')) {
            abort(403, 'Unauthorized action.');
        }

        $manpower = MManpower::withTrashed()->findOrFail($id);
        
        // Placeholder stats
        $stats = [
            'total_hours' => 0,
            'attendance' => '0%',
            'last_active' => '-'
        ];

        return view('master.manpower.show', compact('manpower', 'stats'));
    }

    public function edit(MManpower $manpower)
    {
        if (!userCan('master.manpower.edit')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.manpower.edit', compact('manpower'));
    }

    public function update(Request $request, MManpower $manpower)
    {
        if (!userCan('master.manpower.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'nik' => 'nullable|string|max:50',
            'nama' => 'required|string|max:255',
            'departemen' => 'nullable|string|max:100',
            'bagian' => 'nullable|string|max:100',
        ]);

        // Generate ID baru saat update
        $generatedId = trim($validated['nama']) . '|' . trim($validated['nik'] ?? '');
        
        $validated['mp_id'] = $generatedId;
        $validated['qrcode'] = $generatedId;

        $manpower->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data manpower berhasil diupdate'
        ]);
    }

    public function delete(MManpower $manpower)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        return view('master.manpower.delete', compact('manpower'));
    }
    
    public function destroy(MManpower $manpower)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $manpower->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data manpower berhasil dihapus (Soft Delete)'
        ]);
    }

    public function trash(Request $request)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $query = MManpower::onlyTrashed();

        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nik', 'like', "%{$search}%")
                  ->orWhere('nama', 'like', "%{$search}%");
            });
        }

        $manpowers = $query->orderBy('deleted_at', 'desc')->paginate(10);

        return view('master.manpower.trash', compact('manpowers'));
    }

    public function restore($id)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $manpower = MManpower::withTrashed()->findOrFail($id);
        $manpower->restore();

        return redirect()->back()->with('success', 'Data manpower berhasil dipulihkan');
    }

    public function restoreAll()
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MManpower::onlyTrashed()->restore();

        return redirect()->back()->with('success', 'Semua data sampah berhasil dipulihkan');
    }

    public function forceDelete($id)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $manpower = MManpower::withTrashed()->findOrFail($id);
        $manpower->forceDelete();

        return redirect()->back()->with('success', 'Data manpower berhasil dihapus permanen');
    }

    public function forceDeleteAll()
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        MManpower::onlyTrashed()->forceDelete();

        return redirect()->back()->with('success', 'Semua sampah berhasil dibersihkan permanen');
    }

    public function toggleStatus($id)
    {
        if (!userCan('master.manpower.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $manpower = MManpower::findOrFail($id);
        $manpower->status = !$manpower->status;
        $manpower->save();

        return response()->json([
            'success' => true,
            'message' => 'Status manpower berhasil diupdate',
            'new_status' => $manpower->status
        ]);
    }

    public function bulkDelete(Request $request)
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:m_manpower,id'
        ]);

        MManpower::whereIn('id', $request->ids)->delete();

        return response()->json([
            'success' => true,
            'message' => count($request->ids) . ' data berhasil dihapus'
        ]);
    }

    public function destroyAll()
    {
        if (!userCan('master.manpower.delete')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            MManpower::query()->delete();
            return redirect()->route('master.manpower.index')
                ->with('success', 'Semua data manpower berhasil dihapus (Soft Delete)');
        } catch (\Exception $e) {
            return redirect()->route('master.manpower.index')
                ->with('error', 'Gagal menghapus data: ' . $e->getMessage());
        }
    }

    public function showImportForm()
    {
        if (!userCan('master.manpower.create')) {
            abort(403, 'Unauthorized action.');
        }
        return view('master.manpower.import');
    }

    public function import(Request $request)
    {
         if (!userCan('master.manpower.create')) {
            abort(403, 'Unauthorized action.');
        }

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:2048'
        ]);

        try {
            $file = $request->file('file');
            $path = $file->getRealPath();
            
            $handle = fopen($path, "r");
            // Skip header based on start_row
            $startRow = max(1, (int)$request->input('start_row', 2));
            for ($i = 1; $i < $startRow; $i++) {
                fgetcsv($handle, 1000, ",");
            }
            
            // Map Columns
            $idxNik = $this->columnToIndex($request->input('col_nik', 'A'));
            $idxNama = $this->columnToIndex($request->input('col_nama', 'B'));
            $idxDept = $this->columnToIndex($request->input('col_departemen', 'C'));
            $idxBagian = $this->columnToIndex($request->input('col_bagian', 'D'));
            $idxRole = $this->columnToIndex($request->input('col_role')); // Optional
            $idxPass = $this->columnToIndex($request->input('col_password')); // Optional
            
            $count = 0;
            while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                // Extract Values using dynamic index
                $nik = ($idxNik >= 0 && isset($data[$idxNik])) ? trim($data[$idxNik]) : null;
                $nama = ($idxNama >= 0 && isset($data[$idxNama])) ? trim($data[$idxNama]) : null;
                
                if ($nama) {
                     $dept = ($idxDept >= 0 && isset($data[$idxDept])) ? trim($data[$idxDept]) : null;
                     $bagian = ($idxBagian >= 0 && isset($data[$idxBagian])) ? trim($data[$idxBagian]) : null;
                     
                     // Generate ID
                     $mpId = $nama . '|' . ($nik ?? '');
                     
                     $manpower = MManpower::create([
                        'nik' => $nik,
                        'nama' => $nama,
                        'departemen' => $dept,
                        'bagian' => $bagian,
                        'mp_id' => $mpId,
                        'qrcode' => $mpId,
                        'status' => 1
                     ]);
                     
                     // User Account logic
                     $role = ($idxRole >= 0 && isset($data[$idxRole])) ? (int)$data[$idxRole] : 0;
                     $password = ($idxPass >= 0 && isset($data[$idxPass])) ? trim($data[$idxPass]) : null;

                     $user = \App\Models\User::firstOrCreate(
                        ['user_id' => $mpId],
                        ['role' => 0]
                     );
                     $user->role = $role;
                     if (!empty($password)) {
                         $user->password = bcrypt($password);
                     }
                     $user->save();

                     if ($role === 2) {
                        $managementPerms = \App\Models\Permission::whereIn('slug', [
                            'superadmin.users.index', 'superadmin.users.create', 'superadmin.users.destroy',
                            'superadmin.users.permissions.edit', 'superadmin.users.bulk_permissions'
                        ])->pluck('id');
                        $user->permissions()->sync($managementPerms);
                    }

                     $count++;
                }
            }
            fclose($handle);

            return redirect()->route('master.manpower.index')->with('success', "$count data berhasil diimport");

        } catch (\Exception $e) {
             return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    public function idcard(MManpower $manpower)
    {
        if (empty($manpower->qrcode)) {
            $manpower->update([
                'qrcode' => $this->buildManpowerQrcode($manpower),
            ]);
            $manpower->refresh();
        }
        return view('master.manpower.idcard', compact('manpower'));
    }

    public function export()
    {
        if (!userCan('master.manpower.index')) {
            abort(403, 'Unauthorized action.');
        }

        $fileName = 'data_manpower_' . date('Y-m-d_H-i-s') . '.csv';
        $manpowers = MManpower::orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['NIK', 'Nama', 'Departemen', 'Bagian', 'Status', 'Created At'];

        $callback = function() use($manpowers, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($manpowers as $mp) {
                $row['nik']  = $mp->nik;
                $row['nama']    = $mp->nama;
                $row['departemen']    = $mp->departemen;
                $row['bagian']  = $mp->bagian;
                $row['status']  = $mp->status ? 'Active' : 'Inactive';
                $row['created_at']  = $mp->created_at;

                fputcsv($file, array($row['nik'], $row['nama'], $row['departemen'], $row['bagian'], $row['status'], $row['created_at']));
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
