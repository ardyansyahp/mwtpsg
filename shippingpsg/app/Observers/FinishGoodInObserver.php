<?php

namespace App\Observers;

use App\Models\TFinishGoodIn;
use App\Models\TStockFG;

class FinishGoodInObserver
{
    /**
     * Handle the TFinishGoodIn "created" event.
     */
    public function created(TFinishGoodIn $finishGoodIn): void
    {
        $stock = TStockFG::firstOrCreate(
            ['part_id' => $finishGoodIn->part_id],
            ['qty' => 0]
        );

        $stock->increment('qty', $finishGoodIn->qty);
    }

    /**
     * Handle the TFinishGoodIn "updated" event.
     */
    public function updated(TFinishGoodIn $finishGoodIn): void
    {
        if ($finishGoodIn->isDirty('qty')) {
            $diff = $finishGoodIn->qty - $finishGoodIn->getOriginal('qty');
            
            $stock = TStockFG::firstOrCreate(
                ['part_id' => $finishGoodIn->part_id],
                ['qty' => 0]
            );

            $stock->increment('qty', $diff);
        }
    }

    /**
     * Handle the TFinishGoodIn "deleted" event.
     */
    public function deleted(TFinishGoodIn $finishGoodIn): void
    {
        $stock = TStockFG::where('part_id', $finishGoodIn->part_id)->first();
        if ($stock) {
            $stock->decrement('qty', $finishGoodIn->qty);
        }
    }
}
