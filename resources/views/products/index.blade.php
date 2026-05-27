@extends('layouts.app')

@section('title', 'Produk')
@section('page-title', 'Produk')

@section('content')

<x-page-header title="Manajemen Produk" description="Kelola seluruh SKU, harga, dan stok gudang.">
    <x-slot name="actions">
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <button type="button" onclick="window.dispatchEvent(new CustomEvent('open-modal', { detail: 'import-csv' }))"
            class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
            Import CSV
        </button>
        <a href="{{ route('products.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
            </svg>
            Tambah Produk
        </a>
        @endif
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="ss-card mb-6" style="padding:16px 20px;">
    <form method="GET" action="{{ route('products.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1" style="min-width:200px;">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:#B8CCDB;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0"/>
                </svg>
                <input type="text" name="search" value="{{ request('search') }}"
                    placeholder="Cari nama atau SKU produk..."
                    class="ss-input" style="padding-left:36px;">
            </div>
        </div>
        <div style="min-width:160px;">
            <select name="category" class="ss-select">
                <option value="">Semua Kategori</option>
                @foreach($categories as $cat)
                <option value="{{ $cat->id }}" {{ request('category') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div style="min-width:140px;">
            <select name="stock_status" class="ss-select">
                <option value="">Semua Stok</option>
                <option value="low" {{ request('stock_status') === 'low' ? 'selected' : '' }}>Stok Rendah</option>
                <option value="critical" {{ request('stock_status') === 'critical' ? 'selected' : '' }}>Stok Kritis</option>
                <option value="normal" {{ request('stock_status') === 'normal' ? 'selected' : '' }}>Normal</option>
            </select>
        </div>
        <button type="submit" class="btn-primary" style="min-height:40px;">Terapkan</button>
        @if(request()->hasAny(['search','category','stock_status','sort','direction']))
        <a href="{{ route('products.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>
</div>

{{-- Table --}}
<div class="ss-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>
                        <a href="{{ route('products.index', array_merge(request()->query(), ['sort' => 'name', 'direction' => request('sort') === 'name' && request('direction') === 'asc' ? 'desc' : 'asc'])) }}"
                           style="color:#64748D; text-decoration:none; display:flex; align-items:center; gap:4px;">
                            Produk
                            <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M7 16V4m0 0L3 8m4-4l4 4M17 8v12m0 0l4-4m-4 4l-4-4"/></svg>
                        </a>
                    </th>
                    <th>Kategori</th>
                    <th>Supplier</th>
                    <th class="text-right">Harga</th>
                    <th class="text-right">Total Stok</th>
                    <th>Status</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($products as $product)
                <tr>
                    <td>
                        <div class="flex items-center gap-3">
                            @if($product->image_url)
                            <img src="{{ $product->image_url }}" alt="{{ $product->name }}"
                                 style="width:36px; height:36px; object-fit:cover; border-radius:4px; border:1px solid #E5EDF5; flex-shrink:0;">
                            @else
                            <div class="flex items-center justify-center flex-shrink-0"
                                 style="width:36px; height:36px; border-radius:4px; background:#E8E9FF;">
                                <svg class="w-4 h-4" style="color:#533AFD;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                                </svg>
                            </div>
                            @endif
                            <div>
                                <a href="{{ route('products.show', $product) }}"
                                   style="font-size:13px; font-weight:500; color:#061B31; text-decoration:none;"
                                   onmouseover="this.style.color='#533AFD';"
                                   onmouseout="this.style.color='#061B31';">
                                    {{ $product->name }}
                                </a>
                                <p style="font-size:11px; color:#B8CCDB; margin-top:1px; font-family:monospace;">{{ $product->sku }}</p>
                            </div>
                        </div>
                    </td>
                    <td>
                        @if($product->category)
                        <div class="flex items-center gap-1.5">
                            <div class="w-2 h-2 rounded-full flex-shrink-0"
                                 :style="`background: {{ $product->category->color ?? '#D4DEE9' }}`"></div>
                            <span style="font-size:13px; color:#64748D;">{{ $product->category->name }}</span>
                        </div>
                        @else
                        <span style="font-size:13px; color:#B8CCDB;">—</span>
                        @endif
                    </td>
                    <td style="font-size:13px; color:#64748D;">{{ $product->supplier->name ?? '—' }}</td>
                    <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ $product->formatted_price }}</td>
                    <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($product->total_stock) }}</td>
                    <td>
                        @if($product->is_critical_stock)
                        <span class="badge-alert">Kritis</span>
                        @elseif($product->is_low_stock)
                        <span class="badge-warning">Rendah</span>
                        @else
                        <span class="badge-success">Normal</span>
                        @endif
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1">
                            <a href="{{ route('products.show', $product) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Lihat</a>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
                            <a href="{{ route('products.edit', $product) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Edit</a>
                            <form method="POST" action="{{ route('products.destroy', $product) }}"
                                x-data="{ confirm() { return window.confirm('Hapus produk ini?'); } }"
                                @submit.prevent="if(confirm()) $el.submit()">
                                @csrf @method('DELETE')
                                <button type="submit" class="btn-ghost" style="padding:6px 10px; font-size:12px; color:#EF4444;">Hapus</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7">
                        <div class="py-16 text-center">
                            <svg class="w-10 h-10 mx-auto mb-3" style="color:#D4DEE9;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 10V7"/>
                            </svg>
                            <p style="font-size:14px; color:#64748D; margin-bottom:12px;">Tidak ada produk ditemukan</p>
                            @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
                            <a href="{{ route('products.create') }}" class="btn-primary" style="display:inline-flex;">Tambah Produk Pertama</a>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($products->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">
        {{ $products->withQueryString()->links() }}
    </div>
    @endif
</div>

{{-- Import CSV modal --}}
<x-modal name="import-csv" title="Import Produk dari CSV">
    <form method="POST" action="{{ route('products.import') }}" enctype="multipart/form-data">
        @csrf
        <div class="space-y-4">
            <p style="font-size:14px; color:#64748D; line-height:1.6;">
                Unggah file CSV dengan kolom: <code style="background:#F8FAFC; padding:2px 6px; border-radius:3px; font-size:12px; color:#533AFD;">name, sku, price, minimum_threshold, category_id, supplier_id, quantity</code>
            </p>
            <div>
                <label class="ss-label">File CSV <span style="color:#EF4444;">*</span></label>
                <input type="file" name="csv_file" accept=".csv,.xlsx"
                    class="ss-input" style="height:auto; padding:8px 12px; cursor:pointer;">
                @error('csv_file')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>
        </div>
        <div class="flex items-center justify-end gap-3 mt-6">
            <button type="button" onclick="window.dispatchEvent(new CustomEvent('close-modal', { detail: 'import-csv' }))" class="btn-secondary">Batal</button>
            <button type="submit" class="btn-primary">Mulai Import</button>
        </div>
    </form>
</x-modal>

@endsection
