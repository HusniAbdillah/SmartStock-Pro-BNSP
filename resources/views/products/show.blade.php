@extends('layouts.app')

@section('title', $product->name)
@section('page-title', 'Produk')
@section('breadcrumb', $product->name)

@section('content')

<x-page-header :title="$product->name" :description="'SKU: ' . $product->sku">
    <x-slot name="actions">
        <a href="{{ route('products.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <a href="{{ route('products.edit', $product) }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Edit Produk
        </a>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

    {{-- Left: main info --}}
    <div class="lg:col-span-2 space-y-5">

        {{-- Details card --}}
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Detail Produk</h4>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-0">
                @foreach([
                    ['label' => 'Nama Produk',   'value' => $product->name],
                    ['label' => 'SKU',            'value' => $product->sku, 'mono' => true],
                    ['label' => 'Harga',          'value' => $product->formatted_price],
                    ['label' => 'Kategori',       'value' => $product->category->name ?? '—'],
                    ['label' => 'Supplier',       'value' => $product->supplier->name ?? '—'],
                    ['label' => 'Min. Threshold', 'value' => number_format($product->minimum_threshold) . ' unit'],
                ] as $field)
                <div class="px-6 py-4" style="border-bottom:1px solid #F1F5F9; border-right:1px solid #F1F5F9;">
                    <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">{{ $field['label'] }}</p>
                    <p style="font-size:14px; color:#061B31;" class="{{ ($field['mono'] ?? false) ? 'font-mono' : '' }}">{{ $field['value'] }}</p>
                </div>
                @endforeach
            </div>
            @if($product->description)
            <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">
                <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Deskripsi</p>
                <p style="font-size:14px; color:#64748D; line-height:1.6;">{{ $product->description }}</p>
            </div>
            @endif
        </div>

        {{-- Stock by warehouse --}}
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Stok per Gudang</h4>
            </div>
            <table class="ss-table">
                <thead>
                    <tr>
                        <th>Gudang</th>
                        <th class="text-right">Stok</th>
                        <th>Status</th>
                        <th>Terakhir Diperbarui</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($product->warehouseStocks->sortByDesc('quantity') as $stock)
                    <tr>
                        <td>
                            <a href="{{ route('warehouses.show', $stock->warehouse) }}"
                               style="font-size:13px; font-weight:500; color:#533AFD; text-decoration:none;"
                               onmouseover="this.style.textDecoration='underline';"
                               onmouseout="this.style.textDecoration='none';">
                                {{ $stock->warehouse->name }}
                            </a>
                            <p style="font-size:11px; color:#B8CCDB; margin-top:1px;">{{ $stock->warehouse->city ?? '' }}</p>
                        </td>
                        <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($stock->quantity) }}</td>
                        <td>
                            @if($stock->quantity <= 0)
                            <span class="badge-alert">Habis</span>
                            @elseif($stock->is_low)
                            <span class="badge-warning">Rendah</span>
                            @else
                            <span class="badge-success">Normal</span>
                            @endif
                        </td>
                        <td style="font-size:12px; color:#64748D;">{{ $stock->updated_at->diffForHumans() }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="4" class="text-center py-6" style="font-size:13px; color:#64748D;">Belum ada data stok</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent transactions --}}
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Riwayat Transaksi</h4>
            </div>
            <table class="ss-table">
                <thead>
                    <tr>
                        <th>Referensi</th>
                        <th>Tipe</th>
                        <th>Gudang</th>
                        <th class="text-right">Kuantitas</th>
                        <th>Tanggal</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $tx)
                    <tr>
                        <td>
                            <a href="{{ route('transactions.show', $tx) }}"
                               style="font-size:12px; font-weight:500; color:#533AFD; text-decoration:none; font-family:monospace;">
                                {{ $tx->reference_number }}
                            </a>
                        </td>
                        <td>
                            <span class="{{ match($tx->type) {
                                'Masuk'    => 'badge-success',
                                'Keluar'   => 'badge-alert',
                                'Transfer' => 'badge-info',
                                default    => 'badge-neutral',
                            } }}">{{ $tx->type }}</span>
                        </td>
                        <td style="font-size:13px; color:#64748D;">{{ $tx->warehouse->name ?? '—' }}</td>
                        <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($tx->quantity) }}</td>
                        <td style="font-size:12px; color:#64748D;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                    </tr>
                    @empty
                    <tr><td colspan="5" class="text-center py-6" style="font-size:13px; color:#64748D;">Belum ada transaksi</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Right: image + status --}}
    <div class="space-y-5">
        @if($product->image_url)
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                 style="width:100%; height:200px; object-fit:cover;">
        </div>
        @endif

        <div class="ss-card" style="padding:20px;">
            <h4 style="font-size:13px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:12px;">Status Produk</h4>
            <div class="space-y-3">
                <div class="flex items-center justify-between">
                    <span style="font-size:13px; color:#64748D;">Ketersediaan</span>
                    @if($product->is_active)
                    <span class="badge-success">Aktif</span>
                    @else
                    <span class="badge-neutral">Non-aktif</span>
                    @endif
                </div>
                <div class="flex items-center justify-between">
                    <span style="font-size:13px; color:#64748D;">Kondisi Stok</span>
                    @if($product->is_critical_stock)
                    <span class="badge-alert">Kritis</span>
                    @elseif($product->is_low_stock)
                    <span class="badge-warning">Rendah</span>
                    @else
                    <span class="badge-success">Normal</span>
                    @endif
                </div>
                <div class="pt-3" style="border-top:1px solid #E5EDF5;">
                    <p style="font-size:12px; color:#64748D; margin-bottom:4px;">Total stok semua gudang</p>
                    <p style="font-size:24px; font-weight:300; color:#061B31;">{{ number_format($product->total_stock) }} <span style="font-size:13px;">unit</span></p>
                </div>
            </div>
        </div>

        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <div class="space-y-2">
            <a href="{{ route('transactions.create', ['product_id' => $product->id, 'type' => 'Masuk']) }}" class="btn-primary w-full justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                Catat Barang Masuk
            </a>
            <a href="{{ route('transactions.create', ['product_id' => $product->id, 'type' => 'Keluar']) }}" class="btn-secondary w-full justify-center">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/></svg>
                Catat Barang Keluar
            </a>
        </div>
        @endif
    </div>
</div>

@endsection
