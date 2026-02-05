<?php

namespace App\Imports;

use App\Models\SMPart;
use App\Models\MPerusahaan;
use Illuminate\Support\Facades\DB;
use App\Models\SMPartMaterial;
use App\Models\SMPartSubpart;
use App\Models\SMPartBox;
use App\Models\SMPartLayer;
use App\Models\SMPartPolybag;
use App\Models\SMPartRempart;

class PartImport
{
    private $mapping;
    private $startRow;
    private $stats = [
        'success' => 0,
        'updated' => 0,
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
                
                if ($rowIndex < $this->startRow) {
                    continue;
                }

                if (empty(array_filter($row, function($value) { return $value !== null && trim($value) !== ''; }))) {
                    continue;
                }
                
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

            // Core Identifiers
            $nomorPart = $this->getValue($row, 'col_nomor_part');
            $namaPart = $this->getValue($row, 'col_nama_part');
            $customerName = $this->getValue($row, 'col_customer');
            
            // Basic Validation
            if (empty($nomorPart)) {
                DB::rollBack();
                return; // Skip invalid rows without part number
            }

            // Customer Lookup
            $customerId = null;
            if ($customerName) {
                // Similar supplier lookup strategy
                $searchName = preg_replace('/^(pt\.?|cv\.?|ud\.?)\s+/i', '', trim($customerName));
                $customer = MPerusahaan::where('nama_perusahaan', 'LIKE', '%' . $searchName . '%')
                    ->where('jenis_perusahaan', 'Customer')
                    ->first();
                
                if (!$customer) {
                     $customer = MPerusahaan::where('nama_perusahaan', $customerName)
                        ->where('jenis_perusahaan', 'Customer')
                        ->first();
                }

                if ($customer) {
                    $customerId = $customer->id;
                }
            }

            // Specs
            $modelPart = $this->getValue($row, 'col_model_part');
            $tipePart = $this->getValue($row, 'col_tipe_part'); // New
            $proses = $this->getValue($row, 'col_proses');
            
            // Cycle Times & Weights
            $ctInject = $this->getValue($row, 'col_ct_inject');
            $ctAssy = $this->getValue($row, 'col_ct_assy');
            $nCav1 = $this->getValue($row, 'col_n_cav1');
            $runner = $this->getValue($row, 'col_runner');
            $avgBrutto = $this->getValue($row, 'col_avg_brutto');
            $qtyPackingBox = $this->getValue($row, 'col_qty_packing_box'); // New
            
            // Status & Keterangan
            $statusRaw = $this->getValue($row, 'col_status');
            $keterangan = $this->getValue($row, 'col_keterangan');
            
            $status = true; 
            if ($statusRaw && strtoupper(trim($statusRaw)) === 'DISCONTINUE') {
                $status = false;
            }

            // Check Existing (including Trash)
            $existing = SMPart::withTrashed()->where('nomor_part', $nomorPart)->first();

            if ($existing) {
                // Restore if needed
                if ($existing->trashed()) {
                    $existing->restore();
                }

                // Update
                $updateData = [
                    'nama_part' => $namaPart ?? $existing->nama_part,
                    'customer_id' => $customerId ?? $existing->customer_id,
                    'model_part' => $modelPart ?? $existing->model_part,
                    'tipe_id' => $tipePart ?? $existing->tipe_id, // Update
                    'proses' => $proses ?? $existing->proses,
                    'CT_Inject' => $ctInject ?? $existing->CT_Inject,
                    'CT_Assy' => $ctAssy ?? $existing->CT_Assy,
                    'N_Cav1' => $nCav1 ?? $existing->N_Cav1,
                    'Runner' => $runner ?? $existing->Runner,
                    'Avg_Brutto' => $avgBrutto ?? $existing->Avg_Brutto,
                    'QTY_Packing_Box' => $qtyPackingBox ?? $existing->QTY_Packing_Box, // Update
                    'status' => $status,
                    'keterangan' => $keterangan,
                ];
                $existing->update($updateData);
                
                $this->stats['updated']++;
                // Note: Complex details (materials, layers etc) are hard to update via simple CSV line
                // unless the CSV has specific format for them. For now, we mainly update header info.

            } else {
                // Create
                SMPart::create([
                    'nomor_part' => $nomorPart,
                    'nama_part' => $namaPart ?? '-',
                    'customer_id' => $customerId,
                    'model_part' => $modelPart,
                    'tipe_id' => $tipePart, // Create
                    'proses' => $proses,
                    'CT_Inject' => $ctInject,
                    'CT_Assy' => $ctAssy,
                    'N_Cav1' => $nCav1,
                    'Runner' => $runner,
                    'Avg_Brutto' => $avgBrutto,
                    'QTY_Packing_Box' => $qtyPackingBox, // Create
                    'status' => $status,
                    'keterangan' => $keterangan,
                ]);
                $this->stats['success']++;
            }

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
