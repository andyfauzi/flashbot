@extends('layouts.app')

@section('title', 'Broadcast Promosi')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Riwayat Broadcast</h2>
            <p class="text-secondary mb-0 small">Lihat pesan promosi massal yang telah Anda kirimkan.</p>
        </div>
        <a href="{{ route('chatbot.broadcast.create') }}" class="btn btn-premium btn-premium-brand px-4 py-2 rounded-pill fw-bold shadow-sm">
            <i class="fa-solid fa-paper-plane me-2"></i> Buat Broadcast Baru
        </a>
    </div>

    @if(session('success'))
    <div class="alert alert-success border-0 rounded-4 shadow-sm mb-4">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
    </div>
    @endif

    @if(session('error'))
    <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
    </div>
    @endif

    @if(!$isMeta)
    <div class="alert alert-warning border-0 rounded-4 d-flex gap-3 px-4 py-3 mb-4 shadow-sm" style="background-color: #fff3cd;">
        <i class="fa-solid fa-triangle-exclamation fs-3 text-warning mt-1"></i>
        <div>
            <span class="fw-bold text-dark fs-5">Peringatan Keras (Rawan Banned)</span>
            <p class="mb-0 text-dark">
                Anda saat ini menggunakan <strong>Gateway Sistem (Baileys)</strong>. Mengirim pesan massal/broadcast menggunakan koneksi unofficial sangat berisiko membuat nomor WhatsApp Anda <strong>diblokir permanen (Banned)</strong> oleh pihak WhatsApp.
                <br>Gunakan fitur ini dengan sangat bijak. Kami tidak bertanggung jawab atas pemblokiran nomor.
                Untuk keamanan 100%, sangat disarankan beralih ke <strong>Meta Cloud API</strong>.
            </p>
        </div>
    </div>
    @else
    <div class="alert alert-success border-0 rounded-4 d-flex gap-3 px-4 py-3 mb-4 shadow-sm" style="background-color: #d1fae5;">
        <i class="fa-solid fa-shield-halved fs-3 text-success mt-1"></i>
        <div>
            <span class="fw-bold text-dark fs-5">Koneksi Aman (Meta Cloud API Aktif)</span>
            <p class="mb-0 text-dark">
                Anda menggunakan jalur resmi Meta. Broadcast promosi Anda 100% aman dari ancaman banned (selama mematuhi Commerce Policy).
                Ingat batas limit resmi Meta: <strong>Tier 1: 250 - 1.000 percakapan per hari</strong>.
            </p>
        </div>
    </div>
    @endif

    <div class="card-premium p-0 overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="table table-premium align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4 py-3">Tanggal & Waktu</th>
                        <th>Judul Broadcast</th>
                        <th>Target Filter</th>
                        <th>Status</th>
                        <th class="pe-4 text-center">Penerima</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($broadcasts as $item)
                    <tr>
                        <td class="ps-4">
                            <span class="d-block fw-bold text-dark">{{ $item->created_at->format('d M Y') }}</span>
                            <small class="text-muted">{{ $item->created_at->format('H:i') }} WIB</small>
                        </td>
                        <td>
                            <span class="fw-bold text-dark">{{ $item->judul }}</span>
                            @if($item->meta_template_name)
                                <br><small class="text-muted"><i class="fa-brands fa-meta me-1"></i> Template: {{ $item->meta_template_name }}</small>
                            @endif
                        </td>
                        <td>
                            @if($item->target_filter === 'all')
                                <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Semua Pengguna</span>
                            @else
                                <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Interaksi Rendah</span>
                            @endif
                        </td>
                        <td>
                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">
                                <i class="fa-solid fa-check-circle me-1"></i> Dikirim
                            </span>
                        </td>
                        <td class="pe-4 text-center">
                            <span class="fs-5 fw-bold text-dark">{{ number_format($item->total_penerima) }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center py-5 text-muted">
                            <i class="fa-solid fa-bullhorn fs-1 mb-3 text-light"></i>
                            <p class="mb-0">Belum ada riwayat broadcast promosi.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
