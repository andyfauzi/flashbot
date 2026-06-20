@extends('layouts.app')

@section('title', 'Audit Logs')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Audit Logs & Aktivitas</h2>
            <p class="text-muted mb-0">Merekam segala aktivitas krusial tenant dan super admin.</p>
        </div>
        <a href="{{ route('superadmin.index') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Waktu</th>
                            <th>Aksi (Action)</th>
                            <th>Entitas (Target)</th>
                            <th colspan="2">Detail Ekstra</th>
                            <th class="pe-4">IP Address</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($audits as $audit)
                        <tr>
                            <td class="ps-4 text-muted small" style="white-space: nowrap;">
                                {{ \Carbon\Carbon::parse($audit->created_at)->format('d M Y, H:i') }}
                            </td>
                            <td>
                                <span class="badge bg-primary bg-opacity-10 text-primary border border-primary-subtle rounded-pill px-3 py-2">
                                    {{ $audit->action }}
                                </span>
                            </td>
                            <td class="fw-semibold text-dark">{{ $audit->target ?? '-' }}</td>
                            <td colspan="2">
                                @if($audit->details)
                                    <pre class="small bg-light p-2 rounded text-dark mb-0" style="max-width: 500px; overflow-x: auto;">{{ is_string($audit->details) ? $audit->details : json_encode($audit->details, JSON_PRETTY_PRINT) }}</pre>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="pe-4 text-muted small">{{ $audit->ip ?? '-' }}</td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-file-shield fs-1 text-light mb-3 d-block"></i>
                                Belum ada riwayat aktivitas log.
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
        @if($audits->hasPages())
        <div class="card-footer bg-white border-0 pt-4 pb-4 px-4 d-flex justify-content-end">
            {{ $audits->links('pagination::bootstrap-5') }}
        </div>
        @endif
    </div>
</div>
@endsection
