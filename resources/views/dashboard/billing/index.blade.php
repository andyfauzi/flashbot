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
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-muted mb-4">Paket Anda Saat Ini</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="display-5 fw-bold text-primary text-capitalize me-3">{{ $tenant->plan ?? 'Starter' }}</div>
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
                    <hr>
                    <p class="mb-1"><strong>Berlaku hingga:</strong></p>
                    <p class="text-dark fs-5">{{ $tenant->plan_expires_at && !$isExpired ? $tenant->plan_expires_at->format('d M Y') : '-' }}</p>
                    
                    @if($isExpired)
                        @if($isNew)
                            <div class="alert alert-info mt-3 mb-0">
                                <i class="fa-solid fa-info-circle me-2"></i>Silakan selesaikan pembayaran untuk mulai menggunakan layanan Flashbot.
                            </div>
                        @else
                            <div class="alert alert-danger mt-3 mb-0">
                                <i class="fa-solid fa-triangle-exclamation me-2"></i>Paket Anda telah kedaluwarsa. Silakan perpanjang untuk terus menggunakan layanan.
                            </div>
                        @endif
                    @endif
                </div>
            </div>
        </div>

        <!-- Upgrade Options -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-muted mb-4">Pilihan Paket & Perpanjangan</h5>

                    <!-- Toggle Durasi Langganan -->
                    <div class="mb-4 d-flex justify-content-center">
                        <div class="btn-group" role="group">
                            <input type="radio" class="btn-check" name="plan_duration" id="duration_monthly" value="monthly" autocomplete="off" checked>
                            <label class="btn btn-outline-primary px-4 fw-bold" for="duration_monthly">Bulanan</label>

                            <input type="radio" class="btn-check" name="plan_duration" id="duration_yearly" value="yearly" autocomplete="off">
                            <label class="btn btn-outline-primary px-4 fw-bold" for="duration_yearly">Tahunan (Hemat {{ $discountPercent }}%)</label>
                        </div>
                    </div>

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
                                    <h5 class="mb-3">
                                        <span class="price-display" data-monthly="{{ $priceStarter }}" data-yearly="{{ $priceStarterYearly }}">Rp {{ number_format($priceStarter, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        @foreach($featuresStarter as $feature)
                                        <li><i class="fa-solid fa-check text-success me-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="btn btn-outline-success w-100 rounded-pill btn-upgrade" data-plan="starter">
                                        {{ $tenant->plan == 'starter' ? 'Perpanjang' : 'Pilih Starter' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-2 border-primary shadow-sm hover-shadow transition cursor-pointer text-center h-100 position-relative">
                                <div class="position-absolute top-0 start-50 translate-middle badge rounded-pill bg-primary">Populer</div>
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-primary">Pro</h4>
                                    <h5 class="mb-3">
                                        <span class="price-display" data-monthly="{{ $pricePro }}" data-yearly="{{ $priceProYearly }}">Rp {{ number_format($pricePro, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        @foreach($featuresPro as $feature)
                                        <li><i class="fa-solid fa-check text-primary me-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="btn btn-primary w-100 rounded-pill btn-upgrade text-white fw-bold shadow-sm" data-plan="pro">
                                        {{ $tenant->plan == 'pro' ? 'Perpanjang' : 'Upgrade ke Pro' }}
                                    </button>
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
                                    <h5 class="mb-3">
                                        <span class="price-display" data-monthly="{{ $priceBusiness }}" data-yearly="{{ $priceBusinessYearly }}">Rp {{ number_format($priceBusiness, 0, ',', '.') }}</span>
                                        <small class="text-muted duration-label">/bln</small>
                                    </h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        @foreach($featuresBusiness as $feature)
                                        <li><i class="fa-solid fa-check text-dark me-2"></i>{{ $feature }}</li>
                                        @endforeach
                                    </ul>
                                    <button class="btn btn-outline-dark w-100 rounded-pill btn-upgrade fw-bold" data-plan="business">
                                        {{ $tenant->plan == 'business' ? 'Perpanjang' : 'Upgrade Business' }}
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Riwayat Transaksi -->
    <div class="card border-0 shadow-sm rounded-4 mt-2">
        <div class="card-body p-4">
            <h5 class="fw-bold text-muted mb-4"><i class="fa-solid fa-clock-rotate-left me-2"></i>Riwayat Pembayaran</h5>
            <div class="table-responsive">
                <table class="table table-hover align-middle">
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
    // Toggle Durasi
    document.querySelectorAll('input[name="plan_duration"]').forEach(radio => {
        radio.addEventListener('change', function() {
            const duration = this.value; // 'monthly' or 'yearly'
            document.querySelectorAll('.price-display').forEach(el => {
                const amount = el.getAttribute('data-' + duration);
                el.innerText = 'Rp ' + parseInt(amount).toLocaleString('id-ID');
            });
            document.querySelectorAll('.duration-label').forEach(el => {
                el.innerText = duration === 'yearly' ? '/thn' : '/bln';
            });
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

    document.querySelectorAll('.btn-upgrade').forEach(button => {
        button.addEventListener('click', function() {
            const plan = this.getAttribute('data-plan');
            const btnOriginalText = this.innerHTML;
            this.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Memproses...';
            this.disabled = true;

            // Panggil API Checkout
            fetch('{{ route("dashboard.billing.checkout") }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ 
                    plan: plan,
                    duration: document.querySelector('input[name="plan_duration"]:checked').value,
                    voucher_code: currentVoucher
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
                    // Show fallback modal
                    document.getElementById('fallbackOrderId').innerText = data.order_id;
                    
                    const waNumber = '{{ $waNumber ?? "6281234567890" }}';
                    const waMessage = `Halo admin, saya ingin mengkonfirmasi pembayaran manual untuk berlangganan.\n\nOrder ID: *${data.order_id}*\nPaket: *${plan.toUpperCase()}*\nMohon segera diproses. Berikut saya lampirkan bukti transfer.`;
                    const waUrl = `https://wa.me/${waNumber}?text=${encodeURIComponent(waMessage)}`;
                    document.getElementById('btnConfirmWhatsApp').href = waUrl;
                    
                    const modal = new bootstrap.Modal(document.getElementById('fallbackPaymentModal'));
                    modal.show();
                    
                    button.innerHTML = btnOriginalText;
                    button.disabled = false;
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
                            button.innerHTML = btnOriginalText;
                            button.disabled = false;
                        },
                        onClose: function(){
                            button.innerHTML = btnOriginalText;
                            button.disabled = false;
                        }
                    });
                } else {
                    alert('Gagal mengambil token pembayaran. ' + (data.error || ''));
                    button.innerHTML = btnOriginalText;
                    button.disabled = false;
                }
            })
            .catch(error => {
                console.error('Billing Error:', error);
                alert('❌ Gagal memproses pembayaran:\n' + error.message + '\n\nSilakan coba refresh halaman dan ulangi.');
                button.innerHTML = btnOriginalText;
                button.disabled = false;
            });
        });
    });
</script>
@endsection
