@extends('layouts.app')

@section('title', 'Dashboard Mitra Sales')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
                <i class="fa-solid fa-handshake text-primary me-2"></i> Dashboard Mitra Sales
            </h2>
            <p class="text-muted mt-1 mb-0">Selamat datang, <strong>{{ Auth::user()->name }}</strong></p>
        </div>
        <a href="{{ route('logout') }}" class="btn btn-outline-danger shadow-sm rounded-pill px-4">
            <i class="fa-solid fa-sign-out-alt me-1"></i> Keluar
        </a>
    </div>

    @if(session('warning'))
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('warning') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3 mb-4">
        <!-- Card Total Penjualan -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-primary text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white-50 text-uppercase mb-0">Total Penjualan Saya</h6>
                        <i class="fa-solid fa-chart-line fa-2x text-white-50"></i>
                    </div>
                    <h2 class="fw-bold mb-0">Rp {{ number_format($totalPenjualan, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>

        <!-- Card Total Komisi -->
        <div class="col-md-6">
            <div class="card border-0 shadow-sm rounded-4 bg-success text-white h-100">
                <div class="card-body p-4">
                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold text-white-50 text-uppercase mb-0">Total Komisi Didapatkan</h6>
                        <i class="fa-solid fa-wallet fa-2x text-white-50"></i>
                    </div>
                    <h2 class="fw-bold mb-0">Rp {{ number_format($totalKomisi, 0, ',', '.') }}</h2>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Kode Voucher -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-ticket text-warning me-2"></i>Kode Voucher Anda</h5>
        </div>
        <div class="card-body p-4">
            <div class="d-flex flex-wrap gap-2">
                @forelse($vouchers as $voucher)
                    <span class="badge bg-light text-dark border p-2 px-3 rounded-pill fs-6">
                        <i class="fa-solid fa-tag text-primary me-1"></i> {{ $voucher->kode_voucher }}
                        <small class="text-muted ms-2">(Komisi: {{ $voucher->komisi_persen }}%)</small>
                    </span>
                @empty
                    <p class="text-muted mb-0">Belum ada kode voucher yang ditautkan.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Riwayat Penjualan -->
    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-header bg-white border-0 pt-4 pb-0 px-4">
            <h5 class="fw-bold mb-0"><i class="fa-solid fa-list text-muted me-2"></i>Riwayat Penjualan Saya</h5>
        </div>
        <div class="card-body p-4">
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th>Tanggal & Waktu</th>
                            <th>Toko / Tenant</th>
                            <th>Kode Voucher</th>
                            <th>Nominal Pembayaran</th>
                            <th>Komisi Saya</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td>
                                <div class="fw-bold text-dark">{{ $payment->paid_at->format('d M Y') }}</div>
                                <div class="small text-muted">{{ $payment->paid_at->format('H:i') }}</div>
                            </td>
                            <td>
                                <span class="fw-bold">{{ $payment->tenant->name ?? 'Unknown Tenant' }}</span>
                            </td>
                            <td>
                                <span class="badge bg-primary rounded-pill">{{ $payment->salesVoucher->kode_voucher ?? '-' }}</span>
                            </td>
                            <td>
                                <span class="fw-bold">Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</span>
                            </td>
                            <td>
                                <span class="fw-bold text-success">+ Rp {{ number_format($payment->commission_amount, 0, ',', '.') }}</span>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-5">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i>
                                <p>Belum ada riwayat penjualan dari kode voucher Anda.</p>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
