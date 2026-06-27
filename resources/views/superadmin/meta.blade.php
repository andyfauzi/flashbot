@extends('layouts.superadmin')

@section('title', 'Konfigurasi Meta Pusat')

@section('styles')
<style>
    .header-panel {
        background: linear-gradient(135deg, #10b981 0%, #059669 100%);
        border-radius: 20px;
        padding: 30px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(16, 185, 129, 0.2);
    }
    .custom-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 24px;
    }
</style>
@endsection

@section('content')
<div class="header-panel">
    <h3 class="fw-bold mb-1"><i class="fa-brands fa-whatsapp me-2"></i> Konfigurasi Meta API Pusat</h3>
    <p class="text-white-50 mb-0">Pengaturan koneksi aplikasi WhatsApp Business API untuk seluruh tenant</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-6 mb-4">
        <div class="custom-card h-100">
            <h5 class="fw-bold mb-4">Pengaturan Kredensial Meta</h5>
            <form action="{{ route('superadmin.meta.update') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Meta Phone Number ID</label>
                    <input type="text" name="meta_phone_number_id" class="form-control" placeholder="Contoh: 1234567890" value="{{ $metaPhoneNumberId ?? '' }}" required>
                    <small class="text-muted">ID Nomor Telepon dari dashboard Meta App Anda.</small>
                </div>
                <div class="mb-4">
                    <label class="form-label fw-semibold">Meta Access Token</label>
                    <textarea name="meta_access_token" class="form-control" rows="3" placeholder="EAAB..." required>{{ $metaAccessToken ?? '' }}</textarea>
                    <small class="text-muted">Gunakan System User Access Token yang bersifat permanen.</small>
                </div>
                <button type="submit" class="btn btn-success w-100 fw-bold py-2"><i class="fa-solid fa-save me-2"></i>Simpan Konfigurasi</button>
            </form>
        </div>
    </div>
    
    <div class="col-lg-6 mb-4">
        <div class="custom-card h-100 bg-light border-0">
            <h5 class="fw-bold mb-3"><i class="fa-solid fa-circle-info text-primary me-2"></i>Informasi Webhook</h5>
            <p class="text-muted mb-3">Salin URL di bawah ini ke pengaturan Webhook aplikasi Meta Anda. URL ini digunakan secara terpusat untuk semua tenant yang menggunakan layanan Meta API.</p>
            
            <div class="mb-4">
                <label class="form-label fw-bold">Callback URL (Webhook)</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white font-monospace" value="{{ url('/api/webhook/meta') }}" readonly id="webhookUrl">
                    <button class="btn btn-outline-secondary bg-white" type="button" onclick="copyToClipboard('webhookUrl')">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
            </div>
            
            <div class="mb-3">
                <label class="form-label fw-bold">Verify Token</label>
                <div class="input-group">
                    <input type="text" class="form-control bg-white font-monospace" value="{{ env('META_WEBHOOK_VERIFY_TOKEN', 'masukkan_token_di_env') }}" readonly id="verifyToken">
                    <button class="btn btn-outline-secondary bg-white" type="button" onclick="copyToClipboard('verifyToken')">
                        <i class="fa-regular fa-copy"></i>
                    </button>
                </div>
                <small class="text-muted mt-1 d-block">Token ini diatur di file <code>.env</code> server dengan key <code>META_WEBHOOK_VERIFY_TOKEN</code>.</small>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        copyText.setSelectionRange(0, 99999); /* For mobile devices */
        navigator.clipboard.writeText(copyText.value);
        
        Swal.fire({
            icon: 'success',
            title: 'Tersalin!',
            text: 'Teks berhasil disalin ke clipboard.',
            timer: 1500,
            showConfirmButton: false,
            toast: true,
            position: 'top-end'
        });
    }
</script>
@endsection
