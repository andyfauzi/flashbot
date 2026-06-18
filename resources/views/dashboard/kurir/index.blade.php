@extends('layouts.app')

@section('title', 'Manajemen Kurir')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-truck-ramp-box me-2"></i> Manajemen Kurir
            </h2>
            <p class="text-secondary mb-0 small">Kelola data kurir pengiriman toko untuk penugasan pengantaran pesanan.</p>
        </div>
        <button class="btn btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahKurir">
            <i class="fa-solid fa-plus me-1"></i> Tambah Kurir
        </button>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4" style="width: 10%">ID</th>
                            <th style="width: 40%">Nama Kurir</th>
                            <th style="width: 30%">Nomor WhatsApp (HP)</th>
                            <th class="text-end pe-4" style="width: 20%">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kurirs as $kurir)
                        <tr>
                            <td class="ps-4 text-muted">#{{ $kurir->id }}</td>
                            <td class="fw-bold text-dark">{{ $kurir->nama }}</td>
                            <td>
                                <a href="https://wa.me/{{ $kurir->nomor_hp }}" target="_blank" class="text-decoration-none fw-semibold text-success">
                                    <i class="fa-brands fa-whatsapp me-1"></i> +{{ $kurir->nomor_hp }}
                                </a>
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill me-1" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $kurir->id }}">
                                    <i class="fa-solid fa-pen-to-square me-1"></i> Edit
                                </button>
                                <form action="{{ route('chatbot.kurir.destroy', $kurir->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus kurir {{ $kurir->nama }}?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Kurir -->
                        <div class="modal fade" id="modalEdit{{ $kurir->id }}" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('chatbot.kurir.update', $kurir->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-header border-bottom-0 bg-light rounded-top-4">
                                            <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square text-primary me-2"></i> Edit Data Kurir</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nama Kurir</label>
                                                <input type="text" name="nama" class="form-control form-control-lg" value="{{ $kurir->nama }}" required placeholder="Masukkan nama kurir...">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold">Nomor HP / WhatsApp</label>
                                                <input type="text" name="nomor_hp" class="form-control form-control-lg" value="{{ $kurir->nomor_hp }}" required placeholder="Contoh: 08123456789">
                                                <small class="text-muted mt-2 d-block">Nomor akan otomatis dinormalisasi ke format WhatsApp internasional.</small>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @empty
                        <tr>
                            <td colspan="4" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-truck-fast fa-3x mb-3 text-light"></i>
                                <h5>Belum ada kurir terdaftar</h5>
                                <p class="small text-secondary mb-0">Klik tombol "Tambah Kurir" di kanan atas untuk menambahkan kurir pertama.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Kurir -->
<div class="modal fade" id="modalTambahKurir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('chatbot.kurir.store') }}" method="POST">
                @csrf
                <div class="modal-header border-bottom-0 bg-light rounded-top-4">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-truck-ramp-box text-primary me-2"></i> Tambah Kurir Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Kurir</label>
                        <input type="text" name="nama" class="form-control form-control-lg" required placeholder="Masukkan nama kurir...">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nomor HP / WhatsApp</label>
                        <input type="text" name="nomor_hp" class="form-control form-control-lg" required placeholder="Contoh: 08123456789">
                        <small class="text-muted mt-2 d-block">Sistem akan menormalisasi nomor ini agar bisa terkirim pesan WhatsApp.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Tambah Kurir</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
