<?php

namespace App\Imports;

use App\Models\TSpk;
use App\Models\TSpkDetail;
use App\Models\MPerusahaan;
use App\Models\MPlantGate;
use App\Models\SMPart;
use App\Models\MKendaraan;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SPKImport
{
    private $mapping;
    private $startRow;
    private $stats = [
        'success' => 0,
        'failed' => 0,
        'errors' => []
    ];
    
    // Cache for optimization
    private $spkCache = []; 
    private $customerStartCache = [];
    private $plantGateCache = [];
    private $partCache = [];

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
                
                if ($rowIndex < $this->startRow) {
                    continue;
                }

                // Check for empty row
                if (empty(array_filter($row, function($value) { return $value !== null && trim($value) !== ''; }))) {
                    continue;
                }
                
                // Process each row in its own transaction
                try {
                    DB::beginTransaction();
                    $this->processRow($row, $rowIndex);
                    DB::commit();
                } catch (\Exception $e) {
                    DB::rollBack();
                    // Row-level error - logged but doesn't stop other rows
                    $this->stats['failed']++;
                    $this->stats['errors'][] = "Baris $rowIndex: " . $e->getMessage();
                }
            }

        } finally {
            fclose($handle);
        }
        
        return $this->stats;
    }

    private function processRow($row, $rowIndex)
    {
        try {
            // --- Extract Data from Columns ---
            // SPK Header Data
            $deadlineDateRaw = $this->getValue($row, 'col_deadline');
            $jamDeadline = $this->getValue($row, 'col_jam_deadline'); // NEW: separate time for deadline
            $jamBerangkat = $this->getValue($row, 'col_jam_berangkat');
            $jamKembali = $this->getValue($row, 'col_jam_kembali');
            $cycle = $this->getValue($row, 'col_cycle');
            $customerName = $this->getValue($row, 'col_customer');
            $plantGateRaw = $this->getValue($row, 'col_plant');
            $modelPart = $this->getValue($row, 'col_model_part');
            $platKendaraan = $this->getValue($row, 'col_plat');

            // Part Detail Data
            $nomorPart = $this->getValue($row, 'col_nomor_part');
            $qtyKirim = (int) $this->getValue($row, 'col_qty_kirim');

            // --- Validations & Lookups ---
            if (!$deadlineDateRaw || !$customerName || !$nomorPart) {
                throw new \Exception("Data wajib (Deadline/Customer/Part) tidak lengkap.");
            }

            // 1. Date Formatting
            try {
                $deadlineDate = $this->parseDate($deadlineDateRaw);
            } catch (\Exception $e) {
                throw new \Exception("Format 'Deadline Pulling' salah: $deadlineDateRaw. Gunakan format tanggal yang valid.");
            }
            
            // 2. Time Formatting
            try {
                // DEBUG: Log raw values
                \Log::info("Import Row $rowIndex - Raw Jam Deadline: " . var_export($jamDeadline, true));
                \Log::info("Import Row $rowIndex - Raw Jam Berangkat: " . var_export($jamBerangkat, true));
                
                // Parse deadline time (for tanggal field)
                $deadlineTime = '00:00:00';
                if ($jamDeadline) {
                    $deadlineTime = $this->parseTime($jamDeadline);
                } elseif ($jamBerangkat && !$jamDeadline) {
                    // Fallback: if no separate deadline time, use jam berangkat
                    $deadlineTime = $this->parseTime($jamBerangkat);
                }
                
                // Parse planning times
                if ($jamBerangkat) $jamBerangkat = $this->parseTime($jamBerangkat);
                if ($jamKembali) $jamKembali = $this->parseTime($jamKembali);
                
                // DEBUG: Log parsed values
                \Log::info("Import Row $rowIndex - Parsed Deadline Time: " . var_export($deadlineTime, true));
                \Log::info("Import Row $rowIndex - Parsed Jam Berangkat: " . var_export($jamBerangkat, true));
            } catch (\Exception $e) {
                throw new \Exception("Format Jam salah. Gunakan format jam yang valid (misal: 10:30 AM atau 13:00).");
            }

            // 3. Resolve Customer
            $customerId = $this->resolveCustomer($customerName);
            if (!$customerId) {
                throw new \Exception("Customer tidak ditemukan di sistem: $customerName. Pastikan nama sesuai Master Data.");
            }

            // 4. Resolve Plant Gate
            $plantGateId = null;
            if ($plantGateRaw) {
                $plantGateId = $this->resolvePlantGate($plantGateRaw, $customerId);
            }
            
            if (!$plantGateId && $plantGateRaw) {
                 throw new \Exception("Plant Gate '$plantGateRaw' tidak terdaftar untuk customer '$customerName'.");
            }
            
            // If Plant Gate not provided, maybe Customer only has 1 plant?
            if (!$plantGateId) {
                 $firstPG = MPlantGate::where('customer_id', $customerId)->first();
                 if ($firstPG) {
                     $plantGateId = $firstPG->id;
                 } else {
                     throw new \Exception("Customer '$customerName' tidak memiliki data Plant Gate. Hubungi Admin.");
                 }
            }

            // 5. Resolve Part
            $part = $this->resolvePart($nomorPart);
            if (!$part) {
                throw new \Exception("Nomor Part tidak ditemukan: $nomorPart. Cek Master Part.");
            }

            // 6. Model Part Validation
            $modelPartDb = 'regular'; // Default
            if ($modelPart) {
                $m = strtolower(trim($modelPart));
                if (in_array($m, ['regular', 'ckd', 'cbu', 'rempart', 'reguler'])) {
                    $modelPartDb = $m === 'reguler' ? 'regular' : $m;
                }
            }
            
            // --- SPK Grouping Logic ---
            $spkKey = "{$customerId}_{$plantGateId}_{$deadlineDate}_{$cycle}_{$modelPartDb}";
            
            if (!isset($this->spkCache[$spkKey])) {
                // New SPK Group
                $nomorSpk = $this->generateSpkNumber(); 
                
                $spk = TSpk::create([
                    'nomor_spk' => $nomorSpk,
                    'manpower_pembuat' => auth()->user() ? auth()->user()->name : 'System Import',
                    'customer_id' => $customerId,
                    'plantgate_id' => $plantGateId,
                    'tanggal' => $deadlineDate . ' ' . $deadlineTime,
                    'jam_berangkat_plan' => $jamBerangkat,
                    'jam_datang_plan' => $jamKembali,
                    'cycle_number' => $cycle,
                    'model_part' => $modelPartDb,
                    'nomor_plat' => $platKendaraan,
                ]);
                
                $this->spkCache[$spkKey] = $spk;
                // Don't increment success here - we increment per row processed
            }
            
            $spk = $this->spkCache[$spkKey];

            // --- Add Detail Part ---
            
            // Validate Std Packing Multiples
            $stdPacking = $part->QTY_Packing_Box ?? 0;
            if ($stdPacking > 0) {
                 if ($qtyKirim % $stdPacking !== 0) {
                    throw new \Exception("Qty kirim ($qtyKirim) untuk part $nomorPart harus kelipatan Std Packing ($stdPacking).");
                }
            }
            
            // Check duplications in same SPK
            $existingDetail = TSpkDetail::where('spk_id', $spk->id)
                ->where('part_id', $part->id)
                ->first();

            $qtyPullingBox = ($stdPacking > 0) ? ceil($qtyKirim / $stdPacking) : 0;

            if ($existingDetail) {
                // Update / Accumulate
                $newQty = $existingDetail->jadwal_delivery_pcs + $qtyKirim;
                
                // Re-validate accumulated qty?
                if ($stdPacking > 0 && $newQty % $stdPacking !== 0) {
                      throw new \Exception("Akumulasi Qty kirim ($newQty) untuk part $nomorPart tidak valid (harus kelipatan $stdPacking).");
                }
                
                $existingDetail->update([
                    'jadwal_delivery_pcs' => $newQty,
                    'jumlah_pulling_box' => ($stdPacking > 0) ? ceil($newQty / $stdPacking) : 0
                ]);
            } else {
                // Create new detail
                TSpkDetail::create([
                    'spk_id' => $spk->id,
                    'part_id' => $part->id,
                    'qty_packing_box' => $stdPacking,
                    'jadwal_delivery_pcs' => $qtyKirim,
                    'jumlah_pulling_box' => $qtyPullingBox
                ]);
            }
            
            // Row processed successfully
            $this->stats['success']++;

        } catch (\Exception $e) {
            // Error already logged by caller, just re-throw to trigger row-level rollback
            throw $e;
        }
    }
    
    // --- Helper Methods ---

    private function getValue($row, $mappingKey)
    {
        if (empty($this->mapping[$mappingKey])) {
            return null;
        }

        $columnLetter = strtoupper($this->mapping[$mappingKey]);
        try {
             $columnIndex = $this->columnIndexFromString($columnLetter) - 1;
             
             if (isset($row[$columnIndex])) {
                 $val = trim($row[$columnIndex]);
                 return $val === '' ? null : $val;
             }
             return null;

        } catch (\Exception $e) {
            return null;
        }
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
    
    private function parseDate($dateStr)
    {
        if (!$dateStr) return null;
        
        // Handle Excel numeric date (serial number)
        if (is_numeric($dateStr)) {
            try {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($dateStr)->format('Y-m-d');
            } catch (\Exception $e) {
                // Fallback to treating as timestamp
                return date('Y-m-d', $dateStr);
            }
        }
        
        // Clean up datetime string (remove time component if needed for date parsing)
        $dateOnly = trim(explode(' ', $dateStr)[0]); // Get date part only
        
        // Try standard formats first
        $formats = [
            'Y-m-d',      // 2026-02-04
            'd-m-Y',      // 04-02-2026
            'd/m/Y',      // 04/02/2026 (Indonesian)
            'm/d/Y',      // 02/04/2026 (US format from Excel)
            'Y/m/d',      // 2026/02/04
        ];
        
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $dateOnly);
                if ($d && $d->format($fmt) === $dateOnly) {
                    return $d->format('Y-m-d');
                }
            } catch (\Exception $e) {
                continue;
            }
        }
        
        // Fallback: Try Carbon::parse with full datetime string
        try {
            $parsed = Carbon::parse($dateStr);
            return $parsed->format('Y-m-d');
        } catch (\Exception $e) {
            // Last resort: strtotime
            $timestamp = strtotime($dateStr);
            if ($timestamp !== false) {
                return date('Y-m-d', $timestamp);
            }
        }
        
        throw new \Exception("Format tanggal tidak dikenali: $dateStr");
    }

    private function parseTime($timeStr)
    {
        if (!$timeStr) return null;
        
        try {
            // Handle Excel numeric time (fraction of day - 0.5 = 12:00 PM)
            if (is_numeric($timeStr) && $timeStr < 1) {
                return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($timeStr)->format('H:i:s');
            }
            
            // If it's a full datetime string like "2/4/2026 10:30:00 AM", extract time part
            if (strpos($timeStr, '/') !== false || strpos($timeStr, '-') !== false) {
                // It's a datetime, split and get time part
                $parts = preg_split('/\s+/', trim($timeStr));
                // Find the time part (contains :)
                foreach ($parts as $part) {
                    if (strpos($part, ':') !== false) {
                        $timeStr = $part;
                        // Check if there's AM/PM after
                        $nextKey = array_search($part, $parts) + 1;
                        if (isset($parts[$nextKey]) && in_array(strtoupper($parts[$nextKey]), ['AM', 'PM'])) {
                            $timeStr = $part . ' ' . $parts[$nextKey];
                        }
                        break;
                    }
                }
            }
            
            // Now parse the time string
            // Handle 12-hour format with AM/PM
            if (stripos($timeStr, 'AM') !== false || stripos($timeStr, 'PM') !== false) {
                // Use Carbon to parse 12-hour format
                $dt = Carbon::createFromFormat('g:i:s A', $timeStr);
                if (!$dt) {
                    // Try without seconds
                    $dt = Carbon::createFromFormat('g:i A', $timeStr);
                }
                if (!$dt) {
                    // Try with leading zero
                    $dt = Carbon::createFromFormat('h:i:s A', $timeStr);
                }
                if (!$dt) {
                    $dt = Carbon::createFromFormat('h:i A', $timeStr);
                }
                
                if ($dt) {
                    return $dt->format('H:i:s');
                }
            }
            
            // Handle 24-hour format (HH:mm:ss or HH:mm)
            if (preg_match('/^\d{1,2}:\d{2}(:\d{2})?$/', $timeStr)) {
                $parts = explode(':', $timeStr);
                $h = str_pad($parts[0], 2, '0', STR_PAD_LEFT);
                $m = str_pad($parts[1], 2, '0', STR_PAD_LEFT);
                $s = isset($parts[2]) ? str_pad($parts[2], 2, '0', STR_PAD_LEFT) : '00';
                return "$h:$m:$s";
            }
            
            // Last resort: Carbon::parse
            return Carbon::parse($timeStr)->format('H:i:s');
            
        } catch (\Exception $e) {
            // If all fails, try basic strtotime
            $timestamp = strtotime($timeStr);
            if ($timestamp !== false) {
                return date('H:i:s', $timestamp);
            }
            return null;
        }
    }

    private function resolveCustomer($name)
    {
        if (isset($this->customerStartCache[$name])) return $this->customerStartCache[$name];
        
        $result = MPerusahaan::where('nama_perusahaan', 'LIKE', $name)
            ->where('jenis_perusahaan', 'Customer')
            ->value('id');
            
        if (!$result) {
             $cleanName = preg_replace('/^(pt\.?|cv\.?|ud\.?)\s+/i', '', trim($name));
             $result = MPerusahaan::where('nama_perusahaan', 'LIKE', "%$cleanName%")
                ->where('jenis_perusahaan', 'Customer')
                ->value('id');
        }
        
        $this->customerStartCache[$name] = $result;
        return $result;
    }
    
    private function resolvePlantGate($name, $customerId)
    {
        $key = $customerId . '_' . $name;
        if (isset($this->plantGateCache[$key])) return $this->plantGateCache[$key];
        
        $result = MPlantGate::where('customer_id', $customerId)
            ->where('nama_plantgate', 'LIKE', "%$name%")
            ->value('id');
            
        $this->plantGateCache[$key] = $result;
        return $result;
    }
    
    private function resolvePart($nomorPart)
    {
        if (isset($this->partCache[$nomorPart])) return $this->partCache[$nomorPart];
        
        $part = SMPart::where('nomor_part', $nomorPart)->first();
        
        $this->partCache[$nomorPart] = $part;
        return $part;
    }

    private function generateSpkNumber()
    {
        $month = date('m');
        $year = date('Y');
        
        // Count ALL records including soft deleted ones to avoid duplicate
        $count = TSpk::withTrashed()
            ->whereYear('created_at', $year)
            ->whereMonth('created_at', $month)
            ->count();
            
        $sequence = $count + 1;
        
        return 'SPK/' . $year . '/' . $month . '/' . str_pad($sequence, 4, '0', STR_PAD_LEFT);
    }
}
