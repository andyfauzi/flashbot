@extends('layouts.app')

@section('title', 'Jadwal Reservasi')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <h3 class="fw-bold m-0"><i data-lucide="calendar-clock" class="me-2 text-primary"></i> Jadwal Reservasi</h3>
    <button type="button" class="btn btn-primary shadow-sm rounded-pill px-4 fw-medium" data-bs-toggle="modal" data-bs-target="#modalTambahReservasi">
        <i data-lucide="plus" class="me-1"></i> Buat Reservasi
    </button>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="px-4 py-3 text-secondary">Waktu</th>
                        <th class="py-3 text-secondary">Pelanggan</th>
                        <th class="py-3 text-secondary">Meja</th>
                        <th class="py-3 text-secondary">Pax</th>
                        <th class="py-3 text-secondary">Pembayaran DP</th>
                        <th class="py-3 text-secondary">Status</th>
                        <th class="py-3 text-secondary text-end px-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($reservasis as $r)
                        <tr>
                            <td class="px-4 py-3">
                                <span class="fw-bold d-block">{{ $r->tanggal_waktu->format('d M Y') }}</span>
                                <small class="text-muted">{{ $r->tanggal_waktu->format('H:i') }}</small>
                            </td>
                            <td class="py-3">
                                <span class="fw-bold d-block">{{ $r->nama_pelanggan }}</span>
                                <small class="text-muted">{{ $r->nomor_telepon }}</small>
                            </td>
                            <td class="py-3 fw-bold text-primary">{{ $r->meja ? $r->meja->nomor_meja : 'Tanpa Meja' }}</td>
                            <td class="py-3">{{ $r->jumlah_orang }} org</td>
                            <td class="py-3">
                                @if($r->is_dp_required)
                                    @if($r->status_pembayaran_dp == 'lunas')
                                        <span class="badge bg-success bg-opacity-10 text-success px-2 py-1 rounded">Lunas (Rp {{ number_format($r->nominal_dp,0,',','.') }})</span>
                                    @else
                                        <span class="badge bg-danger bg-opacity-10 text-danger px-2 py-1 rounded">Belum Bayar (Rp {{ number_format($r->nominal_dp,0,',','.') }})</span>
                                    @endif
                                @else
                                    <span class="text-muted small">-</span>
                                @endif
                            </td>
                            <td class="py-3">
                                @if($r->status == 'menunggu')
                                    <span class="badge bg-secondary bg-opacity-10 text-secondary px-3 py-2 rounded-pill">Menunggu</span>
                                @elseif($r->status == 'dikonfirmasi')
                                    <span class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill">Dikonfirmasi</span>
                                @elseif($r->status == 'selesai')
                                    <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill">Selesai</span>
                                @else
                                    <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill">Batal</span>
                                @endif
                            </td>
                            <td class="py-3 text-end px-4">
                                @if($r->status == 'menunggu')
                                <form action="{{ route('dashboard.reservasi.approve', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Setujui reservasi ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-circle me-1" title="Setujui">
                                        <i data-lucide="check" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                                <form action="{{ route('dashboard.reservasi.reject', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Tolak reservasi ini?');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle me-1" title="Tolak">
                                        <i data-lucide="x" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                                @endif
                                <button type="button" class="btn btn-sm btn-outline-primary rounded-circle" data-bs-toggle="modal" data-bs-target="#modalEditReservasi{{ $r->id }}" title="Update Status">
                                    <i data-lucide="edit-2" style="width: 14px; height: 14px;"></i>
                                </button>
                                <form action="{{ route('dashboard.reservasi.destroy', $r) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus reservasi ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-circle ms-1" title="Hapus">
                                        <i data-lucide="trash-2" style="width: 14px; height: 14px;"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>

                        <!-- Modal Edit Reservasi -->
                        <div class="modal fade" id="modalEditReservasi{{ $r->id }}" tabindex="-1" aria-hidden="true">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg rounded-4">
                                    <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                                        <h5 class="modal-title fw-bold"><i data-lucide="edit-2" class="me-2 text-primary"></i> Update Status Reservasi</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                    </div>
                                    <form action="{{ route('dashboard.reservasi.update', $r) }}" method="POST">
                                        @csrf
                                        @method('PUT')
                                        <div class="modal-body px-4 pt-3 pb-4">
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Status Reservasi</label>
                                                <select name="status" class="form-select form-select-lg bg-light border-0" required>
                                                    <option value="menunggu" {{ $r->status == 'menunggu' ? 'selected' : '' }}>Menunggu</option>
                                                    <option value="dikonfirmasi" {{ $r->status == 'dikonfirmasi' ? 'selected' : '' }}>Dikonfirmasi</option>
                                                    <option value="selesai" {{ $r->status == 'selesai' ? 'selected' : '' }}>Selesai</option>
                                                    <option value="batal" {{ $r->status == 'batal' ? 'selected' : '' }}>Batal</option>
                                                </select>
                                                <small class="text-muted mt-1 d-block">Status Selesai/Batal otomatis membuat meja menjadi Tersedia kembali.</small>
                                            </div>
                                            @if($r->is_dp_required)
                                            <div class="mb-3">
                                                <label class="form-label fw-semibold text-secondary">Status Pembayaran DP</label>
                                                <select name="status_pembayaran_dp" class="form-select form-select-lg bg-light border-0" required>
                                                    <option value="belum_bayar" {{ $r->status_pembayaran_dp == 'belum_bayar' ? 'selected' : '' }}>Belum Bayar</option>
                                                    <option value="lunas" {{ $r->status_pembayaran_dp == 'lunas' ? 'selected' : '' }}>Lunas</option>
                                                </select>
                                            </div>
                                            @endif
                                        </div>
                                        <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
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
                                <p class="mb-0">Belum ada reservasi.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    @if($reservasis->hasPages())
    <div class="card-footer bg-white border-top px-4 py-3">
        {{ $reservasis->links() }}
    </div>
    @endif
</div>

<!-- Modal Tambah Reservasi -->
<div class="modal fade" id="modalTambahReservasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-header border-bottom-0 pb-0 px-4 pt-4">
                <h5 class="modal-title fw-bold"><i data-lucide="plus" class="me-2 text-primary"></i> Buat Reservasi Manual</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form action="{{ route('dashboard.reservasi.store') }}" method="POST">
                @csrf
                <div class="modal-body px-4 pt-3 pb-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nama Pelanggan</label>
                            <input type="text" name="nama_pelanggan" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-semibold text-secondary">Nomor Telepon/WA</label>
                            <input type="text" name="nomor_telepon" class="form-control bg-light border-0" required>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-secondary">Waktu Reservasi</label>
                            <input type="datetime-local" name="tanggal_waktu" class="form-control bg-light border-0" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-secondary">Pilih Meja</label>
                            <select name="meja_id" class="form-select bg-light border-0" required>
                                <option value="">-- Pilih Meja Kosong --</option>
                                @foreach($mejas as $m)
                                    <option value="{{ $m->id }}">{{ $m->nomor_meja }} (Kap: {{ $m->kapasitas }} org)</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label fw-semibold text-secondary">Jumlah Orang (Pax)</label>
                            <input type="number" name="jumlah_orang" class="form-control bg-light border-0" min="1" value="2" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-secondary">Catatan Khusus</label>
                        <textarea name="catatan" class="form-control bg-light border-0" rows="2" placeholder="Ulang tahun, kursi anak, dll..."></textarea>
                    </div>

                    <div class="card bg-light border-0">
                        <div class="card-body">
                            <div class="form-check form-switch mb-2">
                                <input class="form-check-input" type="checkbox" role="switch" id="dpSwitch" name="is_dp_required" value="1" onchange="document.getElementById('dpContainer').style.display = this.checked ? 'block' : 'none'">
                                <label class="form-check-label fw-bold" for="dpSwitch">Reservasi dengan Uang Muka (DP)</label>
                            </div>
                            <div id="dpContainer" style="display: none;">
                                <label class="form-label text-secondary small">Nominal DP (Rp)</label>
                                <input type="number" name="nominal_dp" class="form-control border-0 shadow-sm" value="50000" min="0">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top-0 px-4 pb-4 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Reservasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
