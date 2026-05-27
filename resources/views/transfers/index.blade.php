@extends('layouts.app')
@section('title', 'Transfer Gudang')
@section('page-title', 'Transfer Gudang')

@section('content')
<x-page-header title="Transfer Antar Gudang" description="Rekam perpindahan stok antara lokasi gudang.">
    <x-slot name="actions">
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <a href="{{ route('transfers.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
            </svg>
            Buat Transfer
        </a>
        @endif
    </x-slot>
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Referensi</th>
                    <th>Produk</th>
                    <th>Dari Gudang</th>
                    <th>Ke Gudang</th>
                    <th class="text-right">Kuantitas</th>
                    <th>Operator</th>
                    <th>Tanggal</th>
                </tr>
            </thead>
            <tbody>
                @forelse($transfers as $tx)
                <tr>
                    <td>
                        <span style="font-size:12px; font-weight:500; color:#533AFD; font-family:monospace;">
                            {{ $tx->reference_number }}
                        </span>
                    </td>
                    <td>
                        <a href="{{ route('products.show', $tx->product) }}"
                           style="font-size:13px; font-weight:500; color:#061B31; text-decoration:none;"
                           onmouseover="this.style.color='#533AFD';"
                           onmouseout="this.style.color='#061B31';">
                            {{ $tx->product->name ?? '—' }}
                        </a>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" style="color:#EF4444;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                            <span style="font-size:13px; color:#64748D;">{{ $tx->sourceWarehouse->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5" style="color:#10B981;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                            </svg>
                            <span style="font-size:13px; color:#64748D;">{{ $tx->destinationWarehouse->name ?? '—' }}</span>
                        </div>
                    </td>
                    <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($tx->quantity) }}</td>
                    <td style="font-size:12px; color:#64748D;">{{ $tx->operator->name ?? '—' }}</td>
                    <td style="font-size:12px; color:#64748D; white-space:nowrap;">{{ $tx->created_at->format('d M Y H:i') }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada transfer</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($transfers->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $transfers->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
