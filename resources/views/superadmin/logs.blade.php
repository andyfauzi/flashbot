@extends('layouts.app')

@section('title', 'System Logs')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">System Error Logs</h2>
            <p class="text-muted mb-0">Memantau error aplikasi terbaru (maks 1000 baris terakhir).</p>
        </div>
        <a href="{{ route('superadmin.index') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0 bg-dark rounded-4 overflow-hidden">
            <pre class="text-light p-4 m-0" style="max-height: 70vh; overflow-y: auto; font-size: 0.85rem; white-space: pre-wrap; font-family: monospace;">{{ $logs ?: 'Tidak ada log error.' }}</pre>
        </div>
    </div>
</div>
@endsection
