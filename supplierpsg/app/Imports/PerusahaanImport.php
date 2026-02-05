<?php

namespace App\Imports;

use App\Models\MPerusahaan;
use Illuminate\Support\Str;

class PerusahaanImport
{
    /**
     * Process the import from CSV file
     * 
     * @param string $filePath
     * @return array
     */
    public function import($filePath)
    {
        $results = [
            'success' => 0,
            'updated' => 0,
            'errors' => []
        ];

        try {
            // Open CSV file
            if (($handle = fopen($filePath, "r")) !== FALSE) {
                $rowNumber = 0;
                
                while (($data = fgetcsv($handle, 1000, ",")) !== FALSE) {
                    $rowNumber++;
                    
                    try {
                        // Skip header row
                        if ($rowNumber == 1) {
                            continue;
                        }

                        // Get values from CSV columns
                        // Column 0 (A) = BP Code
                        // Column 1 (B) = BP Name
                        // Column 4 (E) = Alamat
                        $kodeSupplier = isset($data[0]) ? trim($data[0]) : null;
                        $namaPerusahaan = isset($data[1]) ? trim($data[1]) : null;
                        $alamat = isset($data[4]) ? trim($data[4]) : null;

                        // Skip empty rows
                        if (empty($kodeSupplier) && empty($namaPerusahaan)) {
                            continue;
                        }

                        if (!$kodeSupplier || !$namaPerusahaan) {
                            $results['errors'][] = "Row $rowNumber: Missing required data (BP Code or BP Name)";
                            continue;
                        }

                        // Determine jenis_perusahaan from kode_supplier
                        $jenisPerusahaan = $this->determineJenisPerusahaan($kodeSupplier);

                        // Generate inisial_perusahaan
                        $inisialPerusahaan = $this->generateInisial($namaPerusahaan);

                        // Check if already exists
                        $existing = MPerusahaan::where('kode_supplier', $kodeSupplier)->first();
                        
                        if ($existing) {
                            // Update existing
                            $existing->update([
                                'nama_perusahaan' => $namaPerusahaan,
                                'inisial_perusahaan' => $inisialPerusahaan,
                                'jenis_perusahaan' => $jenisPerusahaan,
                                'alamat' => $alamat,
                            ]);
                            $results['updated']++;
                        } else {
                            // Create new
                            MPerusahaan::create([
                                'kode_supplier' => $kodeSupplier,
                                'nama_perusahaan' => $namaPerusahaan,
                                'inisial_perusahaan' => $inisialPerusahaan,
                                'jenis_perusahaan' => $jenisPerusahaan,
                                'alamat' => $alamat,
                            ]);
                            $results['success']++;
                        }
                    } catch (\Exception $e) {
                        $results['errors'][] = "Row $rowNumber: " . $e->getMessage();
                    }
                }
                
                fclose($handle);
            } else {
                $results['errors'][] = "Cannot open file";
            }
        } catch (\Exception $e) {
            $results['errors'][] = "File error: " . $e->getMessage();
        }

        return $results;
    }

    /**
     * Determine jenis_perusahaan based on kode_supplier prefix
     * V = Vendor/Supplier
     * C = Customer
     */
    private function determineJenisPerusahaan($kodeSupplier)
    {
        if (str_starts_with($kodeSupplier, 'V')) {
            return 'Vendor';
        } elseif (str_starts_with($kodeSupplier, 'C')) {
            return 'Customer';
        }
        return 'Other';
    }

    /**
     * Generate inisial from nama_perusahaan
     * Takes first letter of each word, max 5 characters
     * If duplicate, add number suffix
     */
    private function generateInisial($namaPerusahaan)
    {
        // Remove common words and get initials
        $words = preg_split('/[\s,\.]+/', $namaPerusahaan);
        $commonWords = ['PT', 'CV', 'UD', 'PD', 'Tbk', 'Indonesia', 'The', 'And', '&'];
        
        $initials = '';
        foreach ($words as $word) {
            if (!in_array($word, $commonWords) && !empty($word)) {
                $initials .= strtoupper(substr($word, 0, 1));
            }
        }

        // Limit to 5 characters
        $initials = substr($initials, 0, 5);
        
        if (empty($initials)) {
            $initials = strtoupper(substr($namaPerusahaan, 0, 3));
        }

        // Check for duplicates and add number if needed
        $originalInitials = $initials;
        $counter = 1;
        
        while (MPerusahaan::where('inisial_perusahaan', $initials)->exists()) {
            $initials = $originalInitials . $counter;
            $counter++;
        }

        return $initials;
    }
}
