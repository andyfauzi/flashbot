@extends('layouts.app')

@section('title', 'Tagihan & Paket')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-credit-card me-2"></i> Tagihan & Paket Langganan
        </h2>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm">
            <i class="fa-solid fa-exclamation-circle me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('info'))
        <div class="alert alert-info alert-dismissible fade show shadow-sm">
            <i class="fa-solid fa-info-circle me-2"></i>{{ session('info') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    
    <div class="row">
        <!-- Current Plan Info -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-4">
                    <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center">
                        <div>
                            <h5 class="fw-bold text-muted mb-2">Paket Anda Saat Ini</h5>
                            <div class="d-flex align-items-center">
                                <div class="display-6 fw-bold text-primary text-capitalize me-3">{{ $tenant->plan ?? 'Starter' }}</div>
                                @php
                                    $isExpired = $tenant->plan_expires_at && $tenant->plan_expires_at < now();
                                    $isNew = $tenant->created_at && $tenant->created_at->diffInDays(now()) < 1;
                                @endphp
                                
                                @if($isExpired && $isNew)
                                    <span class="badge bg-warning text-dark rounded-pill px-3 py-2">Menunggu Pembayaran</span>
                                @elseif($isExpired)
                                    <span class="badge bg-danger rounded-pill px-3 py-2">Kedaluwarsa</span>
                                @elseif($tenant->is_active)
                                    <span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill px-3 py-2">Nonaktif</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-md-end mt-4 mt-md-0">
                            <p class="mb-1 text-muted">Berlaku hingga:</p>
                            <p class="text-dark fs-4 fw-bold mb-0">{{ $tenant->plan_expires_at && !$isExpired ? $tenant->plan_expires_at->format('d M Y') : '-' }}</p>
                        </div>
                    </div>
                    
                    @if($isExpired)
                        <hr class="my-4">
                        @if($isNew)
                            <div class="alert alert-info mb-0 border-0 bg-info bg-opacity-10 text-info-emphasis">
                                <i class="fa-solid fa-info-circle me-2"></i>Silakan selesaikan pembayaran untuk mulai menggunakan layanan Flashbot.
                            </div>
                        @else
                            <div class="alert alert-danger mb-0 border-0 bg-danger bg-opacity-10 text-danger-emphasis">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Paket Anda telah kedaluwarsa. Silakan perpanjang untuk terus menggunakan layanan.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Upgrade Options -->
        <div class="col-12 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-muted mb-4">Pilihan Paket & Perpanjangan</h5>

                    <!-- Toggle Durasi Langganan Dihapus, diganti tombol masing-masing -->

                    <div class="mb-4">
                        <label for="voucher_code" class="form-label fw-bold">Kode Voucher Diskon</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-ticket text-muted"></i></span>
                            <input type="text" id="voucher_code" class="form-control border-start-0" placeholder="Masukkan kode voucher..." style="text-transform: uppercase;">
                            <button class="btn btn-outline-secondary" type="button" id="btn-check-voucher">Cek Voucher</button>
                        </div>
                        <div id="voucher-message" class="mt-2 small fw-bold"></div>
                        <input type="hidden" id="applied_voucher" value="">
                    </div>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-2 border-light hover-shadow transition cursor-pointer text-center h-100">
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-success">Starter</h4>
                                    <h5 class="mb-1">
                                        <span class="price-display">Rp {{ number_format($priceStarter, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <div class="small text-success fw-bold mb-3">
                                        Tahunan: Rp {{ number_format($priceStarterYearly, 0, ',', '.') }} /thn (Hemat {{ $discountPercentStarter }}%)
                                    </div>
                                    <ul class="list-unstyled text-start small mb-4">


                                    </ul>
                                    <div class="d-flex flex-column gap-2 mt-auto pt-3">
                                        <button class="btn btn-outline-success w-100 rounded-pill btn-upgrade fw-bold" data-plan="starter" data-duration="monthly">
                                            {{ $tenant->plan == 'starter' ? 'Perpanjang Bulanan' : 'Pilih Bulanan' }}
                                        </button>
                                        <button class="btn btn-success w-100 rounded-pill btn-upgrade fw-bold shadow-sm" data-plan="starter" data-duration="yearly">
                                            {{ $tenant->plan == 'starter' ? 'Perpanjang Tahunan' : 'Pilih Tahunan' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-2 border-primary shadow-sm hover-shadow transition cursor-pointer text-center h-100 position-relative">
                                <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary">Populer</div>
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-primary">Pro</h4>
                                    <h5 class="mb-1">
                                        <span class="price-display">Rp {{ number_format($pricePro, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <div class="small text-primary fw-bold mb-3">
                                        Tahunan: Rp {{ number_format($priceProYearly, 0, ',', '.') }} /thn (Hemat {{ $discountPercentPro }}%)
                                    </div>
                                    <ul class="list-unstyled text-start small mb-4">


                                    </ul>
                                    <div class="d-flex flex-column gap-2 mt-auto pt-3">
                                        <button class="btn btn-outline-primary w-100 rounded-pill btn-upgrade fw-bold" data-plan="pro" data-duration="monthly">
                                            {{ $tenant->plan == 'pro' ? 'Perpanjang Bulanan' : 'Pilih Bulanan' }}
                                        </button>
                                        <button class="btn btn-primary w-100 rounded-pill btn-upgrade text-white fw-bold shadow-sm" data-plan="pro" data-duration="yearly">
                                            {{ $tenant->plan == 'pro' ? 'Perpanjang Tahunan' : 'Pilih Tahunan' }}
                                        </button>
                                    </div>
                                    @php
                                        $isNewUser = $tenant->created_at && $tenant->created_at->diffInDays(now()) < 1;
                                        $isExpired = $tenant->plan_expires_at && $tenant->plan_expires_at < now();
                                    @endphp
                                    @if($isExpired && $isNewUser)
                                    <form action="{{ route('dashboard.billing.trial') }}" method="POST" class="mt-2">
                                        @csrf
                                        <button type="submit" class="btn btn-outline-primary w-100 rounded-pill fw-bold" onclick="return confirm('Mulai masa coba gratis 30 hari sekarang?')">
                                            <i class="fa-solid fa-gift me-1"></i> Coba Gratis 30 Hari
                                        </button>
                                    </form>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-2 border-dark hover-shadow transition cursor-pointer text-center h-100">
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-dark">Business</h4>
                                    <h5 class="mb-1">
                                        <span class="price-display">Rp {{ number_format($priceBusiness, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <div class="small text-dark fw-bold mb-3">
                                        Tahunan: Rp {{ number_format($priceBusinessYearly, 0, ',', '.') }} /thn (Hemat {{ $discountPercentBusiness }}%)
                                    </div>
                                    <ul class="list-unstyled text-start small mb-4">


                                    </ul>
                                    <div class="d-flex flex-column gap-2 mt-auto pt-3">
                                        <button class="btn btn-outline-dark w-100 rounded-pill btn-upgrade fw-bold" data-plan="business" data-duration="monthly">
                                            {{ $tenant->plan == 'business' ? 'Perpanjang Bulanan' : 'Pilih Bulanan' }}
                                        </button>
                                        <button class="btn btn-dark w-100 rounded-pill btn-upgrade fw-bold shadow-sm" data-plan="business" data-duration="yearly">
                                            {{ $tenant->plan == 'business' ? 'Perpanjang Tahunan' : 'Pilih Tahunan' }}
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-muted mb-4"><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat Pembayaran</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Order ID</th>
                            <th>Paket</th>
                            <th>Nominal</th>
                            <th>Tanggal</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($payments as $payment)
                        <tr>
                            <td class="font-monospace text-muted">{{ $payment->order_id }}</td>
                            <td class="text-capitalize fw-bold">{{ $payment->plan_name }}</td>
                            <td>Rp {{ number_format($payment->gross_amount, 0, ',', '.') }}</td>
                            <td>{{ $payment->created_at->format('d M Y H:i') }}</td>
                            <td>
                                @if($payment->status == 'settlement' || $payment->status == 'capture')
                                    <span class="badge bg-success">Lunas</span>
                                @elseif($payment->status == 'pending')
                                    <span class="badge bg-warning text-dark">Menunggu Pembayaran</span>
                                @else
                                    <span class="badge bg-danger text-capitalize">{{ $payment->status }}</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="text-center text-muted py-4">Belum ada riwayat pembayaran.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Comparison Table for Billing Page -->
    @if(isset($packageMenus) && count($packageMenus) > 0)
    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <h5 class="fw-bold text-muted mb-4"><i class="fa-solid fa-list-check me-2"></i>Bandingkan Semua Fitur</h5>
            <div class="table-responsive bg-white rounded-4 shadow-sm border">
                <table class="table table-hover align-middle mb-0" style="min-width: 800px;">
                    <thead>
                        <tr class="bg-light">
                            <th class="py-3 px-4 w-40 border-0 fw-semibold text-secondary" style="position: sticky; left: 0; background-color: #f8fafc; z-index: 2;">Fitur Utama</th>
                            <th class="text-center py-3 px-4 w-20 border-0 text-success fw-bold">Starter</th>
                            <th class="text-center py-3 px-4 w-20 border-0 text-primary fw-bold">Pro</th>
                            <th class="text-center py-3 px-4 w-20 border-0 text-dark fw-bold">Business</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- Limit Karyawan -->
                        @if(($settings['show_limit_karyawan'] ?? '1') == '1')
                        <tr>
                            <td class="py-3 px-4 fw-medium" style="position: sticky; left: 0; background-color: #fff; z-index: 1;"><i class="fa-solid fa-users text-muted me-2"></i> Limit Karyawan</td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_karyawan_starter'] ?? 2) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_karyawan_starter'] ?? 2) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_starter'] ?? 2) }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_karyawan_pro'] ?? 10) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_karyawan_pro'] ?? 10) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_pro'] ?? 10) }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_karyawan_business'] ?? 999) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_karyawan_business'] ?? 999) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_business'] ?? 999) }} @endif
                            </td>
                        </tr>
                        @endif
                        
                        <!-- Limit Bot WA -->
                        @if(($settings['show_limit_wa'] ?? '1') == '1')
                        <tr>
                            <td class="py-3 px-4 fw-medium" style="position: sticky; left: 0; background-color: #fff; z-index: 1;"><i class="fa-solid fa-robot text-muted me-2"></i> Pesan Bot WA/bln</td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_wa_starter'] ?? 1000) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_wa_starter'] ?? 1000) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_starter'] ?? 1000), 0, ',', '.') }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_wa_pro'] ?? 5000) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_wa_pro'] ?? 5000) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_pro'] ?? 5000), 0, ',', '.') }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold">
                                @if(($settings['limit_wa_business'] ?? 999999) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_wa_business'] ?? 999999) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_business'] ?? 999999), 0, ',', '.') }} @endif
                            </td>
                        </tr>
                        @endif
                        
                        <!-- Limit Device WA -->
                        @if(($settings['show_limit_device'] ?? '1') == '1')
                        <tr>
                            <td class="py-3 px-4 fw-medium border-bottom-0" style="position: sticky; left: 0; background-color: #fff; z-index: 1;"><i class="fa-solid fa-mobile-screen text-muted me-2"></i> Device WA Terhubung</td>
                            <td class="text-center py-3 px-4 fw-bold border-bottom-0">
                                @if(($settings['limit_device_starter'] ?? 1) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_device_starter'] ?? 1) >= 999 ? 'Unlimited' : ($settings['limit_device_starter'] ?? 1) }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold border-bottom-0">
                                @if(($settings['limit_device_pro'] ?? 3) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_device_pro'] ?? 3) >= 999 ? 'Unlimited' : ($settings['limit_device_pro'] ?? 3) }} @endif
                            </td>
                            <td class="text-center py-3 px-4 fw-bold border-bottom-0">
                                @if(($settings['limit_device_business'] ?? 10) == 0) <i class="fa-solid fa-xmark text-muted opacity-50 fs-5"></i>
                                @else {{ ($settings['limit_device_business'] ?? 10) >= 999 ? 'Unlimited' : ($settings['limit_device_business'] ?? 10) }} @endif
                            </td>
                        </tr>
                        @endif
                        
                        <tr><td colspan="4" class="bg-light py-2 px-4 text-muted small fw-semibold text-uppercase">Rincian Fitur Modul</td></tr>

                        @foreach($packageMenus as $menu)
                            @if($menu->show_on_landing_page)
                            <tr>
                                <td class="py-3 px-4 fw-medium" style="position: sticky; left: 0; background-color: #fff; z-index: 1;">{{ $menu->menu_label }}</td>
                                <td class="text-center py-3 px-4">
                                    @if($menu->starter_enabled) <i class="fa-solid fa-check text-success fs-5"></i> @else <i class="fa-solid fa-xmark text-muted opacity-50"></i> @endif
                                </td>
                                <td class="text-center py-3 px-4">
                                    @if($menu->pro_enabled) <i class="fa-solid fa-check text-primary fs-5"></i> @else <i class="fa-solid fa-xmark text-muted opacity-50"></i> @endif
                                </td>
                                <td class="text-center py-3 px-4">
                                    @if($menu->business_enabled) <i class="fa-solid fa-check text-dark fs-5"></i> @else <i class="fa-solid fa-xmark text-muted opacity-50"></i> @endif
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    @endif
</div>

<!-- Modal Pilih Metode Pembayaran -->
<div class="modal fade" id="choosePaymentMethodModal" tabindex="-1" aria-labelledby="choosePaymentMethodModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="choosePaymentMethodModalLabel">
                    <i class="fa-solid fa-wallet text-primary me-2"></i>Pilih Metode Pembayaran
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body pb-4">
                <p class="text-muted mb-4">Silakan pilih metode pembayaran untuk melanjutkan berlangganan.</p>
                <div class="d-grid gap-3">
                    <button class="btn btn-outline-primary py-3 text-start fw-bold fs-6 rounded-3 btn-method-select" data-method="midtrans">
                        <div class="d-flex align-items-center">
                            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle me-3">
                                <i class="fa-solid fa-bolt fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-dark">Midtrans (Otomatis)</div>
                                <div class="small fw-normal text-muted mt-1">QRIS, Virtual Account, E-Wallet, dll. Verifikasi Instan.</div>
                            </div>
                        </div>
                    </button>
                    <button class="btn btn-outline-dark py-3 text-start fw-bold fs-6 rounded-3 btn-method-select" data-method="manual">
                        <div class="d-flex align-items-center">
                            <div class="bg-dark bg-opacity-10 text-dark p-3 rounded-circle me-3">
                                <i class="fa-solid fa-building-columns fa-lg"></i>
                            </div>
                            <div>
                                <div class="text-dark">Transfer Manual</div>
                                <div class="small fw-normal text-muted mt-1">Transfer antar bank. Perlu konfirmasi admin via WhatsApp.</div>
                            </div>
                        </div>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Fallback Pembayaran Manual -->
<div class="modal fade" id="fallbackPaymentModal" tabindex="-1" aria-labelledby="fallbackPaymentModalLabel" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 rounded-4 shadow">
            <div class="modal-header border-bottom-0 pb-0">
                <h5 class="modal-title fw-bold text-dark" id="fallbackPaymentModalLabel">
                    <i class="fa-solid fa-building-columns text-primary me-2"></i>Pembayaran Manual
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-4">
                    <i class="fa-solid fa-triangle-exclamation me-2"></i> Sistem pembayaran otomatis sedang dalam pemeliharaan. Silakan lakukan transfer manual ke rekening berikut.
                </div>
                
                <h6 class="fw-bold mb-2">Instruksi Pembayaran:</h6>
                <div class="bg-light rounded p-3 mb-4 font-monospace" style="font-size: 0.95rem; white-space: pre-wrap;">{{ \App\Models\LandlordSetting::get('payment_instructions_fallback', "BCA: 1234567890\nA.n PT Tenanta Inovasi") }}</div>
                
                <p class="mb-1 text-muted small">Order ID Anda:</p>
                <p class="fw-bold font-monospace fs-5 text-dark" id="fallbackOrderId">-</p>
                
                <div class="text-center mt-4 pt-3 border-top">
                    <p class="text-muted small mb-2">Setelah transfer, mohon konfirmasi ke WhatsApp kami dengan melampirkan bukti transfer dan Order ID di atas.</p>
                    @php
                        $waNumber = \App\Models\LandlordSetting::get('whatsapp_confirmation_number', '6281234567890');
                    @endphp
                    <a href="#" id="btnConfirmWhatsApp" target="_blank" class="btn btn-success w-100 rounded-pill fw-bold py-2">
                        <i class="fa-brands fa-whatsapp me-2"></i> Konfirmasi via WhatsApp
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<!-- Midtrans Snap JS -->
@php
    $midtransIsProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';
    $midtransClientKey = \App\Models\LandlordSetting::get('midtrans_client_key', env('MIDTRANS_CLIENT_KEY'));
    $snapUrl = $midtransIsProduction ? 'https://app.midtrans.com/snap/snap.js' : 'https://app.sandbox.midtrans.com/snap/snap.js';
@endphp
<script src="{{ $snapUrl }}" data-client-key="{{ $midtransClientKey }}"></script>
<script>
    // Handle Collapse Toggle Text Correctly
    document.querySelectorAll('.collapse-toggle-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            setTimeout(() => {
                const isExpanded = this.getAttribute('aria-expanded') === 'true';
                if(isExpanded) {
                    this.innerHTML = 'Sembunyikan Fitur <i class="fa-solid fa-chevron-up"></i>';
                } else {
                    this.innerHTML = 'Lihat Semua Fitur <i class="fa-solid fa-chevron-down"></i>';
                }
            }, 50); // delay slighty to ensure aria-expanded is updated by bootstrap
        });
    });

    let currentVoucher = '';

    document.getElementById('btn-check-voucher').addEventListener('click', function() {
        const code = document.getElementById('voucher_code').value.trim();
        const messageEl = document.getElementById('voucher-message');
        
        if (!code) {
            messageEl.innerHTML = '<span class="text-danger">Silakan masukkan kode voucher.</span>';
            return;
        }

        this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Mengecek...';
        this.disabled = true;

        fetch('{{ route("dashboard.billing.check_voucher") }}', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ 
                voucher_code: code,
                plan: 'starter' // default check against starter just to see validity
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(err => { throw new Error(err.error || 'Server error'); });
            }
            return response.json();
        })
        .then(data => {
            messageEl.innerHTML = `<span class="text-success"><i class="fa-solid fa-check-circle me-1"></i> ${data.message}</span>`;
            document.getElementById('applied_voucher').value = code;
            currentVoucher = code;
            
            // Highlight the fact that prices will be discounted
            document.querySelectorAll('.btn-upgrade').forEach(btn => {
                if(!btn.classList.contains('voucher-applied')) {
                    btn.classList.add('voucher-applied');
                    btn.innerHTML += ` <span class="badge bg-warning text-dark ms-1">Diskon ${data.discount_percent}%</span>`;
                }
            });
        })
        .catch(error => {
            messageEl.innerHTML = `<span class="text-danger"><i class="fa-solid fa-times-circle me-1"></i> ${error.message}</span>`;
            document.getElementById('applied_voucher').value = '';
            currentVoucher = '';
        })
        .finally(() => {
            this.innerHTML = 'Cek Voucher';
            this.disabled = false;
        });
    });

    let selectedPlan = '';
    let selectedDuration = '';

    document.querySelectorAll('.btn-upgrade').forEach(button => {
        button.addEventListener('click', function() {
            selectedPlan = this.getAttribute('data-plan');
            selectedDuration = this.getAttribute('data-duration');
            
            // Tampilkan modal pilih metode pembayaran
            const chooseModal = new bootstrap.Modal(document.getElementById('choosePaymentMethodModal'));
            chooseModal.show();
        });
    });

    document.querySelectorAll('.btn-method-select').forEach(button => {
        button.addEventListener('click', function() {
            const method = this.getAttribute('data-method');
            const btnOriginalText = this.innerHTML;
            
            // Tutup modal pilihan metode
            bootstrap.Modal.getInstance(document.getElementById('choosePaymentMethodModal')).hide();
            
            // Cari tombol asli untuk menampilkan loading
            const originalBtn = document.querySelector(`.btn-upgrade[data-plan="${selectedPlan}"][data-duration="${selectedDuration}"]`);
            const originalBtnText = originalBtn.innerHTML;
            originalBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            originalBtn.disabled = true;

            // Panggil API Checkout
            fetch('{{ route("dashboard.billing.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    plan: selectedPlan,
                    duration: selectedDuration,
                    voucher_code: currentVoucher,
                    payment_method: method
                })
            })
            .then(response => {
                if (!response.ok) {
                    return response.json().then(err => { throw new Error(err.error || 'Server error ' + response.status); });
                }
                return response.json();
            })
            .then(data => {
                if (data.fallback) {
                    if (method === 'midtrans') {
                        // Jika pilih midtrans tapi belum disetup, beri tahu bahwa sistem otomatis belum sedia
                        alert('Sistem pembayaran otomatis belum tersedia saat ini. Kami akan mengalihkan Anda ke metode Transfer Manual.');
                    }
                    
                    // Show fallback modal
                    document.getElementById('fallbackOrderId').innerText = data.order_id;
                    
                    const waNumber = '{{ $waNumber ?? "6281234567890" }}';
                    const waMessage = `Halo admin, saya ingin mengkonfirmasi pembayaran manual untuk berlangganan.\n\nOrder ID: *${data.order_id}*\nPaket: *${selectedPlan.toUpperCase()}*\nMohon segera diproses. Berikut saya lampirkan bukti transfer.`;
                    const waUrl = `https://wa.me/${waNumber}?text=${encodeURIComponent(waMessage)}`;
                    document.getElementById('btnConfirmWhatsApp').href = waUrl;
                    
                    const modal = new bootstrap.Modal(document.getElementById('fallbackPaymentModal'));
                    modal.show();
                    
                    originalBtn.innerHTML = originalBtnText;
                    originalBtn.disabled = false;
                    return;
                }

                if (data.snap_token) {
                    // Buka popup Snap Midtrans
                    window.snap.pay(data.snap_token, {
                        onSuccess: function(result){
                            window.location.reload();
                        },
                        onPending: function(result){
                            window.location.reload();
                        },
                        onError: function(result){
                            alert("Pembayaran gagal!");
                            originalBtn.innerHTML = originalBtnText;
                            originalBtn.disabled = false;
                        },
                        onClose: function(){
                            originalBtn.innerHTML = originalBtnText;
                            originalBtn.disabled = false;
                        }
                    });
                } else {
                    alert('Gagal mengambil token pembayaran. ' + (data.error || ''));
                    originalBtn.innerHTML = originalBtnText;
                    originalBtn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Billing Error:', error);
                alert('❌ Gagal memproses pembayaran:\n' + error.message + '\n\nSilakan coba refresh halaman dan ulangi.');
                originalBtn.innerHTML = originalBtnText;
                originalBtn.disabled = false;
            });
        });
    });
</script>
@endsection
