@extends('layouts.app')

@section('title', 'Laporan Keuangan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-wallet text-primary me-2"></i> Laporan Keuangan Pusat
        </h2>
        <button class="btn btn-primary fw-bold shadow-sm rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#addExpenseModal">
            <i class="fa-solid fa-plus me-1"></i> Input Pengeluaran
        </button>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>Terjadi kesalahan saat menyimpan data.
            <ul class="mb-0 mt-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <!-- Metrik Keuangan Keseluruhan -->
    <h5 class="fw-bold text-muted mb-3"><i class="fa-solid fa-chart-line me-2"></i>Akumulasi Keseluruhan</h5>
    <div class="row g-3 mb-5">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-white-50 text-uppercase mb-3">Total Omset</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalOmsetKeseluruhan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-warning text-dark h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-dark-50 text-uppercase mb-3">Total Komisi Sales</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalKomisiKeseluruhan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-danger text-white h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-white-50 text-uppercase mb-3">Total Pengeluaran</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalPengeluaranKeseluruhan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-white-50 text-uppercase mb-3">Laba Bersih</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($labaBersihKeseluruhan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Metrik Keuangan Bulan Ini -->
    <h5 class="fw-bold text-muted mb-3"><i class="fa-solid fa-calendar-check me-2"></i>Bulan Ini ({{ \Carbon\Carbon::now()->translatedFormat('F Y') }})</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-3">Omset Bulan Ini</h6>
                    <h4 class="fw-bold text-primary mb-0">Rp {{ number_format($omsetBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-3">Komisi Bulan Ini</h6>
                    <h4 class="fw-bold text-warning mb-0">Rp {{ number_format($komisiBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-3">Pengeluaran Bulan Ini</h6>
                    <h4 class="fw-bold text-danger mb-0">Rp {{ number_format($pengeluaranBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm rounded-4 h-100 border-start border-5 border-success">
                <div class="card-body p-4">
                    <h6 class="fw-bold text-muted text-uppercase mb-3">Laba Bersih Bulan Ini</h6>
                    <h4 class="fw-bold text-success mb-0">Rp {{ number_format($labaBersihBulanIni, 0, ',', '.') }}</h4>
                </div>
            </div>
        </div>
    </div>

    <ul class="nav nav-tabs nav-fill mb-4" id="financeTab" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active fw-bold" id="ringkasan-tab" data-bs-toggle="tab" data-bs-target="#ringkasan" type="button" role="tab" aria-controls="ringkasan" aria-selected="true"><i class="fa-solid fa-chart-pie me-2"></i>Ringkasan & Pengeluaran</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="penjualan-tab" data-bs-toggle="tab" data-bs-target="#penjualan" type="button" role="tab" aria-controls="penjualan" aria-selected="false"><i class="fa-solid fa-cart-shopping me-2"></i>Detail Penjualan Paket</button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link fw-bold" id="rekap-tab" data-bs-toggle="tab" data-bs-target="#rekap" type="button" role="tab" aria-controls="rekap" aria-selected="false"><i class="fa-solid fa-users me-2"></i>Rekap Komisi Sales</button>
        </li>
    </ul>

    <div class="tab-content" id="financeTabContent">
        <!-- TAB RINGKASAN -->
        <div class="tab-pane fade show active" id="ringkasan" role="tabpanel" aria-labelledby="ringkasan-tab">
            <div class="row">
                <!-- Riwayat Pengeluaran -->
                <div class="col-12">
                    <div class="card border-0 shadow-sm rounded-4">
                        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list text-muted me-2"></i>Riwayat Pengeluaran Landlord</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Tanggal</th>
                                            <th>Nama Pengeluaran</th>
                                            <th>Kategori</th>
                                            <th>Keterangan</th>
                                            <th>Nominal</th>
                                            <th>Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($expenses as $ex)
                                        <tr>
                                            <td>
                                                <div class="fw-bold">{{ $ex->tanggal->format('d M Y') }}</div>
                                            </td>
                                            <td>
                                                <div class="fw-bold text-dark">{{ $ex->nama_pengeluaran }}</div>
                                            </td>
                                            <td>
                                                @if($ex->kategori)
                                                    <span class="badge bg-secondary rounded-pill px-3">{{ $ex->kategori }}</span>
                                                @else
                                                    <span class="text-muted">-</span>
                                                @endif
                                            </td>
                                            <td>
                                                <span class="text-muted small">{{ $ex->keterangan ?: '-' }}</span>
                                            </td>
                                            <td>
                                                <span class="fw-bold text-danger">Rp {{ number_format($ex->nominal, 0, ',', '.') }}</span>
                                            </td>
                                            <td>
                                                <form action="{{ route('superadmin.finance.destroy', $ex->id) }}" method="POST" onsubmit="return confirm('Hapus data pengeluaran ini?')">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger shadow-sm rounded-circle" style="width: 35px; height: 35px;" title="Hapus">
                                                        <i class="fa-solid fa-trash"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="6" class="text-center text-muted py-5">
                                                <i class="fa-solid fa-receipt fa-3x mb-3 text-light"></i>
                                                <p>Belum ada data pengeluaran yang dicatat.</p>
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

        <!-- TAB DETAIL PENJUALAN PAKET -->
        <div class="tab-pane fade" id="penjualan" role="tabpanel" aria-labelledby="penjualan-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-list-check text-primary me-2"></i>Riwayat Penjualan Langganan Tenant</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle" id="tablePenjualan">
                            <thead class="table-light">
                                <tr>
                                    <th>Waktu Pembayaran</th>
                                    <th>Tenant / Toko</th>
                                    <th>Invoice</th>
                                    <th>Nominal (Gross)</th>
                                    <th>Voucher & Sales</th>
                                    <th>Komisi Sales</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($salesPayments as $sp)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $sp->paid_at->format('d M Y') }}</div>
                                        <small class="text-muted">{{ $sp->paid_at->format('H:i') }}</small>
                                    </td>
                                    <td>
                                        <div class="fw-bold text-dark">{{ $sp->tenant->name ?? 'Unknown' }}</div>
                                        <small class="text-muted">{{ $sp->tenant->domain ?? '-' }}</small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">{{ $sp->order_id }}</span>
                                    </td>
                                    <td>
                                        <span class="fw-bold text-primary">Rp {{ number_format($sp->gross_amount, 0, ',', '.') }}</span>
                                    </td>
                                    <td>
                                        @if($sp->salesVoucher)
                                            <span class="badge bg-info rounded-pill mb-1">{{ $sp->salesVoucher->kode_voucher }}</span><br>
                                            <small class="text-muted"><i class="fa-solid fa-user me-1"></i> {{ $sp->salesVoucher->nama_sales }}</small>
                                        @else
                                            <span class="text-muted small">Tanpa Voucher</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="fw-bold text-success">Rp {{ number_format($sp->commission_amount, 0, ',', '.') }}</span>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i>
                                        <p>Belum ada riwayat penjualan paket.</p>
                                    </td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <!-- TAB REKAP KOMISI SALES -->
        <div class="tab-pane fade" id="rekap" role="tabpanel" aria-labelledby="rekap-tab">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-users-viewfinder text-warning me-2"></i>Rekapitulasi Performa & Komisi Sales</h5>
                </div>
                <div class="card-body p-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead class="table-light">
                                <tr>
                                    <th>Nama Sales</th>
                                    <th>No WA</th>
                                    <th>Kode Voucher</th>
                                    <th class="text-center">Total Transaksi</th>
                                    <th class="text-end">Total Penjualan (Omset)</th>
                                    <th class="text-end">Total Komisi</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($rekapSales as $rs)
                                <tr>
                                    <td>
                                        <span class="fw-bold text-dark"><i class="fa-solid fa-user-tie text-muted me-2"></i>{{ $rs['nama_sales'] }}</span>
                                    </td>
                                    <td>{{ $rs['no_wa_sales'] }}</td>
                                    <td><span class="badge bg-primary rounded-pill">{{ $rs['kode_voucher'] }}</span></td>
                                    <td class="text-center fw-bold">{{ $rs['jumlah_transaksi'] }}</td>
                                    <td class="text-end fw-bold text-primary">Rp {{ number_format($rs['total_penjualan'], 0, ',', '.') }}</td>
                                    <td class="text-end fw-bold text-success">Rp {{ number_format($rs['total_komisi'], 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center text-muted py-5">
                                        <i class="fa-solid fa-users-slash fa-3x mb-3 text-light"></i>
                                        <p>Belum ada data komisi sales.</p>
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

<!-- Modal Tambah Pengeluaran -->
<div class="modal fade" id="addExpenseModal" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <form class="modal-content border-0 shadow rounded-4" action="{{ route('superadmin.finance.store') }}" method="POST">
            @csrf
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-file-invoice text-danger me-2"></i>Input Pengeluaran Baru</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="mb-3">
                    <label class="form-label fw-bold">Tanggal</label>
                    <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nama Pengeluaran</label>
                    <input type="text" name="nama_pengeluaran" class="form-control" placeholder="Contoh: Pembayaran Server AWS" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Kategori (Opsional)</label>
                    <input type="text" name="kategori" class="form-control" placeholder="Contoh: Infrastruktur / Marketing / Gaji">
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Nominal (Rp)</label>
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0">Rp</span>
                        <input type="number" name="nominal" class="form-control border-start-0" min="0" placeholder="0" required>
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold">Keterangan Tambahan (Opsional)</label>
                    <textarea name="keterangan" class="form-control" rows="2" placeholder="Tuliskan detail jika perlu..."></textarea>
                </div>
            </div>
            <div class="modal-footer border-top-0 pt-0 pb-4 px-4">
                <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                <button type="submit" class="btn btn-primary rounded-pill px-4"><i class="fa-solid fa-save me-1"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>
@endsection
