<?php

namespace App\Services;

use App\Models\MaterialBalance;
use App\Models\MBahanBaku;

class MaterialCalculationService
{
    /**
     * Calculate material needed from percentage
     * 
     * @param float $partQuantity Jumlah part yang akan diproduksi
     * @param float $partWeight Berat part per pcs (Netto atau Brutto)
     * @param float $percentage Persentase material (0-100)
     * @return float Kebutuhan material
     */
    public function calculateMaterialNeeded($partQuantity, $partWeight, $percentage)
    {
        $totalWeight = $partQuantity * $partWeight;
        return ($totalWeight * $percentage) / 100;
    }

    /**
     * Calculate supply needed based on std_packing
     * 
     * @param float $neededQty Kebutuhan material
     * @param float $stdPacking Standard packing per unit
     * @param string $uom Unit of measure
     * @return array ['packing_count', 'total_supply', 'remaining']
     */
    public function calculateSupplyNeeded($neededQty, $stdPacking, $uom = 'KG')
    {
        if ($stdPacking <= 0) {
            return [
                'packing_count' => 0,
                'total_supply' => 0,
                'remaining' => 0,
            ];
        }

        $packingCount = ceil($neededQty / $stdPacking);
        $totalSupply = $packingCount * $stdPacking;
        $remaining = $totalSupply - $neededQty;

        return [
            'needed_qty' => $neededQty,
            'std_packing' => $stdPacking,
            'packing_count' => $packingCount,
            'total_supply' => $totalSupply,
            'remaining' => $remaining,
            'uom' => $uom,
        ];
    }

    /**
     * Calculate supply with auto-allocate from balance
     * 
     * @param string $nomorBahanBaku
     * @param string $kategori
     * @param float $neededQty
     * @param float $stdPacking
     * @param string $uom
     * @param string|null $lotNumber
     * @return array
     */
    public function calculateSupplyWithBalance($nomorBahanBaku, $kategori, $neededQty, $stdPacking, $uom = 'KG', $lotNumber = null)
    {
        // Get available balance
        $availableQty = MaterialBalance::getAvailable($nomorBahanBaku, $kategori, $lotNumber);
        
        if ($availableQty >= $neededQty) {
            // Pakai sisa yang ada, tidak perlu supply baru
            return [
                'needed_qty' => $neededQty,
                'use_from_balance' => $neededQty,
                'new_supply_needed' => 0,
                'packing_count' => 0,
                'total_supply' => 0,
                'remaining' => $availableQty - $neededQty,
                'uom' => $uom,
            ];
        } else {
            // Pakai sisa yang ada + supply baru
            $useFromBalance = $availableQty;
            $newSupplyNeeded = $neededQty - $availableQty;
            
            $supplyCalc = $this->calculateSupplyNeeded($newSupplyNeeded, $stdPacking, $uom);
            
            return [
                'needed_qty' => $neededQty,
                'use_from_balance' => $useFromBalance,
                'new_supply_needed' => $newSupplyNeeded,
                'packing_count' => $supplyCalc['packing_count'],
                'total_supply' => $supplyCalc['total_supply'],
                'remaining' => $supplyCalc['remaining'],
                'uom' => $uom,
            ];
        }
    }

    /**
     * Get std_packing and uom from bahan baku
     * 
     * @param string $nomorBahanBaku
     * @param string $kategori
     * @return array ['std_packing', 'uom']
     */
    public function getBahanBakuPacking($nomorBahanBaku, $kategori)
    {
        $bahanBaku = MBahanBaku::where('nomor_bahan_baku', $nomorBahanBaku)
            ->where('kategori', $kategori)
            ->first();
        
        if (!$bahanBaku) {
            return ['std_packing' => 0, 'uom' => 'KG'];
        }

        $detail = $bahanBaku->detail();
        
        if ($detail) {
            return [
                'std_packing' => $detail->std_packing ?? 0,
                'uom' => $detail->uom ?? 'KG',
            ];
        }

        return ['std_packing' => 0, 'uom' => 'KG'];
    }

    /**
     * Calculate material needed for part (with percentage)
     * 
     * @param object $partMaterial SMPartMaterial instance
     * @param float $partQuantity Jumlah part
     * @param float $partWeight Berat part (Netto atau Brutto)
     * @return float Kebutuhan material
     */
    public function calculatePartMaterialNeeded($partMaterial, $partQuantity, $partWeight)
    {
        // Jika material_type adalah material atau masterbatch, gunakan persentase
        if (in_array($partMaterial->material_type, ['material', 'masterbatch'])) {
            return $this->calculateMaterialNeeded($partQuantity, $partWeight, $partMaterial->std_using);
        }
        
        // Untuk yang lain, std_using adalah absolute value
        return $partMaterial->std_using * $partQuantity;
    }
}
