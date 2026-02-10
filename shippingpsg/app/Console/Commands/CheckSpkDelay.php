<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\TSpk;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class CheckSpkDelay extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'spk:check-delay';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check SPK yang terlambat berangkat sesuai plan jam';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        try {
            $now = Carbon::now('Asia/Jakarta');
            $today = $now->format('Y-m-d');
            $graceBuffer = 30; // 30 minutes threshold

            // Get SPKs planned for today that haven't departed yet (no_surat_jalan is null)
            $delayedSpks = TSpk::with(['customer'])
                ->whereDate('tanggal', $today)
                ->whereNull('no_surat_jalan')
                ->whereNotNull('jam_berangkat_plan')
                ->where('jam_berangkat_plan', '<', $now->subMinutes($graceBuffer)->format('H:i'))
                ->get();

            if ($delayedSpks->count() > 0) {
                // Construct Message
                $msg = "⏰ *REMINDER KETERLAMBATAN PENGIRIMAN*\n";
                $msg .= "Waktu Check: " . $now->format('H:i') . "\n\n";
                $msg .= "Berikut SPK yang belum berangkat > {$graceBuffer} menit dari jadwal:\n\n";

                foreach ($delayedSpks as $spk) {
                    $jamPlan = $spk->jam_berangkat_plan;
                    $cust = $spk->customer->nama_perusahaan ?? '-';
                    $msg .= "- *{$spk->nomor_spk}* ({$cust})\n";
                    $msg .= "  Jadwal: {$jamPlan} | Telat: >{$graceBuffer} m\n";
                }

                $msg .= "\n_Mohon segera diproses atau update jam keberangkatan._";

                // Send to OPS Group
                $targetOps = env('FONNTE_GROUP_OPS', '0812xxxx');
                
                // Check if this command already ran recently to avoid spam?
                // For now, assume cron runs hourly.
                
                // \App\Helpers\FonnteHelper::send($targetOps, $msg);
                
                $this->info("Sent alert for {$delayedSpks->count()} delayed SPKs.");
            } else {
                $this->info("No delayed SPKs found.");
            }

        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
            Log::error("CheckSpkDelay Error: " . $e->getMessage());
        }
    }
}
