<?php

namespace App\Imports;

use App\Models\TScheduleHeader;
use App\Models\TScheduleDetail;
use App\Models\MBahanBaku;
use App\Models\MPerusahaan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ControlSupplierImport
{
    private $mapping;
    private $startRow;
    private $stats = [
        'success' => 0,
        'updated' => 0, // Header updated/touched
        'details_updated' => 0,
        'failed' => 0,
        'errors' => []
    ];

    public function __construct($mapping, $startRow = 2)
    {
        $this->mapping = $mapping;
        $this->startRow = $startRow;
    }

    public function import($filePath)
    {
        if (($handle = fopen($filePath, "r")) === FALSE) {
            throw new \Exception("Gagal membuka file.");
        }

        $rowIndex = 0;
        
        try {
            while (($row = fgetcsv($handle, 10000, ",")) !== FALSE) {
                $rowIndex++;
                
                // Skip rows before startRow
                if ($rowIndex < $this->startRow) {
                    continue;
                }
                
                // --- Process Logic (Same as before, adapted for array) ---
                $this->processRow($row, $rowIndex);
            }
        } finally {
            fclose($handle);
        }
        
        return $this->stats;
    }

    private function processRow($row, $rowIndex)
    {
        try {
            DB::beginTransaction();

            // 1. Get mapped values
            $periodeRaw = $this->getValue($row, 'col_periode');
            $supplierName = $this->getValue($row, 'col_supplier');
            $nomorBahanBaku = $this->getValue($row, 'col_bahan_baku');
            $namaBahanBaku = $this->getValue($row, 'col_nama_bahan_baku');
            $poNumber = $this->getValue($row, 'col_po_number');

            // 2. Validate essential fields
            // If both Supplier and PO are empty, likely an empty row at end of file
            if (empty($supplierName) && empty($poNumber)) {
                DB::rollBack();
                return;
            }

            // 3. Parse Periode
            if (empty($periodeRaw)) {
                 // Try to fallback to previous row's periode? No, dangerous.
                 throw new \Exception("Periode kosong (Kolom " . $this->mapping['col_periode'] . ")");
            }
            
            // Try to parse flexible period formats
            // Common: '2024-01', 'Jan-24', 'January 2024', Excel Date Serial
            $periodeDate = null;
            
            // Text parsing
            try {
                // Try standard Y-m first
                if (preg_match('/^\d{4}-\d{2}$/', $periodeRaw)) {
                     $periodeDate = Carbon::createFromFormat('Y-m', $periodeRaw)->startOfMonth();
                } 
                // Try Jan-24 or similar
                elseif (preg_match('/^[A-Za-z]{3}-\d{2}$/', $periodeRaw)) {
                     $periodeDate = Carbon::createFromFormat('M-y', $periodeRaw)->startOfMonth();
                }
                else {
                    $periodeDate = Carbon::parse($periodeRaw)->startOfMonth();
                }
                $periode = $periodeDate->format('Y-m');
            } catch (\Exception $e) {
                 throw new \Exception("Format Periode tidak dikenali: '$periodeRaw'. Gunakan YYYY-MM atau Bulan-Tahun.");
            }

            if (!$periodeDate) {
                 throw new \Exception("Gagal membaca periode.");
            }

            // 4. Find Master Data (Smart Match)
            
            // --- MATCH SUPPLIER ---
            // 1. Exact Name Match
            $supplier = MPerusahaan::where('nama_perusahaan', $supplierName)->first();

            // 2. Loose Match (cleaning common prefixes)
            if (!$supplier) {
                // Remove PT, CV, UD, TB from input
                $cleanSupplier = preg_replace('/^(pt|cv|ud|tb)\.?\s+/i', '', trim($supplierName));
                // Remove generic punctuation
                $cleanSupplier = trim(str_replace(['.', ','], '', $cleanSupplier));
                
                if (strlen($cleanSupplier) > 2) { 
                    $supplier = MPerusahaan::where('nama_perusahaan', 'LIKE', '%' . $cleanSupplier . '%')
                        ->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
                        ->first();
                }
            }
            
            if (!$supplier) {
                // Last matched attempt: Try raw LIKE matching just in case
                $supplier = MPerusahaan::where('nama_perusahaan', 'LIKE', '%' . trim($supplierName) . '%')
                        ->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
                        ->first();
            }

            // --- MATCH BAHAN BAKU ---
            $bahanBaku = null;
            
            // Prefer Number if available
            if (!empty($nomorBahanBaku)) {
                $nomorClean = trim($nomorBahanBaku);
                // 1. Exact Match via Number
                $bahanBaku = MBahanBaku::where('nomor_bahan_baku', $nomorClean)->first();
                // 2. Smart Match via Number
                if (!$bahanBaku) {
                    $normalizedInput = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $nomorClean));
                    $bahanBaku = MBahanBaku::whereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(nomor_bahan_baku, '-', ''), ' ', ''), '/', ''), '.', '') = ?", 
                        [$normalizedInput]
                    )->first();
                }
            }
            
            if (!$bahanBaku && !empty($namaBahanBaku)) {
                $namaClean = trim($namaBahanBaku);
                // 1. Exact Match via Name
                $bahanBaku = MBahanBaku::where('nama_bahan_baku', $namaClean)->first();
                // 2. Smart Match via Name
                if (!$bahanBaku) {
                     $normalizedInput = strtoupper(preg_replace('/[^a-zA-Z0-9]/', '', $namaClean));
                     $bahanBaku = MBahanBaku::whereRaw(
                        "REPLACE(REPLACE(REPLACE(REPLACE(nama_bahan_baku, '-', ''), ' ', ''), '/', ''), '.', '') = ?", 
                        [$normalizedInput]
                    )->first();
                }
            }

            if (!$supplier) {
                throw new \Exception("Supplier '$supplierName' tidak ditemukan (Smart Match gagal).");
            }
            
            if (!$bahanBaku) {
                  $identifiers = [];
                if ($nomorBahanBaku) $identifiers[] = "Nomor: '$nomorBahanBaku'";
                if ($namaBahanBaku) $identifiers[] = "Nama: '$namaBahanBaku'";
                $idStr = implode(', ', $identifiers);
                throw new \Exception("Bahan Baku tidak ditemukan ($idStr).");
            }

            // 5. Create/Find Header
            $header = TScheduleHeader::firstOrCreate(
                [
                    'periode' => $periode,
                    'supplier_id' => $supplier->id,
                    'bahan_baku_id' => $bahanBaku->id,
                    'po_number' => $poNumber,
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
            $this->stats['success']++; 

            // 6. Process Daily Data
            // Logic: Start from col_start_date, iterate until end of month
            
            $startColLetter = strtoupper($this->mapping['col_start_date']);
            $startColIndex = $this->columnIndexFromString($startColLetter) - 1; // 0-indexed
            
            $daysInMonth = $periodeDate->daysInMonth;
            
            for ($day = 1; $day <= $daysInMonth; $day++) {
                $currentColIndex = $startColIndex + ($day - 1);
                
                $qty = isset($row[$currentColIndex]) ? $row[$currentColIndex] : 0;
                
                // Clean qty
                $qty = trim($qty);
                // Remove commas (thousands separator)
                $qty = str_replace(',', '', $qty);
                
                if ($qty === '' || $qty === '-') $qty = 0;
                $qty = floatval($qty);
                
                if ($qty > 0) {
                    $currentDate = Carbon::createFromFormat('Y-m-d', $periode . '-' . str_pad($day, 2, '0', STR_PAD_LEFT))->startOfDay();
                    // Accumulate Logic:
                    // Find existing detail or create new instance
                    $detail = TScheduleDetail::firstOrNew([
                        'schedule_header_id' => $header->id,
                        'tanggal' => $currentDate,
                    ]);

                    // Initialize if new (defaults)
                    if (!$detail->exists) {
                        $detail->po_number = $poNumber;
                        $detail->pc_plan = 0; // Start at 0
                        $detail->pc_status = 'PENDING';
                    } else {
                        // Update PO Number just in case it changed (though usually same header = same PO)
                        $detail->po_number = $poNumber;
                    }

                    // ADD (Accumulate) the quantity
                    $detail->pc_plan += $qty;
                    
                    $detail->save();
                    $this->stats['details_updated']++;
                }
            }
            
            // Recalc Header Total
            $totalPlan = $header->details()->sum('pc_plan');
            $header->total_plan = $totalPlan;
            $header->save();

            DB::commit();

        } catch (\Exception $e) {
            DB::rollBack();
            $this->stats['failed']++;
            $this->stats['errors'][] = "Baris $rowIndex: " . $e->getMessage();
        }
    }

    private function getValue($row, $mappingKey)
    {
        if (empty($this->mapping[$mappingKey])) {
            return null;
        }

        $columnLetter = strtoupper($this->mapping[$mappingKey]);
        try {
             $columnIndex = $this->columnIndexFromString($columnLetter) - 1;
             
             if (isset($row[$columnIndex])) {
                 return trim($row[$columnIndex]);
             }
             return null;
        } catch (\Exception $e) {
            return null;
        }
    }

    // Helper to replace Coordinate::columnIndexFromString from phpspreadsheet
    // Supports A, B, ... Z, AA, AB ... ZZ, AAA ...
    private function columnIndexFromString($pString)
    {
        $n = 0;
        $len = strlen($pString);
        for ($i = 0; $i < $len; ++$i) {
            $n = $n * 26 + ord($pString[$i]) - 0x40;
        }
        return $n;
    }

    public function getStats()
    {
        return $this->stats;
    }
}
