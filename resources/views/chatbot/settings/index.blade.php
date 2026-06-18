@extends('layouts.app')

@section('title', 'Konfigurasi Meta WhatsApp API')

@section('content')
<div class="container-fluid">

    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">
                <i class="fa-brands fa-meta text-primary me-2"></i> Konfigurasi Meta WhatsApp API
            </h2>
            <p class="text-secondary mb-0 small">Atur koneksi gateway resmi Meta · Webhook via Ngrok</p>
        </div>
        <a href="{{ route('chatbot.dashboard') }}" class="btn btn-outline-secondary rounded-4 px-4">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success border-0 rounded-4 d-flex align-items-center gap-2 px-4 py-3 mb-4">
            <i class="fa-solid fa-circle-check fs-5"></i>
            <span>{{ session('sukses') }}</span>
        </div>
    @endif

    @if($errors->any())
        <div class="alert alert-danger border-0 rounded-4 px-4 py-3 mb-4">
            <i class="fa-solid fa-triangle-exclamation me-2"></i>
            @foreach($errors->all() as $err) {{ $err }} @endforeach
        </div>
    @endif

    <div class="row g-4">

        <!-- =============================================
             KOLOM KIRI: Form Konfigurasi
        ============================================= -->
        <div class="col-lg-7">

            <!-- Status Koneksi -->
            <div class="card-premium p-4 mb-4">
                <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-signal me-2 text-primary"></i> Status Koneksi
                </h5>
                @if($status['status'] === 'connected')
                    <div class="alert alert-success border-0 rounded-4 d-flex align-items-center gap-3 py-3">
                        <i class="fa-solid fa-circle-check fs-3"></i>
                        <div>
                            <div class="fw-bold">Terhubung ke Meta!</div>
                            @if(isset($status['verified_name']))
                                <div class="small">Akun: <strong>{{ $status['verified_name'] }}</strong></div>
                            @endif
                            @if(isset($status['phone_number']))
                                <div class="small">Nomor: <strong>{{ $status['phone_number'] }}</strong></div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-3 py-3">
                        <i class="fa-solid fa-plug-circle-xmark fs-3"></i>
                        <div>
                            <div class="fw-bold">Belum terhubung</div>
                            <div class="small text-muted">{{ $status['message'] ?? 'Isi form di bawah untuk mengaktifkan.' }}</div>
                        </div>
                    </div>
                @endif
            </div>

            <!-- Form Konfigurasi -->
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-4" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-gear me-2 text-primary"></i> Pengaturan API
                </h5>

                <form action="{{ route('chatbot.settings.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf

                    <!-- Phone Number ID -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Phone Number ID <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="phone_number_id" id="phone_number_id"
                               class="form-control form-control-premium @error('phone_number_id') is-invalid @enderror"
                               value="{{ old('phone_number_id', $config['phone_number_id']) }}"
                               placeholder="123456789012345"
                               required>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Dapatkan dari: <strong>Meta Developer Console</strong> → WhatsApp → API Setup → Phone Number ID
                        </div>
                    </div>

                    <!-- Access Token -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Access Token (Permanent)
                        </label>
                        <div class="input-group">
                            <input type="password" name="access_token" id="access_token"
                                   class="form-control form-control-premium @error('access_token') is-invalid @enderror"
                                   value=""
                                   placeholder="{{ $config['access_token'] ? $config['access_token'] : 'EAAxxxxxxxxxxxxxxx' }}">
                            <button type="button" class="btn btn-outline-secondary" id="toggleToken"
                                    onclick="toggleVisibility('access_token', 'tokenIcon')">
                                <i class="fa-solid fa-eye" id="tokenIcon"></i>
                            </button>
                        </div>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Biarkan kosong jika tidak ingin mengubah token saat ini. Gunakan <strong>System User Token</strong>.
                        </div>
                    </div>

                    <!-- Verify Token -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Webhook Verify Token <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="verify_token" id="verify_token"
                               class="form-control form-control-premium @error('verify_token') is-invalid @enderror"
                               value="{{ old('verify_token', $config['verify_token']) }}"
                               required>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            String bebas yang Anda buat sendiri. Harus sama persis saat mendaftarkan webhook di Meta Console.
                        </div>
                    </div>

                    <!-- API Version -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">API Version</label>
                        <select name="api_version" class="form-select form-control-premium">
                            @foreach(['v20.0', 'v19.0', 'v18.0'] as $v)
                                <option value="{{ $v }}" {{ $config['api_version'] === $v ? 'selected' : '' }}>{{ $v }}</option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Ngrok URL -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Ngrok Public URL
                        </label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-light">
                                <i class="fa-solid fa-link text-primary"></i>
                            </span>
                            <input type="url" name="ngrok_public_url" id="ngrok_public_url"
                                   class="form-control form-control-premium"
                                   value="{{ old('ngrok_public_url', $config['ngrok_public_url']) }}"
                                   placeholder="https://xxxx-xxx-xxx.ngrok-free.app">
                        </div>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            URL publik dari ngrok (tanpa slash di akhir). URL webhook otomatis: <code>URL_ngrok/webhook/whatsapp</code>
                        </div>
                    </div>

                    <!-- PENGATURAN TOKO & PEMBAYARAN -->
                    <h5 class="fw-bold my-4 pt-3 border-top" style="font-family: var(--font-heading);">
                        <i class="fa-solid fa-store me-2 text-success"></i> Pengaturan Toko & Pembayaran
                    </h5>

                    <!-- Grup Penjualan -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Grup WhatsApp Penjualan (Seller/Admin Group)
                        </label>
                        <select name="group_id_seller" class="form-select form-control-premium">
                            <option value="">-- Pilih Grup Penjualan --</option>
                            @foreach($daftarGrup as $g)
                                <option value="{{ $g->grup_id }}" {{ ($config['group_id_seller'] ?? '') === $g->grup_id ? 'selected' : '' }}>
                                    {{ $g->grup_nama ?? 'Tanpa Nama' }} ({{ $g->grup_id }})
                                </option>
                            @endforeach
                        </select>
                        <div class="form-text text-muted small mt-1">
                            <i class="fa-solid fa-circle-info me-1"></i>
                            Grup tempat Driver/Admin menerima notifikasi pesanan masuk dan mengelola order/stok.
                        </div>
                    </div>

                    <!-- Informasi Rekening Transfer -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Informasi Rekening Transfer Bank
                        </label>
                        <textarea name="bank_transfer_info" rows="3" 
                                  class="form-control form-control-premium @error('bank_transfer_info') is-invalid @enderror"
                                  placeholder="Bank BCA&#10;No Rekening: 123456789&#10;a/n Toko Tenanta.id">{{ old('bank_transfer_info', $config['bank_transfer_info'] ?? '') }}</textarea>
                    </div>

                    <!-- Upload QRIS File -->
                    <div class="mb-4">
                        <label class="form-label fw-bold small text-secondary">
                            Unggah Gambar QRIS Pembayaran (.png, .jpg)
                        </label>
                        <input type="file" name="qris_file" class="form-control form-control-premium @error('qris_file') is-invalid @enderror">
                        @if(file_exists(public_path('uploads/qris.png')))
                            <div class="mt-2 small text-success">
                                <i class="fa-solid fa-image me-1"></i> QRIS aktif saat ini: 
                                <a href="{{ url('uploads/qris.png') }}" target="_blank" class="text-decoration-underline fw-bold">Lihat QRIS</a>
                            </div>
                        @else
                            <div class="mt-2 small text-warning">
                                <i class="fa-solid fa-circle-exclamation me-1"></i> QRIS belum diunggah.
                            </div>
                        @endif
                    </div>

                    <!-- Webhook URL Preview -->
                    <div class="bg-light rounded-4 p-3 border mb-4">
                        <div class="small fw-bold text-secondary mb-2">
                            <i class="fa-solid fa-eye me-1"></i> URL Webhook yang Didaftarkan ke Meta:
                        </div>
                        <div class="bg-white rounded-3 p-2 border" style="font-family: monospace; font-size: 0.8rem; word-break: break-all;" id="webhookPreview">
                            {{ $webhookUrl }}
                        </div>
                    </div>

                    <button type="submit" class="btn btn-premium btn-premium-brand w-100 py-3">
                        <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Konfigurasi
                    </button>
                </form>
            </div>
        </div>

        <!-- =============================================
             KOLOM KANAN: Panduan Ngrok + Meta
        ============================================= -->
        <div class="col-lg-5">

            <!-- Panduan Ngrok -->
            <div class="card-premium p-4 mb-4">
                <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-terminal me-2 text-success"></i> Cara Menjalankan Ngrok
                </h5>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">1</div>
                        <div>
                            <div class="fw-semibold small">Buka CMD / PowerShell di folder project</div>
                            <div class="bg-dark text-light rounded-3 p-2 mt-1" style="font-family:monospace;font-size:0.78rem;">
                                cd c:\xampp\htdocs\male_boot
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">2</div>
                        <div>
                            <div class="fw-semibold small">Jalankan ngrok untuk port 80 (XAMPP)</div>
                            <div class="bg-dark text-light rounded-3 p-2 mt-1" style="font-family:monospace;font-size:0.78rem;">
                                .\ngrok.exe http 80
                            </div>
                            <div class="text-muted small mt-1">Atau port 8000 jika pakai <code>php artisan serve</code></div>
                            <div class="bg-dark text-light rounded-3 p-2 mt-1" style="font-family:monospace;font-size:0.78rem;">
                                .\ngrok.exe http 8000
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">3</div>
                        <div>
                            <div class="fw-semibold small">Salin URL dari output ngrok</div>
                            <div class="bg-dark text-light rounded-3 p-2 mt-1" style="font-family:monospace;font-size:0.78rem;color:#4ade80;">
                                Forwarding: https://xxxx-xxx.ngrok-free.app → localhost:80
                            </div>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-success-subtle text-success border border-success-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">4</div>
                        <div>
                            <div class="fw-semibold small">Tempel URL ke kolom "Ngrok Public URL" di form kiri</div>
                            <div class="text-muted small">Webhook URL otomatis terbentuk: <code>URL/webhook/whatsapp</code></div>
                        </div>
                    </div>
                </div>

                <div class="alert alert-warning border-0 rounded-3 mt-3 py-2 px-3 small">
                    <i class="fa-solid fa-triangle-exclamation me-1"></i>
                    <strong>Catatan:</strong> URL ngrok berubah setiap kali restart (kecuali pakai akun berbayar). Update kembali di form ini & di Meta Console jika URL berubah.
                </div>
            </div>

            <!-- Panduan Meta Console -->
            <div class="card-premium p-4">
                <h5 class="fw-bold mb-3" style="font-family: var(--font-heading);">
                    <i class="fa-brands fa-meta me-2 text-primary"></i> Cara Daftar Webhook di Meta
                </h5>

                <div class="d-flex flex-column gap-3">
                    <div class="d-flex gap-3">
                        <div class="badge bg-info-subtle text-info border border-info-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">1</div>
                        <div class="small">
                            Buka <a href="https://developers.facebook.com" target="_blank" class="fw-bold">developers.facebook.com</a>
                            → Pilih App → <strong>WhatsApp → Configuration</strong>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-info-subtle text-info border border-info-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">2</div>
                        <div class="small">
                            Di bagian <strong>Webhook</strong>, klik <strong>Edit</strong> lalu isi:
                            <ul class="mt-1 mb-0 ps-3">
                                <li><strong>Callback URL:</strong> URL webhook dari form kiri</li>
                                <li><strong>Verify Token:</strong> sama dengan isian "Webhook Verify Token" di form</li>
                            </ul>
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-info-subtle text-info border border-info-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">3</div>
                        <div class="small">
                            Klik <strong>Verify and Save</strong>. Meta akan melakukan GET request ke webhook URL Anda untuk verifikasi.
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-info-subtle text-info border border-info-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;">4</div>
                        <div class="small">
                            Setelah terverifikasi, aktifkan field <strong>messages</strong> di bagian Webhook Fields.
                        </div>
                    </div>

                    <div class="d-flex gap-3">
                        <div class="badge bg-success-subtle text-success border border-success-subtle rounded-circle d-flex align-items-center justify-content-center flex-shrink-0" style="width:32px;height:32px;"><i class="fa-solid fa-check" style="font-size:0.7rem;"></i></div>
                        <div class="small">
                            Kirim pesan test ke nomor WhatsApp Business Anda. Chatbot akan merespons otomatis! 🎉
                        </div>
                    </div>
                </div>

                <div class="mt-3">
                    <a href="https://developers.facebook.com/apps" target="_blank"
                       class="btn btn-sm btn-outline-primary rounded-4 w-100">
                        <i class="fa-brands fa-meta me-1"></i> Buka Meta Developer Console
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
function toggleVisibility(inputId, iconId) {
    const input = document.getElementById(inputId);
    const icon  = document.getElementById(iconId);
    if (input.type === 'password') {
        input.type = 'text';
        icon.classList.replace('fa-eye', 'fa-eye-slash');
    } else {
        input.type = 'password';
        icon.classList.replace('fa-eye-slash', 'fa-eye');
    }
}

// Update preview webhook URL live
document.getElementById('ngrok_public_url')?.addEventListener('input', function() {
    const url = this.value.trim().replace(/\/$/, '');
    const preview = document.getElementById('webhookPreview');
    if (url) {
        preview.textContent = url + '/webhook/whatsapp';
    } else {
        preview.textContent = '{{ url("/webhook/whatsapp") }}';
    }
});
</script>
@endsection
