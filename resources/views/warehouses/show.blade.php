@extends('layouts.app')
@section('title', $warehouse->name)
@section('page-title', 'Gudang')
@section('breadcrumb', $warehouse->name)

@section('content')
<x-page-header :title="$warehouse->name" :description="$warehouse->city . ($warehouse->province ? ', ' . $warehouse->province : '')">
    <x-slot name="actions">
        <a href="{{ route('warehouses.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        @if(auth()->user()->isAdmin())
        <a href="{{ route('warehouses.edit', $warehouse) }}" class="btn-primary">Edit Gudang</a>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2 space-y-5">

        {{-- Info --}}
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Informasi Gudang</h4>
            </div>
            <div class="grid grid-cols-2 gap-0">
                @foreach([
                    ['label' => 'Nama Gudang',  'value' => $warehouse->name],
                    ['label' => 'Kode',         'value' => $warehouse->code ?? '—'],
                    ['label' => 'Kota',         'value' => $warehouse->city ?? '—'],
                    ['label' => 'Provinsi',     'value' => $warehouse->province ?? '—'],
                    ['label' => 'Manajer',      'value' => $warehouse->manager_name ?? '—'],
                    ['label' => 'Telepon',      'value' => $warehouse->phone ?? '—'],
                ] as $f)
                <div class="px-6 py-4" style="border-bottom:1px solid #F1F5F9; border-right:1px solid #F1F5F9;">
                    <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">{{ $f['label'] }}</p>
                    <p style="font-size:14px; color:#061B31;">{{ $f['value'] }}</p>
                </div>
                @endforeach
            </div>
            @if($warehouse->address)
            <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">
                <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">Alamat Lengkap</p>
                <p style="font-size:14px; color:#64748D; line-height:1.6;">{{ $warehouse->address }}</p>
            </div>
            @endif
        </div>

        {{-- Stock list --}}
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Stok Produk di Gudang Ini</h4>
            </div>
            <table class="ss-table">
                <thead>
                    <tr>
                        <th>Produk</th>
                        <th>Kategori</th>
                        <th class="text-right">Stok</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($stocks as $s)
                    <tr>
                        <td>
                            <a href="{{ route('products.show', $s->product) }}"
                               style="font-size:13px; font-weight:500; color:#533AFD; text-decoration:none;">
                                {{ $s->product->name }}
                            </a>
                        </td>
                        <td style="font-size:13px; color:#64748D;">{{ $s->product->category->name ?? '—' }}</td>
                        <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($s->quantity) }}</td>
                        <td>
                            @if($s->quantity <= 0)
                            <span class="badge-alert">Habis</span>
                            @elseif($s->is_low)
                            <span class="badge-warning">Rendah</span>
                            @else
                            <span class="badge-success">Normal</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-8" style="font-size:13px; color:#64748D;">Belum ada stok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right: map + stats --}}
    <div class="space-y-5">
        @if($warehouse->lat && $warehouse->lng)
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-5 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:13px; font-weight:500; color:#061B31;">Lokasi</h4>
            </div>
            <div class="leaflet-map-host" style="height:220px;">
                <div id="miniMap"
                     data-lat="{{ $warehouse->lat }}"
                     data-lng="{{ $warehouse->lng }}"
                     data-name="{{ $warehouse->name }}"
                     data-city="{{ $warehouse->city ?? '' }}"
                     style="width:100%;height:100%;min-height:220px;"></div>
            </div>
            <div class="px-5 py-3">
                <p style="font-size:12px; color:#64748D; font-family:monospace;">{{ $warehouse->lat }}, {{ $warehouse->lng }}</p>
            </div>
        </div>
        @endif

        <div class="ss-card" style="padding:20px;">
            <h4 style="font-size:13px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:12px;">Ringkasan</h4>
            <div class="space-y-3">
                <div class="flex justify-between">
                    <span style="font-size:13px; color:#64748D;">Total Stok</span>
                    <span style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($warehouse->total_stock) }}</span>
                </div>
                <div class="flex justify-between">
                    <span style="font-size:13px; color:#64748D;">Jumlah SKU</span>
                    <span style="font-size:13px; font-weight:500; color:#061B31;">{{ $stocks->count() }}</span>
                </div>
                <div class="flex justify-between">
                    <span style="font-size:13px; color:#64748D;">Status</span>
                    @if($warehouse->is_active)
                    <span class="badge-success">Aktif</span>
                    @else
                    <span class="badge-neutral">Non-aktif</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
SmartStockMaps.ready(function () {
    var mapEl = document.getElementById('miniMap');
    if (!mapEl) return;

    var lat = parseFloat(mapEl.dataset.lat);
    var lng = parseFloat(mapEl.dataset.lng);
    if (!Number.isFinite(lat) || !Number.isFinite(lng)) return;

    var map = SmartStockMaps.createMap('miniMap', {
        zoomControl: true,
        scrollWheelZoom: false,
        dragging: true,
    });
    if (!map) return;

    var name = mapEl.dataset.name || '';
    var city = mapEl.dataset.city || '';
    L.marker([lat, lng], { icon: SmartStockMaps.warehouseIcon() })
        .addTo(map)
        .bindPopup('<strong style="font-size:13px;color:#061B31;">' + name + '</strong><p style="font-size:12px;color:#64748D;margin:4px 0 0;">' + city + '</p>');

    map.setView([lat, lng], 13);
});
</script>
@endpush
