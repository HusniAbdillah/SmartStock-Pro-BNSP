@extends('layouts.app')
@section('title', 'Tambah Gudang')
@section('page-title', 'Gudang')
@section('breadcrumb', 'Tambah')

@section('content')
<x-page-header title="Tambah Gudang Baru" description="Daftarkan lokasi gudang baru ke dalam sistem.">
    <x-slot name="actions">
        <a href="{{ route('warehouses.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('warehouses.store') }}">
        @csrf
        <div class="ss-card space-y-5">
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="name" label="Nama Gudang" placeholder="Gudang Jakarta Pusat" required />
                <x-form-input name="code" label="Kode Gudang" placeholder="JKT-01" />
            </div>
            <div>
                <label class="ss-label">Alamat Lengkap</label>
                <textarea name="address" rows="2" class="ss-textarea" placeholder="Jl. Contoh No. 123...">{{ old('address') }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="city" label="Kota" placeholder="Jakarta" required />
                <x-form-input name="province" label="Provinsi" placeholder="DKI Jakarta" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="manager_name" label="Nama Manajer" placeholder="Nama penanggung jawab" />
                <x-form-input name="phone" label="Nomor Telepon" placeholder="021-xxxxxxx" />
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="lat" label="Latitude" placeholder="-6.2088" type="number" />
                <x-form-input name="lng" label="Longitude" placeholder="106.8456" type="number" />
            </div>
            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #E5EDF5;">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Gudang Aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Simpan Gudang</button>
                    <a href="{{ route('warehouses.index') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
