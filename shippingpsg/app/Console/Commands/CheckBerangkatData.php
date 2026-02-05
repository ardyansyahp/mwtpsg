<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CheckBerangkatData extends Command
{
    protected $signature = 'check:berangkat';
    protected $description = 'Check BERANGKAT data for truck 1234';

    public function handle()
    {
        $results = DB::select("
            SELECT d.id, d.delivery_header_id, d.jam, d.keterangan, d.status, h.no_surat_jalan
            FROM t_shipping_delivery_detail d
            JOIN t_shipping_delivery_header h ON d.delivery_header_id = h.id
            JOIN m_kendaraan k ON h.kendaraan_id = k.id
            WHERE k.nopol_kendaraan LIKE '%1234%'
            AND d.keterangan LIKE 'BERANGKAT%'
            AND d.tanggal = '2026-01-28'
            ORDER BY d.jam, d.id
        ");

        $this->info("Found " . count($results) . " BERANGKAT details:");
        foreach ($results as $r) {
            $this->line("ID: {$r->id}, Header: {$r->delivery_header_id}, SJ: {$r->no_surat_jalan}, Jam: {$r->jam}, Ket: {$r->keterangan}, Status: {$r->status}");
        }
    }
}
