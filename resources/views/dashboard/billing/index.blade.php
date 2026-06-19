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

    <div class="row">
        <!-- Current Plan Info -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-muted mb-4">Paket Anda Saat Ini</h5>
                    <div class="d-flex align-items-center mb-3">
                        <div class="display-5 fw-bold text-primary text-capitalize me-3">{{ $tenant->plan ?? 'Starter' }}</div>
                        @if($tenant->is_active)
                            <span class="badge bg-success rounded-pill px-3 py-2">Aktif</span>
                        @else
                            <span class="badge bg-danger rounded-pill px-3 py-2">Nonaktif / Kedaluwarsa</span>
                        @endif
                    </div>
                    <hr>
                    <p class="mb-1"><strong>Berlaku hingga:</strong></p>
                    <p class="text-dark fs-5">{{ $tenant->plan_expires_at ? $tenant->plan_expires_at->format('d M Y') : 'Tidak terbatas' }}</p>
                    
                    @if($tenant->plan_expires_at && $tenant->plan_expires_at < now())
                        <div class="alert alert-danger mt-3 mb-0">
                            <i class="fa-solid fa-triangle-exclamation me-2"></i>Paket Anda telah kedaluwarsa. Silakan perpanjang untuk terus menggunakan layanan.
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Upgrade Options -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-body p-4">
                    <h5 class="fw-bold text-muted mb-4">Pilihan Paket & Perpanjangan</h5>
                    
                    <div class="row g-3">
                        <div class="col-md-4">
                            <div class="card border-2 border-light hover-shadow transition cursor-pointer text-center h-100">
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-success">Starter</h4>
                                    <h5 class="mb-3">Rp 99.000<small class="text-muted">/bln</small></h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        <li><i class="fa-solid fa-check text-success me-2"></i>1 Cabang Toko</li>
                                        <li><i class="fa-solid fa-check text-success me-2"></i>Maks 50 Produk</li>
                                        <li><i class="fa-solid fa-check text-success me-2"></i>Maks 3 Kasir</li>
                                    </ul>
                                    <button class="btn btn-outline-success w-100 rounded-pill btn-upgrade" data-plan="starter" {{ $tenant->plan == 'starter' && $tenant->is_active ? 'disabled' : '' }}>
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
                                    <h5 class="mb-3">Rp 199.000<small class="text-muted">/bln</small></h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        <li><i class="fa-solid fa-check text-primary me-2"></i>5 Cabang Toko</li>
                                        <li><i class="fa-solid fa-check text-primary me-2"></i>Maks 500 Produk</li>
                                        <li><i class="fa-solid fa-check text-primary me-2"></i>Maks 10 Kasir</li>
                                    </ul>
                                    <button class="btn btn-primary w-100 rounded-pill btn-upgrade text-white fw-bold shadow-sm" data-plan="pro" {{ $tenant->plan == 'pro' && $tenant->is_active ? 'disabled' : '' }}>
                                        {{ $tenant->plan == 'pro' ? 'Perpanjang' : 'Upgrade ke Pro' }}
                                    </button>
                                </div>
                            </div>
                        </div>

                        <div class="col-md-4">
                            <div class="card border-2 border-dark hover-shadow transition cursor-pointer text-center h-100">
                                <div class="card-body p-4">
                                    <h4 class="fw-bold text-dark">Business</h4>
                                    <h5 class="mb-3">Rp 499.000<small class="text-muted">/bln</small></h5>
                                    <ul class="list-unstyled text-start small mb-4">
                                        <li><i class="fa-solid fa-check text-dark me-2"></i>Unlimited Cabang</li>
                                        <li><i class="fa-solid fa-check text-dark me-2"></i>Unlimited Produk</li>
                                        <li><i class="fa-solid fa-check text-dark me-2"></i>Unlimited Kasir</li>
                                    </ul>
                                    <button class="btn btn-outline-dark w-100 rounded-pill btn-upgrade fw-bold" data-plan="business" {{ $tenant->plan == 'business' && $tenant->is_active ? 'disabled' : '' }}>
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
                body: JSON.stringify({ plan: plan })
            })
            .then(response => response.json())
            .then(data => {
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
                console.error('Error:', error);
                alert('Terjadi kesalahan pada sistem.');
                button.innerHTML = btnOriginalText;
                button.disabled = false;
            });
        });
    });
</script>
@endsection
