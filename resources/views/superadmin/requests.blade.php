@extends('layouts.app')

@section('title', 'Pendaftaran Tenant')

@section('styles')
<style>
    .header-panel {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
        margin-bottom: 24px;
    }
    .header-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(79,70,229,0.4) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        opacity: 0.8;
        pointer-events: none;
    }
    .custom-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 24px;
        margin-bottom: 24px;
    }
    .table-custom {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-custom tr {
        background: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        border-radius: 8px;
    }
    .table-custom td {
        padding: 16px;
        border: none;
        vertical-align: middle;
    }
    .table-custom td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .table-custom td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    .subdomain-badge {
        background-color: #f1f5f9;
        color: #475569;
        padding: 4px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="header-panel">
    <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-3">
        <div>
            <h2 class="fw-bold mb-1 fs-4">Pendaftaran Tenant Baru</h2>
            <p class="text-white-50 mb-0 small">Tinjau dan setujui pendaftaran toko baru.</p>
        </div>
        <div>
            <a href="{{ route('superadmin.index') }}" class="btn btn-light rounded-pill px-4 fw-bold">Kembali ke Dashboard</a>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->any())
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first() }}
    </div>
@endif

<div class="row">
    <div class="col-12">
        <div class="custom-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-clipboard-list text-primary me-2"></i>Daftar Pengajuan</h5>
            @if($requests->isEmpty())
                <div class="text-center py-5">
                    <p class="text-muted">Tidak ada pendaftaran tenant baru saat ini.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom align-middle text-nowrap">
                        <thead>
                            <tr style="background: none; box-shadow: none;">
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Tanggal</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Nama Toko / Pemilik</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Detail Kontak</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Bisnis</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Status</th>
                                <th class="text-muted border-0 pb-3 text-end" style="font-weight: 600;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($requests as $req)
                                <tr>
                                    <td>{{ $req->created_at->format('d M Y, H:i') }}</td>
                                    <td>
                                        <div class="fw-bold">{{ $req->store_name }}</div>
                                        <small class="text-muted d-block">{{ $req->owner_name }}</small>
                                        <small class="text-muted"><span class="subdomain-badge">{{ $req->subdomain }}.tenanta.id</span></small>
                                    </td>
                                    <td>
                                        <div class="small"><i class="fa-regular fa-envelope me-1"></i>{{ $req->email }}</div>
                                        <div class="small"><i class="fa-brands fa-whatsapp me-1"></i>{{ $req->whatsapp_number }}</div>
                                    </td>
                                    <td>
                                        <div class="small"><strong>Layanan:</strong> {{ $req->jenis_layanan }}</div>
                                        <div class="small"><strong>Skala:</strong> {{ $req->skala_bisnis ?? '-' }}</div>
                                        <div class="small"><strong>Plan:</strong> {{ $req->plan }}</div>
                                    </td>
                                    <td>
                                        @if($req->status === 'pending')
                                            <span class="badge bg-warning text-dark py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">Pending</span>
                                        @elseif($req->status === 'approved')
                                            <span class="badge bg-success bg-opacity-10 text-success py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">Approved</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-info px-3 rounded-pill fw-semibold me-1 text-white" data-bs-toggle="modal" data-bs-target="#detailModal{{ $req->id }}">
                                            Detail
                                        </button>
                                        
                                        @if($req->status === 'pending')
                                        <form action="{{ route('superadmin.requests.approve', $req->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill fw-semibold me-1" onclick="return confirm('Apakah Anda yakin ingin menyetujui pendaftaran ini? Database akan segera dibuat.')">
                                                <i class="fa-solid fa-check me-1"></i> Approve
                                            </button>
                                        </form>

                                        <form action="{{ route('superadmin.requests.reject', $req->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-semibold me-1" onclick="return confirm('Apakah Anda yakin ingin menolak pendaftaran ini?')">
                                                <i class="fa-solid fa-xmark me-1"></i> Reject
                                            </button>
                                        </form>
                                        @endif
                                        
                                        <form action="{{ route('superadmin.requests.destroy', $req->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger px-3 rounded-pill fw-semibold" onclick="return confirm('Apakah Anda yakin ingin menghapus data pendaftaran ini selamanya?')">
                                                <i class="fa-solid fa-trash me-1"></i> Hapus
                                            </button>
                                        </form>
                                    </td>
                                </tr>

                                <!-- Modal Detail -->
                                <div class="modal fade text-start" id="detailModal{{ $req->id }}" tabindex="-1" aria-hidden="true">
                                    <div class="modal-dialog">
                                        <div class="modal-content">
                                            <div class="modal-header">
                                                <h5 class="modal-title fw-bold text-dark">Detail Pendaftaran: {{ $req->store_name }}</h5>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                            </div>
                                            <div class="modal-body text-dark">
                                                <p><strong>Alamat Toko:</strong><br>{{ $req->store_address }}</p>
                                                <hr>
                                                <p><strong>Nama Pemilik:</strong> {{ $req->owner_name }}</p>
                                                <p><strong>Email:</strong> {{ $req->email }}</p>
                                                <p><strong>WhatsApp:</strong> {{ $req->whatsapp_number }}</p>
                                                <hr>
                                                <p><strong>Jenis Layanan:</strong> {{ $req->jenis_layanan }}</p>
                                                <p><strong>Skala Bisnis:</strong> {{ $req->skala_bisnis ?? '-' }}</p>
                                                <p><strong>Plan Dipilih:</strong> {{ $req->plan }}</p>
                                                <p><strong>Trial:</strong> {{ $req->is_trial ? 'Ya' : 'Tidak' }}</p>
                                            </div>
                                            <div class="modal-footer">
                                                <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Tutup</button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
