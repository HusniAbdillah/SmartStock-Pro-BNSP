<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Transaksi — SmartStock Pro</title>
    <style>
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1E293B;
            background: #FFFFFF;
            line-height: 1.45;
        }
        .page { padding: 20px 24px 16px; }

        /* ── Header ───────────────────────────────────────────── */
        .header {
            background-color: #25345F;
            color: #FFFFFF;
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 14px;
        }
        .header-inner { display: table; width: 100%; }
        .header-left  { display: table-cell; vertical-align: middle; width: 55%; }
        .header-right { display: table-cell; vertical-align: middle; text-align: right; width: 45%; }
        .brand-row    { display: table; }
        .brand-logo   { display: table-cell; vertical-align: middle; padding-right: 10px; }
        .brand-logo img { height: 38px; width: auto; }
        .brand-text   { display: table-cell; vertical-align: middle; }
        .brand-name   { font-size: 17px; font-weight: bold; color: #FFFFFF; letter-spacing: -0.3px; }
        .brand-name span { color: #FFAA6E; }
        .brand-sub    { font-size: 8px; color: #B0BDD8; margin-top: 2px; }
        .report-title { font-size: 12px; font-weight: bold; color: #FFFFFF; letter-spacing: 0.06em;
                        text-transform: uppercase; margin-bottom: 4px; }
        .report-meta  { font-size: 8px; color: #B0BDD8; line-height: 1.6; }

        /* ── KPI cards ─────────────────────────────────────────── */
        .kpi-row    { display: table; width: 100%; margin-bottom: 14px; }
        .kpi-cell   { display: table-cell; width: 25%; padding-right: 8px; }
        .kpi-cell:last-child { padding-right: 0; }
        .kpi-card   { border: 1px solid #D4DEE9; border-radius: 5px; padding: 9px 12px; background: #F8FAFC; }
        .kpi-card.kpi-brand   { background: #EEF2FF; border-color: #C7D2FE; border-left: 3px solid #533AFD; }
        .kpi-card.kpi-success { background: #EDFAF3; border-color: #A7F3D0; border-left: 3px solid #10B981; }
        .kpi-card.kpi-warning { background: #FFF3E8; border-color: #FBD0A8; border-left: 3px solid #FF6118; }
        .kpi-card.kpi-info    { background: #EFF6FF; border-color: #BFDBFE; border-left: 3px solid #3B82F6; }
        .kpi-label  { font-size: 7px; font-weight: 700; letter-spacing: 0.08em; text-transform: uppercase; color: #5A6A85; }
        .kpi-value  { font-size: 16px; font-weight: bold; color: #1E293B; margin-top: 3px; }
        .kpi-brand   .kpi-value { color: #4330D4; }
        .kpi-success .kpi-value { color: #065F46; }
        .kpi-warning .kpi-value { color: #C2410C; }
        .kpi-info    .kpi-value { color: #1D4ED8; }
        .kpi-sub    { font-size: 7px; color: #94A3B8; margin-top: 2px; }

        /* ── Section headers ───────────────────────────────────── */
        .section-header {
            background: #EBF0F7;
            border-left: 4px solid #10B981;
            padding: 7px 10px;
            font-size: 9px;
            font-weight: 700;
            color: #1E293B;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 0;
        }

        /* ── Tables ────────────────────────────────────────────── */
        table { width: 100%; border-collapse: collapse; margin-bottom: 14px; font-size: 8.5px; }
        th {
            background: #1E293B;
            color: #FFFFFF;
            padding: 6px 8px;
            text-align: left;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
        }
        td { padding: 5px 8px; border-bottom: 1px solid #EFF4F9; vertical-align: top; }
        tr.row-alt td { background: #F8FAFC; }
        tr:last-child td { border-bottom: none; }

        /* ── Badges ─────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 6px;
            border-radius: 3px;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge-in    { background: #D1FAE5; color: #065F46; }
        .badge-out   { background: #FEE2E2; color: #991B1B; }
        .badge-adj   { background: #FEF3C7; color: #92400E; }
        .amount-in   { text-align: right; font-weight: 700; color: #065F46; }
        .amount-out  { text-align: right; font-weight: 700; color: #991B1B; }

        /* ── Footer ─────────────────────────────────────────────── */
        .footer {
            border-top: 2px solid #E5EDF5;
            padding-top: 8px;
            margin-top: 6px;
            display: table;
            width: 100%;
        }
        .footer-left  { display: table-cell; vertical-align: middle; font-size: 7.5px; color: #94A3B8; }
        .footer-right { display: table-cell; vertical-align: middle; text-align: right; font-size: 7.5px; color: #94A3B8; }
        .page-break   { page-break-after: always; }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="brand-row">
                    @if($logoBase64)
                    <div class="brand-logo">
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="SmartStock Pro">
                    </div>
                    @endif
                    <div class="brand-text">
                        <div class="brand-name">Smart<span>Stock</span> Pro</div>
                        <div class="brand-sub">PT Maju Bersama Digital</div>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="report-title">Laporan Transaksi Inventaris</div>
                <div class="report-meta">
                    Periode: {{ tgl_indo($dateFrom, 'd F Y') }} – {{ tgl_indo($dateTo, 'd F Y') }}<br>
                    Dicetak: {{ tgl_indo(now(), 'd F Y') }}, {{ now()->format('H:i') }} WIB<br>
                    Operator: {{ auth()->user()->name ?? 'Sistem' }}
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI Cards ───────────────────────────────────────────── --}}
    <div class="kpi-row">
        <div class="kpi-cell">
            <div class="kpi-card kpi-brand">
                <div class="kpi-label">Total Transaksi</div>
                <div class="kpi-value">{{ number_format($transactions->count()) }}</div>
                <div class="kpi-sub">Semua jenis transaksi</div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-success">
                <div class="kpi-label">Barang Masuk</div>
                <div class="kpi-value">{{ number_format($totalIn) }}</div>
                <div class="kpi-sub">unit masuk gudang</div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-warning">
                <div class="kpi-label">Barang Keluar</div>
                <div class="kpi-value">{{ number_format($totalOut) }}</div>
                <div class="kpi-sub">unit keluar gudang</div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-info">
                <div class="kpi-label">Gudang Aktif</div>
                <div class="kpi-value">{{ $activeWarehouses }}</div>
                <div class="kpi-sub">memiliki transaksi</div>
            </div>
        </div>
    </div>

    {{-- ── Tabel Transaksi ─────────────────────────────────────── --}}
    <div class="section-header">Rincian Transaksi ({{ $transactions->count() }} data)</div>
    @if($transactions->isEmpty())
        <div style="padding:20px; text-align:center; color:#94A3B8; border:1px solid #E5EDF5; border-top:none; border-radius:0 0 4px 4px;">
            Tidak ada transaksi dalam periode ini.
        </div>
    @else
    <table>
        <thead>
            <tr>
                <th style="width:12%;">Tanggal</th>
                <th style="width:5%;">Tipe</th>
                <th style="width:22%;">Produk</th>
                <th style="width:8%;">SKU</th>
                <th style="width:18%;">Gudang</th>
                <th style="width:6%; text-align:right;">Jumlah</th>
                <th style="width:10%;">Operator</th>
                <th style="width:19%;">Catatan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($transactions as $i => $trx)
            <tr class="{{ $i % 2 === 1 ? 'row-alt' : '' }}">
                <td>{{ $trx->created_at->format('d/m/Y H:i') }}</td>
                <td>
                    @if($trx->type === 'masuk')
                        <span class="badge badge-in">Masuk</span>
                    @elseif($trx->type === 'keluar')
                        <span class="badge badge-out">Keluar</span>
                    @else
                        <span class="badge badge-adj">{{ ucfirst($trx->type) }}</span>
                    @endif
                </td>
                <td style="font-weight:500;">{{ $trx->product->name ?? '—' }}</td>
                <td style="font-family:monospace; color:#533AFD;">{{ $trx->product->sku ?? '—' }}</td>
                <td>{{ $trx->warehouse->name ?? '—' }}</td>
                <td class="{{ $trx->type === 'masuk' ? 'amount-in' : 'amount-out' }}">
                    {{ $trx->type === 'masuk' ? '+' : '-' }}{{ number_format($trx->quantity) }}
                </td>
                <td>{{ $trx->operator->name ?? 'Sistem' }}</td>
                <td style="color:#64748B;">{{ Str::limit($trx->notes ?? '', 45) ?: '—' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Rekap per Gudang ─────────────────────────────────────── --}}
    @if($warehouseSummary->count() > 0)
    <div class="section-header" style="margin-top:4px;">Rekap per Gudang</div>
    <table>
        <thead>
            <tr>
                <th>Gudang</th>
                <th style="text-align:right;">Transaksi</th>
                <th style="text-align:right;">Total Masuk</th>
                <th style="text-align:right;">Total Keluar</th>
                <th style="text-align:right;">Saldo Bersih</th>
            </tr>
        </thead>
        <tbody>
            @foreach($warehouseSummary as $i => $ws)
            <tr class="{{ $i % 2 === 1 ? 'row-alt' : '' }}">
                <td style="font-weight:500;">{{ $ws->warehouse_name }}</td>
                <td style="text-align:right;">{{ number_format($ws->jumlah_transaksi) }}</td>
                <td style="text-align:right; color:#065F46;">+{{ number_format($ws->total_masuk) }}</td>
                <td style="text-align:right; color:#991B1B;">-{{ number_format($ws->total_keluar) }}</td>
                <td class="{{ ($ws->total_masuk - $ws->total_keluar) >= 0 ? 'amount-in' : 'amount-out' }}">
                    {{ $ws->total_masuk - $ws->total_keluar >= 0 ? '+' : '' }}{{ number_format($ws->total_masuk - $ws->total_keluar) }}
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Footer ────────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">
            SmartStock Pro © {{ date('Y') }} — PT Maju Bersama Digital &nbsp;|&nbsp;
            Laporan ini dicetak secara otomatis oleh sistem
        </div>
        <div class="footer-right">
            Dokumen Rahasia — Hanya untuk penggunaan internal
        </div>
    </div>

</div>
</body>
</html>
