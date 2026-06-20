@extends('layouts.app')

@section('title', 'Pengaturan Payment Gateway')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Pengaturan Payment Gateway</h2>
            <p class="text-muted mb-0">Konfigurasi metode pembayaran untuk toko Anda.</p>
        </div>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i>{{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>{{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <form action="{{ route('dashboard.pengaturan.payment.update') }}" method="POST">
        @csrf
        
        <div class="row">
            <!-- Xendit Configuration -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-primary mb-0"><i data-lucide="wallet" class="me-2"></i>Xendit (Tagihan via WhatsApp)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Aktifkan Xendit untuk membuat Link Pembayaran (Payment Link) otomatis saat tagihan dikirimkan lewat WhatsApp Bot.</p>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="is_payment_gateway_active" name="is_payment_gateway_active" value="1" {{ old('is_payment_gateway_active', $identitas->is_payment_gateway_active ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="is_payment_gateway_active">Aktifkan Xendit</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Xendit Secret API Key</label>
                            <input type="password" class="form-control" name="xendit_api_key" value="{{ old('xendit_api_key', $identitas->xendit_api_key ?? '') }}" placeholder="xnd_production_..." autocomplete="new-password">
                            <small class="text-muted d-block mt-1">Gunakan API Key tipe Secret dari dashboard Xendit Anda.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Xendit Webhook Token</label>
                            <input type="password" class="form-control" name="xendit_webhook_token" value="{{ old('xendit_webhook_token', $identitas->xendit_webhook_token ?? '') }}" placeholder="Token untuk verifikasi webhook">
                            <small class="text-muted d-block mt-1">Dapatkan dari pengaturan Callback/Webhook di dashboard Xendit.</small>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Midtrans Configuration -->
            <div class="col-md-6 mb-4">
                <div class="card border-0 shadow-sm h-100 rounded-4">
                    <div class="card-header bg-white border-0 pt-4 pb-0">
                        <h5 class="fw-bold text-primary mb-0"><i data-lucide="credit-card" class="me-2"></i>Midtrans (Katalog & Portal)</h5>
                    </div>
                    <div class="card-body">
                        <p class="text-muted small">Aktifkan Midtrans (Snap) agar pelanggan bisa langsung melakukan pembayaran secara online ketika memesan melalui Katalog (Self-Order).</p>
                        
                        <div class="mb-4">
                            <div class="form-check form-switch form-switch-lg">
                                <input class="form-check-input" type="checkbox" id="is_midtrans_active" name="is_midtrans_active" value="1" {{ old('is_midtrans_active', $identitas->is_midtrans_active ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label ms-2" for="is_midtrans_active">Aktifkan Midtrans</label>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Midtrans Server Key</label>
                            <input type="password" class="form-control" name="midtrans_server_key" value="{{ old('midtrans_server_key', $identitas->midtrans_server_key ?? '') }}" placeholder="SB-Mid-server-..." autocomplete="new-password">
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Midtrans Client Key</label>
                            <input type="text" class="form-control" name="midtrans_client_key" value="{{ old('midtrans_client_key', $identitas->midtrans_client_key ?? '') }}" placeholder="SB-Mid-client-...">
                        </div>
                        <div class="mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="midtrans_is_production" name="midtrans_is_production" value="1" {{ old('midtrans_is_production', $identitas->midtrans_is_production ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-semibold text-danger" for="midtrans_is_production">
                                    Gunakan Production Mode (Live)
                                </label>
                            </div>
                            <small class="text-muted">Biarkan tidak dicentang jika masih menggunakan akun Sandbox/Testing.</small>
                        </div>
                        <div class="mt-4 p-3 bg-light rounded border">
                            <h6 class="fw-bold mb-1">Webhook URL Midtrans:</h6>
                            <code class="d-block mb-2">{{ url('/api/webhook/tenant/midtrans') }}</code>
                            <small class="text-muted d-block">Salin URL di atas ke menu <strong>Settings > Configuration > Payment Notification URL</strong> di dashboard Midtrans Anda.</small>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm rounded-4 mb-4">
            <div class="card-body bg-light rounded-4">
                <div class="d-flex align-items-center">
                    <i data-lucide="info" class="text-primary me-3" style="width: 24px; height: 24px;"></i>
                    <p class="mb-0 text-muted small">
                        <strong>Catatan:</strong> Anda bisa mengaktifkan keduanya secara bersamaan. Xendit akan digunakan saat sistem Bot WhatsApp mengirim tagihan. Sedangkan Midtrans akan otomatis digunakan sebagai *popup payment* saat pelanggan melakukan *checkout* di halaman Katalog Anda.
                    </p>
                </div>
            </div>
        </div>

        <div class="text-end">
            <button type="submit" class="btn btn-primary rounded-pill px-5 fw-bold">
                <i data-lucide="save" class="me-2"></i>Simpan Pengaturan
            </button>
        </div>
    </form>
</div>

<style>
    .form-switch-lg .form-check-input {
        width: 3rem;
        height: 1.5rem;
    }
</style>
@endsection
