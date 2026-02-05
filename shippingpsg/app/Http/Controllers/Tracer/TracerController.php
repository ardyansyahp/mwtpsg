<?php

namespace App\Http\Controllers\Tracer;

use App\Http\Controllers\Controller;
use App\Models\TAssyIn;
use App\Models\TAssyOut;
use App\Models\TFinishGoodIn;
use App\Models\TInjectIn;
use App\Models\TInjectOut;
use App\Models\TWipIn;
use App\Models\TWipOut;
use App\Models\TSupply;
use App\Models\TSupplyDetail;
use App\Models\ReceivingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class TracerController extends Controller
{
    public function index()
    {
        return view('tracer.tracer');
    }

    public function trace($lotNumber)
    {
        try {
            $lotNumber = urldecode($lotNumber);
            $lotNumberNormalized = trim($lotNumber);

            Log::info('Tracer trace called', ['lot_number' => $lotNumber]);

            $traceData = $this->buildTraceData($lotNumberNormalized);

            if (!$traceData) {
                return response()->json([
                    'success' => false,
                    'message' => 'Data tidak ditemukan untuk lot number: ' . $lotNumber
                ]);
            }

            return response()->json([
                'success' => true,
                'data' => $traceData
            ]);
        } catch (\Exception $e) {
            Log::error('Tracer trace error', [
                'lot_number' => $lotNumber ?? 'unknown',
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Terjadi kesalahan: ' . $e->getMessage()
            ], 500);
        }
    }

    private function buildTraceData($lotNumber)
    {
        $traceData = [
            'lot_number' => $lotNumber,
            'timeline' => [],
            'part' => null,
            'current_status' => null,
            'quantity' => null,
            'batch_number' => $lotNumber,
            'finishgood' => null,
            'assy' => null,
            'wip' => null,
            'inject' => null,
            'receiving' => null
        ];

        // Try to find the starting point
        // 1. Check Finish Good
        $fg = TFinishGoodIn::with(['manpower', 'part.customer', 'assyOut.assyIn.wipOut.wipIn.injectOut.injectIn.supplyDetail.receivingDetail'])
            ->where('lot_number', $lotNumber)
            ->first();

        if ($fg) {
            $this->traceFromFinishGood($fg, $traceData);
        } else {
            // 2. Check Assy Out
            $assyOut = TAssyOut::with(['part.customer', 'assyIn.wipOut.wipIn.injectOut.injectIn.supplyDetail.receivingDetail'])
                ->where('lot_number', $lotNumber)
                ->first();

            if ($assyOut) {
                $this->traceFromAssyOut($assyOut, $traceData);
            } else {
                // 3. Check Inject Out
                $injectOut = TInjectOut::with(['planningRun.mold.part.customer', 'injectIn.supplyDetail.receivingDetail'])
                    ->where('lot_number', $lotNumber)
                    ->first();

                if ($injectOut) {
                    $this->traceFromInjectOut($injectOut, $traceData);
                } else {
                    // 4. Check Receiving Detail
                    $receivingDetail = ReceivingDetail::with(['bahanBaku', 'receiving.supplier'])
                        ->where('lot_number', $lotNumber)
                        ->first();

                    if ($receivingDetail) {
                        $this->traceFromReceiving($receivingDetail, $traceData);
                    }
                }
            }
        }

        if (empty($traceData['timeline'])) return null;

        // Sort timeline (newest first)
        usort($traceData['timeline'], function($a, $b) {
            $timeA = $a['timestamp_raw'] ? $a['timestamp_raw']->timestamp : 0;
            $timeB = $b['timestamp_raw'] ? $b['timestamp_raw']->timestamp : 0;
            return $timeB - $timeA;
        });

        // Calculate durations
        for ($i = 0; $i < count($traceData['timeline']) - 1; $i++) {
            $t1 = $traceData['timeline'][$i]['timestamp_raw']->timestamp;
            $t2 = $traceData['timeline'][$i+1]['timestamp_raw']->timestamp;
            $traceData['timeline'][$i]['duration'] = abs($t1 - $t2) / 3600;
        }

        // Clean up raw timestamps for JSON
        foreach ($traceData['timeline'] as &$event) {
            $event['timestamp'] = $event['timestamp_raw'] ? $event['timestamp_raw']->toIso8601String() : null;
            unset($event['timestamp_raw']);
        }

        return $traceData;
    }

    private function traceFromFinishGood($fg, &$traceData)
    {
        $traceData['current_status'] = 'Finish Good';
        $traceData['quantity'] = ($fg->qty ?? '1') . ' unit';
        
        if ($fg->part) {
            $this->populatePartInfo($fg->part, $traceData);
        }

        $traceData['finishgood'] = [
            'Tanggal Masuk' => $fg->waktu_scan ? $fg->waktu_scan->format('Y-m-d H:i:s') : '-',
            'Operator' => $fg->manpower->nama ?? '-',
            'LOT' => $fg->lot_number,
            'Status' => 'Completed'
        ];

        $this->addTimelineEvent($traceData, 'finishgood', 'Finish Good In', 'Part masuk ke Finish Good', $fg->waktu_scan ?? $fg->created_at, $fg->manpower->nama ?? '-');

        if ($fg->assyOut) {
            $this->traceFromAssyOut($fg->assyOut, $traceData);
        }
    }

    private function traceFromAssyOut($assyOut, &$traceData)
    {
        if (!$traceData['current_status']) {
            $traceData['current_status'] = 'Assembly';
            if ($assyOut->part) $this->populatePartInfo($assyOut->part, $traceData);
        }

        if (!$traceData['assy']) {
            $traceData['assy'] = [
                'Tanggal Keluar' => $assyOut->waktu_scan ? $assyOut->waktu_scan->format('Y-m-d H:i:s') : '-',
                'LOT Assy Out' => $assyOut->lot_number,
                'Qty Assy Out' => '-',
                'Destination' => 'Finish Good'
            ];
            $this->addTimelineEvent($traceData, 'assy', 'Assembly Out', 'Part selesai di-assembly', $assyOut->waktu_scan ?? $assyOut->created_at);
        }

        if ($assyOut->assyIn) {
            $assyIn = $assyOut->assyIn;
            $traceData['assy']['Tanggal Masuk'] = $assyIn->waktu_scan ? $assyIn->waktu_scan->format('Y-m-d H:i:s') : '-';
            
            $this->addTimelineEvent($traceData, 'assy', 'Assembly In', 'Part masuk ke meja assembly', $assyIn->waktu_scan ?? $assyIn->created_at, $assyIn->manpower ?? '-');

            // Trace main components (from WIP)
            if ($assyIn->wipOut) {
                $this->traceFromWipOut($assyIn->wipOut, $traceData);
            }
            
            // Trace subparts (from Supply)
            if ($assyIn->supplyDetail && $assyIn->supplyDetail->supply) {
                $supply = $assyIn->supplyDetail->supply;
                $this->addTimelineEvent($traceData, 'supply', 'Material Supply (Subpart)', 'Subpart dikirim ke assembly', $supply->created_at);
                foreach($supply->details as $sd) {
                    if ($sd->receivingDetail) {
                        $this->traceFromReceiving($sd->receivingDetail, $traceData);
                    }
                }
            }
        }
    }

    private function traceFromWipOut($wipOut, &$traceData)
    {
        if (!$traceData['current_status']) $traceData['current_status'] = 'WIP';

        if (!$traceData['wip']) {
            $traceData['wip'] = [
                'Tanggal Keluar' => $wipOut->waktu_scan_out ? $wipOut->waktu_scan_out->format('Y-m-d H:i:s') : '-',
                'LOT' => $wipOut->lot_number,
                'Box Number' => $wipOut->box_number ?? '-'
            ];
            $this->addTimelineEvent($traceData, 'wip', 'WIP Out', 'Part keluar dari WIP', $wipOut->waktu_scan_out ?? $wipOut->created_at);
        }

        if ($wipOut->wipIn) {
            $wipIn = $wipOut->wipIn;
            $traceData['wip']['Tanggal Masuk'] = $wipIn->waktu_scan_in ? $wipIn->waktu_scan_in->format('Y-m-d H:i:s') : '-';
            
            $this->addTimelineEvent($traceData, 'wip', 'WIP In', 'Part masuk ke area WIP', $wipIn->waktu_scan_in ?? $wipIn->created_at);

            if ($wipIn->injectOut) {
                $this->traceFromInjectOut($wipIn->injectOut, $traceData);
            } else if ($wipOut->injectOut) {
                 $this->traceFromInjectOut($wipOut->injectOut, $traceData);
            }
        }
    }

    private function traceFromInjectOut($injectOut, &$traceData)
    {
        if (!$traceData['current_status']) {
            $traceData['current_status'] = 'Injection';
            $part = $injectOut->planningRun->mold->part ?? null;
            if ($part) $this->populatePartInfo($part, $traceData);
        }

        if (!$traceData['inject']) {
            $traceData['inject'] = [
                'Tanggal Produksi' => $injectOut->waktu_scan ? $injectOut->waktu_scan->format('Y-m-d H:i:s') : '-',
                'LOT' => $injectOut->lot_number
            ];
            $this->addTimelineEvent($traceData, 'inject', 'Injection Out', 'Proses Injection Selesai', $injectOut->waktu_scan ?? $injectOut->created_at);
        }

        if ($injectOut->injectIn) {
            $injectIn = $injectOut->injectIn;
            $traceData['inject']['Tanggal Scan In'] = $injectIn->waktu_scan ? $injectIn->waktu_scan->format('Y-m-d H:i:s') : '-';
            $traceData['inject']['Mesin'] = $injectIn->mesin->no_mesin ?? '-';
            
            $this->addTimelineEvent($traceData, 'inject', 'Injection In', 'Material di-scan masuk ke mesin', $injectIn->waktu_scan ?? $injectIn->created_at, $injectIn->manpower ?? '-');

            if ($injectIn->supplyDetail && $injectIn->supplyDetail->supply) {
                $supply = $injectIn->supplyDetail->supply;
                $this->addTimelineEvent($traceData, 'supply', 'Material Supply (Material)', 'Material dikirim ke produksi', $supply->created_at);
                
                // Get ALL materials from this supply
                foreach($supply->details as $sd) {
                    if ($sd->receivingDetail) {
                        $this->traceFromReceiving($sd->receivingDetail, $traceData);
                    }
                }
            }
        }
    }

    private function traceFromReceiving($rd, &$traceData)
    {
        if (!$traceData['receiving']) {
            $traceData['receiving'] = [
                'receivings' => []
            ];
        }

        // Prevent duplicate receiving info for the same lot
        foreach($traceData['receiving']['receivings'] as $existing) {
            foreach($existing['items'] as $item) {
                if ($item['lot'] === $rd->lot_number) return;
            }
        }

        $rcvInfo = [
            'tanggal' => $rd->created_at ? $rd->created_at->format('Y-m-d H:i:s') : '-',
            'supplier' => $rd->receiving->supplier->nama_perusahaan ?? '-',
            'inisial' => $rd->receiving->supplier->inisial_perusahaan ?? '-',
            'items' => [[
                'kategori' => $rd->bahanBaku->kategori ?? 'Material',
                'bahan_baku' => $rd->bahanBaku->nama_bahan_baku ?? '-',
                'nomor' => $rd->nomor_bahan_baku,
                'qty' => $rd->qty . ' ' . $rd->uom,
                'lot' => $rd->lot_number
            ]]
        ];

        $traceData['receiving']['receivings'][] = $rcvInfo;

        $this->addTimelineEvent($traceData, 'receiving', 'Material Receiving', 'Material diterima dari supplier', $rd->created_at);
    }

    private function populatePartInfo($part, &$traceData)
    {
        $traceData['part'] = [
            'nomor_part' => $part->nomor_part ?? '-',
            'nama_part' => $part->nama_part ?? '-',
            'tipe' => ['nama_part' => ($part->tipe_part ?? '-')],
            'perusahaan' => ['nama_perusahaan' => ($part->customer->nama_perusahaan ?? '-')]
        ];
    }

    private function addTimelineEvent(&$traceData, $stage, $name, $action, $timestamp, $operator = '-')
    {
        if (!$timestamp) return;

        $traceData['timeline'][] = [
            'stage' => $stage,
            'stage_name' => $name,
            'action' => $action,
            'timestamp_raw' => $timestamp,
            'operator' => is_string($operator) ? $operator : ($operator->nama ?? '-'),
            'status' => 'Completed'
        ];
    }
}
