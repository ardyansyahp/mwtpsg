<style>
@page {
  size: 90mm 60mm;
  margin: 0;
}
@media print {
  .no-print { display: none !important; }
  html, body { margin: 0; padding: 0; }
  .label-page { break-inside: avoid; page-break-inside: avoid; overflow: hidden; }
}

.label-page {
  width: 90mm;
  height: 60mm;
  box-sizing: border-box;
  page-break-after: always;
  padding: 2mm;
  margin: 0;
  font-family: Arial, sans-serif;
}

.label-table {
  width: 100%;
  height: 100%;
  border-collapse: collapse;
  table-layout: fixed;
  font-size: 7px;
}

.label-table td {
  border: 1px solid #000;
  padding: 1px 2px;
  vertical-align: top;
}

.header-row {
  height: 12mm;
}

.header-left {
  width: 20mm;
  text-align: center;
  vertical-align: middle;
}

.header-center {
  text-align: center;
  vertical-align: middle;
  padding: 1mm;
}

.header-right {
  width: 25mm;
  text-align: center;
  vertical-align: middle;
}

.logo-circle {
  width: 15mm;
  height: 15mm;
  border-radius: 50%;
  background-color: #4CAF50;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
  color: white;
  font-weight: bold;
  font-size: 8px;
}

.logo-text {
  font-size: 10px;
  line-height: 1;
}

.logo-subtext {
  font-size: 6px;
  line-height: 1;
  margin-top: 1px;
}

.company-name {
  font-size: 10px;
  font-weight: 800;
  line-height: 1.2;
  letter-spacing: 0.3px;
}

.company-subtitle {
  font-size: 7px;
  font-style: italic;
  margin-top: 1px;
}

.status-ok {
  font-size: 18px;
  font-weight: 900;
  color: #000;
}

.qr-wrap {
  width: 20mm;
  height: 20mm;
  display: flex;
  align-items: center;
  justify-content: center;
  margin: 0 auto;
}

.qr-wrap img {
  display: block;
  max-width: 100%;
  max-height: 100%;
}

.data-row {
  font-size: 7px;
}

.data-label {
  font-weight: 700;
  padding-right: 2px;
  width: 25mm;
}

.data-value {
  font-weight: 700;
}

.data-table {
  width: 100%;
  border-collapse: collapse;
  font-size: 7px;
}

.data-table td {
  border: none;
  padding: 1px 2px;
  vertical-align: top;
}

.footer-row {
  height: auto;
}

.operator-name {
  font-size: 14px;
  font-weight: 900;
  padding: 2px;
}

.footer-left {
  vertical-align: bottom;
  padding: 2px;
}

.footer-right {
  text-align: right;
  vertical-align: bottom;
  padding: 2px;
  font-size: 6px;
}

.lot-number {
  font-size: 7px;
  font-weight: 700;
  margin-top: 2px;
}

.center { text-align: center; }
.right { text-align: right; }
.bold { font-weight: 700; }
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }
</style>

<div class="no-print" style="display:flex;gap:8px;align-items:center;justify-content:space-between;margin-bottom:12px;">
  <div>
    <div style="font-weight:700;font-size:16px;">Label Produksi Inject In</div>
    <div style="color:#555;font-size:12px;">
      ID: {{ $injectIn->id }} | 
      Mesin: {{ $injectIn->mesin->no_mesin ?? '-' }} | 
      Tanggal: {{ \Carbon\Carbon::parse($injectIn->waktu_scan)->setTimezone('Asia/Jakarta')->format('Y-m-d H:i:s') }}
    </div>
  </div>
  <div style="display:flex;gap:8px;">
    <button onclick="window.print()" style="padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#111;color:#fff;cursor:pointer;">Print / Save PDF</button>
    <button onclick="window.close()" style="padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#f3f3f3;cursor:pointer;">Tutup</button>
  </div>
</div>

<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>

@php
  $planningRun = $injectIn->planningRun;
  $part = $planningRun && $planningRun->mold ? $planningRun->mold->part : null;
  $tipe = $part && $part->tipe ? $part->tipe->nama_part : '-';
  $nomorPart = $part ? $part->nomor_part : '-';
  $namaPart = $part ? $part->nama_part : '-';
  $qtyPacking = $part ? ($part->QTY_Packing_Box ?? 0) : 0;
  $waktuScan = \Carbon\Carbon::parse($injectIn->waktu_scan)->setTimezone('Asia/Jakarta');
  $prodDate = $waktuScan->format('d/m/y');
  
  // Hitung shift dari planning run start_at (bukan waktu_scan)
  $shift = 1; // default
  if ($planningRun && $planningRun->start_at) {
    $startAt = \Carbon\Carbon::parse($planningRun->start_at)->setTimezone('Asia/Jakarta');
    $jam = (int) $startAt->format('H');
    if ($jam >= 7 && $jam < 15) {
      $shift = 1; // 07:00 - 14:59 (7 pagi - 3 sore)
    } elseif ($jam >= 15 && $jam < 23) {
      $shift = 2; // 15:00 - 22:59 (3 sore - 11 malam)
    } else {
      $shift = 3; // 23:00 - 06:59 (11 malam - 7 pagi)
    }
  }
  
  $manpower = $injectIn->manpower ?? '-';
  
  // Extract bagian akhir lot number (105-29-9-25-1)
  $lotNumberFull = $injectIn->lot_number ?? '';
  $lotNumberParts = explode('|', $lotNumberFull);
  $lotNumberEnd = count($lotNumberParts) > 3 ? $lotNumberParts[3] : '';
  
  $timestamp = $waktuScan->format('d/m/Y H:i:s');
@endphp

<div class="label-page" id="label-{{ $injectIn->id }}" data-lot-number="{{ $injectIn->lot_number }}">
  <table class="label-table">
    <colgroup>
      <col style="width: 20mm;">
      <col style="width: 43mm;">
      <col style="width: 25mm;">
    </colgroup>
    
    {{-- Header Row --}}
    <tr class="header-row">
      <td class="header-left">
        <div class="logo-circle">
          <div class="logo-text">MW</div>
          <div class="logo-subtext">Cikarang</div>
        </div>
      </td>
      <td class="header-center">
        <div class="company-name">PT MADA WIKRI TUNGGAL</div>
        <div class="company-subtitle">Metal & Plastics Industries</div>
      </td>
      <td class="header-right">
        <div class="status-ok">OK</div>
        <div class="qr-wrap" data-qr-box></div>
      </td>
    </tr>
    
    {{-- Data Row --}}
    <tr class="data-row">
      <td colspan="2" style="padding: 2px;">
        <table class="data-table">
          <tr>
            <td class="data-label">PART NAME</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $namaPart }}</td>
          </tr>
          <tr>
            <td class="data-label">PART NUMBER</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $nomorPart }}</td>
          </tr>
          <tr>
            <td class="data-label">TYPE</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $tipe }}</td>
          </tr>
          <tr>
            <td class="data-label">QTY</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $qtyPacking }} PCS</td>
            <td class="data-label" style="width: 15mm;">BOX NO</td>
            <td class="data-value">:</td>
            <td class="data-value"></td>
          </tr>
          <tr>
            <td class="data-label">PROD. DATE</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $prodDate }}</td>
            <td class="data-label">REMARK</td>
            <td class="data-value">:</td>
            <td class="data-value"></td>
          </tr>
          <tr>
            <td class="data-label">SHIFT</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $shift }}</td>
          </tr>
          <tr>
            <td class="data-label">Operator Produksi</td>
            <td class="data-value">:</td>
            <td class="data-value">{{ $manpower }}</td>
            <td class="data-label">OQC</td>
            <td class="data-value">:</td>
            <td class="data-value"></td>
          </tr>
        </table>
      </td>
    </tr>
    
    {{-- Footer Row --}}
    <tr class="footer-row">
      <td colspan="2" class="footer-left">
        <div class="operator-name">{{ $manpower }}</div>
        <div class="lot-number">NO LOT : {{ $lotNumberEnd }}</div>
      </td>
      <td class="footer-right">
        {{ $timestamp }}
      </td>
    </tr>
  </table>
</div>

<script>
(function(){
  const label = document.getElementById('label-{{ $injectIn->id }}');
  if (!label || typeof qrcode !== 'function') return;

  const lotNumber = label.getAttribute('data-lot-number') || '';
  const qrBox = label.querySelector('[data-qr-box]');
  if (!qrBox || !lotNumber) return;

  const qr = qrcode(0, 'M');
  qr.addData(lotNumber);
  qr.make();
  qrBox.innerHTML = qr.createImgTag(5, 0);
})();
</script>

