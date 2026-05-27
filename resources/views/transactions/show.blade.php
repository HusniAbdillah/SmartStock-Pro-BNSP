@extends('layouts.app')
@section('title', 'Detail Transaksi')
@section('page-title', 'Transaksi')
@section('breadcrumb', $transaction->reference_number)

@section('content')
<x-page-header :title="'Transaksi ' . $transaction->reference_number">
    <x-slot name="actions">
        <a href="{{ route('transactions.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <div class="ss-card" style="padding:0; overflow:hidden;">
        <div class="px-6 py-4 flex items-center justify-between" style="border-bottom:1px solid #E5EDF5;">
            <h4 style="font-size:14px; font-weight:500; color:#061B31;">Detail Transaksi</h4>
            <span class="{{ match($transaction->type) {
                'Masuk'    => 'badge-success',
                'Keluar'   => 'badge-alert',
                'Transfer' => 'badge-info',
                default    => 'badge-neutral',
            } }}">{{ $transaction->type }}</span>
        </div>
        <div class="grid grid-cols-2 gap-0">
            @foreach([
                ['label' => 'No. Referensi', 'value' => $transaction->reference_number, 'mono' => true],
                ['label' => 'Tipe',          'value' => $transaction->type],
                ['label' => 'Produk',        'value' => $transaction->product->name ?? '—'],
                ['label' => 'Gudang',        'value' => $transaction->warehouse->name ?? '—'],
                ['label' => 'Kuantitas',     'value' => number_format($transaction->quantity) . ' unit'],
                ['label' => 'Harga Satuan',  'value' => $transaction->unit_price ? 'Rp ' . number_format($transaction->unit_price) : '—'],
                ['label' => 'Supplier',      'value' => $transaction->supplier->name ?? '—'],
                ['label' => 'Operator',      'value' => $transaction->operator->name ?? '—'],
                ['label' => 'Tanggal',       'value' => $transaction->created_at->format('d M Y H:i')],
                ['label' => 'Status',        'value' => $transaction->status ?? 'Selesai'],
            ] as $f)
            <div class="px-6 py-4" style="border-bottom:1px solid #F1F5F9; border-right:1px solid #F1F5F9;">
                <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">{{ $f['label'] }}</p>
                <p style="font-size:14px; color:#061B31;" class="{{ ($f['mono'] ?? false) ? 'font-mono' : '' }}">{{ $f['value'] }}</p>
            </div>
            @endforeach
        </div>
        @if($transaction->notes)
        <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">
            <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:6px;">Keterangan</p>
            <p style="font-size:14px; color:#64748D; line-height:1.6;">{{ $transaction->notes }}</p>
        </div>
        @endif
    </div>
</div>
@endsection
