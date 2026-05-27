@extends('layouts.app')
@section('title', 'Log Error')
@section('page-title', 'Log Error')

@section('content')
<x-page-header title="Log Error Sistem" description="Monitor dan selesaikan error yang tercatat di aplikasi.">
</x-page-header>

<div class="ss-card" style="padding:0; overflow:hidden;">
    <div style="overflow-x:auto;">
        <table class="ss-table">
            <thead>
                <tr>
                    <th>Tingkat</th>
                    <th>Pesan</th>
                    <th>URL</th>
                    <th>Status</th>
                    <th>Waktu</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
                @forelse($errorLogs as $err)
                <tr x-data="{ expanded: false }">
                    <td>
                        <span class="{{ match(strtolower($err->severity ?? 'info')) {
                            'critical'  => 'badge-alert',
                            'error'     => 'badge-alert',
                            'warning'   => 'badge-warning',
                            'info'      => 'badge-info',
                            default     => 'badge-neutral',
                        } }}">{{ ucfirst($err->severity ?? 'info') }}</span>
                    </td>
                    <td style="max-width:320px;">
                        <p style="font-size:13px; color:#061B31; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                            {{ Str::limit($err->message, 80) }}
                        </p>
                        <div x-show="expanded" x-cloak class="mt-2 p-3" style="background:#F8FAFC; border-radius:4px; border:1px solid #E5EDF5;">
                            <pre style="font-size:11px; color:#64748D; white-space:pre-wrap; word-break:break-all; font-family:monospace; max-height:160px; overflow-y:auto;">{{ $err->stack_trace ?? $err->message }}</pre>
                        </div>
                    </td>
                    <td style="font-size:12px; font-family:monospace; color:#64748D; max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                        {{ $err->url ?? '—' }}
                    </td>
                    <td>
                        @if($err->resolved_at)
                        <span class="badge-success">Selesai</span>
                        @else
                        <span class="badge-alert">Belum</span>
                        @endif
                    </td>
                    <td style="font-size:12px; color:#64748D; white-space:nowrap;">
                        {{ \Carbon\Carbon::parse($err->occurred_at ?? $err->created_at)->format('d M Y H:i') }}
                    </td>
                    <td>
                        <div class="flex items-center justify-end gap-1">
                            <button @click="expanded = !expanded" class="btn-ghost" style="padding:5px 8px; font-size:11px;">
                                <span x-text="expanded ? 'Sembunyikan' : 'Detail'"></span>
                            </button>
                            @if(!$err->resolved_at)
                            <form method="POST" action="{{ route('error-logs.resolve', $err) }}">
                                @csrf @method('PATCH')
                                <button type="submit" class="btn-ghost" style="padding:5px 8px; font-size:11px; color:#10B981;">Selesaikan</button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="text-center py-12">
                        <svg class="w-10 h-10 mx-auto mb-3" style="color:#D4DEE9;" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p style="font-size:13px; color:#64748D;">Tidak ada error tercatat</p>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
    @if($errorLogs->hasPages())
    <div class="px-6 py-4" style="border-top:1px solid #E5EDF5;">{{ $errorLogs->withQueryString()->links() }}</div>
    @endif
</div>
@endsection
