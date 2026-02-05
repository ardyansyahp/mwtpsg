<?php

namespace App\Http\Controllers\BahanBaku;

use App\Http\Controllers\Controller;
use App\Models\MBahanBaku;
use App\Models\MManpower;
use App\Models\MPerusahaan;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Carbon\Carbon;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class ReceivingController extends Controller
{
    private function buildQrcodeFromLot(string $tanggalReceiving, ?string $nomorBahanBaku, ?string $lotNumber, int $seq = 1): string
    {
        // Format sederhana, gampang dibaca, tanpa dependency eksternal
        // Contoh: RCV-20251218-MAT0001-LOT123-01
        $date = preg_replace('/[^0-9]/', '', $tanggalReceiving) ?: now()->format('Ymd');

        $nbb = $nomorBahanBaku ? strtoupper(trim($nomorBahanBaku)) : 'NA';
        $nbb = preg_replace('/[^A-Z0-9]/', '', $nbb) ?: 'NA';

        $lot = $lotNumber ? strtoupper(trim($lotNumber)) : '';
        $lot = $lot ? preg_replace('/[^A-Z0-9]/', '', $lot) : 'LOT';

        return sprintf('RCV-%s-%s-%s-%02d', $date, $nbb, $lot, $seq);
    }

    public function index(Request $request)
    {
        $query = Receiving::with(['supplier']);

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('no_surat_jalan', 'like', "%{$search}%")
                  ->orWhere('no_purchase_order', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            });
        }

        // Sorting
        $sortColumn = $request->get('sort_by', 'id');
        $sortDirection = $request->get('sort_order', 'desc');

        // Validate sort column to prevent SQL injection
        $allowedSortColumns = ['id', 'tanggal_receiving', 'no_surat_jalan', 'no_purchase_order', 'manpower', 'shift'];
        if (in_array($sortColumn, $allowedSortColumns)) {
            $query->orderBy($sortColumn, $sortDirection);
        } else {
            $query->orderBy('id', 'desc');
        }

        $perPage = $request->get('per_page', 10);
        $perPage = is_numeric($perPage) ? (int)$perPage : 10;

        $receivings = $query->withCount('details')
            ->paginate($perPage)->withQueryString();

        return view('bahanbaku.receiving.receiving', compact('receivings'));
    }

    public function create()
    {
        if (!userCan('bahanbaku.receiving.create')) {
            abort(403, 'Unauthorized action.');
        }

        $suppliers = MPerusahaan::active()->where('jenis_perusahaan', 'Supplier')
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        $bahanbakus = MBahanBaku::orderBy('nama_bahan_baku')->get();

        return view('bahanbaku.receiving.create', compact('suppliers', 'bahanbakus'));
    }

    public function store(Request $request)
    {
        if (!userCan('bahanbaku.receiving.create')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tanggal_receiving' => 'required|date',
            'supplier_id' => 'nullable|exists:M_Perusahaan,id',
            'no_surat_jalan' => 'nullable|string|max:100',
            'no_purchase_order' => 'nullable|string|max:100',
            // manpower pakai varchar (bebas isi nama/keterangan/jumlah)
            'manpower' => 'nullable|string|max:100',
            'shift' => 'nullable|string|max:20',

            'details' => 'nullable|array',
            'details.*.nomor_bahan_baku' => 'nullable|string|max:100|exists:M_BahanBaku,nomor_bahan_baku',
            'details.*.lot_number' => 'required_with:details.*.qty|string|max:100',
            'details.*.internal_lot_number' => 'nullable|string|max:255',
            'details.*.qty' => 'nullable|numeric|min:0.001',
            'details.*.uom' => 'nullable|string|max:50',
            // qrcode auto-generate dari lot_number (tetap boleh dikirim kalau mau override)
            'details.*.qrcode' => 'nullable|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated) {
            $details = $validated['details'] ?? [];
            unset($validated['details']);

            $receiving = Receiving::create($validated);

            $filtered = array_values(array_filter($details, function ($row) {
                $qty = $row['qty'] ?? null;
                $lot = $row['lot_number'] ?? null;
                return !empty($lot) && $qty !== null && $qty !== '';
            }));

            if (count($filtered) > 0) {
                // generate qrcode dari internal_lot_number atau lot_number, dan pastikan unik
                foreach ($filtered as $i => &$row) {
                    $row['qrcode'] = trim((string)($row['qrcode'] ?? ''));
                    $row['internal_lot_number'] = trim((string)($row['internal_lot_number'] ?? ''));
                    
                    if ($row['internal_lot_number'] !== '') {
                        $base = $row['internal_lot_number'];
                    } elseif ($row['qrcode'] !== '') {
                        $base = $row['qrcode'];
                    } else {
                        $base = $this->buildQrcodeFromLot(
                            (string) $receiving->tanggal_receiving?->format('Y-m-d'),
                            $row['nomor_bahan_baku'] ?? null,
                            (string) ($row['lot_number'] ?? ''),
                            $i + 1
                        );
                    }

                    $candidate = $base;
                    $suffix = 1;
                    while (ReceivingDetail::where('qrcode', $candidate)->exists()) {
                        $candidate = $base . '-' . $suffix;
                        $suffix++;
                    }
                    $row['qrcode'] = $candidate;
                }
                unset($row);

                $receiving->details()->createMany($filtered);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Receiving berhasil ditambahkan',
        ]);
    }

    public function edit(Receiving $receiving)
    {
        if (!userCan('bahanbaku.receiving.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $receiving->load(['details', 'supplier']);

        $suppliers = MPerusahaan::active()->where('jenis_perusahaan', 'Supplier')
            ->orWhereNull('jenis_perusahaan')
            ->orderBy('nama_perusahaan')
            ->get();

        $bahanbakus = MBahanBaku::orderBy('nama_bahan_baku')->get();

        return view('bahanbaku.receiving.edit', compact('receiving', 'suppliers', 'bahanbakus'));
    }

    public function detail(Receiving $receiving)
    {
        $receiving->load(['supplier', 'details.bahanBaku']);

        return view('bahanbaku.receiving.detail', compact('receiving'));
    }

    public function labels(Receiving $receiving)
    {
        $receiving->load(['supplier', 'details.bahanBaku']);

        return view('bahanbaku.receiving.labels', compact('receiving'));
    }

    public function update(Request $request, Receiving $receiving)
    {
        if (!userCan('bahanbaku.receiving.edit')) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tanggal_receiving' => 'required|date',
            'supplier_id' => 'nullable|exists:M_Perusahaan,id',
            'no_surat_jalan' => 'nullable|string|max:100',
            'no_purchase_order' => 'nullable|string|max:100',
            // manpower pakai varchar (bebas isi nama/keterangan/jumlah)
            'manpower' => 'nullable|string|max:100',
            'shift' => 'nullable|string|max:20',

            'details' => 'nullable|array',
            'details.*.nomor_bahan_baku' => 'nullable|string|max:100|exists:M_BahanBaku,nomor_bahan_baku',
            'details.*.lot_number' => 'required_with:details.*.qty|string|max:100',
            'details.*.qty' => 'nullable|numeric|min:0.001',
            'details.*.uom' => 'nullable|string|max:50',
            // qrcode auto-generate dari lot_number (tetap boleh dikirim kalau mau override)
            'details.*.qrcode' => 'nullable|string|max:255|distinct',
        ]);

        DB::transaction(function () use ($validated, $receiving) {
            $details = $validated['details'] ?? [];
            unset($validated['details']);

            $receiving->update($validated);

            $receiving->details()->delete();

            $filtered = array_values(array_filter($details, function ($row) {
                $qrcode = $row['qrcode'] ?? null;
                $qty = $row['qty'] ?? null;
                $lot = $row['lot_number'] ?? null;
                return !empty($lot) && $qty !== null && $qty !== '';
            }));

            if (count($filtered) > 0) {
                foreach ($filtered as $i => &$row) {
                    $row['qrcode'] = trim((string)($row['qrcode'] ?? ''));
                    if ($row['qrcode'] === '') {
                        $base = $this->buildQrcodeFromLot(
                            (string) $receiving->tanggal_receiving?->format('Y-m-d'),
                            $row['nomor_bahan_baku'] ?? null,
                            (string) ($row['lot_number'] ?? ''),
                            $i + 1
                        );

                        $candidate = $base;
                        $suffix = 1;
                        while (ReceivingDetail::where('qrcode', $candidate)->exists()) {
                            $suffix++;
                            $candidate = Str::limit($base, 245, '') . '-' . $suffix;
                        }
                        $row['qrcode'] = $candidate;
                    }
                }
                unset($row);

                $receiving->details()->createMany($filtered);
            }
        });

        return response()->json([
            'success' => true,
            'message' => 'Receiving berhasil diupdate',
        ]);
    }

    public function delete(Receiving $receiving)
    {
        if (!userCan('bahanbaku.receiving.delete')) {
            abort(403, 'Unauthorized action.');
        }

        $receiving->load(['supplier'])
            ->loadCount('details');

        return view('bahanbaku.receiving.delete', compact('receiving'));
    }

    public function destroy(Receiving $receiving)
    {
        if (!userCan('bahanbaku.receiving.delete')) {
            abort(403, 'Unauthorized action.');
        }

        DB::transaction(function () use ($receiving) {
            // Revert changes to Schedule Detail
            foreach ($receiving->details as $detail) {
                if ($detail->schedule_detail_id && $detail->qty > 0) {
                    $scheduleDetail = TScheduleDetail::find($detail->schedule_detail_id);
                    if ($scheduleDetail) {
                        $scheduleDetail->pc_act = max(0, $scheduleDetail->pc_act - $detail->qty);
                        $scheduleDetail->save();
                        
                        // Recalculate schedule header if method exists
                        if (method_exists($scheduleDetail, 'recalculate')) {
                            $scheduleDetail->recalculate();
                        }
                    }
                }
            }
            
            $receiving->delete();
        });

        return response()->json([
            'success' => true,
            'message' => 'Receiving berhasil dihapus',
        ]);
    }

    /**
     * API untuk mencari manpower berdasarkan QR code
     */
    public function findManpowerByQr(Request $request)
    {
        $qrcode = $request->input('qrcode');
        
        if (!$qrcode) {
            return response()->json([
                'success' => false,
                'message' => 'QR code tidak boleh kosong',
            ], 400);
        }

        // Cari berdasarkan mp_id (bukan qrcode)
        $manpower = MManpower::where('mp_id', $qrcode)->first();

        if (!$manpower) {
            return response()->json([
                'success' => false,
                'message' => 'Karyawan tidak ditemukan',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => [
                'id' => $manpower->id,
                'mp_id' => $manpower->mp_id,
                'nik' => $manpower->nik,
                'nama' => $manpower->nama,
                'departemen' => $manpower->departemen,
                'bagian' => $manpower->bagian,
                'qrcode' => $manpower->qrcode,
            ],
        ]);
    }

    /**
     * API untuk mendapatkan bahan baku berdasarkan supplier_id
     */
    public function getBahanBakuBySupplier(Request $request)
    {
        $supplierId = $request->input('supplier_id');
        
        if (!$supplierId) {
            return response()->json([
                'success' => false,
                'message' => 'Supplier ID tidak boleh kosong',
            ], 400);
        }

        $bahanBakus = MBahanBaku::where('supplier_id', $supplierId)
            ->orderBy('nama_bahan_baku')
            ->get();

        return response()->json([
            'success' => true,
            'data' => $bahanBakus->map(function ($bb) {
                return [
                    'id' => $bb->id,
                    'nomor_bahan_baku' => $bb->nomor_bahan_baku,
                    'nama_bahan_baku' => $bb->nama_bahan_baku,
                    'kategori' => $bb->kategori,
                ];
            }),
        ]);
    }

    /**
     * Form receiving berdasarkan PO Number (konsep baru)
     */
    public function createByPO()
    {
        if (!userCan('bahanbaku.receiving.create')) {
            abort(403, 'Unauthorized action.');
        }

        return view('bahanbaku.receiving.create-by-po');
    }

    /**
     * API: Fetch schedule berdasarkan PO Number
     */
    public function fetchScheduleByPO(Request $request)
    {
        $poNumber = $request->input('po_number');
        
        if (!$poNumber) {
            return response()->json([
                'success' => false,
                'message' => 'PO Number wajib diisi'
            ], 400);
        }

        try {
            // Cari schedule header berdasarkan PO Number
            $schedules = TScheduleHeader::with(['supplier', 'bahanBaku', 'details'])
                ->where('po_number', $poNumber)
                ->get();

            if ($schedules->isEmpty()) {
                return response()->json([
                    'success' => false,
                    'message' => 'PO Number tidak ditemukan di schedule'
                ], 404);
            }

            // Ambil supplier info (asumsi 1 PO = 1 supplier)
            $supplier = $schedules->first()->supplier;

            // Group schedule details by date and bahan baku
            $scheduleItems = [];
            
            foreach ($schedules as $schedule) {
                foreach ($schedule->details as $detail) {
                    $key = $schedule->bahan_baku_id . '-' . $detail->tanggal->format('Y-m-d');
                    
                    if (!isset($scheduleItems[$key])) {
                        $scheduleItems[$key] = [
                            'schedule_detail_id' => $detail->id,
                            'bahan_baku_id' => $schedule->bahan_baku_id,
                            'nomor_bahan_baku' => $schedule->bahanBaku->nomor_bahan_baku ?? '',
                            'nama_bahan_baku' => $schedule->bahanBaku->nama_bahan_baku ?? '',
                            'tanggal_schedule' => $detail->tanggal->format('Y-m-d'),
                            'tanggal_schedule_formatted' => $detail->tanggal->format('d M Y'),
                            'pc_plan' => $detail->pc_plan ?? 0,
                            'pc_act' => $detail->pc_act ?? 0,
                            'sisa' => ($detail->pc_plan ?? 0) - ($detail->pc_act ?? 0),
                            'uom' => $schedule->bahanBaku->uom ?? 'PCS',
                        ];
                    }
                }
            }

            return response()->json([
                'success' => true,
                'data' => [
                    'supplier' => [
                        'id' => $supplier->id ?? null,
                        'nama' => $supplier->nama_perusahaan ?? 'Unknown',
                    ],
                    'po_number' => $poNumber,
                    'items' => array_values($scheduleItems),
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Save receiving berdasarkan PO + sync ke schedule
     */
    public function storeByPO(Request $request)
    {
        if (!userCan('bahanbaku.receiving.create')) {
            abort(403, 'Unauthorized action.');
        }

        try {
            $validated = $request->validate([
                'po_number' => 'required|string',
                'supplier_id' => 'required|integer|exists:M_Perusahaan,id',
                'no_surat_jalan' => 'nullable|string',
                'manpower' => 'required|string',
                'shift' => 'required|string|in:1,2,3',
                'items' => 'required|array|min:1',
                'items.*.schedule_detail_id' => 'required|integer|exists:t_schedule_detail,id',
                'items.*.nomor_bahan_baku' => 'required|string',
                'items.*.qty' => 'nullable|numeric|min:0',
                'items.*.internal_lot_number' => 'nullable|string',
                'items.*.lot_number' => 'nullable|string',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $e->errors()
            ], 422);
        }

        try {
            $receiving = null;
            
            DB::transaction(function () use ($request, &$receiving) {
                // Create Receiving Header
                $receiving = Receiving::create([
                    'tanggal_receiving' => now()->format('Y-m-d'),
                    'supplier_id' => $request->supplier_id,
                    'no_surat_jalan' => $request->no_surat_jalan,
                    'no_purchase_order' => $request->po_number,
                    'manpower' => $request->manpower,
                    'shift' => $request->shift,
                ]);

                // Create Receiving Details + Sync ke Schedule
                foreach ($request->items as $index => $item) {
                    $qty = (float) ($item['qty'] ?? 0);
                    if ($qty <= 0) continue; // Skip jika qty 0 atau kosong

                    // Generate QR Code (Use Internal Lot Number as Base)
                    $internalLot = trim($item['internal_lot_number'] ?? '');
                    
                    if (!empty($internalLot)) {
                        $base = $internalLot;
                    } else {
                        // Fallback generator
                        $base = $this->buildQrcodeFromLot(
                            now()->format('Y-m-d'),
                            $item['nomor_bahan_baku'],
                            $item['lot_number'] ?? null,
                            $index + 1
                        );
                    }

                    $candidate = $base;
                    $suffix = 1;
                    // Ensure global uniqueness in receiving_detail
                    while (ReceivingDetail::where('qrcode', $candidate)->exists()) {
                        $candidate = $base . '-' . $suffix;
                        $suffix++;
                    }
                    $qrcode = $candidate;

                    // Create Receiving Detail
                    $receivingDetail = ReceivingDetail::create([
                        'receiving_id' => $receiving->id,
                        'schedule_detail_id' => $item['schedule_detail_id'] ?? null,
                        'nomor_bahan_baku' => $item['nomor_bahan_baku'],
                        'lot_number' => $item['lot_number'] ?? null,
                        'internal_lot_number' => $item['internal_lot_number'] ?? null,
                        'qty' => $item['qty'],
                        'uom' => $item['uom'] ?? 'PCS',
                        'qrcode' => $qrcode,
                    ]);

                    // Sync ke Schedule Detail (Update PC_ACT)
                    $scheduleDetail = TScheduleDetail::find($item['schedule_detail_id']);
                    if ($scheduleDetail) {
                        $scheduleDetail->pc_act += $item['qty'];
                        $scheduleDetail->save();
                        
                        // Recalculate schedule header totals
                        $scheduleDetail->recalculate();
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => 'Receiving berhasil disimpan dan schedule terupdate',
                'receiving_id' => $receiving->id
            ]);
        } catch (\Exception $e) {
            \Log::error('Error saving receiving by PO: ' . $e->getMessage(), [
                'trace' => $e->getTraceAsString(),
                'request_data' => $request->all()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine()
            ], 500);
        }
    }

    /**
     * Generate QR Code labels per item dari receiving
     */
    public function labelsByPO($receivingId)
    {
        $receiving = Receiving::with(['supplier', 'details.bahanBaku'])
            ->findOrFail($receivingId);

        return view('bahanbaku.receiving.labels-by-po', compact('receiving'));
    }
}
