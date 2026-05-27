@extends('layouts.app')
@section('title', 'Pengguna')
@section('page-title', 'Pengguna')

@section('content')
<x-page-header title="Manajemen Pengguna" description="Kelola akun dan hak akses pengguna sistem.">
    <x-slot name="actions">
        <a href="{{ route('users.create') }}" class="btn-primary">
            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
            Tambah Pengguna
        </a>
    </x-slot>
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <table class="ss-table">
        <thead>
            <tr>
                <th>Pengguna</th>
                <th>Role</th>
                <th>Status</th>
                <th>Bergabung</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($users as $user)
            <tr>
                <td>
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded flex items-center justify-center flex-shrink-0"
                             style="background:#E8E9FF; font-size:11px; font-weight:600; color:#533AFD;">
                            {{ strtoupper(substr($user->name, 0, 2)) }}
                        </div>
                        <div>
                            <p style="font-size:13px; font-weight:500; color:#061B31;">{{ $user->name }}</p>
                            <p style="font-size:11px; color:#B8CCDB;">{{ $user->email }}</p>
                        </div>
                    </div>
                </td>
                <td>
                    <span class="{{ match($user->role) {
                        'Admin'          => 'badge-info',
                        'Manajer Gudang' => 'badge-success',
                        'Staf Gudang'    => 'badge-warning',
                        'Viewer'         => 'badge-neutral',
                        default          => 'badge-neutral',
                    } }}">{{ $user->role }}</span>
                </td>
                <td>
                    @if($user->is_active)
                    <span class="badge-success">Aktif</span>
                    @else
                    <span class="badge-neutral">Non-aktif</span>
                    @endif
                </td>
                <td style="font-size:12px; color:#64748D;">{{ $user->created_at->format('d M Y') }}</td>
                <td>
                    <div class="flex items-center justify-end gap-1">
                        <a href="{{ route('users.edit', $user) }}" class="btn-ghost" style="padding:6px 10px; font-size:12px;">Edit</a>
                        @if($user->id !== auth()->id())
                        <form method="POST" action="{{ route('users.destroy', $user) }}"
                            @submit.prevent="if(confirm('Hapus pengguna ini?')) $el.submit()">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn-ghost" style="padding:6px 10px; font-size:12px; color:#EF4444;">Hapus</button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @empty
            <tr><td colspan="5" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada pengguna</td></tr>
            @endforelse
        </tbody>
    </table>
    @if($users->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $users->links() }}</div>
    @endif
</div>
@endsection
