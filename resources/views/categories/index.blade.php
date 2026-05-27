@extends('layouts.app')
@section('title', 'Kategori')
@section('page-title', 'Kategori')

@section('content')
<x-page-header title="Kategori Produk" description="Kelompokkan produk berdasarkan kategori.">
    <x-slot name="actions">
        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
        <a href="{{ route('categories.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Kategori
        </a>
        @endif
    </x-slot>
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <table class="ss-table">
        <thead>
            <tr>
                <th>Kategori</th>
                <th>Deskripsi</th>
                <th class="text-right">Jumlah Produk</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($categories as $cat)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-3 h-3 rounded-full flex-shrink-0" :style="`background: {{ $cat->color ?? '#533AFD' }}`"></div>
                        <span style="font-size:13px; font-weight:500; color:#061B31;">{{ $cat->name }}</span>
                    </div>
                </td>
                <td style="font-size:13px; color:#64748D; max-width:300px;">
                    {{ Str::limit($cat->description, 80) ?: '—' }}
                </td>
                <td class="text-right" style="font-size:13px; font-weight:500; color:#061B31;">
                    {{ number_format($cat->products_count ?? $cat->product_count) }}
                </td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        @if(auth()->user()->isAdmin() || auth()->user()->isManagerGudang())
                        <a href="{{ route('categories.edit', $cat) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Edit</a>
                        <form method="POST" action="{{ route('categories.destroy', $cat) }}"
                            @submit.prevent="if(confirm('Hapus kategori ini?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost" style="padding:6px 10px; font-size:12px; color:#EF4444;">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="4" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada kategori</td>
            </tr>
            @endforelse
        </tbody>
    </table>
    @if(method_exists($categories, 'hasPages') && $categories->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $categories->links() }}</div>
    @endif
</div>
@endsection
