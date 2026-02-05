<?php

namespace App\Imports;

use App\Models\MBahanBaku;
use App\Models\MPerusahaan;
use App\Models\MBahanBakuMaterial;
use App\Models\MBahanBakuSubpart;
use App\Models\MBahanBakuBox;
use App\Models\MBahanBakuLayer;
use App\Models\MBahanBakuPolybag;
use App\Models\MBahanBakuRempart;
use Illuminate\Support\Facades\DB;

class BahanBakuImport
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
                
                // Skip rows before startRow
                if ($rowIndex < $this->startRow) {
                    continue;
                }

                // Skip empty rows - check if all fields are empty
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

            // Get values based on mapping
            $kategori = $this->getValue($row, 'col_kategori');
            $nomorBahanBaku = $this->getValue($row, 'col_nomor');
            $namaBahanBaku = $this->getValue($row, 'col_nama');
            
            // Basic validation
            if (empty($kategori)) {
                DB::rollBack();
                return;
            }

            // Normalize kategori
            $kategori = strtolower(trim($kategori));
            $validKategoris = ['material', 'masterbatch', 'subpart', 'box', 'layer', 'polybag', 'rempart'];
            
            if (!in_array($kategori, $validKategoris)) {
                throw new \Exception("Kategori '$kategori' tidak valid");
            }

            // Retrieve other mapped values
            $supplierName = $this->getValue($row, 'col_supplier');
            $jenis = $this->getValue($row, 'col_jenis'); 
            $uom = $this->getValue($row, 'col_uom');
            $stdPacking = $this->getValue($row, 'col_std_packing');
            $jenisPacking = $this->getValue($row, 'col_jenis_packing');
            
            // Dimensions
            $panjang = $this->getValue($row, 'col_panjang');
            $lebar = $this->getValue($row, 'col_lebar');
            $tinggi = $this->getValue($row, 'col_tinggi');
            $tinggi = $this->getValue($row, 'col_tinggi');
            $kodeBox = $this->getValue($row, 'col_kode_box');

            // Status & Keterangan
            $statusRaw = $this->getValue($row, 'col_status');
            $keterangan = $this->getValue($row, 'col_keterangan');
            
            $status = true; // Default Active
            if ($statusRaw && strtoupper(trim($statusRaw)) === 'DISCONTINUE') {
                $status = false;
            }

            // Find Supplier ID if name provided
            $supplierId = null;
            if ($supplierName) {
                // Remove PT/CV prefixes for better matching
                $searchName = preg_replace('/^(pt\.?|cv\.?|ud\.?)\s+/i', '', trim($supplierName));
                
                $supplier = MPerusahaan::where('nama_perusahaan', 'LIKE', '%' . $searchName . '%')
                    ->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
                    ->first();
                    
                if (!$supplier) {
                     // Try exact match
                     $supplier = MPerusahaan::where('nama_perusahaan', $supplierName)
                        ->whereIn('jenis_perusahaan', ['Supplier', 'Maker', 'Vendor'])
                        ->first();
                }
                
                if ($supplier) {
                    $supplierId = $supplier->id;
                }
            }

            // Generate Nomor Bahan Baku if empty and allowed categories
            if (empty($nomorBahanBaku) && in_array($kategori, ['box', 'layer', 'polybag'])) {
                $nomorBahanBaku = $this->generateNomorBahanBaku([
                    'kategori' => $kategori,
                    'jenis' => $jenis,
                    'panjang' => $panjang,
                    'lebar' => $lebar,
                    'tinggi' => $tinggi,
                    'kode_box' => $kodeBox
                ]);
            }
            
            // Determine Nama for Main Table
            $mainNama = $namaBahanBaku;
            if (empty($mainNama)) {
                $mainNama = $nomorBahanBaku ?? '-';
            }

            // Check if exists
            $existing = null;
            if ($nomorBahanBaku) {
                // Check if exists, including soft deleted
                $existing = MBahanBaku::withTrashed()->where('nomor_bahan_baku', $nomorBahanBaku)->first();
            }

            if ($existing) {
                if ($existing->trashed()) {
                    $existing->restore();
                }
                
                // Update
                $existing->update([
                    'kategori' => $kategori,
                    'nama_bahan_baku' => $mainNama,
                    'supplier_id' => $supplierId ?? $existing->supplier_id,
                    'status' => $status,
                    'keterangan' => $keterangan,
                ]);
                $bahanBaku = $existing;
                $this->stats['updated']++;
                
                // Delete old details to recreate
                $bahanBaku->material?->delete();
                $bahanBaku->subpart?->delete();
                $bahanBaku->box?->delete();
                $bahanBaku->layer?->delete();
                $bahanBaku->polybag?->delete();
                $bahanBaku->rempart?->delete();

            } else {
                // Create
                $bahanBaku = MBahanBaku::create([
                    'kategori' => $kategori,
                    'nama_bahan_baku' => $mainNama,
                    'nomor_bahan_baku' => $nomorBahanBaku,
                    'supplier_id' => $supplierId,
                    'status' => $status,
                    'keterangan' => $keterangan,
                ]);
                $this->stats['success']++;
            }

            // Create Detail
            if (in_array($kategori, ['material', 'masterbatch'])) {
                MBahanBakuMaterial::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'nama_bahan_baku' => $namaBahanBaku ?? $mainNama,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
            } elseif ($kategori === 'subpart') {
                MBahanBakuSubpart::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'nama_bahan_baku' => $namaBahanBaku ?? $mainNama,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
            } elseif ($kategori === 'box') {
                $jenisClean = $jenis ? strtolower(str_replace(' ', '_', $jenis)) : null;

                MBahanBakuBox::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis' => $jenisClean,
                    'kode_box' => $kodeBox,
                    'panjang' => $panjang,
                    'lebar' => $lebar,
                    'tinggi' => $tinggi,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
            } elseif ($kategori === 'layer') {
                $jenisClean = $jenis ? strtolower(str_replace(' ', '_', $jenis)) : null;
                MBahanBakuLayer::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis' => $jenisClean,
                    'panjang' => $panjang,
                    'lebar' => $lebar,
                    'tinggi' => $tinggi,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
            } elseif ($kategori === 'polybag') {
                $jenisClean = $jenis ? strtolower(str_replace(' ', '_', $jenis)) : 'ldpe';
                MBahanBakuPolybag::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis' => $jenisClean,
                    'panjang' => $panjang,
                    'lebar' => $lebar,
                    'tinggi' => $tinggi,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
            } elseif ($kategori === 'rempart') {
                $jenisClean = $jenis ? strtolower(str_replace(' ', '_', $jenis)) : null;
                MBahanBakuRempart::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'jenis' => $jenisClean,
                    'std_packing' => $stdPacking,
                    'uom' => $uom,
                    'jenis_packing' => $jenisPacking,
                ]);
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
             // Convert 'A' to index 0
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

    private function generateNomorBahanBaku($data)
    {
        $kategori = $data['kategori'];
        $jenis = $data['jenis'] ?? '';
        
        if (empty($jenis)) {
            return null;
        }

        $jenisLabel = ucfirst(str_replace('_', ' ', $jenis));
        
        $jenisLower = strtolower($jenis);
        if ($jenisLower == 'polybox') $jenisLabel = 'Polybox';
        if ($jenisLower == 'impraboard') $jenisLabel = 'Impraboard';
        if ($jenisLower == 'ldpe') $jenisLabel = 'LDPE';
        
        $nomor = $jenisLabel;
        $panjang = $data['panjang'] ?? null;
        $lebar = $data['lebar'] ?? null;
        $tinggi = $data['tinggi'] ?? null;
        $kodeBox = $data['kode_box'] ?? null;

        if ($kategori === 'box') {
            if ($kodeBox) {
                $nomor .= '-' . $kodeBox;
            }
        }
        
        if ($panjang && $lebar && $tinggi) {
            $nomor .= '-' . $panjang . 'x' . $lebar . 'x' . $tinggi . 'cm';
        } elseif ($panjang && $lebar) {
            $nomor .= '-' . $panjang . 'x' . $lebar . 'cm';
        } elseif ($panjang) {
            $nomor .= '-' . $panjang . 'cm';
        }

        return $nomor;
    }
}
