<?php

namespace App\Imports;

use App\Models\MPerusahaan;
use App\Models\MBahanBaku;
use App\Models\Receiving;
use App\Models\ReceivingDetail;
use Carbon\Carbon;

class SAPReceivingImport
{
    protected $results = [
        'success' => 0,
        'skipped' => 0,
        'errors' => [],
    ];
    
    protected $manpowerName;

    public function __construct($manpowerName = 'SAP Import')
    {
        $this->manpowerName = $manpowerName;
    }

    /**
     * Process the import from CSV file (exported from SAP Excel)
     * 
     * @param string $filePath
     * @return array
     */
    public function import($filePath)
    {
        try {
            // Open CSV file
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $rowNumber = 0;
                
                while (($data = fgetcsv($handle, 10000, ",")) !== FALSE) {
                    $rowNumber++;
                    
                    try {
                        // Skip header row
                        if ($rowNumber == 1) {
                            continue;
                        }

                        // Skip empty rows
                        if (empty(array_filter($data))) {
                            continue;
                        }
                        
                        // Extract data from columns (0-indexed)
                        // Column B (index 1) = PO Number
                        // Column C (index 2) = Receiving Date
                        // Column F (index 5) = Supplier Name
                        // Column G (index 6) = Item Code (Nomor Bahan Baku)
                        // Column H (index 7) = Item Description (Nama Bahan Baku)
                        // Column I (index 8) = Quantity
                        // Column N (index 13) = Surat Jalan Number
                        $poNumber = isset($data[1]) ? trim($data[1]) : '';
                        $tanggalReceiving = $data[2] ?? null;
                        $supplierName = isset($data[5]) ? trim($data[5]) : '';
                        $nomorBahanBaku = isset($data[6]) ? trim($data[6]) : '';
                        $namaBahanBaku = isset($data[7]) ? trim($data[7]) : '';
                        $qty = $data[8] ?? 0;
                        $noSuratJalan = isset($data[13]) ? trim($data[13]) : '';

                        // Validate required fields (PO, Supplier, and EITHER Item Code OR Item Name)
                        if (empty($poNumber) || empty($supplierName) || (empty($nomorBahanBaku) && empty($namaBahanBaku))) {
                            $this->results['skipped']++;
                            $this->results['errors'][] = "Row {$rowNumber}: Missing required data (PO, Supplier, or Item info)";
                            continue;
                        }

                        // Parse date
                        try {
                            if (is_numeric($tanggalReceiving)) {
                                // Excel date serial number
                                $baseDate = new \DateTime('1899-12-30');
                                $baseDate->modify("+{$tanggalReceiving} days");
                                $tanggalReceiving = Carbon::instance($baseDate);
                            } else {
                                $tanggalReceiving = Carbon::parse($tanggalReceiving);
                            }
                        } catch (\Exception $e) {
                            $this->results['skipped']++;
                            $this->results['errors'][] = "Row {$rowNumber}: Invalid date format";
                            continue;
                        }

                        // Find supplier by name (fuzzy match)
                        $supplier = MPerusahaan::where('nama_perusahaan', 'LIKE', "%{$supplierName}%")->first();
                        if (!$supplier) {
                            $this->results['skipped']++;
                            $this->results['errors'][] = "Row {$rowNumber}: Supplier '{$supplierName}' not found";
                            continue;
                        }

                        // Find bahan baku logic
                        $bahanBaku = null;
                        
                        // 1. Try by Nomor Bahan Baku (Column G)
                        if (!empty($nomorBahanBaku)) {
                            $bahanBaku = MBahanBaku::where('nomor_bahan_baku', $nomorBahanBaku)->first();
                        }
                        
                        // 2. If not found or G is empty, try by Nama Bahan Baku (Column H)
                        if (!$bahanBaku && !empty($namaBahanBaku)) {
                             // Try exact match first
                            $bahanBaku = MBahanBaku::where('nama_bahan_baku', $namaBahanBaku)->first();
                            
                            // If still not found, maybe try LIKE? (Optional, safer to stick to exact for imports to avoid wrong mapping)
                            if (!$bahanBaku) {
                                $bahanBaku = MBahanBaku::where('nama_bahan_baku', 'LIKE', $namaBahanBaku)->first();
                            }
                        }

                        if (!$bahanBaku) {
                            $this->results['skipped']++;
                            $identifier = !empty($nomorBahanBaku) ? "Code '{$nomorBahanBaku}'" : "Name '{$namaBahanBaku}'";
                            $this->results['errors'][] = "Row {$rowNumber}: Item {$identifier} not found in Master Data";
                            continue;
                        }
                        
                        // Ensure we have the correct nomor_bahan_baku from the DB record found
                        $nomorBahanBaku = $bahanBaku->nomor_bahan_baku;

                        // Determine UOM from kategori
                        $uom = $this->getUOMFromKategori($bahanBaku->kategori);

                        // Find or create receiving header
                        $receiving = Receiving::firstOrCreate(
                            [
                                'tanggal_receiving' => $tanggalReceiving,
                                'supplier_id' => $supplier->id,
                                'no_purchase_order' => $poNumber,
                            ],
                            [
                                'no_surat_jalan' => $noSuratJalan,
                                'manpower' => $this->manpowerName,
                                'shift' => 1,
                            ]
                        );

                        // Create receiving detail
                        ReceivingDetail::create([
                            'receiving_id' => $receiving->id,
                            'nomor_bahan_baku' => $nomorBahanBaku,
                            'qty' => $qty,
                            'uom' => $uom,
                            'lot_number' => 'SAP-' . $poNumber . '-' . $tanggalReceiving->format('Ymd'),
                            'internal_lot_number' => 'INT-' . $receiving->id . '-' . $rowNumber,
                            'qrcode' => null, // Skip QR code generation for SAP import
                        ]);

                        $this->results['success']++;

                    } catch (\Exception $e) {
                        $this->results['skipped']++;
                        $this->results['errors'][] = "Row {$rowNumber}: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
            } else {
                $this->results['errors'][] = "Cannot open file";
            }
        } catch (\Exception $e) {
            $this->results['errors'][] = "File error: " . $e->getMessage();
        }

        return $this->results;
    }

    protected function getUOMFromKategori($kategori)
    {
        $uomMap = [
            'material' => 'KG',
            'masterbatch' => 'KG',
            'subpart' => 'PCS',
            'box' => 'PCS',
            'layer' => 'PCS',
            'polybag' => 'PCS',
            'rempart' => 'PCS',
        ];
        
        return $uomMap[$kategori] ?? 'PCS';
    }

    public function getResults()
    {
        return $this->results;
    }
}
