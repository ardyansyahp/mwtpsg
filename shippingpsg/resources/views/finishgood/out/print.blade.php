<!DOCTYPE html>
<html>
<head>
    <title>SPK & Checksheet Kelengkapan Delivery - {{ $spk->nomor_spk }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: Arial, sans-serif; padding: 20px; font-size: 10px; }
        
        /* Header Layout */
        .header { display: flex; align-items: center; justify-content: space-between; margin-bottom: 20px; padding-bottom: 0; }
        
        /* Logo Section: Flex column to center logo over text, width auto to hug content */
        .logo-section { display: flex; flex-direction: column; align-items: center; width: auto; }
        .logo { width: 50px; height: auto; margin-bottom: 2px; }
        .company-name { font-size: 8px; font-weight: bold; color: #333; letter-spacing: 0.2px; white-space: nowrap; }
        
        .title-section { flex: 1; text-align: center; }
        .main-title { font-size: 14px; font-weight: bold; text-transform: uppercase; color: #333; letter-spacing: 0.5px; }
        
        .document-info { width: auto; font-size: 7px; }
        .doc-table { width: 100%; border-collapse: collapse; }
        .doc-table td { padding: 1px 0; vertical-align: top; }
        .doc-label { width: 70px; }
        .doc-sep { width: 10px; text-align: center; }
        
        .info-section { display: flex; margin-bottom: 15px; }
        .info-col { flex: 1; }
        .info-table { width: 100%; font-size: 9px; }
        .info-table tr td { padding: 3px 0; vertical-align: top; }
        .info-table .label { width: 130px; font-weight: normal; color: #333; }
        .info-table .separator { width: 10px; text-align: center; }
        
        /* Table Styles */
        table.main-table { width: 100%; border-collapse: collapse; margin-bottom: 10px; table-layout: fixed; }
        table.main-table th, table.main-table td { border: 1px solid #000; padding: 2px; text-align: center; font-size: 8px; line-height: 1.1; overflow: hidden; white-space: nowrap; text-overflow: ellipsis; }
        /* Allow headers to wrap and grow taller */
        table.main-table th { background-color: transparent; font-weight: bold; text-transform: uppercase; vertical-align: middle; height: auto; white-space: normal; font-size: 7px !important; }
        table.main-table .text-left { text-align: left; padding-left: 5px; }
        
        /* Smaller font for tight headers */
        th.col-cycle,
        th.col-point {
            font-size: 6px !important;
            line-height: 1;
        }

        /* Column Widths - Optimized */
        /* Column Widths - Optimized */
        .col-no { width: 3%; }
        .col-part-no { width: 12%; }
        .col-part-name { width: 18%; }
        .col-jadwal { width: 7%; }
        .col-std { width: 6%; }
        .col-jumlah { width: 7%; }
        .col-cycle { width: 7%; }
        .col-balance { width: 6%; }
        .col-point { width: 4%; }
        .col-sj { width: 3%; }
        
        .point-control-legend { margin: 10px 0; font-size: 8px; }
        .point-control-legend strong { display: block; margin-bottom: 3px; font-size: 8px; }
        
        .statement { font-size: 8px; font-style: italic; margin-bottom: 20px; }
        
        .signature-table { width: 60%; border-collapse: collapse; margin-top: 10px; margin-left: auto; }
        .signature-table th, .signature-table td { border: 1px solid #000; text-align: center; font-size: 8px; }
        .signature-table th { padding: 5px; font-weight: bold; }
        .signature-table td.sig-space { height: 90px; }
        .signature-table tfoot td { border: none; text-align: right; padding: 2px 0; font-size: 7px; }

        @media print {
            @page { size: A4 landscape; margin: 10mm; }
            body { padding: 0; -webkit-print-color-adjust: exact; }
            .print-btn { display: none; }
        }
    </style>
</head>
<body>
    <!-- Print Button -->
    <button onclick="window.print()" class="print-btn" style="position: fixed; top: 10px; right: 10px; padding: 8px 16px; background: #007bff; color: white; border: none; border-radius: 4px; cursor: pointer; z-index: 1000; font-size: 12px; font-weight: bold;">Cetak Document</button>

    <!-- Header -->
    <div class="header">
        <div class="logo-section">
            <img src="{{ asset('assets/images/logo.png') }}" alt="Logo MWT" class="logo">
            <div class="company-name">PT MADA WIKRI TUNGGAL</div>
        </div>
        <div class="title-section">
            <h1 class="main-title">SPK & CHECKSHEET KELENGKAPAN DELIVERY</h1>
        </div>
        <div class="document-info">
            <table class="doc-table">
                <tr>
                    <td class="doc-label">NO DOKUMEN</td>
                    <td class="doc-sep">:</td>
                    <td>FORM-PPIC-3-06</td>
                </tr>
                <tr>
                    <td class="doc-label">TGL EFEKTIF</td>
                    <td class="doc-sep">:</td>
                    <td>{{ date('d F Y') }}</td>
                </tr>
                <tr>
                    <td class="doc-label">REVISI</td>
                    <td class="doc-sep">:</td>
                    <td>04</td>
                </tr>
                <tr>
                    <td class="doc-label">HAL</td>
                    <td class="doc-sep">:</td>
                    <td>1 dari 1</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Info Section -->
    <div class="info-section">
        <div class="info-col">
            <table class="info-table">
                <tr><td class="label">NAMA CUSTOMER</td><td class="separator">:</td><td>{{ $spk->customer->nama_perusahaan ?? '-' }}</td></tr>
                <tr><td class="label">HARI, TGL, BULAN</td><td class="separator">:</td><td>{{ $spk->tanggal->isoFormat('dddd, D MMMM YYYY') }}</td></tr>
                <tr><td class="label">NO KENDARAAN</td><td class="separator">:</td><td>{{ $spk->nomor_plat ?? '-' }}</td></tr>
            </table>
        </div>
        <div class="info-col" style="padding-left: 50px;">
            <table class="info-table">
                <tr><td class="label">PLANT / GATE</td><td class="separator">:</td><td>{{ $spk->plantgate->nama_plantgate ?? '-' }}</td></tr>
                <tr><td class="label">NO SURAT JALAN</td><td class="separator">:</td><td>{{ $spk->no_surat_jalan ?? '-' }}</td></tr>
                <tr><td class="label">STATUS PART F/G</td><td class="separator">:</td><td>Reguler</td></tr>
                <tr>
                    <td class="label">CYCLE</td>
                    <td class="separator">:</td>
                    <td>
                        <strong>{{ $cycleNumber }}</strong>
                        @if($spk->parentSpk)
                            <br><small style="font-size: 6px;">(Split dari {{ $spk->parentSpk->nomor_spk }})</small>
                        @endif
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Main Table -->
    <table class="main-table">
        <thead>
            <tr>
                <th rowspan="2" class="col-no">NO</th>
                <th rowspan="2" class="col-part-no">NOMOR PART</th>
                <th rowspan="2" class="col-part-name">NAMA PART</th>
                <th class="col-jadwal">JADWAL<br>DELIV</th>
                <th class="col-std">STD<br>PACK</th>
                <th class="col-jumlah">TOTAL<br>QTY</th>
                @foreach($availableCycles as $cycle)
                <th colspan="2">ACT PULL<br>CYC {{ $cycle }}</th>
                @endforeach
                <th class="col-balance">SISA</th>
                <th colspan="4" style="width: 14%;">POINT CONTROL</th>
                <th colspan="2" style="width: 5%;">SRT JLN</th>
            </tr>
            <tr>
                <th>(PCS)</th>
                <th>(P/B)</th>
                <th>(BOX)</th>
                @foreach($availableCycles as $cycle)
                <th class="col-cycle">(BOX)</th>
                <th class="col-cycle">(PCS)</th>
                @endforeach
                <th>(BOX)</th>
                <th class="col-point">LABEL VS PART</th>
                <th class="col-point">LABEL VS QTY</th>
                <th class="col-point">STD PACK</th>
                <th class="col-point">IRD</th>
                <th class="col-sj">PPIC</th>
                <th class="col-sj">GA</th>
            </tr>
        </thead>
        <tbody>
            @forelse($details as $index => $detail)
            <tr>
                <td>{{ $index + 1 }}</td>
                <td class="text-left">{{ $detail['part']->nomor_part ?? '-' }}</td>
                <td class="text-left">{{ $detail['part']->nama_part ?? '-' }}</td>
                <td>{{ number_format($detail['jadwal_delivery_pcs']) }}</td>
                <td>{{ number_format($detail['qty_packing_box']) }}</td>
                <td>{{ number_format($detail['jumlah_pulling_box']) }}</td>
                @foreach($availableCycles as $cycle)
                <td>{{ number_format($detail['actual_cycles'][$cycle]['qty_box'] ?? 0) }}</td>
                <td>{{ number_format($detail['actual_cycles'][$cycle]['qty_pcs'] ?? 0) }}</td>
                @endforeach
                <td style="{{ $detail['balance_box'] > 0 ? 'background-color: #fee; color: #c00; font-weight: bold;' : '' }}">{{ number_format($detail['balance_box']) }}</td>
                <td>✓</td>
                <td>✓</td>
                <td>✓</td>
                <td>✓</td>
                <td></td>
                <td></td>
            </tr>
            @empty
            <tr><td colspan="{{ 13 + (count($availableCycles) * 2) }}">Tidak ada data</td></tr>
            @endforelse
            
            @php $emptyRows = 15 - count($details); @endphp
            @if($emptyRows > 0)
            @for($i = 0; $i < $emptyRows; $i++)
            <tr style="height: 18px;">
                <td>{{ count($details) + $i + 1 }}</td>
                <td class="text-left"></td>
                <td class="text-left"></td>
                <td></td>
                <td></td>
                <td></td>
                @foreach($availableCycles as $cycle)
                <td></td>
                <td></td>
                @endforeach
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
                <td></td>
            </tr>
            @endfor
            @endif
        </tbody>
    </table>

    <!-- Legend -->
        <div class="point-control-legend" style="text-align: right; margin-bottom: 5px; font-size: 8px;">
            <div style="display: inline-block; text-align: left;">
                <strong>POINT CONTROL</strong><br>
                <span style="margin-right: 20px;">✓ = SESUAI</span> — = TIDAK TERSEDIA<br>
                X = TIDAK SESUAI
            </div>
        </div>

        <p class="statement" style="margin-bottom: 5px; font-size: 9px;">Dengan ini menyatakan bahwa kelengkapan delivery part dan dokumen ke customer telah terpenuhi dan lengkap melalui metode pengecekan aktual.</p>

        <table class="signature-table">
            <thead>
                <tr>
                    <th colspan="3">PT MADA WIKRI TUNGGAL</th>
                    <th colspan="1">CUSTOMER</th>
                </tr>
                <tr>
                    <th width="25%">PPIC</th>
                    <th width="25%">GA</th>
                    <th width="25%">SECURITY</th>
                    <th width="25%">PT ASTRA HONDA MOTOR</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                    <td class="sig-space"></td>
                </tr>
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align: right; border: 1px solid #000; padding: 2px; font-size: 7px; font-style: italic;">F-PPIC-3-06 Rev:01</td>
                </tr>
            </tfoot>
        </table>
</body>
</html>
