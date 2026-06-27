@extends('layouts.app')

@section('title', 'Pengaturan Satuan')

@section('content')
<div class="container-fluid">
    <div class="d-flex align-items-center mb-4">
        <h3 class="mb-0 fw-bold"><i class="fa-solid fa-scale-unbalanced text-primary me-2"></i> Pengaturan Satuan & Konversi</h3>
        <button class="btn btn-primary ms-auto" data-bs-toggle="modal" data-bs-target="#modalTambahKonversi">
            <i class="fa-solid fa-plus me-1"></i> Tambah Konversi
        </button>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-1"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-exclamation-circle me-1"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Satuan Awal</th>
                            <th>Satuan Akhir (Terkecil)</th>
                            <th>Nilai Konversi</th>
                            <th>Keterangan</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($konversis as $konversi)
                            <tr>
                                <td class="ps-4 fw-bold">{{ $konversi->satuan_awal }}</td>
                                <td>{{ $konversi->satuan_akhir }}</td>
                                <td>
                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 fs-6">
                                        {{ rtrim(rtrim(number_format($konversi->nilai_konversi, 4, ',', '.'), '0'), ',') }}
                                    </span>
                                </td>
                                <td class="text-muted small">
                                    {{ $konversi->keterangan ?: "1 {$konversi->satuan_awal} = " . rtrim(rtrim(number_format($konversi->nilai_konversi, 4, ',', '.'), '0'), ',') . " {$konversi->satuan_akhir}" }}
                                </td>
                                <td class="text-end pe-4">
                                    <button class="btn btn-sm btn-outline-primary" onclick="editKonversi({{ $konversi->id }}, '{{ $konversi->satuan_awal }}', '{{ $konversi->satuan_akhir }}', {{ $konversi->nilai_konversi }}, '{{ $konversi->keterangan }}')">
                                        <i class="fa-solid fa-edit"></i>
                                    </button>
                                    <form action="{{ route('dashboard.hpp.satuan.destroy', $konversi->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin menghapus konversi ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="text-center py-5 text-muted">
                                    <i class="fa-solid fa-scale-unbalanced fs-1 mb-3 opacity-25"></i>
                                    <p class="mb-0">Belum ada data konversi satuan.</p>
                                    <button class="btn btn-sm btn-primary mt-3" data-bs-toggle="modal" data-bs-target="#modalTambahKonversi">Buat Sekarang</button>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit Konversi -->
<div class="modal fade" id="modalTambahKonversi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content rounded-4 border-0 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalTitle">Tambah Konversi Satuan</h5>
                <button type="button" class="btn-close shadow-none" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <form action="{{ route('dashboard.hpp.satuan.store') }}" method="POST" id="formKonversi">
                    @csrf
                    <input type="hidden" name="_method" id="formMethod" value="POST">
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Satuan Awal</label>
                        <input type="text" class="form-control" name="satuan_awal" id="satuan_awal" placeholder="Contoh: Kilogram" required>
                        <small class="text-muted">Satuan pembelian / kemasan besar.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Satuan Akhir (Terkecil)</label>
                        <input type="text" class="form-control" name="satuan_akhir" id="satuan_akhir" placeholder="Contoh: Gram" required>
                        <small class="text-muted">Satuan dasar yang digunakan dalam resep.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nilai Konversi</label>
                        <div class="input-group">
                            <input type="number" step="0.0001" class="form-control" name="nilai_konversi" id="nilai_konversi" placeholder="Contoh: 1000" required>
                        </div>
                        <small class="text-muted">Contoh: Jika 1 Kilogram = 1000 Gram, isi 1000.</small>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan (Opsional)</label>
                        <input type="text" class="form-control" name="keterangan" id="keterangan" placeholder="Contoh: 1 Kilogram = 1000 Gram">
                    </div>

                    <div class="d-flex justify-content-end mt-4">
                        <button type="button" class="btn btn-light me-2" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary px-4">Simpan Konversi</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
    function editKonversi(id, awal, akhir, nilai, keterangan) {
        document.getElementById('modalTitle').innerText = 'Edit Konversi Satuan';
        document.getElementById('formKonversi').action = '/dashboard/hpp/satuan/' + id;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('satuan_awal').value = awal;
        document.getElementById('satuan_akhir').value = akhir;
        document.getElementById('nilai_konversi').value = nilai;
        document.getElementById('keterangan').value = keterangan;
        
        new bootstrap.Modal(document.getElementById('modalTambahKonversi')).show();
    }
    
    // Reset form when modal is closed
    document.getElementById('modalTambahKonversi').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalTitle').innerText = 'Tambah Konversi Satuan';
        document.getElementById('formKonversi').action = '{{ route('dashboard.hpp.satuan.store') }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('formKonversi').reset();
    });
</script>
@endpush
