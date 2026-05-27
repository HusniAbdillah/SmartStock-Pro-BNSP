@extends('layouts.app')
@section('title', 'Catat Transaksi')
@section('page-title', 'Transaksi')
@section('breadcrumb', 'Catat Baru')

@section('content')
<x-page-header title="Catat Transaksi Baru" description="Rekam pergerakan barang masuk atau keluar gudang.">
    <x-slot name="actions">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl" x-data="transactionForm()" data-type="{{ old('type', request('type', 'Masuk')) }}">
    <form method="POST" action="{{ route('transactions.store') }}">
        @csrf
        <div class="ss-card space-y-5">

            {{-- Type selector --}}
            <div>
                <label class="ss-label">Tipe Transaksi <span style="color:#EF4444;">*</span></label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="Masuk" x-model="type" class="sr-only"
                            {{ old('type', request('type', 'Masuk')) === 'Masuk' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5 px-4 py-3 rounded"
                             :style="type === 'Masuk' ? 'background:#D1FAE5; border:2px solid #10B981;' : 'background:#F8FAFC; border:2px solid #E5EDF5;'"
                             style="border-radius:4px; transition:all 120ms ease;">
                            <svg class="w-5 h-5" :style="type === 'Masuk' ? 'color:#065F46;' : 'color:#B8CCDB;'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/>
                            </svg>
                            <span :style="type === 'Masuk' ? 'color:#065F46; font-weight:500;' : 'color:#64748D;'" style="font-size:14px;">Barang Masuk</span>
                        </div>
                    </label>
                    <label class="cursor-pointer">
                        <input type="radio" name="type" value="Keluar" x-model="type" class="sr-only"
                            {{ old('type', request('type')) === 'Keluar' ? 'checked' : '' }}>
                        <div class="flex items-center gap-2.5 px-4 py-3 rounded"
                             :style="type === 'Keluar' ? 'background:#FEE2E2; border:2px solid #EF4444;' : 'background:#F8FAFC; border:2px solid #E5EDF5;'"
                             style="border-radius:4px; transition:all 120ms ease;">
                            <svg class="w-5 h-5" :style="type === 'Keluar' ? 'color:#991B1B;' : 'color:#B8CCDB;'" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M20 12H4"/>
                            </svg>
                            <span :style="type === 'Keluar' ? 'color:#991B1B; font-weight:500;' : 'color:#64748D;'" style="font-size:14px;">Barang Keluar</span>
                        </div>
                    </label>
                </div>
                @error('type')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Product --}}
            <div>
                <label class="ss-label">Produk <span style="color:#EF4444;">*</span></label>
                <select name="product_id" class="ss-select @error('product_id') error @enderror"
                    @change="updateStock($event.target.value)">
                    <option value="">Pilih produk...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id', request('product_id')) == $p->id ? 'selected' : '' }}>
                        {{ $p->name }} (Stok: {{ $p->total_stock }})
                    </option>
                    @endforeach
                </select>
                @error('product_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Warehouse --}}
            <div>
                <label class="ss-label">Gudang <span style="color:#EF4444;">*</span></label>
                <select name="warehouse_id" class="ss-select @error('warehouse_id') error @enderror">
                    <option value="">Pilih gudang...</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                    @endforeach
                </select>
                @error('warehouse_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Quantity & price --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">Kuantitas <span style="color:#EF4444;">*</span></label>
                    <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                        min="1" class="ss-input @error('quantity') error @enderror">
                    @error('quantity')<p class="ss-error-msg">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="ss-label">Harga Satuan (Rp)</label>
                    <input type="number" name="unit_price" value="{{ old('unit_price') }}"
                        min="0" step="100" placeholder="Opsional" class="ss-input">
                </div>
            </div>

            {{-- Supplier (masuk only) --}}
            <div x-show="type === 'Masuk'">
                <label class="ss-label">Supplier</label>
                <select name="supplier_id" class="ss-select">
                    <option value="">Tanpa supplier</option>
                    @foreach($suppliers as $sup)
                    <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Notes --}}
            <div>
                <label class="ss-label">Keterangan</label>
                <textarea name="notes" rows="2" class="ss-textarea"
                    placeholder="Catatan tambahan (opsional)">{{ old('notes') }}</textarea>
            </div>

            <div class="flex items-center gap-3 pt-2" style="border-top:1px solid #E5EDF5;">
                <button type="submit" class="btn-primary">Simpan Transaksi</button>
                <a href="{{ route('transactions.index') }}" class="btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function transactionForm() {
    var el = document.querySelector('[data-type]');
    return {
        type: el ? el.dataset.type : 'Masuk',
        updateStock: function(productId) {
            // Could fetch live stock data via AJAX if needed
        }
    };
}
</script>
@endpush
