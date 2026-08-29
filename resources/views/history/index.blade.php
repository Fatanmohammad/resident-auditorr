@extends('layouts.app')

@push('styles')
<style>
    .history-container { 
        display: flex; 
        flex-direction: column; 
        gap: 1.25rem; 
    }
    .badge-status-done { 
        background-color: #f0fdf4; 
        color: #16a34a; 
        border: 1px solid #bbf7d0; 
        padding: 0.25rem 0.6rem; 
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 700; 
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
    .badge-status-pending { 
        background-color: #fffbe3; 
        color: #d97706; 
        border: 1px solid #fef3c7; 
        padding: 0.25rem 0.6rem; 
        border-radius: 20px; 
        font-size: 0.75rem; 
        font-weight: 700; 
        display: inline-flex;
        align-items: center;
        gap: 4px;
    }
</style>
@endpush

@section('content')
<div class="container-fluid py-4">
    <div class="history-container">
        <!-- Header Halaman -->
        <div class="page-header" style="display: flex; justify-content: space-between; align-items: center;">
            <div>
                <h1 style="margin: 0; font-size: 1.25rem; font-weight: 700; color: var(--bs-blue-dark, #1e3a8a);">Riwayat Perubahan Audit (Audit Log)</h1>
                <p style="color: var(--text-muted, #64748b); font-size: 0.85rem; margin-top: 0.25rem;">Catatan jejak rekam perbaikan, review, dan aktivitas pada KKA Offsite.</p>
            </div>
            <div>
                <a href="{{ route('ra-offsite.kka.index') }}" class="btn btn-outline-secondary" style="font-size: 0.82rem; padding: 0.4rem 0.8rem; border-radius: 6px; display: inline-flex; align-items: center; gap: 6px;">
                    <i class="bi bi-arrow-left"></i> Kembali ke KKA
                </a>
            </div>
        </div>

        <!-- Tabel Log Aktivitas -->
        <div class="card" style="border: 1px solid var(--border-color, #e2e8f0); border-radius: 8px; background: #fff;">
            <div class="card-header" style="display: flex; justify-content: space-between; align-items: center; padding: 1rem 1.25rem; border-bottom: 1px solid var(--border-color, #e2e8f0);">
                <div class="card-title" style="margin: 0; font-size: 1rem; font-weight: 700;">Daftar Log Aktivitas</div>
            </div>
            <div class="card-body" style="padding: 0;">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0" style="width: 100%;">
                        <thead style="background-color: #f8fafc; font-size: 0.78rem; color: #475569; text-transform: uppercase; font-weight: 700;">
                            <tr>
                                <th style="width: 4%; text-align: center; padding: 0.75rem 1rem;">NO</th>
                                <th style="width: 14%; padding: 0.75rem 1rem;">WAKTU LOG</th>
                                <th style="width: 12%; padding: 0.75rem 1rem;">USER / EDITOR</th>
                                <th style="width: 10%; padding: 0.75rem 1rem;">KODE UNIT</th>
                                <th style="width: 12%; padding: 0.75rem 1rem;">AKSI</th>
                                <th style="padding: 0.75rem 1rem;">KETERANGAN / CATATAN</th>
                                <th style="width: 12%; text-align: center; padding: 0.75rem 1rem;">STATUS</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($logs as $index => $log)
                                <tr style="border-bottom: 1px solid #f1f5f9;">
                                    <td style="text-align: center; color: var(--text-muted, #64748b); font-size: 0.8rem;">
                                        {{ method_exists($logs, 'firstItem') ? $logs->firstItem() + $index : $index + 1 }}
                                    </td>
                                    <td style="white-space: nowrap; font-size: 0.82rem;">
                                        {{ isset($log->created_at) ? \Carbon\Carbon::parse($log->created_at)->format('d/m/Y H:i') : '-' }}
                                    </td>
                                    <td style="font-weight: 600; font-size: 0.83rem;">
                                        {{ $log->user_name ?? $log->user_id ?? 'System' }}
                                    </td>
                                    <td style="font-size: 0.82rem;">
                                        {{ $log->kode_unit ?? '-' }}
                                    </td>
                                    <td style="font-size: 0.82rem; font-weight: 600;">
                                        {{ $log->action ?? 'Update Review' }}
                                    </td>
                                    <td style="font-size: 0.83rem; color: #334155;">
                                        {{ $log->keterangan ?? $log->description ?? '-' }}
                                    </td>
                                    <td style="text-align: center;">
                                        @if(in_array(strtolower($log->status_review ?? ''), ['selesai', 'reviewed', 'closed']))
                                            <span class="badge-status-done"><i class="bi bi-check-circle-fill"></i> Selesai</span>
                                        @else
                                            <span class="badge-status-pending"><i class="bi bi-clock-fill"></i> Belum Review</span>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="7" style="padding: 3rem 1rem; text-align: center;">
                                        <i class="bi bi-clock-history" style="font-size: 2.5rem; color: var(--text-muted, #94a3b8);"></i>
                                        <p style="margin-top: 0.5rem; color: var(--text-muted, #64748b); font-size: 0.9rem;">Belum ada riwayat aktivitas log recorded.</p>
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            @if(method_exists($logs, 'hasPages') && $logs->hasPages())
                <div style="padding: 0.8rem 1.25rem; display: flex; justify-content: flex-end; border-top: 1px solid var(--border-color, #e2e8f0); background: #f8fafc;">
                    {{ $logs->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection