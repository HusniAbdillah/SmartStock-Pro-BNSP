@extends('layouts.app')
@section('title', 'Tambah Kategori')
@section('page-title', 'Kategori')
@section('breadcrumb', 'Tambah')

@section('content')
<x-page-header title="Tambah Kategori" description="Buat kategori baru untuk mengelompokkan produk.">
    <x-slot name="actions">
        <a href="{{ route('categories.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-lg">
    <form method="POST" action="{{ route('categories.store') }}">
        @csrf
        <div class="ss-card space-y-5">
            <x-form-input name="name" label="Nama Kategori" placeholder="Elektronik, Furniture, dll." required />

            <div>
                <label class="ss-label">Deskripsi</label>
                <textarea name="description" rows="3" class="ss-textarea"
                    placeholder="Keterangan singkat kategori ini">{{ old('description') }}</textarea>
            </div>

            <div>
                <label class="ss-label">Warna Identitas</label>
                <div class="flex items-center gap-3">
                    <input type="color" name="color" value="{{ old('color', '#533AFD') }}"
                        style="width:40px; height:40px; border:1px solid #D4DEE9; border-radius:4px; padding:2px; cursor:pointer; background:none;">
                    <span style="font-size:13px; color:#64748D;">Pilih warna untuk identifikasi visual kategori</span>
                </div>
            </div>

            <div class="flex items-center gap-3 pt-2" style="border-top:1px solid #E5EDF5;">
                <button type="submit" class="btn-primary">Simpan Kategori</button>
                <a href="{{ route('categories.index') }}" class="btn-secondary">Batal</a>
            </div>
        </div>
    </form>
</div>
@endsection
