@extends('layouts.app')
@section('title', 'Laporan')
@section('page-title', 'Laporan')

@section('content')
<x-page-header title="Pusat Laporan" description="Ekspor laporan inventaris dalam format PDF.">
</x-page-header>

<div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 mb-8">

    {{-- Inventory report --}}
    <div class="ss-card">
        <div class="w-10 h-10 rounded mb-4 flex items-center justify-center" style="background:#E8E9FF;">
            <svg class="w-5 h-5" style="color:#533AFD;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <h4 style="font-size:16px; font-weight:400; color:#061B31; margin-bottom:8px;">Laporan Inventaris</h4>
        <p style="font-size:13px; color:#64748D; line-height:1.6; margin-bottom:20px;">
            Laporan lengkap kondisi stok semua produk di seluruh gudang.
        </p>
        <form method="POST" action="{{ route('reports.generate') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="type" value="inventory">
            <div>
                <label class="ss-label" style="font-size:12px;">Gudang</label>
                <select name="warehouse_id" class="ss-select" style="font-size:13px;">
                    <option value="">Semua Gudang</option>
                    @foreach($warehouses as $wh)
                    <option value="{{ $wh->id }}">{{ $wh->name }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="btn-primary w-full justify-center" style="font-size:13px;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor PDF
            </button>
        </form>
    </div>

    {{-- Transaction report --}}
    <div class="ss-card">
        <div class="w-10 h-10 rounded mb-4 flex items-center justify-center" style="background:#D1FAE5;">
            <svg class="w-5 h-5" style="color:#065F46;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
        </div>
        <h4 style="font-size:16px; font-weight:400; color:#061B31; margin-bottom:8px;">Laporan Transaksi</h4>
        <p style="font-size:13px; color:#64748D; line-height:1.6; margin-bottom:20px;">
            Ringkasan aktivitas barang masuk dan keluar dalam periode tertentu.
        </p>
        <form method="POST" action="{{ route('reports.generate') }}" class="space-y-3">
            @csrf
            <input type="hidden" name="type" value="transactions">
            <div class="grid grid-cols-2 gap-2">
                <div>
                    <label class="ss-label" style="font-size:12px;">Dari</label>
                    <input type="date" name="date_from" value="{{ now()->startOfMonth()->format('Y-m-d') }}" class="ss-input" style="font-size:13px;">
                </div>
                <div>
                    <label class="ss-label" style="font-size:12px;">Sampai</label>
                    <input type="date" name="date_to" value="{{ now()->format('Y-m-d') }}" class="ss-input" style="font-size:13px;">
                </div>
            </div>
            <button type="submit" class="btn-primary w-full justify-center" style="font-size:13px; background:#10B981;"
                onmouseover="this.style.background='#059669'"
                onmouseout="this.style.background='#10B981'">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Ekspor PDF
            </button>
        </form>
    </div>

    {{-- Large report (async) --}}
    <div class="ss-card" style="background:linear-gradient(135deg, rgba(83,58,253,0.04) 0%, rgba(255,97,24,0.02) 100%);">
        <div class="w-10 h-10 rounded mb-4 flex items-center justify-center" style="background:#FFF0E8;">
            <svg class="w-5 h-5" style="color:#FF6118;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
            </svg>
        </div>
        <h4 style="font-size:16px; font-weight:400; color:#061B31; margin-bottom:8px;">Laporan Komprehensif</h4>
        <p style="font-size:13px; color:#64748D; line-height:1.6; margin-bottom:20px;">
            Laporan lengkap semua modul (async). Diproses di background — hasil dikirim via notifikasi.
        </p>
        <form method="POST" action="{{ route('reports.generate-large') }}">
            @csrf
            <button type="submit" class="btn-secondary w-full justify-center" style="font-size:13px;">
                <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Generate (Background)
            </button>
        </form>
    </div>
</div>

{{-- Recent exports --}}
@if(isset($recentReports) && count($recentReports))
<div class="ss-card" style="padding:0; overflow:hidden;">
    <div class="px-6 py-4" style="border-bottom:1px solid #E5EDF5;">
        <h4 style="font-size:14px; font-weight:500; color:#061B31;">Laporan Terbaru</h4>
    </div>
    <table class="ss-table">
        <thead><tr><th>Nama File</th><th>Tipe</th><th>Dibuat</th><th></th></tr></thead>
        <tbody>
            @foreach($recentReports as $r)
            <tr>
                <td style="font-size:13px; font-family:monospace; color:#061B31;">{{ $r['name'] }}</td>
                <td><span class="badge-info">{{ $r['type'] }}</span></td>
                <td style="font-size:12px; color:#64748D;">{{ $r['created_at'] }}</td>
                <td class="text-right">
                    <a href="{{ $r['url'] }}" class="btn-ghost" style="padding:5px 10px; font-size:12px;" download>Unduh</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection
