@extends('layouts.app')
@section('title', 'Tambah Supplier')
@section('page-title', 'Supplier')
@section('breadcrumb', 'Tambah')

@section('content')
<x-page-header title="Tambah Supplier Baru">
    <x-slot name="actions">
        <a href="{{ route('suppliers.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('suppliers.store') }}">
        @csrf
        <div class="ss-card space-y-5">
            <x-form-input name="name" label="Nama Supplier" placeholder="PT. Contoh Supplier" required />
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="email" label="Email" type="email" placeholder="supplier@email.com" />
                <x-form-input name="phone" label="Telepon" placeholder="021-xxxxxxx" />
            </div>
            <div>
                <label class="ss-label">Alamat</label>
                <textarea name="address" rows="2" class="ss-textarea" placeholder="Alamat lengkap supplier">{{ old('address') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="city" label="Kota" placeholder="Jakarta" />
                <x-form-input name="npwp" label="NPWP" placeholder="xx.xxx.xxx.x-xxx.xxx" />
            </div>
            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #E5EDF5;">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Supplier Aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Simpan Supplier</button>
                    <a href="{{ route('suppliers.index') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
