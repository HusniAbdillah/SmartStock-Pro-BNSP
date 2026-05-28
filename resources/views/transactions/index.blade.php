@extends('layouts.app')
@section('title', 'Transaksi')
@section('page-title', 'Transaksi')

@section('content')
<x-page-header title="Riwayat Transaksi" description="Semua aktivitas barang masuk, keluar, dan transfer.">
    <x-slot name="actions">
        @if(auth()->user()->canModify())
        <a href="{{ route('transactions.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Catat Transaksi
        </a>
        @endif
    </x-slot>
</x-page-header>

{{-- Filters --}}
<div class="ss-card mb-6" style="padding:16px 20px;">
    <form method="GET" action="{{ route('transactions.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1" style="min-width:200px;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari no. referensi atau produk..."
                class="ss-input">
        </div>
        <select name="type" class="ss-select" style="min-width:140px;">
            <option value="">Semua Tipe</option>
            <option value="Masuk"    {{ request('type') === 'Masuk'    ? 'selected' : '' }}>Barang Masuk</option>
            <option value="Keluar"   {{ request('type') === 'Keluar'   ? 'selected' : '' }}>Barang Keluar</option>
            <option value="Transfer" {{ request('type') === 'Transfer' ? 'selected' : '' }}>Transfer</option>
        </select>
        <select name="warehouse_id" class="ss-select" style="min-width:160px;">
            <option value="">Semua Gudang</option>
            @foreach($warehouses as $wh)
            <option value="{{ $wh->id }}" {{ request('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
            @endforeach
        </select>
        <button type="submit" class="btn-primary">Terapkan</button>
        @if(request()->hasAny(['search','type','warehouse_id','date_from','date_to']))
        <a href="{{ route('transactions.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>
</div>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Referensi</th>
                    <th>Tipe</th>
                    <th>Produk</th>
                    <th>Gudang</th>
                    <th class="text-right">Kuantitas</th>
                    <th>Operator</th>
                    <th>Tanggal</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($transactions as $tx)
                <tr>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}"
                           style="font-size:12px; font-weight:500; color:#533AFD; text-decoration:none; font-family:monospace;"
                           onmouseover="this.style.textDecoration='underline';"
                           onmouseout="this.style.textDecoration='none';">
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
                    <td>
                        <a href="{{ route('products.show', $tx->product) }}"
                           style="font-size:13px; font-weight:500; color:#061B31; text-decoration:none;"
                           onmouseover="this.style.color='#533AFD';"
                           onmouseout="this.style.color='#061B31';">
                            {{ $tx->product->name ?? '—' }}
                        </a>
                    </td>
                    <td style="font-size:13px; color:#64748D;">{{ $tx->warehouse->name ?? '—' }}</td>
                    <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($tx->quantity) }}</td>
                    <td style="font-size:12px; color:#64748D;">{{ $tx->operator->name ?? '—' }}</td>
                    <td style="font-size:12px; color:#64748D; white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                    <td>
                        <a href="{{ route('transactions.show', $tx) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Lihat</a>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada transaksi</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transactions->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">
        {{ $transactions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
