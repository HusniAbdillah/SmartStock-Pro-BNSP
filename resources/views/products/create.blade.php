@extends('layouts.app')

@section('title', 'Tambah Produk')
@section('page-title', 'Produk')
@section('breadcrumb', 'Tambah')

@section('content')

<x-page-header title="Tambah Produk Baru" description="Isi detail produk dan stok awal di gudang.">
    <x-slot name="actions">
        <a href="{{ route('products.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('products.store') }}" enctype="multipart/form-data"
      x-data="productForm()">
    @csrf

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Main fields --}}
        <div class="lg:col-span-2 space-y-5">
            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:20px;">Informasi Produk</h4>
                <div class="space-y-5">

                    <x-form-input name="name" label="Nama Produk" placeholder="Masukkan nama produk" required />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input name="sku" label="Kode SKU" placeholder="Otomatis jika kosong"
                            hint="Biarkan kosong untuk generate otomatis" />
                        <div>
                            <label class="ss-label">Harga Satuan (Rp) <span style="color:#EF4444;">*</span></label>
                            <input type="number" name="price" value="{{ old('price') }}"
                                min="0" step="100" placeholder="0" class="ss-input @error('price') error @enderror">
                            @error('price')<p class="ss-error-msg">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="ss-label">Kategori <span style="color:#EF4444;">*</span></label>
                            <select name="category_id" class="ss-select @error('category_id') error @enderror">
                                <option value="">Pilih kategori...</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ss-label">Supplier</label>
                            <select name="supplier_id" class="ss-select">
                                <option value="">Tanpa supplier</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id') == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div>
                        <x-form-input name="minimum_threshold" label="Batas Minimum Stok" type="number"
                            placeholder="10" value="{{ old('minimum_threshold', 10) }}"
                            hint="Peringatan akan muncul saat stok mencapai batas ini" />
                    </div>

                    <div>
                        <label class="ss-label">Deskripsi</label>
                        <textarea name="description" rows="3" placeholder="Keterangan produk (opsional)"
                            class="ss-textarea">{{ old('description') }}</textarea>
                    </div>
                </div>
            </div>

            {{-- Initial stock --}}
            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:4px;">Stok Awal</h4>
                <p style="font-size:13px; color:#64748D; margin-bottom:20px;">Tentukan gudang dan jumlah stok pertama kali masuk.</p>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="ss-label">Gudang <span style="color:#EF4444;">*</span></label>
                        <select name="warehouse_id" class="ss-select @error('warehouse_id') error @enderror">
                            <option value="">Pilih gudang...</option>
                            @foreach($warehouses as $wh)
                            <option value="{{ $wh->id }}" {{ old('warehouse_id') == $wh->id ? 'selected' : '' }}>{{ $wh->name }}</option>
                            @endforeach
                        </select>
                        @error('warehouse_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <x-form-input name="initial_stock" label="Jumlah Stok Awal" type="number"
                            placeholder="0" value="{{ old('initial_stock', 0) }}" />
                    </div>
                </div>
            </div>
        </div>

        {{-- Sidebar: image + status --}}
        <div class="space-y-5">
            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:16px;">Gambar Produk</h4>

                <div class="mb-4">
                    <div x-show="imagePreview" class="mb-3">
                        <img :src="imagePreview" alt="Preview"
                             style="width:100%; height:160px; object-fit:cover; border-radius:4px; border:1px solid #E5EDF5;">
                    </div>
                    <div x-show="!imagePreview"
                         class="flex flex-col items-center justify-center mb-3"
                         style="height:120px; border:2px dashed #D4DEE9; border-radius:4px; background:#F8FAFC;">
                        <svg class="w-8 h-8 mb-2" style="color:#D4DEE9;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <p style="font-size:12px; color:#B8CCDB;">Belum ada gambar</p>
                    </div>
                    <input type="file" name="image" accept="image/*" id="imageInput"
                        @change="previewImage($event)"
                        style="display:none;">
                    <button type="button" onclick="document.getElementById('imageInput').click()" class="btn-secondary w-full" style="font-size:13px;">
                        <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                        </svg>
                        Pilih Gambar
                    </button>
                </div>
                <p style="font-size:11px; color:#B8CCDB;">Format: JPG, PNG, WebP. Maks 2MB.</p>
            </div>

            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:16px;">Status</h4>
                <div class="flex items-center justify-between">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Produk Aktif</label>
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', '1') ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                </div>
                <p style="font-size:12px; color:#64748D; margin-top:6px;">Produk non-aktif tidak muncul di transaksi baru.</p>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn-primary w-full">Simpan Produk</button>
                <a href="{{ route('products.index') }}" class="btn-secondary w-full text-center">Batal</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function productForm() {
    return {
        imagePreview: null,
        previewImage(e) {
            const file = e.target.files[0];
            if (!file) return;
            const reader = new FileReader();
            reader.onload = (ev) => this.imagePreview = ev.target.result;
            reader.readAsDataURL(file);
        }
    };
}
</script>
@endpush
