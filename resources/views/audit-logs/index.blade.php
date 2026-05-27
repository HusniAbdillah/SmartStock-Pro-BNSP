@extends('layouts.app')
@section('title', 'Audit Log')
@section('page-title', 'Audit Log')

@section('content')
<x-page-header title="Audit Log" description="Rekam jejak seluruh aktivitas perubahan data di sistem.">
</x-page-header>

{{-- Filters --}}
<div class="ss-card mb-6" style="padding:16px 20px;">
    <form method="GET" action="{{ route('audit-logs.index') }}" class="flex flex-wrap items-center gap-3">
        <div class="flex-1" style="min-width:200px;">
            <input type="text" name="search" value="{{ request('search') }}"
                placeholder="Cari aksi, tabel, atau pengguna..." class="ss-input">
        </div>
        <select name="user_id" class="ss-select" style="min-width:180px;">
            <option value="">Semua Pengguna</option>
            @foreach($users as $u)
            <option value="{{ $u->id }}" {{ request('user_id') == $u->id ? 'selected' : '' }}>{{ $u->name }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="ss-input" style="max-width:160px;">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="ss-input" style="max-width:160px;">
        <button type="submit" class="btn-primary">Terapkan</button>
        @if(request()->hasAny(['search','user_id','date_from','date_to']))
        <a href="{{ route('audit-logs.index') }}" class="btn-ghost">Reset</a>
        @endif
    </form>
</div>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Waktu</th>
                    <th>Pengguna</th>
                    <th>Aksi</th>
                    <th>Tabel</th>
                    <th>ID Record</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                @forelse($logs as $log)
                <tr>
                    <td style="font-size:12px; color:#64748D; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($log->created_at)->format('d M Y H:i:s') }}
                    </td>
                    <td>
                        <p style="font-size:13px; font-weight:500; color:#061B31;">{{ $log->user->name ?? 'Sistem' }}</p>
                        <p style="font-size:11px; color:#B8CCDB;">{{ $log->user->role ?? '' }}</p>
                    </td>
                    <td>
                        <span class="{{ match(strtoupper($log->action ?? '')) {
                            'CREATE', 'INSERT' => 'badge-success',
                            'UPDATE'           => 'badge-info',
                            'DELETE'           => 'badge-alert',
                            'LOGIN'            => 'badge-warning',
                            default            => 'badge-neutral',
                        } }}">{{ $log->action }}</span>
                    </td>
                    <td style="font-size:12px; font-family:monospace; color:#64748D;">{{ $log->table_name ?? '—' }}</td>
                    <td style="font-size:12px; font-family:monospace; color:#B8CCDB;">{{ $log->record_id ?? '—' }}</td>
                    <td style="font-size:12px; font-family:monospace; color:#B8CCDB;">{{ $log->ip_address ?? '—' }}</td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center py-12" style="font-size:13px; color:#64748D;">Belum ada catatan audit</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($logs->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $logs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
