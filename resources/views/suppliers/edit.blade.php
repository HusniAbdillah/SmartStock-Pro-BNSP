@extends('layouts.app')
@section('title', 'Edit Supplier')
@section('page-title', 'Supplier')
@section('breadcrumb', 'Edit')

@section('content')
<x-page-header title="Edit Supplier" :description="$supplier->name">
    <x-slot name="actions">
        <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-2xl">
    <form method="POST" action="{{ route('suppliers.update', $supplier) }}">
        @csrf @method('PUT')
        <div class="ss-card space-y-5">
            <x-form-input name="name" label="Nama Supplier" :value="$supplier->name" required />
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="email" label="Email" type="email" :value="$supplier->email" />
                <x-form-input name="phone" label="Telepon" :value="$supplier->phone" />
            </div>
            <div>
                <label class="ss-label">Alamat</label>
                <textarea name="address" rows="2" class="ss-textarea">{{ old('address', $supplier->address) }}</textarea>
            </div>
            <div class="grid grid-cols-2 gap-4">
                <x-form-input name="city" label="Kota" :value="$supplier->city" />
                <x-form-input name="npwp" label="NPWP" :value="$supplier->npwp" />
            </div>
            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #E5EDF5;">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Supplier Aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('suppliers.show', $supplier) }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
