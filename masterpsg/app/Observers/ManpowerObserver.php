<?php

namespace App\Observers;

use App\Models\MManpower;

class ManpowerObserver
{
    /**
     * Handle the MManpower "created" event.
     */
    public function created(MManpower $mManpower): void
    {
        // Automatically create a user for this manpower
        if ($mManpower->mp_id) {
            \App\Models\User::firstOrCreate(
                ['user_id' => $mManpower->mp_id],
                [
                    'password' => null, // Passwordless by default
                    'role' => 0,
                ]
            );
        }
    }

    /**
     * Handle the MManpower "updated" event.
     */
    public function updated(MManpower $mManpower): void
    {
        // If mp_id changes, we might want to update the user_id too, 
        // but that's risky if they have history. 
        // For now, let's just ensure a user exists if one doesn't.
        if ($mManpower->mp_id) {
            \App\Models\User::firstOrCreate(
                ['user_id' => $mManpower->mp_id],
                [
                    'password' => null,
                    'role' => 0,
                ]
            );
        }
    }

    /**
     * Handle the MManpower "deleted" event.
     */
    public function deleted(MManpower $mManpower): void
    {
        // Optional: delete the user when manpower is deleted?
        // Usually safer to keep the user or soft delete it, but User model doesn't have soft deletes by default.
        // Let's leave it for now to preserve history.
    }

    /**
     * Handle the MManpower "restored" event.
     */
    public function restored(MManpower $mManpower): void
    {
        //
    }

    /**
     * Handle the MManpower "force deleted" event.
     */
    public function forceDeleted(MManpower $mManpower): void
    {
        //
    }
}
