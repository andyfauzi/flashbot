@extends('layouts.app')

@section('title', 'Manajemen Voucher Sales')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-ticket text-primary me-2"></i> Manajemen Voucher & Afiliasi Sales
        </h2>
        <button class="btn btn-primary fw-bold shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addVoucherModal">
            <i class="fa-solid fa-plus me-1"></i> Tambah Voucher
        </button>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Rangkuman / Summary (Opsional bisa ditambah grafis nanti) -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="row text-center">
                        <div class="col-md-4 border-end">
                            <h6 class="text-muted fw-bold">Total Voucher Aktif</h6>
                            <h3 class="fw-bold text-primary mb-0">{{ $vouchers->where('is_active', true)->count() }}</h3>
                        </div>
                        <div class="col-md-4 border-end">
                            <h6 class="text-muted fw-bold">Total Penggunaan</h6>
                            <h3 class="fw-bold text-success mb-0">{{ $vouchers->sum('payments_count') }} <small class="text-muted fs-6">kali</small></h3>
                        </div>
                        <div class="col-md-4">
                            <h6 class="text-muted fw-bold">Total Komisi Diberikan</h6>
                            <h3 class="fw-bold text-warning mb-0">Rp {{ number_format($vouchers->sum('payments_sum_commission_amount'), 0, ',', '.') }}</h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Daftar Voucher -->
        <div class="col-12">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Kode Voucher</th>
                                    <th>Informasi Sales</th>
                                    <th>Diskon (Tenant)</th>
                                    <th>Komisi (Sales)</th>
                                    <th>Peruntukan Paket</th>
                                    <th>Penggunaan</th>
                                    <th>Total Komisi</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($vouchers as $v)
                                <tr>
                                    <td>
                                        <span class="badge bg-primary fs-6">{{ $v->kode_voucher }}</span>
                                    </td>
                                    <td>
                                        <div class="fw-bold">{{ $v->nama_sales }}</div>
                                        @if($v->no_wa_sales)
                                        <div class="small text-muted"><i class="fa-brands fa-whatsapp text-success me-1"></i> {{ $v->no_wa_sales }}</div>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-danger">{{ $v->diskon_persen }}%</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">{{ $v->komisi_persen }}%</span>
                                    </td>
                                    <td>
                                        @if($v->target_paket == 'semua')
                                            <span class="badge bg-secondary rounded-pill px-3">Semua Paket</span>
                                        @else
                                            <span class="badge bg-info text-dark rounded-pill px-3 text-capitalize">Paket {{ $v->target_paket }}</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary rounded-pill px-3">{{ $v->payments_count }}x dipakai</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-warning">Rp {{ number_format($v->payments_sum_commission_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @if($v->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check-circle me-1"></i> Aktif</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-times-circle me-1"></i> Nonaktif</span>
                                        @endif
                                    </td>
                                    <td>
                                        <div class="d-flex gap-2">
                                            <form action="{{ route('superadmin.vouchers.toggle', $v->id) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="btn btn-sm btn-{{ $v->is_active ? 'warning' : 'success' }}" title="{{ $v->is_active ? 'Nonaktifkan' : 'Aktifkan' }}">
                                                    <i class="fa-solid fa-{{ $v->is_active ? 'ban' : 'check' }}"></i>
                                                </button>
                                            </form>
                                            @if($v->payments_count == 0)
                                            <form action="{{ route('superadmin.vouchers.destroy', $v->id) }}" method="POST" onsubmit="return confirm('Hapus voucher ini secara permanen?')">
                                                @csrf
                                                @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-danger" title="Hapus">
                                                    <i class="fa-solid fa-trash"></i>
                                                </button>
                                            </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="9" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-ticket fa-3x mb-3 text-light"></i>
                                        <p>Belum ada kode voucher sales yang dibuat.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah Voucher -->
<div class="modal fade" id="addVoucherModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow rounded-4" action="{{ route('superadmin.vouchers.store') }}" method="POST">
            @csrf
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-ticket text-primary me-2"></i>Tambah Voucher Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-info border-0 shadow-sm small">
                    <i class="fa-solid fa-info-circle me-2"></i> Kode voucher akan digunakan oleh pengguna (Tenant) saat pembayaran untuk mendapatkan diskon. Komisi akan dihitung dari total harga setelah diskon.
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Kode Voucher</label>
                    <input type="text" name="kode_voucher" class="form-control" placeholder="Contoh: FLASH2026" required style="text-transform: uppercase;">
                    <div class="form-text">Gunakan huruf dan angka tanpa spasi.</div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Sales / Mitra</label>
                    <input type="text" name="nama_sales" class="form-control" placeholder="Contoh: Budi Santoso" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">No WhatsApp Sales (Opsional)</label>
                    <input type="text" name="no_wa_sales" class="form-control" placeholder="Contoh: 628123456789">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Peruntukan Paket</label>
                    <select name="target_paket" class="form-select" required>
                        <option value="semua">Berlaku untuk Semua Paket</option>
                        <option value="starter">Khusus Paket Starter</option>
                        <option value="pro">Khusus Paket Pro</option>
                        <option value="business">Khusus Paket Business</option>
                    </select>
                </div>
                <div class="row g-3">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Diskon untuk Tenant (%)</label>
                        <div class="input-group">
                            <input type="number" name="diskon_persen" class="form-control" value="10" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Komisi untuk Sales (%)</label>
                        <div class="input-group">
                            <input type="number" name="komisi_persen" class="form-control" value="20" min="0" max="100" required>
                            <span class="input-group-text">%</span>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Voucher</button>
            </div>
        </form>
    </div>
</div>
@endsection
