<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckDeliveryHeaders extends Command
{
    protected $signature = 'check:headers';
    protected $description = 'Check delivery headers for truck 1234';

    public function handle()
    {
        $results = DB::select("
            SELECT h.id, h.no_surat_jalan, h.status, h.tanggal_berangkat, k.nopol_kendaraan
            FROM t_shipping_delivery_header h
            JOIN m_kendaraan k ON h.kendaraan_id = k.id
            WHERE k.nopol_kendaraan LIKE '%1234%'
            AND DATE(h.tanggal_berangkat) = '2026-01-28'
            ORDER BY h.id
        ");

        $this->info("Found " . count($results) . " delivery headers:");
        foreach ($results as $r) {
            $this->line("ID: {$r->id}, SJ: {$r->no_surat_jalan}, Status: {$r->status}, Date: {$r->tanggal_berangkat}");
            
            // Get details for this header
            $details = DB::select("
                SELECT id, jam, keterangan, status, foto_bukti
                FROM t_shipping_delivery_detail
                WHERE delivery_header_id = ?
                ORDER BY jam, id
            ", [$r->id]);
            
            foreach ($details as $d) {
                $this->line("  Detail ID: {$d->id}, Jam: {$d->jam}, Ket: {$d->keterangan}, Status: {$d->status}, Foto: {$d->foto_bukti}");
            }
        }
    }
}
