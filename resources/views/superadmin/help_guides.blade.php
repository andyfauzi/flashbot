@extends('layouts.app')

@section('title', 'Manajemen Pusat Bantuan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Manajemen Pusat Bantuan</h2>
            <p class="text-muted mb-0">Kelola FAQ dan Panduan Dasar yang akan tampil di dashboard semua Tenant.</p>
        </div>
        <a href="{{ route('superadmin.index') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 d-flex justify-content-between align-items-center">
            <h5 class="fw-bold text-primary mb-0"><i class="fa-solid fa-list me-2"></i> Daftar Panduan</h5>
            <button type="button" class="btn btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#tambahModal">
                <i class="fa-solid fa-plus me-1"></i> Tambah Panduan
            </button>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th width="5%">Urutan</th>
                            <th width="30%">Pertanyaan / Judul</th>
                            <th width="45%">Jawaban</th>
                            <th width="20%" class="text-end">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($guides as $guide)
                        <tr>
                            <td class="text-center fw-bold">{{ $guide->urutan }}</td>
                            <td class="fw-semibold text-dark">{{ $guide->pertanyaan }}</td>
                            <td class="text-muted small text-truncate" style="max-width: 300px;">
                                {{ Str::limit(strip_tags($guide->jawaban), 100) }}
                            </td>
                            <td class="text-end">
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-pill me-1" 
                                    data-bs-toggle="modal" data-bs-target="#editModal{{ $guide->id }}">
                                    <i class="fa-solid fa-edit"></i> Edit
                                </button>
                                <form action="{{ route('superadmin.help_guides.destroy', $guide->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill" onclick="return confirm('Yakin ingin menghapus panduan ini?')">
                                        <i class="fa-solid fa-trash"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="editModal{{ $guide->id }}" tabindex="-1" aria-labelledby="editModalLabel{{ $guide->id }}" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-lg">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <div class="modal-header border-0 pb-0">
                                        <h5 class="modal-title fw-bold text-primary" id="editModalLabel{{ $guide->id }}"><i class="fa-solid fa-edit me-2"></i> Edit Panduan</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('superadmin.help_guides.update', $guide->id) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body p-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary small">Pertanyaan / Judul Panduan</label>
                                                <input type="text" class="form-control" name="pertanyaan" value="{{ $guide->pertanyaan }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary small">Isi Panduan (Bisa format HTML/Teks biasa)</label>
                                                <textarea class="form-control" name="jawaban" rows="6" required>{{ $guide->jawaban }}</textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold text-secondary small">Nomor Urutan Tampil</label>
                                                <input type="number" class="form-control" name="urutan" value="{{ $guide->urutan }}" style="max-width: 150px;">
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
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
                                <i class="fa-solid fa-folder-open fs-1 text-light mb-3 d-block"></i>
                                Belum ada data panduan. Silakan klik "Tambah Panduan".
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="tambahModal" tabindex="-1" aria-labelledby="tambahModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold text-primary" id="tambahModalLabel"><i class="fa-solid fa-plus-circle me-2"></i> Tambah Panduan Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('superadmin.help_guides.store') }}" method="POST">
                @csrf
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Pertanyaan / Judul Panduan</label>
                        <input type="text" class="form-control" name="pertanyaan" placeholder="Contoh: Bagaimana cara mengatur bot?" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Isi Panduan (Bisa format HTML/Teks biasa)</label>
                        <textarea class="form-control" name="jawaban" rows="6" placeholder="Masukkan jawaban dari panduan..." required></textarea>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold text-secondary small">Nomor Urutan Tampil</label>
                        <input type="number" class="form-control" name="urutan" value="0" style="max-width: 150px;">
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Panduan</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
