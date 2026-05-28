<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Laporan Inventaris — SmartStock Pro</title>
    <style>
        /* ── Reset & base ─────────────────────────────────────── */
        * { margin:0; padding:0; box-sizing:border-box; }
        body {
            font-family: 'DejaVu Sans', sans-serif;
            font-size: 9px;
            color: #1E293B;
            background: #FFFFFF;
            line-height: 1.45;
        }
        .page { padding: 20px 24px 16px; }

        /* ── Header bar ────────────────────────────────────────── */
        .header {
            background-color: #25345F;
            color: #FFFFFF;
            padding: 14px 18px;
            border-radius: 6px;
            margin-bottom: 14px;
        }
        .header-inner {
            display: table;
            width: 100%;
        }
        .header-left {
            display: table-cell;
            vertical-align: middle;
            width: 55%;
        }
        .header-right {
            display: table-cell;
            vertical-align: middle;
            text-align: right;
            width: 45%;
        }
        .brand-row {
            display: table;
        }
        .brand-logo {
            display: table-cell;
            vertical-align: middle;
            padding-right: 10px;
        }
        .brand-logo img {
            height: 38px;
            width: auto;
        }
        .brand-text {
            display: table-cell;
            vertical-align: middle;
        }
        .brand-name {
            font-size: 17px;
            font-weight: bold;
            color: #FFFFFF;
            letter-spacing: -0.3px;
        }
        .brand-name span { color: #FFAA6E; }
        .brand-sub {
            font-size: 8px;
            color: #B0BDD8;
            margin-top: 2px;
        }
        .report-title {
            font-size: 12px;
            font-weight: bold;
            color: #FFFFFF;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            margin-bottom: 4px;
        }
        .report-meta {
            font-size: 8px;
            color: #B0BDD8;
            line-height: 1.6;
        }

        /* ── KPI cards ─────────────────────────────────────────── */
        .kpi-row {
            display: table;
            width: 100%;
            margin-bottom: 14px;
        }
        .kpi-cell {
            display: table-cell;
            width: 24%;
            padding-right: 8px;
        }
        .kpi-cell:last-child { padding-right: 0; }
        .kpi-card {
            border: 1px solid #D4DEE9;
            border-radius: 5px;
            padding: 9px 12px;
            background: #F8FAFC;
        }
        .kpi-card.kpi-brand {
            background: #EEF2FF;
            border-color: #C7D2FE;
            border-left: 3px solid #533AFD;
        }
        .kpi-card.kpi-warning {
            background: #FFF3E8;
            border-color: #FBD0A8;
            border-left: 3px solid #FF6118;
        }
        .kpi-card.kpi-danger {
            background: #FFF0F0;
            border-color: #FECACA;
            border-left: 3px solid #DC2626;
        }
        .kpi-card.kpi-success {
            background: #EDFAF3;
            border-color: #A7F3D0;
            border-left: 3px solid #10B981;
        }
        .kpi-label {
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: #5A6A85;
        }
        .kpi-value {
            font-size: 16px;
            font-weight: bold;
            color: #1E293B;
            margin-top: 3px;
        }
        .kpi-brand  .kpi-value { color: #4330D4; }
        .kpi-warning .kpi-value { color: #C2410C; }
        .kpi-danger .kpi-value  { color: #B91C1C; }
        .kpi-success .kpi-value { color: #065F46; }

        /* ── Section headers ───────────────────────────────────── */
        .section-header {
            background: #EBF0F7;
            border-left: 4px solid #533AFD;
            padding: 7px 10px;
            font-size: 9px;
            font-weight: 700;
            color: #1E293B;
            letter-spacing: 0.04em;
            text-transform: uppercase;
            margin-bottom: 0;
        }

        /* ── Tables ────────────────────────────────────────────── */
        table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 14px;
            font-size: 8.5px;
        }
        table.compact td,
        table.compact th { padding: 4px 7px; }

        thead tr { background-color: #25345F; }
        thead th {
            padding: 6px 8px;
            text-align: left;
            font-size: 7.5px;
            font-weight: 700;
            letter-spacing: 0.05em;
            text-transform: uppercase;
            color: #FFFFFF;
        }
        /* nth-child tidak didukung DomPDF, pakai class .row-alt di baris genap */
        tbody td {
            padding: 5px 8px;
            border-bottom: 1px solid #DDE5F0;
            vertical-align: middle;
            color: #1E293B;
        }
        tbody tr.row-alt td { background: #F4F7FB; }
        tbody tr:last-child td { border-bottom: none; }

        /* Row accent strips */
        .row-danger  td:first-child { border-left: 3px solid #DC2626; }
        .row-warning td:first-child { border-left: 3px solid #FF6118; }
        .row-success td:first-child { border-left: 3px solid #10B981; }

        /* ── Badges ────────────────────────────────────────────── */
        .badge {
            display: inline-block;
            padding: 2px 7px;
            border-radius: 10px;
            font-size: 7px;
            font-weight: 700;
            letter-spacing: 0.04em;
            text-transform: uppercase;
        }
        .badge-danger  { background: #FEE2E2; color: #991B1B; }
        .badge-warning { background: #FFF0E8; color: #9A3412; }
        .badge-success { background: #D1FAE5; color: #065F46; }
        .badge-info    { background: #E8E9FF; color: #4338CA; }
        .badge-neutral { background: #F1F5F9; color: #475569; }
        .badge-masuk   { background: #D1FAE5; color: #065F46; }
        .badge-keluar  { background: #FEE2E2; color: #991B1B; }
        .badge-transfer { background: #FEF3C7; color: #92400E; }

        /* ── Typography helpers ────────────────────────────────── */
        .text-right  { text-align: right; }
        .text-center { text-align: center; }
        .text-muted  { color: #64748D; }
        .font-bold   { font-weight: 700; }
        .font-mono   { font-family: 'DejaVu Sans Mono', monospace; font-size: 7.5px; }
        .text-brand  { color: #533AFD; }
        .text-danger { color: #DC2626; }
        .text-success { color: #065F46; }

        /* ── Divider ───────────────────────────────────────────── */
        .divider {
            height: 1px;
            background: #E5EDF5;
            margin: 12px 0;
        }

        /* ── Footer ────────────────────────────────────────────── */
        .footer {
            border-top: 1px solid #E5EDF5;
            padding-top: 8px;
            margin-top: 12px;
            display: table;
            width: 100%;
        }
        .footer-left {
            display: table-cell;
            font-size: 7px;
            color: #94A3B8;
            vertical-align: middle;
        }
        .footer-right {
            display: table-cell;
            text-align: right;
            font-size: 7px;
            color: #94A3B8;
            vertical-align: middle;
        }
        .page-stamp {
            display: inline-block;
            border: 1px solid #D4DEE9;
            border-radius: 3px;
            padding: 2px 7px;
            font-size: 7px;
            color: #533AFD;
            font-weight: 700;
            letter-spacing: 0.04em;
        }
    </style>
</head>
<body>
<div class="page">

    {{-- ── Header ──────────────────────────────────────────────── --}}
    <div class="header">
        <div class="header-inner">
            <div class="header-left">
                <div class="brand-row">
                    <div class="brand-logo">
                        {{-- logoBase64 disiapkan controller (sudah di-resize ke 100px) --}}
                        @if($logoBase64)
                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="SmartStock Pro">
                        @else
                        <div style="width:38px;height:38px;background-color:#533AFD;border-radius:8px;text-align:center;padding-top:9px;">
                            <svg width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="white" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/></svg>
                        </div>
                        @endif
                    </div>
                    <div class="brand-text">
                        <div class="brand-name">SmartStock <span>Pro</span></div>
                        <div class="brand-sub">PT Maju Bersama Digital</div>
                    </div>
                </div>
            </div>
            <div class="header-right">
                <div class="report-title">
                    <span class="accent-dot"></span>Laporan Inventaris
                </div>
                <div class="report-meta">
                    Dibuat&nbsp;: {{ tgl_indo(now(), 'd F Y') }}, {{ now()->format('H:i') }} WIB<br>
                    Periode&nbsp;&nbsp;: {{ tgl_indo(now(), 'F Y') }}<br>
                    Cakupan&nbsp;: {{ $filterLabel ?? 'Seluruh Gudang Aktif' }}
                    @if(empty($filterLabel)) ({{ $warehouses->count() }} lokasi)@endif
                </div>
            </div>
        </div>
    </div>

    {{-- ── KPI Summary ──────────────────────────────────────────── --}}
    <div class="kpi-row">
        <div class="kpi-cell">
            <div class="kpi-card kpi-brand">
                <div class="kpi-label">Total Nilai Inventaris</div>
                <div class="kpi-value">
                    @if($totalValue >= 1000000000)
                        Rp {{ number_format($totalValue / 1000000000, 2) }} M
                    @else
                        Rp {{ number_format($totalValue / 1000000, 1) }} Jt
                    @endif
                </div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-success">
                <div class="kpi-label">Total Stok (Unit)</div>
                <div class="kpi-value">{{ number_format($totalStock) }}</div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-warning">
                <div class="kpi-label">Item Stok Rendah</div>
                <div class="kpi-value">{{ $criticalItems->where('quantity', '>', 0)->count() }}</div>
            </div>
        </div>
        <div class="kpi-cell">
            <div class="kpi-card kpi-danger">
                <div class="kpi-label">Item Stok Habis</div>
                <div class="kpi-value">{{ $criticalItems->where('quantity', 0)->count() }}</div>
            </div>
        </div>
    </div>

    {{-- ── Ringkasan per Gudang ─────────────────────────────────── --}}
    <div class="section-header">Ringkasan Stok per Gudang</div>
    <table class="compact">
        <thead>
            <tr>
                <th style="width:30%">Gudang</th>
                <th>Kota</th>
                <th class="text-right">Total Stok (Unit)</th>
                <th class="text-right">Estimasi Nilai (Rp)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($warehouses as $wh)
            @php
                $whQty   = $stockSummary->where('warehouse_name', $wh->name)->sum('quantity');
                $whValue = $stockSummary->where('warehouse_name', $wh->name)->sum('nilai');
            @endphp
            <tr class="{{ $loop->even ? 'row-alt' : '' }}">
                <td class="font-bold">{{ $wh->name }}</td>
                <td class="text-muted">{{ $wh->city }}</td>
                <td class="text-right font-bold">{{ number_format($whQty) }}</td>
                <td class="text-right">{{ number_format($whValue, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($wh->is_active)
                    <span class="badge badge-success">Aktif</span>
                    @else
                    <span class="badge badge-neutral">Non-aktif</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Detail Stok Produk per Gudang ───────────────────────── --}}
    <div class="section-header">Detail Stok Produk per Gudang</div>
    <table>
        <thead>
            <tr>
                <th style="width:22%">Nama Produk</th>
                <th class="font-mono">SKU</th>
                <th>Kategori</th>
                <th>Gudang</th>
                <th class="text-right">Qty</th>
                <th class="text-right" style="width:13%">Nilai (Rp)</th>
                <th class="text-center">Status</th>
            </tr>
        </thead>
        <tbody>
            @foreach($stockSummary as $item)
            @php
                $isCritical = (int)$item->quantity === 0;
                $isWarning  = !$isCritical && (int)$item->quantity <= (int)$item->minimum_threshold;
                $statusClass = $isCritical ? 'row-danger' : ($isWarning ? 'row-warning' : 'row-success');
                $altClass    = $loop->even ? 'row-alt' : '';
            @endphp
            <tr class="{{ $statusClass }} {{ $altClass }}">
                <td class="font-bold">{{ $item->product_name }}</td>
                <td class="font-mono text-muted">{{ $item->sku }}</td>
                <td class="text-muted">{{ $item->category_name }}</td>
                <td class="text-muted">{{ $item->city }}</td>
                <td class="text-right font-bold">{{ number_format($item->quantity) }}</td>
                <td class="text-right">{{ number_format($item->nilai, 0, ',', '.') }}</td>
                <td class="text-center">
                    @if($isCritical)
                    <span class="badge badge-danger">Habis</span>
                    @elseif($isWarning)
                    <span class="badge badge-warning">Rendah</span>
                    @else
                    <span class="badge badge-success">Normal</span>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>

    {{-- ── Transaksi Terbaru ────────────────────────────────────── --}}
    @if($recentTransactions->count() > 0)
    <div class="section-header">
        Transaksi Terbaru &nbsp;—&nbsp; {{ $recentTransactions->count() }} Data Terakhir
    </div>
    <table class="compact">
        <thead>
            <tr>
                <th class="font-mono">No. Referensi</th>
                <th style="width:22%">Produk</th>
                <th>Gudang</th>
                <th class="text-center">Tipe</th>
                <th class="text-right">Qty</th>
                <th>Operator</th>
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recentTransactions as $tx)
            <tr class="{{ $loop->even ? 'row-alt' : '' }}">
                <td class="font-mono text-brand">{{ $tx->reference_number ?? '—' }}</td>
                <td class="font-bold">{{ \Illuminate\Support\Str::limit($tx->product->name ?? '—', 28) }}</td>
                <td class="text-muted">{{ $tx->warehouse->city ?? '—' }}</td>
                <td class="text-center">
                    @if($tx->type === 'Masuk')
                    <span class="badge badge-masuk">Masuk</span>
                    @elseif($tx->type === 'Keluar')
                    <span class="badge badge-keluar">Keluar</span>
                    @else
                    <span class="badge badge-transfer">Transfer</span>
                    @endif
                </td>
                <td class="text-right font-bold">{{ number_format($tx->quantity) }}</td>
                <td class="text-muted">{{ $tx->operator->name ?? '—' }}</td>
                <td class="text-muted">{{ $tx->created_at->format('d/m/Y H:i') }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif

    {{-- ── Footer ───────────────────────────────────────────────── --}}
    <div class="footer">
        <div class="footer-left">
            Dokumen ini bersifat <strong>RAHASIA</strong> dan hanya untuk penggunaan internal.<br>
            Dibuat otomatis oleh <strong>SmartStock Pro</strong> &copy; {{ date('Y') }} — PT Maju Bersama Digital.
        </div>
        <div class="footer-right">
            <div class="page-stamp">RESMI &amp; TERVERIFIKASI</div><br>
            {{ now()->format('d/m/Y H:i') }} WIB
        </div>
    </div>

</div>
</body>
</html>
