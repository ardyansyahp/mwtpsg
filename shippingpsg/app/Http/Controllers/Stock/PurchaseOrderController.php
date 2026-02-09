<?php

namespace App\Http\Controllers\Stock;

use App\Http\Controllers\Controller;
use App\Models\TPurchaseOrderCustomer;
use App\Models\SMPart;
use Illuminate\Http\Request;

class PurchaseOrderController extends Controller
{
    public function index(Request $request)
    {
        $query = TPurchaseOrderCustomer::with('part.customer');

        if ($request->search) {
            $query->where('po_number', 'like', "%{$request->search}%")
                  ->orWhereHas('part', function($q) use ($request) {
                      $q->where('nomor_part', 'like', "%{$request->search}%")
                        ->orWhere('nama_part', 'like', "%{$request->search}%")
                        ->orWhereHas('customer', function($sq) use ($request) {
                            $sq->where('nama_perusahaan', 'like', "%{$request->search}%")
                              ->orWhere('inisial_perusahaan', 'like', "%{$request->search}%");
                        });
                  });
        }

        $pos = $query->orderBy('created_at', 'desc')->paginate(15);
        $parts = SMPart::all(); // For modal create

        return view('stock.purchase_order.index', compact('pos', 'parts'));
    }

    public function create()
    {
        $parts = SMPart::orderBy('nomor_part')->get();
        return view('stock.purchase_order.create', compact('parts'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:sm_part,id',
            'po_number' => 'required|string|max:100',
            'qty' => 'required|integer|min:1',
            'delivery_frequency' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        TPurchaseOrderCustomer::create($validated);

        return redirect()->route('stock.po.index')->with('success', 'Purchase Order berhasil ditambahkan.');
    }

    public function edit(TPurchaseOrderCustomer $purchaseOrder)
    {
        // Parameter binding name depends on route definition. 
        // Resource 'po' -> parameter usually 'po'. 
        // But in destroy it was '$purchaseOrder'. Let's check route: 'po'.
        // Implicit binding should work if variable name matches.
        // Wait, Route::resource uses {po} for the parameter.
        // So argument name should be $po ideally, but $purchaseOrder works if type hinted correctly in Laravel 7+, 
        // but to be safe, I will stick to what's there or just use $po if I change it.
        // The existing destroy used $purchaseOrder.
        
        $parts = SMPart::orderBy('nomor_part')->get();
        return view('stock.purchase_order.edit', compact('purchaseOrder', 'parts'));
    }

    public function update(Request $request, TPurchaseOrderCustomer $purchaseOrder)
    {
        $validated = $request->validate([
            'part_id' => 'required|exists:sm_part,id',
            'po_number' => 'required|string|max:100',
            'qty' => 'required|integer|min:1',
            'delivery_frequency' => 'nullable|string',
            'month' => 'required|integer|min:1|max:12',
            'year' => 'required|integer|min:2020|max:2099',
        ]);

        $purchaseOrder->update($validated);

        return redirect()->route('stock.po.index')->with('success', 'Purchase Order berhasil diperbarui.');
    }

    public function destroy(TPurchaseOrderCustomer $purchaseOrder)
    {
        $purchaseOrder->delete();
        return redirect()->back()->with('success', 'Purchase Order berhasil dihapus.');
    }

    public function importForm()
    {
        return view('stock.purchase_order.import');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:csv,txt|max:10240',
            'start_row' => 'required|integer|min:1',
        ]);

        try {
            $file = $request->file('file');
            $mapping = $request->except(['_token', 'file', 'start_row']);
            $startRow = $request->input('start_row', 2);
            $mapping['col_po_number'] = $request->col_po_number;
            $mapping['col_nomor_part'] = $request->col_nomor_part;
            $mapping['col_qty'] = $request->col_qty;
            $mapping['col_month'] = $request->col_month;
            $mapping['col_year'] = $request->col_year;
            $mapping['col_delivery_frequency'] = $request->col_delivery_frequency;

            $handle = fopen($file->getRealPath(), "r");
            if ($handle === FALSE) {
                throw new \Exception("Gagal membuka file.");
            }

            $stats = [
                'success' => 0,
                'failed' => 0,
                'errors' => []
            ];

            $rowIndex = 0;
            \DB::beginTransaction();

            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $rowIndex++;
                if ($rowIndex < $startRow) continue;

                if (empty(array_filter($row, function($value) { return $value !== null && trim($value) !== ''; }))) {
                    continue;
                }

                try {
                    $poNumber = $this->getValue($row, $mapping, 'col_po_number');
                    $nomorPart = $this->getValue($row, $mapping, 'col_nomor_part');
                    $qty = $this->getValue($row, $mapping, 'col_qty');
                    $month = $this->getValue($row, $mapping, 'col_month');
                    $year = $this->getValue($row, $mapping, 'col_year');
                    $deliveryFreq = $this->getValue($row, $mapping, 'col_delivery_frequency');

                    if (!$poNumber || !$nomorPart || !$qty || !$month || !$year) {
                        throw new \Exception("Data wajib tidak lengkap (PO/Part/Qty/Bulan/Tahun).");
                    }

                    $part = SMPart::where('nomor_part', $nomorPart)->first();
                    if (!$part) throw new \Exception("Part tidak ditemukan: $nomorPart");

                    $qty = (int) str_replace(['.', ','], '', $qty);
                    $month = (int) $month;
                    $year = (int) $year;

                    // Update or Create based on unique combo ideally
                    TPurchaseOrderCustomer::updateOrCreate(
                        [
                            'po_number' => $poNumber,
                            'part_id' => $part->id,
                            'month' => $month,
                            'year' => $year,
                        ],
                        [
                            'qty' => $qty,
                            'delivery_frequency' => $deliveryFreq
                        ]
                    );

                    $stats['success']++;

                } catch (\Exception $e) {
                    $stats['failed']++;
                    $stats['errors'][] = "Baris $rowIndex: " . $e->getMessage();
                }
            }

            fclose($handle);
            \DB::commit();

            if (!empty($stats['errors'])) {
                $msg = 'Import Selesai dengan beberapa error. ' . count($stats['errors']) . ' baris bermasalah.';
                return redirect()->back()
                    ->with('warning', $msg . " Berhasil: {$stats['success']} PO.")
                    ->with('import_errors', $stats['errors']);
            }

            return redirect()->route('stock.po.index')->with('success', "Import Berhasil! {$stats['success']} PO baru/updated.");

        } catch (\Exception $e) {
            \DB::rollBack();
            return redirect()->back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    public function export(Request $request)
    {
        $fileName = 'data_po_' . date('Y-m-d_H-i-s') . '.csv';
        
        $query = TPurchaseOrderCustomer::with('part');

        if ($request->search) {
            $query->where('po_number', 'like', "%{$request->search}%")
                  ->orWhereHas('part', function($q) use ($request) {
                      $q->where('nomor_part', 'like', "%{$request->search}%")
                        ->orWhere('nama_part', 'like', "%{$request->search}%")
                        ->orWhereHas('customer', function($sq) use ($request) {
                            $sq->where('nama_perusahaan', 'like', "%{$request->search}%")
                              ->orWhere('inisial_perusahaan', 'like', "%{$request->search}%");
                        });
                  });
        }
        
        $items = $query->orderBy('year', 'desc')->orderBy('month', 'desc')->get();

        $headers = [
            "Content-type"        => "text/csv",
            "Content-Disposition" => "attachment; filename=$fileName",
            "Pragma"              => "no-cache",
            "Cache-Control"       => "must-revalidate, post-check=0, pre-check=0",
            "Expires"             => "0"
        ];

        $columns = ['PO Number', 'Customer', 'Part Number', 'Part Name', 'Qty', 'Month', 'Year', 'Delivery Frequency', 'Created At'];

        $callback = function() use($items, $columns) {
            $file = fopen('php://output', 'w');
            fputcsv($file, $columns);

            foreach ($items as $item) {
                fputcsv($file, [
                    $item->po_number,
                    $item->part->customer->nama_perusahaan ?? '-',
                    $item->part->nomor_part ?? '-',
                    $item->part->nama_part ?? '-',
                    $item->qty,
                    $item->month,
                    $item->year,
                    $item->delivery_frequency,
                    $item->created_at->format('Y-m-d H:i')
                ]);
            }
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    private function getValue($row, $mapping, $key)
    {
        if (empty($mapping[$key])) return null;
        
        $colLetter = strtoupper($mapping[$key]);
        $index = $this->columnIndexFromString($colLetter) - 1;
        
        return isset($row[$index]) ? trim($row[$index]) : null;
    }

    private function columnIndexFromString($pString)
    {
        $pString = strtoupper($pString);
        $len = strlen($pString);
        $result = 0;
        for ($i = 0; $i < $len; $i++) {
            $result = $result * 26 + (ord($pString[$i]) - 64);
        }
        return $result;
    }
}
