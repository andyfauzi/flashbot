@extends('layouts.app')

@section('title', 'Manajemen Meja')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0"><i data-lucide="layout-grid" class="me-2 text-primary"></i> Manajemen Meja</h3>
    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#modalTambahMeja">
        <i data-lucide="plus" class="me-1"></i> Tambah Meja
    </button>
</div>

@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i data-lucide="check-circle" class="me-2" style="width:16px;height:16px;"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show rounded-3 mb-4" role="alert">
        <i data-lucide="alert-circle" class="me-2" style="width:16px;height:16px;"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-secondary">ID Meja</th>
                        <th class="py-3 text-secondary">Nama / Label</th>
                        <th class="py-3 text-secondary">Deskripsi</th>
                        <th class="py-3 text-secondary">Kapasitas</th>
                        <th class="py-3 text-secondary">Status</th>
                        <th class="py-3 text-secondary">Aktif</th>
                        <th class="py-3 text-secondary text-end px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($mejas as $meja)
                        <tr>
                            <td class="px-4 py-3 fw-bold text-muted">{{ $meja->nomor_meja }}</td>
                            <td class="py-3 fw-semibold">{{ $meja->nama_meja ?: '-' }}</td>
                            <td class="py-3 text-muted" style="max-width: 200px; font-size: 13px;">{{ $meja->deskripsi ?: '-' }}</td>
                            <td class="py-3">{{ $meja->kapasitas }} Orang</td>
                            <td class="py-3">
                                @if($meja->status == 'tersedia')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Tersedia</span>
                                @elseif($meja->status == 'direservasi')
                                    <span class="badge bg-warning bg-opacity-10 text-warning px-3 py-2 rounded-pill">Direservasi</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Terisi</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($meja->is_active)
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i data-lucide="check" style="width:12px;height:12px;"></i> Aktif</span>
                                @else
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill"><i data-lucide="x" style="width:12px;height:12px;"></i> Nonaktif</span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                <button type="button" class="btn btn-sm btn-outline-success rounded-circle" data-bs-toggle="modal" data-bs-target="#modalQr{{ $meja->id }}" title="Lihat QR Code">
                                    <i data-lucide="qr-code" style="width: 14px; height: 14px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#modalEditMeja{{ $meja->id }}" title="Edit">
                                    <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                                </button>
                                <form action="{{ route('dashboard.meja.destroy', $meja) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus meja ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle" title="Hapus">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal QR Code Meja -->
                        <div class="modal fade" id="modalQr{{ $meja->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered modal-sm">
                                <div class="modal-content border-0 shadow-lg rounded-4 text-center">
                                    <div class="modal-header border-bottom-0 pb-0 px-4 pt-4 text-center d-flex justify-content-between align-items-center w-100">
                                        <h5 class="modal-title fw-bold">QR {{ $meja->nama_meja ?: $meja->nomor_meja }}</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <div class="modal-body px-4 pt-3 pb-4">
                                        @php 
                                            $domainUrl = request()->getSchemeAndHttpHost();
                                            $tenantSlug = app('current_tenant')->subdomain ?? 'demo';
                                            $qrUrl = $domainUrl . route('portal.index', ['nama_toko_slug' => $tenantSlug, 'meja' => $meja->id], false); 
                                            $qrApiUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($qrUrl);
                                        @endphp
                                        <img src="{{ $qrApiUrl }}" alt="QR Code {{ $meja->nomor_meja }}" class="img-fluid rounded mb-3" style="max-width: 200px;">
                                        <p class="text-muted small mb-3">Pengunjung dapat scan QR Code ini untuk otomatis memesan di meja ini.</p>
                                        <a href="{{ $qrApiUrl }}" download="QR_Meja_{{ $meja->nomor_meja }}.png" target="_blank" class="btn btn-success rounded-pill px-4 w-100 fw-bold">Download QR</a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Modal Edit Meja -->
                        <div class="modal fade" id="modalEditMeja{{ $meja->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                                        <h5 class="modal-title fw-bold"><i data-lucide="edit-2" class="me-2 text-primary"></i> Edit Meja</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('dashboard.meja.update', $meja) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 pt-3 pb-2">
                                            <div class="row g-3">
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-secondary small">ID / Kode Meja <span class="text-danger">*</span></label>
                                                    <input type="text" name="nomor_meja" class="form-control bg-light border-0" value="{{ $meja->nomor_meja }}" required placeholder="Misal: 01">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-secondary small">Nama Label Meja</label>
                                                    <input type="text" name="nama_meja" class="form-control bg-light border-0" value="{{ $meja->nama_meja }}" placeholder="Misal: VIP Room, Gazebo A">
                                                </div>
                                                <div class="col-12">
                                                    <label class="form-label fw-semibold text-secondary small">Deskripsi</label>
                                                    <input type="text" name="deskripsi" class="form-control bg-light border-0" value="{{ $meja->deskripsi }}" placeholder="Misal: Dekat jendela, kapasitas keluarga, privat">
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-secondary small">Kapasitas (Orang) <span class="text-danger">*</span></label>
                                                    <input type="number" name="kapasitas" class="form-control bg-light border-0" value="{{ $meja->kapasitas }}" min="1" required>
                                                </div>
                                                <div class="col-6">
                                                    <label class="form-label fw-semibold text-secondary small">Status <span class="text-danger">*</span></label>
                                                    <select name="status" class="form-select bg-light border-0" required>
                                                        <option value="tersedia" {{ $meja->status == 'tersedia' ? 'selected' : '' }}>Tersedia</option>
                                                        <option value="direservasi" {{ $meja->status == 'direservasi' ? 'selected' : '' }}>Direservasi</option>
                                                        <option value="terisi" {{ $meja->status == 'terisi' ? 'selected' : '' }}>Terisi</option>
                                                    </select>
                                                </div>
                                                <div class="col-12">
                                                    <div class="form-check form-switch">
                                                        <input class="form-check-input" type="checkbox" name="is_active" id="isActive{{ $meja->id }}" {{ $meja->is_active ? 'checked' : '' }}>
                                                        <label class="form-check-label fw-semibold text-secondary small" for="isActive{{ $meja->id }}">
                                                            Meja ini aktif dan bisa dipesan oleh pelanggan
                                                        </label>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i data-lucide="inbox" class="mb-3 opacity-50" style="width: 48px; height: 48px;"></i>
                                <p class="mb-0">Belum ada data meja.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($mejas->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $mejas->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Meja -->
<div class="modal fade" id="modalTambahMeja" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i data-lucide="plus" class="me-2 text-primary"></i> Tambah Meja Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard.meja.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pt-3 pb-2">
                    <div class="row g-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary small">ID / Kode Meja <span class="text-danger">*</span></label>
                            <input type="text" name="nomor_meja" class="form-control bg-light border-0" placeholder="Misal: 01, VIP-1" required>
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary small">Nama Label Meja</label>
                            <input type="text" name="nama_meja" class="form-control bg-light border-0" placeholder="Misal: VIP Room, Gazebo A">
                        </div>
                        <div class="col-12">
                            <label class="form-label fw-semibold text-secondary small">Deskripsi</label>
                            <input type="text" name="deskripsi" class="form-control bg-light border-0" placeholder="Misal: Dekat jendela, privat, outdoor, dll.">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold text-secondary small">Kapasitas (Orang) <span class="text-danger">*</span></label>
                            <input type="number" name="kapasitas" class="form-control bg-light border-0" value="2" min="1" required>
                        </div>
                        <div class="col-12">
                            <div class="form-check form-switch">
                                <input class="form-check-input" type="checkbox" name="is_active" id="isActiveTambah" checked>
                                <label class="form-check-label fw-semibold text-secondary small" for="isActiveTambah">
                                    Meja ini aktif dan bisa dipesan oleh pelanggan
                                </label>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="status" value="tersedia">
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-2">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Meja</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
