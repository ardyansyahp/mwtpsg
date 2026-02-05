<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Models\MBahanBaku;
use App\Models\MPerusahaan;
use App\Models\MBahanBakuMaterial;
use App\Models\MBahanBakuSubpart;
use App\Models\MBahanBakuBox;
use App\Models\MBahanBakuLayer;
use App\Models\MBahanBakuPolybag;
use App\Models\MBahanBakuRempart;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Imports\BahanBakuImport;

class BahanBakuController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if (!userCan('master.bahanbaku.view')) abort(403);

        $query = MBahanBaku::query()->with(['supplier', 'material', 'subpart', 'box', 'layer', 'polybag', 'rempart']);

        // Search
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('nama_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nomor_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('kategori', 'like', "%{$search}%");
            });
        }

        // Filter Kategori
        if ($request->has('kategori') && $request->kategori) {
            $query->where('kategori', $request->kategori);
        }

        // Filter Status
        if ($request->has('status') && $request->status !== null) {
            $query->where('status', $request->status);
        }

        // Sort
        $sortColumn = $request->get('sort_by', 'created_at');
        $sortDirection = $request->get('sort_order', 'desc');
        $query->orderBy($sortColumn, $sortDirection);

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $bahanbakus = $query->paginate($perPage)->withQueryString();

        return view('submaster.bahanbaku.bahanbaku', compact('bahanbakus'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        if (!userCan('master.bahanbaku.create')) abort(403);

        $suppliers = MPerusahaan::active()->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('submaster.bahanbaku.create', compact('suppliers'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        if (!userCan('master.bahanbaku.create')) abort(403);

        $kategori = $request->kategori;
        
        // 1. Check Trash Only if Nomor Bahan Baku is manually entered
        // Note: For box/layer/polybag, nomor is generated, so maybe check later?
        // But usually unique check is on nomor_bahan_baku.
        if ($request->filled('nomor_bahan_baku')) {
             $existingTrash = MBahanBaku::onlyTrashed()
                ->where('nomor_bahan_baku', $request->nomor_bahan_baku)
                ->first();

            if ($existingTrash) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data dengan Nomor Bahan Baku tersebut sudah ada di SAMPAH (Trash). Silahkan restore data tersebut.'
                ], 422);
            }
        }
        
        // Validation Rules
        $group1 = ['material', 'masterbatch'];
        $group2 = ['subpart', 'box', 'layer', 'polybag', 'rempart'];
        
        $rules = [
            'kategori' => 'required|in:material,masterbatch,subpart,box,layer,polybag,rempart',
            'nomor_bahan_baku' => 'nullable|string|max:100|unique:m_bahanbaku,nomor_bahan_baku',
            'nomor_bahan_baku' => 'nullable|string|max:100|unique:m_bahanbaku,nomor_bahan_baku',
            'supplier_id' => 'nullable|exists:m_perusahaan,id',
            'keterangan' => 'nullable|string',
        ];

        // Group 1: MATERIAL, MASTERBATCH
        if (in_array($kategori, $group1)) {
            $rules['nama_bahan_baku'] = 'required|string|max:255';
            $rules['std_packing'] = 'nullable|numeric|min:0';
            $rules['uom'] = 'nullable|string|max:50';
            $rules['jenis_packing'] = 'nullable|string|max:50';
        }
        // Group 2: SUBPART, BOX, LAYER, POLYBAG, REMPART
        elseif (in_array($kategori, $group2)) {
            if ($kategori === 'subpart') {
                $rules['nama_bahan_baku'] = 'required|string|max:255';
            }
            $rules['std_packing'] = 'nullable|numeric|min:0';
            $rules['uom'] = 'nullable|string|max:50';
            $rules['jenis_packing'] = 'nullable|string|max:50';
            
            if ($kategori === 'box') {
                $rules['jenis'] = 'required|in:polybox,impraboard';
                $rules['kode_box'] = 'nullable|string|max:50';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'layer') {
                $rules['jenis'] = 'required|in:ldpe,polyfoam_sheet,layer_sheet,karton,foam_sheet,foam_bag';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'polybag') {
                $rules['jenis'] = 'required|in:ldpe';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'rempart') {
                $rules['jenis'] = 'required|in:karton_box_p0_d0,polybag_p0_p0,gasket_duplex_p0_ld,foam_sheet_p0_s0,hologram_p0_h0,label_a,label_b';
            }
        }

        $validated = $request->validate($rules);

        // Auto-generate nomor_bahan_baku for box, layer, polybag if empty
        if (empty($validated['nomor_bahan_baku']) && in_array($kategori, ['box', 'layer', 'polybag'])) {
            $validated['nomor_bahan_baku'] = $this->generateNomorBahanBaku($validated);
            
            // Check Generated Nomor in Trash
            if ($validated['nomor_bahan_baku']) {
                 $existingTrash = MBahanBaku::onlyTrashed()
                    ->where('nomor_bahan_baku', $validated['nomor_bahan_baku'])
                    ->first();

                if ($existingTrash) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data (Auto-Generated) dengan Nomor: ' . $validated['nomor_bahan_baku'] . ' sudah ada di SAMPAH. Silahkan restore.'
                    ], 422);
                }
                
                // Also check active Unique manually because validate() skipped it (it was null then)
                 if (MBahanBaku::where('nomor_bahan_baku', $validated['nomor_bahan_baku'])->exists()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Data (Auto-Generated) dengan Nomor: ' . $validated['nomor_bahan_baku'] . ' sudah ada.'
                    ], 422);
                }
            }
        }

        // Tentukan nama_bahan_baku untuk tabel induk
        $namaBahanBaku = $validated['nama_bahan_baku'] ?? ($validated['nomor_bahan_baku'] ?? '-');

        DB::beginTransaction();
        try {
            // Create main record
            $bahanBaku = MBahanBaku::create([
                'kategori' => $validated['kategori'],
                'nama_bahan_baku' => $namaBahanBaku,
                'nomor_bahan_baku' => $validated['nomor_bahan_baku'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'status' => true,
                'qrcode' => $validated['nomor_bahan_baku'] ?? null, // Default QRCode = Nomor Bahan Baku
                'keterangan' => $validated['keterangan'] ?? null,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // Create detail based on kategori
            $this->createDetail($bahanBaku, $validated, $kategori);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data bahan baku berhasil ditambahkan'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal menyimpan data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        if (!userCan('master.bahanbaku.view')) abort(403);
        $bahanbaku = MBahanBaku::withTrashed()->with(['supplier', 'material', 'subpart', 'box', 'layer', 'polybag', 'rempart'])->findOrFail($id);
        
        // Pass qrcode string
        $qrCode = $bahanbaku->qrcode ?? $bahanbaku->nomor_bahan_baku;

        return view('submaster.bahanbaku.show', compact('bahanbaku', 'qrCode'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(MBahanBaku $bahanbaku)
    {
        if (!userCan('master.bahanbaku.edit')) abort(403);

        $bahanbaku->load(['supplier', 'material', 'subpart', 'box', 'layer', 'polybag', 'rempart']);
        
        $suppliers = MPerusahaan::active()->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        return view('submaster.bahanbaku.edit', compact('bahanbaku', 'suppliers'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, MBahanBaku $bahanbaku)
    {
        if (!userCan('master.bahanbaku.edit')) abort(403);

        $kategori = $request->kategori;
        $group1 = ['material', 'masterbatch'];
        $group2 = ['subpart', 'box', 'layer', 'polybag', 'rempart'];
        
        $rules = [
            'kategori' => 'required|in:material,masterbatch,subpart,box,layer,polybag,rempart',
            'nomor_bahan_baku' => 'nullable|string|max:100|unique:m_bahanbaku,nomor_bahan_baku,' . $bahanbaku->id,
            'nomor_bahan_baku' => 'nullable|string|max:100|unique:m_bahanbaku,nomor_bahan_baku,' . $bahanbaku->id,
            'supplier_id' => 'nullable|exists:m_perusahaan,id',
            'keterangan' => 'nullable|string',
        ];

        // Validation Rules Logic (Same as Store)
        if (in_array($kategori, $group1)) {
            $rules['nama_bahan_baku'] = 'required|string|max:255';
            $rules['std_packing'] = 'nullable|numeric|min:0';
            $rules['uom'] = 'nullable|string|max:50';
            $rules['jenis_packing'] = 'nullable|string|max:50';
        } elseif (in_array($kategori, $group2)) {
            if ($kategori === 'subpart') {
                $rules['nama_bahan_baku'] = 'required|string|max:255';
            }
             // Box usually doesn't need packing info validation if not required? keeping same as store
            $rules['std_packing'] = 'nullable|numeric|min:0';
            $rules['uom'] = 'nullable|string|max:50';
            $rules['jenis_packing'] = 'nullable|string|max:50';
            
            if ($kategori === 'box') {
                $rules['jenis'] = 'required|in:polybox,impraboard';
                $rules['kode_box'] = 'nullable|string|max:50';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'layer') {
                $rules['jenis'] = 'required|in:ldpe,polyfoam_sheet,layer_sheet,karton,foam_sheet,foam_bag';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'polybag') {
                $rules['jenis'] = 'required|in:ldpe';
                $rules['panjang'] = 'nullable|numeric|min:0';
                $rules['lebar'] = 'nullable|numeric|min:0';
                $rules['tinggi'] = 'nullable|numeric|min:0';
            } elseif ($kategori === 'rempart') {
                $rules['jenis'] = 'required|in:karton_box_p0_d0,polybag_p0_p0,gasket_duplex_p0_ld,foam_sheet_p0_s0,hologram_p0_h0,label_a,label_b';
            }
        }

        $validated = $request->validate($rules);

        // Auto-generate nomor_bahan_baku for box, layer, polybag if empty
        if (empty($validated['nomor_bahan_baku']) && in_array($kategori, ['box', 'layer', 'polybag'])) {
            $validated['nomor_bahan_baku'] = $this->generateNomorBahanBaku($validated);
        }

        $namaBahanBaku = $validated['nama_bahan_baku'] ?? ($validated['nomor_bahan_baku'] ?? '-');

        DB::beginTransaction();
        try {
            // Update main record
            $bahanbaku->update([
                'kategori' => $validated['kategori'],
                'nama_bahan_baku' => $namaBahanBaku,
                'nomor_bahan_baku' => $validated['nomor_bahan_baku'] ?? null,
                'supplier_id' => $validated['supplier_id'] ?? null,
                'keterangan' => $validated['keterangan'] ?? null,
                'qrcode' => $validated['nomor_bahan_baku'] ?? null, // Sync QRCode
                'updated_at' => now(),
            ]);

            // Clean old details and create new
            $bahanbaku->material?->delete();
            $bahanbaku->subpart?->delete();
            $bahanbaku->box?->delete();
            $bahanbaku->layer?->delete();
            $bahanbaku->polybag?->delete();
            $bahanbaku->rempart?->delete();

            $this->createDetail($bahanbaku, $validated, $kategori);

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Data bahan baku berhasil diperbarui'
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Gagal memperbarui data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage (Soft Delete).
     */
    public function destroy(MBahanBaku $bahanbaku)
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);

        try {
            $bahanbaku->delete();
            return response()->json([
                'success' => true,
                'message' => 'Data bahan baku berhasil dihapus'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal menghapus data: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Helper to create detail records
     */
    private function createDetail($bahanBaku, $validated, $kategori)
    {
        $group1 = ['material', 'masterbatch'];
        
        if (in_array($kategori, $group1)) {
            MBahanBakuMaterial::create(['bahan_baku_id' => $bahanBaku->id, 'nama_bahan_baku' => $validated['nama_bahan_baku'], 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        } elseif ($kategori === 'subpart') {
            MBahanBakuSubpart::create(['bahan_baku_id' => $bahanBaku->id, 'nama_bahan_baku' => $validated['nama_bahan_baku'], 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        } elseif ($kategori === 'box') {
            MBahanBakuBox::create(['bahan_baku_id' => $bahanBaku->id, 'jenis' => $validated['jenis'], 'kode_box' => $validated['kode_box'] ?? null, 'panjang' => $validated['panjang'] ?? null, 'lebar' => $validated['lebar'] ?? null, 'tinggi' => $validated['tinggi'] ?? null, 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        } elseif ($kategori === 'layer') {
            MBahanBakuLayer::create(['bahan_baku_id' => $bahanBaku->id, 'jenis' => $validated['jenis'], 'panjang' => $validated['panjang'] ?? null, 'lebar' => $validated['lebar'] ?? null, 'tinggi' => $validated['tinggi'] ?? null, 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        } elseif ($kategori === 'polybag') {
            MBahanBakuPolybag::create(['bahan_baku_id' => $bahanBaku->id, 'jenis' => $validated['jenis'], 'panjang' => $validated['panjang'] ?? null, 'lebar' => $validated['lebar'] ?? null, 'tinggi' => $validated['tinggi'] ?? null, 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        } elseif ($kategori === 'rempart') {
            MBahanBakuRempart::create(['bahan_baku_id' => $bahanBaku->id, 'jenis' => $validated['jenis'], 'std_packing' => $validated['std_packing'] ?? null, 'uom' => $validated['uom'] ?? null, 'jenis_packing' => $validated['jenis_packing'] ?? null]);
        }
    }

    /**
     * Generate nomor bahan baku
     */
    private function generateNomorBahanBaku($validated)
    {
        $kategori = $validated['kategori'];
        $jenis = $validated['jenis'] ?? '';
        
        if (empty($jenis)) return null;
        
        $jenisLabels = [
            'box' => ['polybox' => 'Polybox', 'impraboard' => 'Impraboard'],
            'layer' => ['ldpe' => 'LDPE', 'polyfoam_sheet' => 'Polyfoam Sheet', 'layer_sheet' => 'Layer Sheet', 'karton' => 'Karton', 'foam_sheet' => 'Foam Sheet', 'foam_bag' => 'Foam Bag'],
            'polybag' => ['ldpe' => 'LDPE'],
        ];
        
        $jenisLabel = $jenisLabels[$kategori][$jenis] ?? ucfirst(str_replace('_', ' ', $jenis));
        $nomor = $jenisLabel;
        $panjang = $validated['panjang'] ?? null;
        $lebar = $validated['lebar'] ?? null;
        $tinggi = $validated['tinggi'] ?? null;
        
        if ($kategori === 'box') {
            $kodeBox = $validated['kode_box'] ?? null;
            if ($kodeBox) $nomor .= '-' . $kodeBox;
        }
        
        if ($panjang && $lebar && $tinggi) $nomor .= '-' . $panjang . 'x' . $lebar . 'x' . $tinggi . 'cm';
        elseif ($panjang && $lebar) $nomor .= '-' . $panjang . 'x' . $lebar . 'cm';
        elseif ($panjang) $nomor .= '-' . $panjang . 'cm';
        
        return $nomor;
    }

    /**
     * Delete Confirmation View
     */
    public function delete(MBahanBaku $bahanbaku)
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        $bahanbaku->load('supplier');
        return view('submaster.bahanbaku.delete', compact('bahanbaku'));
    }

    /**
     * Trash View
     */
    public function trash()
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        $trashed = MBahanBaku::onlyTrashed()->with('supplier')->orderBy('deleted_at', 'desc')->paginate(10);
        return view('submaster.bahanbaku.trash', compact('trashed'));
    }

    /**
     * Restore
     */
    public function restore($id)
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        MBahanBaku::onlyTrashed()->findOrFail($id)->restore();
        return back()->with('success', 'Data berhasil dipulihkan');
    }

    /**
     * Restore All
     */
    public function restoreAll()
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        MBahanBaku::onlyTrashed()->restore();
        return back()->with('success', 'Semua data sampah berhasil dipulihkan');
    }

    /**
     * Force Delete
     */
    public function forceDelete($id)
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        MBahanBaku::onlyTrashed()->findOrFail($id)->forceDelete();
        return back()->with('success', 'Data permanen dihapus key');
    }

    /**
     * Empty Trash
     */
    public function forceDeleteAll()
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        MBahanBaku::onlyTrashed()->forceDelete();
        return back()->with('success', 'Sampah berhasil dikosongkan');
    }

    /**
     * Destroy All (Reset Active Data)
     */
    public function destroyAll()
    {
         if (!userCan('master.bahanbaku.delete')) abort(403);
         MBahanBaku::query()->delete();
         return back()->with('success', 'Semua data berhasil dihapus');
    }

    /**
     * Bulk Delete
     */
    public function bulkDelete(Request $request)
    {
        if (!userCan('master.bahanbaku.delete')) abort(403);
        $request->validate(['ids' => 'required|array']);
        MBahanBaku::whereIn('id', $request->ids)->delete();
        return response()->json(['success' => true]);
    }

    /**
     * Toggle Status
     */
    public function toggleStatus($id)
    {
        if (!userCan('master.bahanbaku.edit')) abort(403);
        try {
            $bahanbaku = MBahanBaku::findOrFail($id);
            $bahanbaku->status = !$bahanbaku->status;
            $bahanbaku->save();
            return response()->json(['success' => true, 'message' => 'Status berhasil diubah', 'new_status' => $bahanbaku->status]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Show Import Form
     */
    public function showImportForm()
    {
        if (!userCan('master.bahanbaku.create')) abort(403);
        return view('submaster.bahanbaku.import');
    }

    /**
     * Import Process
     */
    public function import(Request $request)
    {
        if (!userCan('master.bahanbaku.create')) abort(403);

        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
            'col_kategori' => 'required',
            'col_nomor' => 'required',
            'col_nama' => 'required',
            'col_status' => 'nullable',
            'col_keterangan' => 'nullable',
        ]);

        try {
            $file = $request->file('file');
            $startRow = $request->start_row;
            
            $mapping = [
                'col_kategori' => $request->col_kategori,
                'col_nomor' => $request->col_nomor,
                'col_nama' => $request->col_nama,
                'col_supplier' => $request->col_supplier,
                'col_jenis' => $request->col_jenis,
                'col_uom' => $request->col_uom,
                'col_std_packing' => $request->col_std_packing,
                'col_jenis_packing' => $request->col_jenis_packing,
                'col_panjang' => $request->col_panjang,
                'col_lebar' => $request->col_lebar,
                'col_tinggi' => $request->col_tinggi,
                'col_kode_box' => $request->col_kode_box,
                'col_status' => $request->col_status,
                'col_keterangan' => $request->col_keterangan,
            ];

            $importer = new BahanBakuImport($mapping, $startRow);
            $importer->import($file->getRealPath());
            
            $stats = $importer->getStats();
            
            $message = sprintf(
                'Import selesai! Sukses: %d, Diupdate: %d, Gagal: %d', 
                $stats['success'], 
                $stats['updated'], 
                $stats['failed']
            );

            if (!empty($stats['errors'])) {
                $errorMsg = implode('<br>', array_slice($stats['errors'], 0, 5));
                if (count($stats['errors']) > 5) $errorMsg .= '<br>...dan lainnya.';
                return redirect()->route('master.bahanbaku.index')->with('warning', $message . '<br>Errors:<br>' . $errorMsg);
            }

            return redirect()->route('master.bahanbaku.index')->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    /**
     * Export
     */
    public function export()
    {
        if (!userCan('master.bahanbaku.view')) abort(403);
        $fileName = 'data_bahanbaku_' . date('Y-m-d_H-i-s') . '.csv';
        $data = MBahanBaku::with('supplier')->orderBy('created_at', 'desc')->get();

        $headers = [
            "Content-type" => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma" => "no-cache",
            "Cache-Control" => "must-revalidate, post-check=0, pre-check=0",
            "Expires" => "0"
        ];

        $columns = ['No', 'Kategori', 'Nomor Bahan Baku', 'Nama Bahan Baku', 'Supplier', 'Status', 'Created At'];

        $callback = function() use($data, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($data as $i => $item) {
                fputcsv($file, [
                    $i + 1,
                    $item->kategori,
                    $item->nomor_bahan_baku,
                    $item->nama_bahan_baku,
                    $item->supplier ? $item->supplier->nama_perusahaan : '-',
                    $item->status ? 'Active' : 'Inactive',
                    $item->created_at->format('Y-m-d H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
