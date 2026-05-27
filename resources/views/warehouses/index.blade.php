@extends('layouts.app')
@section('title', 'Gudang')
@section('page-title', 'Gudang')

@section('content')
<x-page-header title="Manajemen Gudang" description="5 lokasi gudang aktif di seluruh Indonesia.">
    <x-slot name="actions">
        @if(auth()->user()->isAdmin())
        <a href="{{ route('warehouses.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Gudang
        </a>
        @endif
    </x-slot>
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <table class="ss-table">
        <thead>
            <tr>
                <th>Gudang</th>
                <th>Kota / Provinsi</th>
                <th>Manajer</th>
                <th class="text-right">Total Stok</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($warehouses as $wh)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0" style="background:#E8E9FF;">
                            <svg class="w-4 h-4" style="color:#533AFD;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                        </div>
                        <div>
                            <a href="{{ route('warehouses.show', $wh) }}"
                               style="font-size:13px; font-weight:500; color:#061B31; text-decoration:none;"
                               onmouseover="this.style.color='#533AFD';"
                               onmouseout="this.style.color='#061B31';">
                                {{ $wh->name }}
                            </a>
                            @if($wh->address)
                            <p style="font-size:11px; color:#B8CCDB; margin-top:1px;">{{ Str::limit($wh->address, 40) }}</p>
                            @endif
                        </div>
                    </div>
                </td>
                <td style="font-size:13px; color:#64748D;">{{ $wh->city }}{{ $wh->province ? ', ' . $wh->province : '' }}</td>
                <td style="font-size:13px; color:#64748D;">{{ $wh->manager_name ?? '—' }}</td>
                <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">{{ number_format($wh->total_stock) }}</td>
                <td>
                    @if($wh->is_active)
                    <span class="badge-success">Aktif</span>
                    @else
                    <span class="badge-neutral">Non-aktif</span>
                    @endif
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('warehouses.show', $wh) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Detail</a>
                        @if(auth()->user()->isAdmin())
                        <a href="{{ route('warehouses.edit', $wh) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada gudang</td></tr>
            @endforelse
        </tbody>
    </table>
</div>
@endsection
