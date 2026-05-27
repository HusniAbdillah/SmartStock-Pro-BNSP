@extends('layouts.app')
@section('title', $supplier->name)
@section('page-title', 'Supplier')
@section('breadcrumb', $supplier->name)

@section('content')
<x-page-header :title="$supplier->name">
    <x-slot name="actions">
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <a href="{{ route('suppliers.edit', $supplier) }}" class="btn-primary">Edit Supplier</a>
        @endif
    </x-slot>
</x-page-header>

<div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
    <div class="lg:col-span-2">
        <div class="ss-card" style="padding:0; overflow:hidden;">
            <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
                <h4 style="font-size:14px; font-weight:500; color:#061B31;">Informasi Supplier</h4>
            </div>
            <div class="grid grid-cols-2 gap-0">
                @foreach([
                    ['label' => 'Nama',       'value' => $supplier->name],
                    ['label' => 'Email',      'value' => $supplier->email ?? '—'],
                    ['label' => 'Telepon',    'value' => $supplier->phone ?? '—'],
                    ['label' => 'Kota',       'value' => $supplier->city ?? '—'],
                    ['label' => 'NPWP',       'value' => $supplier->npwp ?? '—'],
                    ['label' => 'Status',     'value' => $supplier->is_active ? 'Aktif' : 'Non-aktif'],
                ] as $f)
                <div class="px-6 py-4" style="border-bottom:1px solid #F1F5F9; border-right:1px solid #F1F5F9;">
                    <p style="font-size:11px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:4px;">{{ $f['label'] }}</p>
                    <p style="font-size:14px; color:#061B31;">{{ $f['value'] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    <div class="ss-card" style="padding:20px;">
        <h4 style="font-size:13px; font-weight:500; color:#B8CCDB; text-transform:uppercase; letter-spacing:0.06em; margin-bottom:12px;">Produk Terkait</h4>
        <div class="space-y-2">
            @forelse($supplier->products->take(8) as $p)
            <a href="{{ route('products.show', $p) }}" class="flex items-center justify-between py-1.5"
               style="text-decoration:none; border-bottom:1px solid #F1F5F9;">
                <span style="font-size:13px; color:#533AFD;">{{ $p->name }}</span>
                <span class="badge-neutral" style="font-size:11px;">{{ number_format($p->total_stock) }}</span>
            </a>
            @empty
            <p style="font-size:13px; color:#64748D;">Belum ada produk</p>
            @endforelse
            @if($supplier->products->count() > 8)
            <p style="font-size:12px; color:#B8CCDB; margin-top:8px;">+{{ $supplier->products->count() - 8 }} produk lainnya</p>
            @endif
        </div>
    </div>
</div>
@endsection
