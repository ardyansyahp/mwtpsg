<?php

namespace App\Http\Controllers;

use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use App\Models\MBahanBaku;
use App\Models\Receiving;
use Illuminate\Http\Request;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Imports\SAPReceivingImport;
use App\Imports\ControlSupplierImport;
use Illuminate\Support\Facades\Auth;

class ControlSupplierController extends Controller
{
    /**
     * Tampilkan halaman monitoring per item (Excel-like view)
     */
    public function monitoring(Request $request)
    {
        $periode = $request->input('periode');
        if (!$periode || strlen($periode) < 7) {
            $periode = date('Y-m');
        }
        $kategori = $request->input('kategori', '');
        
        // Generate dates for the month
        $periodeDate = Carbon::createFromFormat('Y-m', $periode);
        $startDate = $periodeDate->copy()->startOfMonth();
        $endDate = $periodeDate->copy()->endOfMonth();
        
        $dates = [];
        for ($date = $startDate->copy(); $date->lte($endDate); $date->addDay()) {
            $dates[] = [
                'date' => $date->copy(),
                'day_name' => $date->format('D'),
                'day_num' => $date->format('d'),
                'month_name' => $date->format('M'),
                'is_weekend' => $date->isWeekend(),
            ];
        }
        
        // Get bahan baku with supplier (Paginated)
        $bahanBakuQuery = MBahanBaku::with('supplier')
            ->whereNotNull('supplier_id')
            ->whereHas('supplier');
        
        // Filter by kategori if provided
        if ($kategori) {
            if ($kategori === 'material') {
                $bahanBakuQuery->whereIn('kategori', ['material', 'masterbatch']);
            } else {
                $bahanBakuQuery->where('kategori', $kategori);
            }
        }

        // Filter via Search
        $search = $request->input('search');
        if ($search) {
            $bahanBakuQuery->where(function($q) use ($search) {
                $q->where('nama_bahan_baku', 'like', "%{$search}%")
                  ->orWhere('nomor_bahan_baku', 'like', "%{$search}%")
                  ->orWhereHas('supplier', function($sq) use ($search) {
                      $sq->where('nama_perusahaan', 'like', "%{$search}%");
                  });
            });
        }
        
        // OPTIMIZATION: Paginate the results
        $perPage = $request->get('per_page', 20);
        $perPage = is_numeric($perPage) ? (int)$perPage : 20;

        $bahanBakuList = $bahanBakuQuery->orderBy('nama_bahan_baku')->paginate($perPage)->withQueryString();
        $bahanBakuIds = $bahanBakuList->pluck('id');
        
        // Get schedules ONLY for the visible items
        // Eager load details filtered by date range essentially happens naturally via header
        $schedules = TScheduleHeader::with(['details' => function($q) use ($startDate, $endDate) {
                // Filter details within current month (optimizes detail fetching)
                $q->whereBetween('tanggal', [$startDate->format('Y-m-d'), $endDate->format('Y-m-d')]);
            }])
            ->where('periode', $periode)
            ->whereIn('bahan_baku_id', $bahanBakuIds)
            ->get()
            ->groupBy('bahan_baku_id');
        
        // Initialize item map from Paginated List
        $items = [];
        foreach ($bahanBakuList as $bb) {
            $key = $bb->id;
            
            $itemData = [
                'bahan_baku_id' => $bb->id,
                'nomor_bahan_baku' => $bb->nomor_bahan_baku,
                'nama_bahan_baku' => $bb->nama_bahan_baku,
                'kategori' => $bb->kategori,
                'supplier_id' => $bb->supplier_id,
                'supplier_name' => $bb->supplier->nama_perusahaan ?? '',
                'po_numbers' => [],
                'daily_details' => [],
                'total_plan' => 0,
                'total_act' => 0,
                'total_blc' => 0,
                'total_ar' => 0,
                'total_sr' => 0,
                'status' => 'OPEN',
            ];

            // Fill with schedule data if exists
            if (isset($schedules[$key])) {
                $itemSchedules = $schedules[$key];
                
                foreach ($itemSchedules as $schedule) {
                    if ($schedule->po_number && !in_array($schedule->po_number, $itemData['po_numbers'])) {
                        $itemData['po_numbers'][] = $schedule->po_number;
                    }

                    // Use stored aggregation if available and verified, but here we aggregate details for safety and daily logic
                    foreach ($schedule->details as $detail) {
                        $dateStr = $detail->tanggal->format('Y-m-d');
                        
                        if (!isset($itemData['daily_details'][$dateStr])) {
                            $itemData['daily_details'][$dateStr] = [
                                'plan' => 0,
                                'act' => 0,
                                'blc' => 0,
                                'ar' => 0,
                                'sr' => 0,
                                'status' => 'PENDING',
                                'ponumb' => $schedule->po_number ?? '',
                            ];
                        } else {
                             // Append PO if different
                            $currentPO = $itemData['daily_details'][$dateStr]['ponumb'];
                            if ($currentPO && strpos($currentPO, (string)$schedule->po_number) === false) {
                                $itemData['daily_details'][$dateStr]['ponumb'] .= ', ' . $schedule->po_number;
                            }
                        }
                        
                        $itemData['daily_details'][$dateStr]['plan'] += $detail->pc_plan ?? 0;
                        $itemData['daily_details'][$dateStr]['act'] += $detail->pc_act ?? 0;
                    }
                }
            }
            
            // Final Calculation for this Item
            // 1. Calculate Daily Cumulative & Daily Status
            $cumulativePlan = 0;
            $cumulativeAct = 0;
            
            // Ensure all dates are present in array? Optional (view handles empty).
            // But sorting fits better here.
            ksort($itemData['daily_details']);
            
            foreach ($itemData['daily_details'] as $dateStr => &$daily) {
                // Add to item totals
                $itemData['total_plan'] += $daily['plan'];
                $itemData['total_act'] += $daily['act'];
                
                // Daily Cumulative
                $cumulativePlan += $daily['plan'];
                $cumulativeAct += $daily['act'];
                
                $daily['blc'] = $cumulativePlan - $cumulativeAct;
                
                if ($cumulativePlan > 0) {
                    $daily['ar'] = ($cumulativeAct / $cumulativePlan) * 100;
                }
                
                if ($daily['plan'] > 0) {
                     $progress = min($cumulativeAct / $daily['plan'], 1.0);
                     $daily['sr'] = $progress * 100;
                }
                
                // Status logic
                if ($cumulativeAct == 0) {
                    $daily['status'] = 'OPEN';
                } elseif ($cumulativeAct >= $cumulativePlan && $cumulativePlan > 0) {
                    $daily['status'] = 'CLOSE';
                } else {
                    $daily['status'] = 'PENDING';
                }
            }
            
            // 2. Calculate Item Totals
            $itemData['total_blc'] = $itemData['total_plan'] - $itemData['total_act'];
            
            if ($itemData['total_plan'] > 0) {
                $itemData['total_ar'] = ($itemData['total_act'] / $itemData['total_plan']) * 100;
                
                 // SR for Item is usually average of daily progress or progress matching.
                 // Using simple progress here:
                 $progress = min($itemData['total_act'] / $itemData['total_plan'], 1.0);
                 $itemData['total_sr'] = $progress * 100;
            }
            
            if ($itemData['total_act'] == 0) {
                $itemData['status'] = 'OPEN';
            } elseif ($itemData['total_act'] >= $itemData['total_plan']) {
                $itemData['status'] = 'CLOSE';
            } else {
                $itemData['status'] = 'PENDING';
            }
            
            $items[] = $itemData;
        }
        
        // Return view without layout (pass paginator for links)
        return view('controlsupplier.monitoring-content', compact('items', 'dates', 'periode', 'bahanBakuList'));
    }
    
    /**
     * API: Update PO Number
     */
    public function updatePONumb(Request $request)
    {
        $request->validate([
            'bahan_baku_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'periode' => 'required|string',
            'tanggal' => 'required|date',
            'ponumb' => 'required|string',
            'old_ponumb' => 'nullable|string',
        ]);
        
        $tanggal = Carbon::parse($request->tanggal);
        
        try {
            DB::transaction(function () use ($request, $tanggal) {
                // Find or create schedule header used for the NEW PO
                $header = TScheduleHeader::firstOrCreate(
                    [
                        'periode' => $request->periode,
                        'supplier_id' => $request->supplier_id,
                        'bahan_baku_id' => $request->bahan_baku_id,
                        'po_number' => $request->ponumb,
                    ],
                    [
                        'total_plan_auto' => 0,
                        'total_plan' => 0,
                        'total_act' => 0,
                        'total_blc' => 0,
                        'total_status' => 'OPEN',
                        'total_ar' => 0,
                        'total_sr' => 0,
                    ]
                );
                
                // Logic for Move/Rename vs New
                if ($request->filled('old_ponumb') && $request->old_ponumb !== $request->ponumb) {
                    // Seeking the OLD header and detail to move it
                    $oldHeader = TScheduleHeader::where('periode', $request->periode)
                        ->where('supplier_id', $request->supplier_id)
                        ->where('bahan_baku_id', $request->bahan_baku_id)
                        ->where('po_number', $request->old_ponumb)
                        ->first();
                        
                    $detailMoved = false;
                    
                    if ($oldHeader) {
                        $detail = TScheduleDetail::where('schedule_header_id', $oldHeader->id)
                            ->whereDate('tanggal', $tanggal)
                            ->first();
                            
                        if ($detail) {
                            $detail->schedule_header_id = $header->id;
                            $detail->po_number = $request->ponumb;
                            $detail->save();
                            $detailMoved = true;
                            
                             // Cleanup old header if no details left
                            if ($oldHeader->details()->count() === 0) {
                                $oldHeader->delete();
                            }
                        }
                    }
                    
                    if (!$detailMoved) {
                        // Fallback implies old detail wasn't found, so just create new
                       TScheduleDetail::updateOrCreate(
                            [
                                'schedule_header_id' => $header->id,
                                'tanggal' => $tanggal,
                            ],
                            [
                                'po_number' => $request->ponumb,
                                'pc_status' => 'PENDING',
                            ]
                        );
                    }
                } else {
                    // Standard Add/Ensure logic
                    TScheduleDetail::updateOrCreate(
                        [
                            'schedule_header_id' => $header->id,
                            'tanggal' => $tanggal,
                        ],
                        [
                            'po_number' => $request->ponumb,
                            'pc_status' => 'PENDING',
                        ]
                    );
                }
            });
            
            return response()->json(['success' => true, 'message' => 'PO Number berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * API: Detach PO Number (Delete detail)
     */
    public function detachPONumb(Request $request)
    {
        $request->validate([
            'bahan_baku_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'periode' => 'required|string',
            'tanggal' => 'required|date',
            'ponumb' => 'required|string',
        ]);

        $tanggal = Carbon::parse($request->tanggal);

        try {
            DB::transaction(function () use ($request, $tanggal) {
                $header = TScheduleHeader::where('periode', $request->periode)
                    ->where('supplier_id', $request->supplier_id)
                    ->where('bahan_baku_id', $request->bahan_baku_id)
                    ->where('po_number', $request->ponumb)
                    ->first();

                if ($header) {
                    TScheduleDetail::where('schedule_header_id', $header->id)
                        ->whereDate('tanggal', $tanggal)
                        ->delete();
                    
                    // Cleanup header if no details left
                    if ($header->details()->count() === 0) {
                        $header->delete();
                    }
                }
            });

            return response()->json(['success' => true, 'message' => 'PO Number berhasil dihapus']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * API: Update Plan
     */
    public function updatePlan(Request $request)
    {
        $request->validate([
            'bahan_baku_id' => 'required|integer',
            'supplier_id' => 'required|integer',
            'periode' => 'required|string',
            'tanggal' => 'required|date',
            'qty' => 'required|numeric',
            'ponumb' => 'required|string',
        ]);
        
        $tanggal = Carbon::parse($request->tanggal);
        
        try {
            DB::transaction(function () use ($request, $tanggal) {
                // Find header by PO Number
                $header = TScheduleHeader::where('periode', $request->periode)
                    ->where('supplier_id', $request->supplier_id)
                    ->where('bahan_baku_id', $request->bahan_baku_id)
                    ->where('po_number', $request->ponumb)
                    ->firstOrFail();
                
                // Update or create detail
                $detail = TScheduleDetail::updateOrCreate(
                    [
                        'schedule_header_id' => $header->id,
                        'tanggal' => $tanggal,
                    ],
                    [
                        'po_number' => $request->ponumb,
                        'pc_plan' => $request->qty,
                    ]
                );
                
                // Recalculate
                $detail->recalculate();
            });
            
            return response()->json(['success' => true, 'message' => 'Plan berhasil disimpan']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Auto-sync dari Receiving ke Schedule (ketika PPIC input receiving)
     */
    public function syncFromReceiving($receivingId)
    {
        try {
            $receiving = Receiving::with('details.bahanBaku')->findOrFail($receivingId);
            
            foreach ($receiving->details as $detail) {
                if (!$detail->bahanBaku) continue;
                
                $tanggal = $receiving->tanggal_receiving;
                $periode = $tanggal->format('Y-m');
                
                // Find matching schedule detail
                $scheduleHeaders = TScheduleHeader::where('periode', $periode)
                    ->where('po_number', $receiving->no_purchase_order)
                    ->where('bahan_baku_id', $detail->bahanBaku->id)
                    ->get();
                
                foreach ($scheduleHeaders as $header) {
                    $scheduleDetail = TScheduleDetail::where('schedule_header_id', $header->id)
                        ->whereDate('tanggal', $tanggal)
                        ->first();
                    
                    if ($scheduleDetail) {
                        // Update PC_ACT
                        $scheduleDetail->pc_act += $detail->qty;
                        $scheduleDetail->recalculate();
                    }
                }
            }
            
            return response()->json(['success' => true, 'message' => 'Sync berhasil']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }
    
    /**
     * Import SAP Excel for Receiving Data
     */
    public function importSAPExcel(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:csv,txt|max:10240', // Max 10MB, CSV only
        ]);
        
        try {
            $file = $request->file('file');
            $filePath = $file->getRealPath();
            
            // Determine manpower name based on user department
            $manpowerName = 'SAP Import';
            $user = Auth::user();
            if ($user && $user->manpower && strtolower($user->manpower->departemen) === 'purchasing') {
                 $manpowerName .= ' Staff';
            }

            // Create import instance
            $import = new SAPReceivingImport($manpowerName);
            
            // Process the CSV file
            $results = $import->import($filePath);
            
            // Sync all created receivings to schedule
            if ($results['success'] > 0) {
                // Get all receivings created in the last minute (from this import)
                $recentReceivings = Receiving::where('created_at', '>=', Carbon::now()->subMinute())
                    ->where('manpower', 'SAP Import')
                    ->get();
                
                foreach ($recentReceivings as $receiving) {
                    $this->syncReceivingToSchedule($receiving);
                }
            }
            
            return response()->json([
                'success' => true,
                'message' => 'Import completed',
                'data' => $results,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Import failed: ' . $e->getMessage(),
            ], 500);
        }
    }
    
    /**
     * Sync a single receiving to schedule details
     */
    protected function syncReceivingToSchedule($receiving)
    {
        foreach ($receiving->details as $detail) {
            if (!$detail->bahanBaku) {
                \Log::warning("Receiving detail {$detail->id} has no bahanBaku relation");
                continue;
            }
            
            $tanggal = $receiving->tanggal_receiving;
            $periode = $tanggal->format('Y-m');
            
            // Find matching schedule header
            $header = TScheduleHeader::where('periode', $periode)
                ->where('bahan_baku_id', $detail->bahanBaku->id)
                ->where('supplier_id', $receiving->supplier_id)
                ->first();
            
            // If no header exists, create one
            if (!$header) {
                $header = TScheduleHeader::create([
                    'periode' => $periode,
                    'po_number' => null, // Don't set PO from CSV import
                    'bahan_baku_id' => $detail->bahanBaku->id,
                    'supplier_id' => $receiving->supplier_id,
                    'pc_plan_total' => 0,
                    'pc_act_total' => 0,
                    'pc_blc_total' => 0,
                    'pc_ar_total' => 0,
                    'pc_sr_total' => 0,
                    'pc_status_total' => 'PENDING',
                    'freq' => 0,
                ]);
                \Log::info("Created schedule header {$header->id} for periode {$periode}, item {$detail->bahanBaku->id}");
            }
            
            // Find or create schedule detail for this date
            $scheduleDetail = TScheduleDetail::firstOrCreate(
                [
                    'schedule_header_id' => $header->id,
                    'tanggal' => $tanggal,
                ],
                [
                    'po_number' => null, // Don't set PO from CSV import, only from manual planning
                    'pc_plan' => 0,
                    'pc_act' => 0,
                    'pc_status' => 'PENDING',
                ]
            );
            
            // Update PC_ACT
            $scheduleDetail->pc_act += $detail->qty;
            $scheduleDetail->save();
            $scheduleDetail->recalculate();
            
            \Log::info("Updated schedule detail {$scheduleDetail->id}: added {$detail->qty} to pc_act, new total: {$scheduleDetail->pc_act}");
        }
    }

    /**
     * Show Import Form
     */
    public function showImportForm()
    {
        return view('controlsupplier.import');
    }

    /**
     * Process Import
     */
    public function importProcess(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
            'col_periode' => 'required',
            'col_supplier' => 'required',
            'col_po_number' => 'required',
            'col_start_date' => 'required',
        ]);

        if (!$request->col_bahan_baku && !$request->col_nama_bahan_baku) {
            return back()->with('error', 'Harap isi salah satu kolom: Nomor Bahan Baku atau Nama Bahan Baku');
        }

        try {
            $file = $request->file('file');
            $startRow = $request->start_row;
            
            // Mapping
            $mapping = [
                'col_periode' => $request->col_periode,
                'col_supplier' => $request->col_supplier,
                'col_bahan_baku' => $request->col_bahan_baku,          // Nomor
                'col_nama_bahan_baku' => $request->col_nama_bahan_baku, // Nama
                'col_po_number' => $request->col_po_number,
                'col_start_date' => $request->col_start_date,
            ];

            $importer = new ControlSupplierImport($mapping, $startRow);
            $importer->import($file->getRealPath());
            
            $stats = $importer->getStats();
            
            $message = sprintf(
                'Import selesai! Sukses Header: %d, Details Qty diupdate: %d, Gagal: %d', 
                $stats['success'], 
                $stats['details_updated'],
                $stats['failed']
            );

            if (!empty($stats['errors'])) {
                // Formatting errors
                $errorMsg = '<ul>';
                foreach (array_slice($stats['errors'], 0, 5) as $err) {
                    $errorMsg .= "<li>$err</li>";
                }
                if (count($stats['errors']) > 5) $errorMsg .= '<li>...dan lainnya</li>';
                $errorMsg .= '</ul>';
                
                return redirect()->route('controlsupplier.import.form')
                    ->with('error', "Sebagian data gagal diimport. $message")
                    ->with('import_errors', $stats['errors']); 
            }

            return redirect()->route('controlsupplier.monitoring')
                ->with('success', $message);

        } catch (\Exception $e) {
            return back()->with('error', 'Critical Error: ' . $e->getMessage());
        }
    }

    public function resetData(Request $request)
    {
        $request->validate([
            'periode' => 'required',
        ]);

        $periode = $request->periode;

        try {
            DB::beginTransaction();

            $headers = TScheduleHeader::where('periode', $periode)->get();
            $count = $headers->count();

            if ($count == 0) {
                return back()->with('error', "Tidak ada data untuk periode $periode.");
            }

            foreach ($headers as $header) {
                // Manually delete details if cascade isn't set up, just to be safe
                $header->details()->delete();
                $header->delete();
            }

            DB::commit();

            return back()->with('success', "Berhasil menghapus $count data Supplier Plan untuk periode $periode.");

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal reset data: ' . $e->getMessage());
        }
    }
}
