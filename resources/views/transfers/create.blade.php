@extends('layouts.app')
@section('title', 'Transfer Gudang')
@section('page-title', 'Transfer Gudang')
@section('breadcrumb', 'Buat Transfer')

@section('content')
<x-page-header title="Transfer Stok Antar Gudang" description="Pindahkan stok secara atomik antara dua lokasi gudang.">
    <x-slot name="actions">
        <a href="{{ route('transfers.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl" x-data="transferForm()">
    <form method="POST" action="{{ route('transfers.store') }}">
        @csrf
        <div class="ss-card space-y-5">

            {{-- Product --}}
            <div>
                <label class="ss-label">Produk yang Ditransfer <span style="color:#EF4444;">*</span></label>
                <select name="product_id" class="ss-select @error('product_id') error @enderror"
                    @change="productId = $event.target.value; fetchStock()">
                    <option value="">Pilih produk...</option>
                    @foreach($products as $p)
                    <option value="{{ $p->id }}" {{ old('product_id') == $p->id ? 'selected' : '' }}>
                        {{ $p->name }}
                    </option>
                    @endforeach
                </select>
                @error('product_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Warehouse pair --}}
            <div class="grid grid-cols-2 gap-4">
                <div>
                    <label class="ss-label">Gudang Asal <span style="color:#EF4444;">*</span></label>
                    <select name="source_warehouse_id" class="ss-select @error('source_warehouse_id') error @enderror"
                        @change="sourceId = $event.target.value; fetchStock()">
                        <option value="">Pilih gudang asal...</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('source_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('source_warehouse_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
                    <p x-show="sourceStock !== null" style="font-size:12px; color:#64748D; margin-top:4px;">
                        Stok tersedia: <strong x-text="sourceStock + ' unit'" style="color:#061B31;"></strong>
                    </p>
                </div>
                <div>
                    <label class="ss-label">Gudang Tujuan <span style="color:#EF4444;">*</span></label>
                    <select name="destination_warehouse_id" class="ss-select @error('destination_warehouse_id') error @enderror">
                        <option value="">Pilih gudang tujuan...</option>
                        @foreach($warehouses as $wh)
                        <option value="{{ $wh->id }}" {{ old('destination_warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                        @endforeach
                    </select>
                    @error('destination_warehouse_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
                </div>
            </div>

            {{-- Quantity --}}
            <div>
                <label class="ss-label">Jumlah Transfer <span style="color:#EF4444;">*</span></label>
                <input type="number" name="quantity" value="{{ old('quantity', 1) }}"
                    min="1" :max="sourceStock ?? 9999"
                    class="ss-input @error('quantity') error @enderror">
                @error('quantity')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            {{-- Notes --}}
            <div>
                <label class="ss-label">Keterangan</label>
                <textarea name="notes" rows="2" class="ss-textarea"
                    placeholder="Alasan transfer atau catatan lainnya">{{ old('notes') }}</textarea>
            </div>

            {{-- Warning --}}
            <div class="alert-warning" style="font-size:13px;">
                <svg class="w-5 h-5 flex-shrink-0" style="color:#F59E0B;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <span>Transfer menggunakan transaksi database atomik. Pastikan gudang asal dan tujuan berbeda.</span>
            </div>

            <div class="flex items-center gap-3 pt-2" style="border-top:1px solid #E5EDF5;">
                <button type="submit" class="btn-primary">Proses Transfer</button>
                <a href="{{ route('transfers.index') }}" class="btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>
function transferForm() {
    return {
        productId: null,
        sourceId: null,
        sourceStock: null,
        async fetchStock() {
            if (!this.productId || !this.sourceId) { this.sourceStock = null; return; }
            try {
                const r = await fetch(`/api/stock?product_id=${this.productId}&warehouse_id=${this.sourceId}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                });
                const d = await r.json();
                this.sourceStock = d.quantity ?? 0;
            } catch (_) { this.sourceStock = null; }
        }
    };
}
</script>
@endpush
