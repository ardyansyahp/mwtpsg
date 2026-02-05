<style>
@page {
  size: 90mm 60mm;
  margin: 0;
}
@media print {
  .no-print { display: none !important; }
  html, body { margin: 0; padding: 0; }
  /* pastikan 1 label tidak kepotong jadi 2 halaman */
  .label-page { break-inside: avoid; page-break-inside: avoid; overflow: hidden; }
}

/* 1 label = 1 page (90x60mm) */
.label-page {
  width: 90mm;
  height: 60mm;
  box-sizing: border-box;
  page-break-after: always;
  padding: 0;
  margin: 0;
}

.label-table {
  width: 100%;
  height: 100%;
  border-collapse: collapse;
  font-family: Arial, sans-serif;
  font-size: 8px;
  table-layout: fixed;
}
.label-table td, .label-table th {
  border: 1px solid #000;
  padding: 1px 2px;
  vertical-align: middle;
}
.center { text-align: center; }
.right { text-align: right; }
.bold { font-weight: 700; }
.mono { font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, Consolas, "Liberation Mono", "Courier New", monospace; }

.logoBox {
  height: 16mm;
}
.logoImg {
  max-width: 100%;
  max-height: 100%;
  display: block;
  margin: 0 auto;
  object-fit: contain;
}
.titleBox {
  font-size: 12px;
  font-weight: 800;
  letter-spacing: 0.5px;
}
.small { font-size: 8px; }

.qrWrap {
  width: 20mm;
  height: 20mm;
  display: flex;
  align-items: center;
  justify-content: center;
}
.qrWrap img { display:block; max-width: 100%; max-height: 100%; }

.months th { font-size: 8px; }
.months td { font-size: 8px; padding: 1px; text-align:center; }
.months td.active { background: #000; color: #fff; font-weight: 700; }

/* inner info table (no grid lines) */
.inner-table { width: 100%; border-collapse: collapse; }
.inner-table td { border: none !important; padding: 1px 2px; vertical-align: top; }
.info-block { font-size: 7px; line-height: 1.15; }
.info-block .label { font-weight: 700; }
.info-block .value { font-weight: 700; font-size: 7px; }
</style>

<div class="no-print" style="display:flex;gap:8px;align-items:center;justify-content:space-between;margin-bottom:12px;">
  <div>
    <div style="font-weight:700;font-size:16px;">Label Material (90x60mm)</div>
    <div style="color:#555;font-size:12px;">
      Tanggal: {{ optional($receiving->tanggal_receiving)->format('Y-m-d') }} |
      Supplier: {{ $receiving->supplier->nama_perusahaan ?? '-' }} |
      SJ: {{ $receiving->no_surat_jalan ?? '-' }}
    </div>
  </div>
  <div style="display:flex;gap:8px;">
    <button onclick="window.print()" style="padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#111;color:#fff;cursor:pointer;">Print / Save PDF</button>
    <button onclick="window.close()" style="padding:8px 12px;border-radius:8px;border:1px solid #ddd;background:#f3f3f3;cursor:pointer;">Tutup</button>
  </div>
</div>

<script src="{{ asset('assets/js/qrcode.min.js') }}"></script>

<div id="labelsRoot">
  @foreach($receiving->details as $d)
    @php
      $month = (int) optional($receiving->tanggal_receiving)->format('n');
      $uom = $d->bahanBaku->uom ?? 'KG';
    @endphp
    <div class="label-page" id="qr-{{ $d->id }}" data-qrcode="{{ $d->qrcode }}">
      <table class="label-table">
        <colgroup>
          <col style="width: 22mm;">
          <col style="width: 43mm;">
          <col style="width: 25mm;">
        </colgroup>
        <tr>
          <td class="center logoBox" style="padding:0;">
            <img src="{{ asset('assets/images/logopt.png') }}" alt="Logo" class="logoImg">
          </td>
          <td class="center titleBox" colspan="2">LABEL MATERIAL</td>
        </tr>
        <tr>
          <td colspan="2">
            <table class="inner-table info-block">
              <tr>
                <td style="width:26mm;" class="label">NAMA MATERIAL</td>
                <td style="width:3mm;">:</td>
                <td class="value">{{ $d->bahanBaku->nama_bahan_baku ?? '-' }}</td>
              </tr>
              <tr>
                <td class="label">QTY (KG/BAG)</td>
                <td>:</td>
                <td>
                  <span class="value">{{ $d->qty }}</span> <span class="value">{{ strtoupper($uom) }}</span>
                </td>
              </tr>
              <tr>
                <td class="label">TANGGAL KEDATANGAN</td>
                <td>:</td>
                <td class="value">{{ optional($receiving->tanggal_receiving)->format('d/m/y') }}</td>
              </tr>
              <tr>
                <td class="label">NOMOR LOT INTERNAL</td>
                <td>:</td>
                <td class="mono value" style="word-break:break-all;">{{ $d->qrcode }}</td>
              </tr>
            </table>
          </td>
          <td rowspan="3" style="padding:0;">
            <table class="label-table months" style="width:100%;height:100%;border-collapse:collapse;">
              <tr>
                <th colspan="2" class="center">BULAN KEDATANGAN</th>
              </tr>
              <tr>
                <td class="{{ $month === 1 ? 'active' : '' }}">JAN</td>
                <td class="{{ $month === 2 ? 'active' : '' }}">FEB</td>
              </tr>
              <tr>
                <td class="{{ $month === 3 ? 'active' : '' }}">MAR</td>
                <td class="{{ $month === 4 ? 'active' : '' }}">APR</td>
              </tr>
              <tr>
                <td class="{{ $month === 5 ? 'active' : '' }}">MEI</td>
                <td class="{{ $month === 6 ? 'active' : '' }}">JUN</td>
              </tr>
              <tr>
                <td class="{{ $month === 7 ? 'active' : '' }}">JUL</td>
                <td class="{{ $month === 8 ? 'active' : '' }}">AGT</td>
              </tr>
              <tr>
                <td class="{{ $month === 9 ? 'active' : '' }}">SEP</td>
                <td class="{{ $month === 10 ? 'active' : '' }}">OKT</td>
              </tr>
              <tr>
                <td class="{{ $month === 11 ? 'active' : '' }}">NOV</td>
                <td class="{{ $month === 12 ? 'active' : '' }}">DES</td>
              </tr>
              <tr>
                <th colspan="2" class="center">CATATAN :</th>
              </tr>
            </table>
          </td>
        </tr>
        <tr>
          <td class="center bold">STATUS MATERIAL</td>
          <td class="center bold">QR CODE</td>
        </tr>
        <tr>
          <td class="center" style="font-size:18px;font-weight:800;">OK</td>
          <td class="center">
            <div class="qrWrap" data-qr-box></div>
          </td>
        </tr>
        <tr>
          <td colspan="3" class="right small" style="padding-right:4px;">
            FORM-PPIC-3-11 &nbsp;&nbsp;&nbsp; REV01
          </td>
        </tr>
      </table>
    </div>
  @endforeach
</div>

<script>
(function(){
  const root = document.getElementById('labelsRoot');
  if (!root || typeof qrcode !== 'function') return;

  const cards = Array.from(root.querySelectorAll('[data-qrcode]'));
  for (const card of cards) {
    const text = card.getAttribute('data-qrcode') || '';
    const box = card.querySelector('[data-qr-box]');
    if (!box || !text) continue;

    const qr = qrcode(0, 'M');
    qr.addData(text);
    qr.make();
    // size disesuaikan untuk 90x60mm (QR ~22mm)
    box.innerHTML = qr.createImgTag(5, 0);
  }
})();
</script>
