<?php

namespace App\Observers;

use App\Models\TFinishGoodOut;
use App\Models\TStockFG;

class FinishGoodOutObserver
{
    /**
     * Handle the TFinishGoodOut "created" event.
     */
    public function created(TFinishGoodOut $finishGoodOut): void
    {
        // Gunakan part_id dari FinishGoodIn agar stock yang dikurangi sesuai dengan fisik barang yang masuk
        // (bukan part_id dari SPK yang mungkin beda ID-nya meski barang sama/parent-child)
        $partId = $finishGoodOut->finishGoodIn->part_id ?? $finishGoodOut->part_id;

        $stock = TStockFG::firstOrCreate(
            ['part_id' => $partId],
            ['qty' => 0]
        );

        $stock->decrement('qty', $finishGoodOut->qty);
    }

    /**
     * Handle the TFinishGoodOut "updated" event.
     */
    public function updated(TFinishGoodOut $finishGoodOut): void
    {
        if ($finishGoodOut->isDirty('qty')) {
            // Jika qty berubah, update stock selisihnya
            $diff = $finishGoodOut->qty - $finishGoodOut->getOriginal('qty');

            $partId = $finishGoodOut->finishGoodIn->part_id ?? $finishGoodOut->part_id;

            $stock = TStockFG::firstOrCreate(
                ['part_id' => $partId],
                ['qty' => 0]
            );

            $stock->decrement('qty', $diff);
        }
    }

    /**
     * Handle the TFinishGoodOut "deleted" event.
     */
    public function deleted(TFinishGoodOut $finishGoodOut): void
    {
        // Kembalikan stock ke part aslinya
        $partId = $finishGoodOut->finishGoodIn->part_id ?? $finishGoodOut->part_id;
        
        $stock = TStockFG::where('part_id', $partId)->first();
        if ($stock) {
            $stock->increment('qty', $finishGoodOut->qty);
        }
    }
}
