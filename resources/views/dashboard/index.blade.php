@extends('layouts.app')

@section('title', 'Dashboard')
@section('page-title', 'Dashboard')

@section('content')

{{-- KPI Row --}}
<div class="grid grid-cols-2 lg:grid-cols-4 gap-5 mb-8">

    <x-stat-card
        label="Total Produk"
        :value="number_format($totalProducts)"
        :icon="'<svg fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'1.75\' width=\'20\' height=\'20\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7\'/></svg>'"
        color="#533AFD"
        :detail="$totalProducts . ' SKU aktif'"
    />

    <x-stat-card
        label="Stok Rendah"
        :value="number_format($lowStockCount)"
        :icon="'<svg fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'1.75\' width=\'20\' height=\'20\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z\'/></svg>'"
        color="#EF4444"
        :detail="'Perlu restok segera'"
    />

    <x-stat-card
        label="Transaksi Hari Ini"
        :value="number_format($todayTransactions)"
        :icon="'<svg fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'1.75\' width=\'20\' height=\'20\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2\'/></svg>'"
        color="#10B981"
        detail="Aktivitas hari ini"
    />

    <x-stat-card
        label="Total Gudang"
        :value="number_format($totalWarehouses)"
        :icon="'<svg fill=\'none\' viewBox=\'0 0 24 24\' stroke=\'currentColor\' stroke-width=\'1.75\' width=\'20\' height=\'20\'><path stroke-linecap=\'round\' stroke-linejoin=\'round\' d=\'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4\'/></svg>'"
        color="#533AFD"
        detail="Aktif beroperasi"
    />
</div>

{{-- Charts row --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5 mb-8">

    {{-- Transaction trend --}}
    <div class="lg:col-span-2 ss-card" style="padding:24px;">
        <div class="flex items-center justify-between mb-6">
            <div>
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Tren Transaksi</h4>
                <p style="font-size:12px; color:#64748D; margin-top:2px;">7 hari terakhir</p>
            </div>
        </div>
        <div style="height:200px;">
            <canvas id="trendChart"></canvas>
        </div>
    </div>

    {{-- Stock by warehouse --}}
    <div class="ss-card" style="padding:24px;">
        <div class="mb-6">
            <h4 style="font-size:14px; font-weight:500; color:#061B31;">Stok per Gudang</h4>
            <p style="font-size:12px; color:#64748D; margin-top:2px;">Distribusi saat ini</p>
        </div>
        <div style="height:160px;" class="mb-4">
            <canvas id="warehouseChart"></canvas>
        </div>
        <div class="space-y-2">
            @foreach($warehouseStocks->take(4) as $i => $ws)
            @php
            $colors = ['#533AFD','#10B981','#F59E0B','#EF4444','#8B5CF6'];
            $c = $colors[$i % count($colors)];
            @endphp
            <div class="flex items-center justify-between">
                <div class="flex items-center gap-2">
                    <div class="w-2 h-2 rounded-full flex-shrink-0" :style="`background: {{ $c }}`"></div>
                    <span style="font-size:12px; color:#061B31;">{{ Str::limit($ws->name, 18) }}</span>
                </div>
                <span style="font-size:12px; font-weight:500; color:#64748D;">{{ number_format($ws->total_stock ?? 0) }}</span>
            </div>
            @endforeach
        </div>
    </div>
</div>

{{-- Bottom row: low stock + map + server --}}
<div class="grid grid-cols-1 lg:grid-cols-3 gap-5">

    {{-- Low stock alerts --}}
    <div class="ss-card" style="padding:0; overflow:hidden;">
        <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
            <div class="flex items-center justify-between">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Stok Rendah</h4>
                @if($lowStockCount > 0)
                <span class="badge-alert">{{ $lowStockCount }} item</span>
                @endif
            </div>
        </div>
        <div style="max-height:280px; overflow-y:auto;">
            @forelse($lowStockProducts as $product)
            <a href="{{ route('products.show', $product) }}"
               class="flex items-center justify-between px-6 py-3 no-underline"
               style="border-bottom:1px solid #F1F5F9; transition:background-color 100ms ease;"
               onmouseover="this.style.backgroundColor='#F8FAFC';"
               onmouseout="this.style.backgroundColor='transparent';">
                <div class="min-w-0 flex-1">
                    <p style="font-size:13px; font-weight:500; color:#061B31; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">{{ $product->name }}</p>
                    <p style="font-size:12px; color:#64748D; margin-top:1px;">{{ $product->category->name ?? '—' }}</p>
                </div>
                <div class="ml-3 text-right flex-shrink-0">
                    <span class="badge-alert">{{ $product->total_stock }} unit</span>
                    <p style="font-size:11px; color:#B8CCDB; margin-top:2px;">min {{ $product->minimum_threshold }}</p>
                </div>
            </a>
            @empty
            <div class="px-6 py-8 text-center">
                <svg class="w-8 h-8 mx-auto mb-2" style="color:#D4DEE9;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p style="font-size:13px; color:#64748D;">Semua stok dalam kondisi baik</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Warehouse map --}}
    <div class="ss-card" style="padding:0; overflow:hidden;">
        <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
            <h4 style="font-size:14px; font-weight:500; color:#061B31;">Lokasi Gudang</h4>
            <p style="font-size:12px; color:#64748D; margin-top:2px;">{{ count($mapData) }} gudang aktif</p>
        </div>
        <div class="leaflet-map-host" style="height:280px;">
            <div id="warehouseMap" style="width:100%;height:100%;min-height:280px;"></div>
            @if(count($mapData) === 0)
            <div class="leaflet-map-empty">Belum ada koordinat gudang. Tambahkan latitude/longitude di data gudang.</div>
            @endif
        </div>
    </div>

    {{-- Server monitor --}}
    <div class="ss-card" style="padding:0; overflow:hidden;" x-data="serverMonitor()" x-init="init()">
        <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
            <div class="flex items-center justify-between">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Monitor Server</h4>
                <div class="flex items-center gap-1.5">
                    <div class="w-2 h-2 rounded-full" :class="status === 'ok' ? 'bg-green-400' : 'bg-red-400'" style="animation: pulse 2s infinite;"></div>
                    <span style="font-size:12px; color:#64748D;" x-text="status === 'ok' ? 'Online' : 'Masalah'"></span>
                </div>
            </div>
        </div>
        <div class="px-6 py-5 space-y-5">
            {{-- CPU --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span style="font-size:12px; color:#64748D;">CPU Load</span>
                    <span style="font-size:12px; font-weight:500; color:#061B31;" x-text="cpu + '%'"></span>
                </div>
                <div style="height:4px; background:#E5EDF5; border-radius:2px; overflow:hidden;">
                    <div :style="'width:' + cpu + '%; background:' + (cpu > 80 ? '#EF4444' : cpu > 60 ? '#F59E0B' : '#533AFD') + '; height:100%; border-radius:2px; transition:width 500ms ease;'"></div>
                </div>
            </div>
            {{-- Memory --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span style="font-size:12px; color:#64748D;">Memori</span>
                    <span style="font-size:12px; font-weight:500; color:#061B31;" x-text="memory + '%'"></span>
                </div>
                <div style="height:4px; background:#E5EDF5; border-radius:2px; overflow:hidden;">
                    <div :style="'width:' + memory + '%; background:' + (memory > 80 ? '#EF4444' : memory > 60 ? '#F59E0B' : '#10B981') + '; height:100%; border-radius:2px; transition:width 500ms ease;'"></div>
                </div>
            </div>
            {{-- Response time --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <span style="font-size:12px; color:#64748D;">Response Time</span>
                    <span style="font-size:12px; font-weight:500; color:#061B31;" x-text="responseTime + ' ms'"></span>
                </div>
                <div style="height:4px; background:#E5EDF5; border-radius:2px; overflow:hidden;">
                    <div :style="'width:' + Math.min(responseTime / 5, 100) + '%; background:' + (responseTime > 300 ? '#EF4444' : responseTime > 150 ? '#F59E0B' : '#10B981') + '; height:100%; border-radius:2px; transition:width 500ms ease;'"></div>
                </div>
            </div>

            <div class="pt-2" style="border-top:1px solid #E5EDF5;">
                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <p style="font-size:11px; color:#B8CCDB; margin-bottom:2px;">Versi PHP</p>
                        <p style="font-size:12px; font-weight:500; color:#061B31;" x-text="phpVersion"></p>
                    </div>
                    <div>
                        <p style="font-size:11px; color:#B8CCDB; margin-bottom:2px;">Update</p>
                        <p style="font-size:12px; font-weight:500; color:#061B31;" x-text="lastUpdated"></p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Recent transactions --}}
<div class="mt-8">
    <div class="ss-card" style="padding:0; overflow:hidden;">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #E5EDF5;">
            <h4 style="font-size:14px; font-weight:500; color:#061B31;">Transaksi Terbaru</h4>
            <a href="{{ route('transactions.index') }}" class="btn-ghost" style="padding:6px 12px; font-size:13px;">
                Lihat semua
                <svg class="w-3.5 h-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                </svg>
            </a>
        </div>
        <div style="overflow-x:auto;">
            <table class="ss-table">
                <thead>
                    <tr>
                        <th>No. Referensi</th>
                        <th>Produk</th>
                        <th>Gudang</th>
                        <th>Tipe</th>
                        <th class="text-right">Kuantitas</th>
                        <th>Waktu</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentTransactions as $tx)
                    <tr>
                        <td>
                            <a href="{{ route('transactions.show', $tx) }}"
                               style="font-size:13px; font-weight:500; color:#533AFD; text-decoration:none; font-family:monospace;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $tx->reference_number }}
                            </a>
                        </td>
                        <td style="font-size:13px; color:#061B31;">{{ $tx->product->name ?? '—' }}</td>
                        <td style="font-size:13px; color:#64748D;">{{ $tx->warehouse->name ?? '—' }}</td>
                        <td>
                            @php
                            $badgeClass = match($tx->type) {
                                'Masuk'    => 'badge-success',
                                'Keluar'   => 'badge-alert',
                                'Transfer' => 'badge-info',
                                default    => 'badge-neutral',
                            };
                            @endphp
                            <span class="{{ $badgeClass }}">{{ $tx->type }}</span>
                        </td>
                        <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">
                            {{ $tx->type === 'Keluar' ? '-' : '+' }}{{ number_format($tx->quantity) }}
                        </td>
                        <td style="font-size:12px; color:#64748D;">{{ $tx->created_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-8" style="font-size:13px; color:#64748D;">Belum ada transaksi</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script type="application/json" id="dashboard-data">@json($dashboardData)</script>
<script>
(function () {
    const pd = JSON.parse(document.getElementById('dashboard-data').textContent);

    // Transaction trend chart
    const trendCtx = document.getElementById('trendChart');
    if (trendCtx) {
        new Chart(trendCtx, {
            type: 'line',
            data: {
                labels: pd.trendLabels,
                datasets: [
                    {
                        label: 'Masuk',
                        data: pd.trendMasuk,
                        borderColor: '#10B981',
                        backgroundColor: 'rgba(16,185,129,0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#10B981',
                    },
                    {
                        label: 'Keluar',
                        data: pd.trendKeluar,
                        borderColor: '#EF4444',
                        backgroundColor: 'rgba(239,68,68,0.08)',
                        borderWidth: 2,
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                        pointBackgroundColor: '#EF4444',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        position: 'bottom',
                        labels: { font: { family: 'Inter', size: 11 }, color: '#64748D', boxWidth: 10, boxHeight: 10, padding: 16 }
                    },
                    tooltip: { mode: 'index', intersect: false }
                },
                scales: {
                    x: {
                        grid: { color: '#E5EDF5', drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#64748D' }
                    },
                    y: {
                        beginAtZero: true,
                        grid: { color: '#E5EDF5', drawBorder: false },
                        ticks: { font: { family: 'Inter', size: 11 }, color: '#64748D', precision: 0 }
                    }
                }
            }
        });
    }

    // Warehouse donut chart
    const warehouseCtx = document.getElementById('warehouseChart');
    if (warehouseCtx) {
        const wsData = pd.warehouseStocks;
        new Chart(warehouseCtx, {
            type: 'doughnut',
            data: {
                labels: wsData.map(function(w) { return w.name; }),
                datasets: [{
                    data: wsData.map(function(w) { return w.total_stock || 0; }),
                    backgroundColor: ['#533AFD','#10B981','#F59E0B','#EF4444','#8B5CF6'],
                    borderWidth: 0,
                    hoverOffset: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '72%',
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: {
                        label: function(ctx) { return ' ' + ctx.label + ': ' + new Intl.NumberFormat('id-ID').format(ctx.raw); }
                    }}
                }
            }
        });
    }

    // Leaflet map (via SmartStockMaps helper)
    SmartStockMaps.ready(function () {
        var mapEl = document.getElementById('warehouseMap');
        if (!mapEl) return;

        var map = SmartStockMaps.createMap('warehouseMap', { scrollWheelZoom: false });
        if (!map) return;

        var mapDataArr = pd.mapData || [];
        var bounds = [];
        var icon = SmartStockMaps.warehouseIcon();

        mapDataArr.forEach(function (w) {
            var lat = parseFloat(w.lat);
            var lng = parseFloat(w.lng);
            if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

            var marker = L.marker([lat, lng], { icon: icon }).addTo(map);
            marker.bindPopup(
                '<div style="font-family:Inter,sans-serif;padding:4px;">' +
                '<strong style="font-size:13px;color:#061B31;">' + w.name + '</strong>' +
                '<p style="font-size:12px;color:#64748D;margin:4px 0 0;">' + (w.city || '') + '</p></div>'
            );
            bounds.push([lat, lng]);
        });

        SmartStockMaps.fitView(map, bounds);
    });
})();

// Server monitor Alpine component
function serverMonitor() {
    return {
        cpu: 0, memory: 0, responseTime: 0,
        phpVersion: '\u2014', lastUpdated: '\u2014',
        status: 'ok',
        init: function() {
            this.fetch();
            setInterval(this.fetch.bind(this), 8000);
        },
        fetch: async function() {
            try {
                var r = await window.fetch('/api/server-resources', { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
                var d = await r.json();
                this.cpu          = d.cpu || 0;
                this.memory       = d.memory || 0;
                this.responseTime = d.response_time || 0;
                this.phpVersion   = d.php_version || '\u2014';
                this.lastUpdated  = new Date().toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
                this.status       = 'ok';
            } catch (_) {
                this.status = 'error';
            }
        }
    };
}
</script>
@endpush
