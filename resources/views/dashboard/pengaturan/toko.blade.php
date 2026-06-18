@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Toko</h1>
        <p class="text-muted mb-0">Kelola identitas dan info struk toko Anda.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('dashboard.pengaturan.toko.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="nama_toko" class="form-label fw-bold">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_toko') is-invalid @enderror" id="nama_toko" name="nama_toko" value="{{ old('nama_toko', $identitas->nama_toko ?? '') }}" required>
                        @error('nama_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="alamat_toko" class="form-label fw-bold">Alamat Toko</label>
                        <textarea class="form-control @error('alamat_toko') is-invalid @enderror" id="alamat_toko" name="alamat_toko" rows="3">{{ old('alamat_toko', $identitas->alamat_toko ?? '') }}</textarea>
                        @error('alamat_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="nomor_telepon" class="form-label fw-bold">Nomor Telepon</label>
                        <input type="text" class="form-control @error('nomor_telepon') is-invalid @enderror" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $identitas->nomor_telepon ?? '') }}">
                        @error('nomor_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="pesan_footer" class="form-label fw-bold">Pesan Bawah (Footer) Struk</label>
                        <textarea class="form-control @error('pesan_footer') is-invalid @enderror" id="pesan_footer" name="pesan_footer" rows="3" placeholder="Terima kasih atas kunjungan Anda!">{{ old('pesan_footer', $identitas->pesan_footer ?? '') }}</textarea>
                        <div class="form-text">Pesan yang akan dicetak di bagian paling bawah struk kasir.</div>
                        @error('pesan_footer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="logo" class="form-label fw-bold">Logo Toko (Opsional)</label>
                        @if(isset($identitas->logo_path) && $identitas->logo_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $identitas->logo_path) }}" alt="Logo Toko" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        @endif
                        <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/jpg">
                        <div class="form-text">Gambar akan otomatis dicetak hitam-putih di struk printer thermal. Maks 2MB.</div>
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Metode Pembayaran Chatbot (Sangat Privat) -->
                    <div class="card border border-warning shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-warning text-dark border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-lock me-2"></i> Metode Pembayaran Chatbot (Sangat Privat)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="nomor_rekening" class="form-label fw-bold text-dark">Informasi Nomor Rekening (Untuk Transfer)</label>
                                <textarea class="form-control @error('nomor_rekening') is-invalid @enderror" id="nomor_rekening" name="nomor_rekening" rows="3" placeholder="Contoh:&#10;BCA: 123456789 a/n Ninsky Bakery&#10;Mandiri: 987654321 a/n Ninsky Bakery">{{ old('nomor_rekening', $identitas->nomor_rekening ?? '') }}</textarea>
                                <div class="form-text text-muted">Detail ini akan dibaca secara privat oleh AI Chatbot dan dikirimkan saat pelanggan menanyakan nomor rekening/transfer.</div>
                                @error('nomor_rekening') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="qris" class="form-label fw-bold text-dark">File QRIS Toko (Gambar)</label>
                                @if(isset($identitas->qris_path) && $identitas->qris_path)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $identitas->qris_path) }}" alt="QRIS Toko" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                @endif
                                <input class="form-control @error('qris') is-invalid @enderror" type="file" id="qris" name="qris" accept="image/jpeg, image/png, image/jpg">
                                <div class="form-text text-muted">Maks 2MB. Gambar QRIS ini akan dikirimkan otomatis oleh bot jika pelanggan memilih metode QRIS atau menanyakan kode pembayaran.</div>
                                @error('qris') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Payment Gateway Xendit / Mekari Pay -->
                    <div class="card border border-success shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-success text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-credit-card me-2"></i> Payment Gateway (Xendit / Mekari Pay)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" name="is_payment_gateway_active" id="pgActive" value="1" {{ old('is_payment_gateway_active', $identitas->is_payment_gateway_active ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark" for="pgActive">Aktifkan Pembayaran Otomatis</label>
                                <div class="form-text text-muted">Jika diaktifkan, chatbot akan membuat link pembayaran otomatis. Jika dimatikan, chatbot akan memberikan instruksi transfer manual (Nomor Rekening / QRIS).</div>
                            </div>
                            
                            <div class="mb-3">
                                <label for="xendit_api_key" class="form-label fw-bold text-dark">Secret API Key</label>
                                <input type="password" class="form-control @error('xendit_api_key') is-invalid @enderror" id="xendit_api_key" name="xendit_api_key" value="{{ old('xendit_api_key', $identitas->xendit_api_key ?? '') }}" placeholder="xnd_production_xxx...">
                                <div class="form-text text-muted">Dapatkan dari dashboard Xendit/Mekari Pay (Settings > API Keys).</div>
                                @error('xendit_api_key') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="xendit_webhook_token" class="form-label fw-bold text-dark">Webhook Verification Token</label>
                                <input type="password" class="form-control @error('xendit_webhook_token') is-invalid @enderror" id="xendit_webhook_token" name="xendit_webhook_token" value="{{ old('xendit_webhook_token', $identitas->xendit_webhook_token ?? '') }}" placeholder="Token untuk validasi webhook">
                                <div class="form-text text-muted">Dapatkan dari pengaturan Webhook (Callback Token). URL Webhook Anda adalah: <code>{{ url('/api/webhook/xendit') }}</code></div>
                                @error('xendit_webhook_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Koneksi WhatsApp (Meta Cloud API) -->
                    <div class="card border border-info shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-info text-dark border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-brands fa-whatsapp me-2"></i> Koneksi WhatsApp Bot</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="alert alert-warning mb-4" role="alert">
                                <h6 class="alert-heading fw-bold"><i class="fa-solid fa-triangle-exclamation me-2"></i>Informasi Penting & Keamanan</h6>
                                <p class="mb-2 text-dark" style="font-size: 0.9rem;">
                                    Secara bawaan, bot menggunakan <strong>Gateway Sistem (Baileys)</strong> yang dikelola oleh kami tanpa biaya tambahan. Anda bebas beralih ke <strong>Meta Cloud API Mandiri</strong> untuk mendapatkan jaminan 100% anti-banned dan performa resmi, namun perhatikan hal berikut:
                                </p>
                                <ul class="mb-0 text-dark" style="font-size: 0.9rem;">
                                    <li><strong>Biaya Meta:</strong> Meta akan mengenakan biaya per percakapan (conversation) sesuai harga resmi mereka. Tagihan akan masuk ke kartu kredit Anda yang terhubung di Facebook Business.</li>
                                    <li><strong>Keamanan Token:</strong> Jaga kerahasiaan <em>Permanent Access Token</em> Anda. Token ini memberi akses penuh untuk mengirim pesan atas nama bisnis Anda.</li>
                                    <li><strong>Fallback Aman:</strong> Jika integrasi Meta Anda gagal/bermasalah, Anda dapat kapan saja kembali ke <strong>Gateway Sistem</strong> dengan merubah pengaturan ini. Tidak ada data yang hilang.</li>
                                </ul>
                            </div>

                            <div class="mb-4">
                                <label for="whatsapp_gateway" class="form-label fw-bold text-dark">Pilih Jalur Koneksi</label>
                                <select class="form-select @error('whatsapp_gateway') is-invalid @enderror" id="whatsapp_gateway" name="whatsapp_gateway" onchange="toggleMetaConfig()" required>
                                    <option value="sistem" {{ old('whatsapp_gateway', $identitas->whatsapp_gateway ?? 'sistem') == 'sistem' ? 'selected' : '' }}>Gunakan Gateway Sistem (Gratis & Dikelola Pusat)</option>
                                    <option value="meta_mandiri" {{ old('whatsapp_gateway', $identitas->whatsapp_gateway ?? 'sistem') == 'meta_mandiri' ? 'selected' : '' }}>Gunakan Meta WhatsApp Cloud API (Koneksi Pribadi)</option>
                                </select>
                                @error('whatsapp_gateway') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div id="meta_config_section" style="display: none;">
                                <div class="mb-3">
                                    <label for="meta_phone_number_id" class="form-label fw-bold text-dark">Phone Number ID</label>
                                    <input type="text" class="form-control @error('meta_phone_number_id') is-invalid @enderror" id="meta_phone_number_id" name="meta_phone_number_id" value="{{ old('meta_phone_number_id', $identitas->meta_phone_number_id ?? '') }}" placeholder="Contoh: 1045231...">
                                    <div class="form-text text-muted">Didapatkan dari dashboard Meta Developer.</div>
                                    @error('meta_phone_number_id') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="meta_access_token" class="form-label fw-bold text-dark">Permanent Access Token</label>
                                    <input type="password" class="form-control @error('meta_access_token') is-invalid @enderror" id="meta_access_token" name="meta_access_token" value="{{ old('meta_access_token', $identitas->meta_access_token ?? '') }}" placeholder="EAALx...">
                                    <div class="form-text text-muted">Pastikan Anda menggunakan System User Token yang tidak memiliki masa kadaluarsa (Permanent).</div>
                                    @error('meta_access_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="meta_webhook_token" class="form-label fw-bold text-dark">Webhook Verify Token</label>
                                    <input type="password" class="form-control @error('meta_webhook_token') is-invalid @enderror" id="meta_webhook_token" name="meta_webhook_token" value="{{ old('meta_webhook_token', $identitas->meta_webhook_token ?? '') }}" placeholder="Masukkan teks unik apapun (misal: rahasia_toko_123)">
                                    <div class="form-text text-muted">Token bebas buatan Anda sendiri untuk verifikasi keamanan.</div>
                                    @error('meta_webhook_token') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="alert alert-secondary p-3 mt-3">
                                    <strong class="text-dark">Webhook URL untuk Meta:</strong><br>
                                    <code class="fs-6">{{ url('/api/webhook/meta') }}</code>
                                    <p class="mt-2 mb-0 text-muted small">Salin URL dan Webhook Token di atas ke pengaturan Webhook di Dashboard Meta For Developers Anda, dan pilih event <code>messages</code>.</p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Model Bisnis -->
                    <div class="card border border-info shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-info text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-store-slash me-2"></i> Pengaturan Model Bisnis (Dine-in / Take Away)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="jenis_layanan" class="form-label fw-bold text-dark">Jenis Layanan F&B</label>
                                <select class="form-select @error('jenis_layanan') is-invalid @enderror" id="jenis_layanan" name="jenis_layanan" required>
                                    <option value="dine_in" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'dine_in' ? 'selected' : '' }}>Hanya Dine-in (Makan di Tempat)</option>
                                    <option value="take_away" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'take_away' ? 'selected' : '' }}>Hanya Take Away / Delivery</option>
                                    <option value="keduanya" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'keduanya' ? 'selected' : '' }}>Melayani Keduanya</option>
                                </select>
                                <div class="form-text text-muted">Mengubah jenis layanan akan menyesuaikan fitur yang muncul di Dashboard (seperti Manajemen Meja dan Reservasi).</div>
                                @error('jenis_layanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="wajib_dp_reservasi" name="wajib_dp_reservasi" value="1" {{ old('wajib_dp_reservasi', $identitas->wajib_dp_reservasi ?? false) ? 'checked' : '' }}>
                                <label class="form-check-label fw-bold text-dark ms-1" for="wajib_dp_reservasi">Wajib Uang Muka (DP) untuk Reservasi Meja</label>
                                <div class="form-text text-muted">Jika diaktifkan, pelanggan harus membayar DP saat mereservasi meja via Chatbot.</div>
                            </div>
                        </div>
                    </div>

                    <!-- Identitas Bot AI -->
                    <div class="card border border-secondary shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-secondary text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-robot me-2"></i> Pengaturan Identitas Bot AI</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="nama_bot" class="form-label fw-bold text-dark">Nama Bot Assistant</label>
                                <input type="text" class="form-control @error('nama_bot') is-invalid @enderror" id="nama_bot" name="nama_bot" value="{{ old('nama_bot', $identitas->nama_bot ?? 'Teta Assistant') }}">
                                <div class="form-text text-muted">Nama panggil AI untuk pelanggan. (Default: Teta Assistant).</div>
                                @error('nama_bot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="karakter_bot" class="form-label fw-bold text-dark">Karakter / Sifat Bot</label>
                                <input type="text" class="form-control @error('karakter_bot') is-invalid @enderror" id="karakter_bot" name="karakter_bot" value="{{ old('karakter_bot', $identitas->karakter_bot ?? 'Customer Service Virtual (AI) ramah') }}">
                                <div class="form-text text-muted">Deskripsikan bagaimana sifat AI melayani pelanggan. (Contoh: "Customer Service Virtual (AI) ramah").</div>
                                @error('karakter_bot') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pilihan Tema Warna -->
                    <div class="card border border-primary shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-primary text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-palette me-2"></i> Pengaturan Tema Warna</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="tema_portal" class="form-label fw-bold text-dark">Tema Warna Portal Customer</label>
                                    <select class="form-select @error('tema_portal') is-invalid @enderror" id="tema_portal" name="tema_portal" required>
                                        <option value="cool" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'cool' ? 'selected' : '' }}>Cool (Harmoni Violet & Slate)</option>
                                        <option value="warm" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'warm' ? 'selected' : '' }}>Warm (Energetik Oranye & Kuning)</option>
                                        <option value="kalem" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'kalem' ? 'selected' : '' }}>Kalem (Pastel Hijau & Tosca)</option>
                                    </select>
                                    <div class="form-text text-muted">Mempengaruhi warna tombol dan gradien header pada Portal Pemesanan Mandiri.</div>
                                    @error('tema_portal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tema_desktop" class="form-label fw-bold text-dark">Tema Warna Dashboard Desktop</label>
                                    <select class="form-select @error('tema_desktop') is-invalid @enderror" id="tema_desktop" name="tema_desktop" required>
                                        <option value="cool" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'cool' ? 'selected' : '' }}>Cool (Harmoni Violet & Slate)</option>
                                        <option value="warm" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'warm' ? 'selected' : '' }}>Warm (Energetik Oranye & Kuning)</option>
                                        <option value="kalem" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'kalem' ? 'selected' : '' }}>Kalem (Pastel Hijau & Tosca)</option>
                                    </select>
                                    <div class="form-text text-muted">Mempengaruhi aksen warna utama di seluruh dashboard admin dan kasir offline POS.</div>
                                    @error('tema_desktop') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function toggleMetaConfig() {
        var gateway = document.getElementById('whatsapp_gateway').value;
        var metaSection = document.getElementById('meta_config_section');
        if (gateway === 'meta_mandiri') {
            metaSection.style.display = 'block';
        } else {
            metaSection.style.display = 'none';
        }
    }
    
    // Run on load
    document.addEventListener('DOMContentLoaded', function() {
        toggleMetaConfig();
    });
</script>
@endsection
