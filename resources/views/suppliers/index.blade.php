@extends('layouts.app')
@section('title', 'Supplier')
@section('page-title', 'Supplier')

@section('content')
<x-page-header title="Manajemen Supplier" description="Kelola mitra pemasok produk.">
    <x-slot name="actions">
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <a href="{{ route('suppliers.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Supplier
        </a>
        @endif
    </x-slot>
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <table class="ss-table">
        <thead>
            <tr>
                <th>Supplier</th>
                <th>Kontak</th>
                <th>Kota</th>
                <th class="text-right">Jumlah Produk</th>
                <th>Status</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($suppliers as $sup)
            <tr>
                <td>
                    <div>
                        <a href="{{ route('suppliers.show', $sup) }}"
                           style="font-size:13px; font-weight:500; color:#061B31; text-decoration:none;"
                           onmouseover="this.style.color='#533AFD';"
                           onmouseout="this.style.color='#061B31';">
                            {{ $sup->name }}
                        </a>
                        @if($sup->email)
                        <p style="font-size:11px; color:#B8CCDB; margin-top:1px;">{{ $sup->email }}</p>
                        @endif
                    </div>
                </td>
                <td style="font-size:13px; color:#64748D;">{{ $sup->phone ?? '—' }}</td>
                <td style="font-size:13px; color:#64748D;">{{ $sup->city ?? '—' }}</td>
                <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">
                    {{ $sup->products_count ?? $sup->products->count() }}
                </td>
                <td>
                    @if($sup->is_active)
                    <span class="badge-success">Aktif</span>
                    @else
                    <span class="badge-neutral">Non-aktif</span>
                    @endif
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('suppliers.show', $sup) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Detail</a>
                        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
                        <a href="{{ route('suppliers.edit', $sup) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Edit</a>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="6" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada supplier</td></tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($suppliers, 'hasPages') && $suppliers->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $suppliers->links() }}</div>
    @endif
</div>
@endsection
