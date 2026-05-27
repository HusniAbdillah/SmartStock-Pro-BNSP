@extends('layouts.app')

@section('title', 'Edit Produk')
@section('page-title', 'Produk')
@section('breadcrumb', 'Edit')

@section('content')

<x-page-header title="Edit Produk" :description="$product->name">
    <x-slot name="actions">
        <a href="{{ route('products.show', $product) }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<form method="POST" action="{{ route('products.update', $product) }}" enctype="multipart/form-data"
      x-data="productForm()"
      data-image-url="{{ $product->image_url }}"
      data-has-image="{{ $product->image_path ? 'true' : 'false' }}">
    @csrf @method('PUT')

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-5">
            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:20px;">Informasi Produk</h4>
                <div class="space-y-5">
                    <x-form-input name="name" label="Nama Produk" :value="$product->name" required />

                    <div class="grid grid-cols-2 gap-4">
                        <x-form-input name="sku" label="Kode SKU" :value="$product->sku" />
                        <div>
                            <label class="ss-label">Harga Satuan (Rp) <span style="color:#EF4444;">*</span></label>
                            <input type="number" name="price" value="{{ old('price', $product->price) }}"
                                min="0" step="100" class="ss-input @error('price') error @enderror">
                            @error('price')<p class="ss-error-msg">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="ss-label">Kategori <span style="color:#EF4444;">*</span></label>
                            <select name="category_id" class="ss-select">
                                <option value="">Pilih kategori...</option>
                                @foreach($categories as $cat)
                                <option value="{{ $cat->id }}" {{ old('category_id', $product->category_id) == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id')<p class="ss-error-msg">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label class="ss-label">Supplier</label>
                            <select name="supplier_id" class="ss-select">
                                <option value="">Tanpa supplier</option>
                                @foreach($suppliers as $sup)
                                <option value="{{ $sup->id }}" {{ old('supplier_id', $product->supplier_id) == $sup->id ? 'selected' : '' }}>{{ $sup->name }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <x-form-input name="minimum_threshold" label="Batas Minimum Stok" type="number"
                        :value="$product->minimum_threshold" />

                    <div>
                        <label class="ss-label">Deskripsi</label>
                        <textarea name="description" rows="3" class="ss-textarea"
                            placeholder="Keterangan produk (opsional)">{{ old('description', $product->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        <div class="space-y-5">
            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:16px;">Gambar Produk</h4>
                <div class="mb-4">
                    <div x-show="imagePreview || hasExisting" class="mb-3">
                        <img :src="imagePreview || existingImage" alt="Preview"
                             style="width:100%; height:160px; object-fit:cover; border-radius:4px; border:1px solid #E5EDF5;">
                    </div>
                    <div x-show="!imagePreview && !hasExisting"
                         class="flex flex-col items-center justify-center mb-3"
                         style="height:100px; border:2px dashed #D4DEE9; border-radius:4px; background:#F8FAFC;">
                        <p style="font-size:12px; color:#B8CCDB;">Belum ada gambar</p>
                    </div>
                    <input type="file" name="image" accept="image/*" id="imageInput"
                        @change="previewImage($event)" style="display:none;">
                    <button type="button" onclick="document.getElementById('imageInput').click()" class="btn-secondary w-full" style="font-size:13px;">Ganti Gambar</button>
                </div>
            </div>

            <div class="ss-card">
                <h4 style="font-size:14px; font-weight:500; color:#061B31; margin-bottom:16px;">Status</h4>
                <div class="flex items-center justify-between">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Produk Aktif</label>
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $product->is_active) ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                </div>
            </div>

            <div class="flex flex-col gap-3">
                <button type="submit" class="btn-primary w-full">Simpan Perubahan</button>
                <a href="{{ route('products.show', $product) }}" class="btn-secondary w-full text-center">Batal</a>
            </div>
        </div>
    </div>
</form>

@endsection

@push('scripts')
<script>
function productForm() {
    var formEl = document.querySelector('[data-image-url]');
    return {
        imagePreview: null,
        existingImage: formEl ? formEl.dataset.imageUrl : '',
        hasExisting: formEl ? formEl.dataset.hasImage === 'true' : false,
        previewImage: function(e) {
            var file = e.target.files[0];
            if (!file) return;
            var reader = new FileReader();
            var self = this;
            reader.onload = function(ev) { self.imagePreview = ev.target.result; };
            reader.readAsDataURL(file);
        }
    };
}
</script>
@endpush
