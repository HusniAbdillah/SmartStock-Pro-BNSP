@extends('layouts.app')
@section('title', 'Edit Pengguna')
@section('page-title', 'Pengguna')
@section('breadcrumb', 'Edit')

@section('content')
<x-page-header title="Edit Pengguna" :description="$user->name">
    <x-slot name="actions">
        <a href="{{ route('users.index') }}" class="btn-secondary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            Kembali
        </a>
    </x-slot>
</x-page-header>

<div class="max-w-lg">
    <form method="POST" action="{{ route('users.update', $user) }}">
        @csrf @method('PUT')
        <div class="ss-card space-y-5">
            <x-form-input name="name" label="Nama Lengkap" :value="$user->name" required />
            <x-form-input name="email" label="Email" type="email" :value="$user->email" required />

            <div>
                <label class="ss-label">Role <span style="color:#EF4444;">*</span></label>
                <select name="role" class="ss-select @error('role') error @enderror">
                    @foreach(['Admin', 'Manajer Gudang', 'Staf Gudang', 'Viewer'] as $role)
                    <option value="{{ $role }}" {{ old('role', $user->role) === $role ? 'selected' : '' }}>{{ $role }}</option>
                    @endforeach
                </select>
                @error('role')<p class="ss-error-msg">{{ $message }}</p>@enderror
            </div>

            <div class="pt-2" style="border-top:1px solid #E5EDF5;">
                <p style="font-size:12px; font-weight:500; color:#64748D; margin-bottom:12px; text-transform:uppercase; letter-spacing:0.05em;">Ganti Kata Sandi (opsional)</p>
                <div class="space-y-4">
                    <x-form-input name="password" label="Kata Sandi Baru" type="password"
                        placeholder="Kosongkan jika tidak ingin mengganti" />
                    <x-form-input name="password_confirmation" label="Konfirmasi Kata Sandi" type="password"
                        placeholder="Ulangi kata sandi baru" />
                </div>
            </div>

            <div class="flex items-center justify-between pt-2" style="border-top:1px solid #E5EDF5;">
                <div class="flex items-center gap-2">
                    <input type="checkbox" name="is_active" id="is_active" value="1"
                        {{ old('is_active', $user->is_active) ? 'checked' : '' }}
                        style="width:16px; height:16px; accent-color:#533AFD; cursor:pointer;">
                    <label for="is_active" style="font-size:14px; color:#061B31;">Akun Aktif</label>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="btn-primary">Simpan Perubahan</button>
                    <a href="{{ route('users.index') }}" class="btn-secondary">Batal</a>
                </div>
            </div>
        </div>
    </form>
</div>
@endsection
