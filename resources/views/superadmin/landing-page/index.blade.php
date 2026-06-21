@extends('layouts.app')

@section('title', 'Pengaturan Landing Page')

@section('styles')
<style>
    .header-panel {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        position: relative;
        overflow: hidden;
        border-radius: 20px;
        padding: 40px 30px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
        margin-bottom: 30px;
    }
    .header-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(79,70,229,0.4) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        opacity: 0.8;
        pointer-events: none;
    }
    .custom-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 24px;
        margin-bottom: 24px;
    }
</style>
@endsection

@section('content')
<div class="header-panel">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1">Pengaturan Landing Page</h2>
            <p class="text-white-50 mb-0">Atur konten, hero banner, dan harga platform</p>
        </div>
        <div>
            <div class="badge bg-light text-dark fw-bold rounded-pill px-3 py-2 border"><i class="fa-solid fa-crown text-warning me-1"></i> Super Admin</div>
        </div>
    </div>
</div>

@if(session('sukses'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('sukses') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ route('superadmin.landing_page.update') }}" method="POST" enctype="multipart/form-data">
    @csrf
    
    <div class="row">
        <div class="col-lg-8">
            <!-- Hero Section -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-heading text-primary me-2"></i>Bagian Utama (Hero)</h5>
                <div class="row mb-3">
                    <div class="col-md-8">
                        <label class="form-label">Judul Besar (Hero Title)</label>
                        <input type="text" class="form-control" name="hero_title" value="{{ $settings['hero_title'] ?? 'Platform SaaS Kasir Pintar' }}" required>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label">Ukuran Font Judul</label>
                        <input type="text" class="form-control" name="hero_title_size" value="{{ $settings['hero_title_size'] ?? '4rem' }}" placeholder="Contoh: 4rem, 48px">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Subjudul (Hero Subtitle)</label>
                    <textarea class="form-control" name="hero_subtitle" rows="2" required>{{ $settings['hero_subtitle'] ?? 'Kelola puluhan cabang toko Anda dalam satu pintu dengan teknologi cerdas.' }}</textarea>
                </div>
                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Teks Tombol Aksi (CTA Text)</label>
                        <input type="text" class="form-control" name="cta_text" value="{{ $settings['cta_text'] ?? 'Mulai Sekarang' }}">
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label">URL Tombol Aksi (CTA Link)</label>
                        <input type="text" class="form-control" name="cta_link" value="{{ $settings['cta_link'] ?? '#pricing' }}">
                    </div>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Dashboard (Hero Image)</label>
                    @if(isset($settings['hero_image']) && !empty($settings['hero_image']))
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $settings['hero_image']) }}" alt="Hero Image" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif
                    <input type="file" class="form-control" name="hero_image" accept="image/*">
                    <div class="form-text">Upload gambar untuk mengganti gambar default di halaman depan (Rekomendasi: 1200x800px, maksimal 2MB). Kosongkan jika tidak ingin mengubah.</div>
                </div>
            </div>

            <!-- Pricing Information -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-tags text-success me-2"></i>Pengaturan Harga (Pricing)</h5>
                
                <div class="row">
                    <!-- Starter Plan -->
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-success">Paket Starter</h6>
                            <label class="form-label small mt-2">Harga / Bulan</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_starter" value="{{ $settings['price_starter'] ?? 'Rp 99.000' }}">
                            <label class="form-label small">Harga / Tahun</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_starter_yearly" value="{{ $settings['price_starter_yearly'] ?? 'Rp 990.000' }}">
                            <label class="form-label small">Deskripsi/Fitur</label>
                            <textarea class="form-control form-control-sm" name="features_starter" rows="3">{{ $settings['features_starter'] ?? "1 Cabang Toko\nFitur Kasir Dasar\nLaporan Standar" }}</textarea>
                        </div>
                    </div>
                    <!-- Pro Plan -->
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded border-primary">
                            <h6 class="fw-bold text-primary">Paket Pro</h6>
                            <label class="form-label small mt-2">Harga / Bulan</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_pro" value="{{ $settings['price_pro'] ?? 'Rp 199.000' }}">
                            <label class="form-label small">Harga / Tahun</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_pro_yearly" value="{{ $settings['price_pro_yearly'] ?? 'Rp 1.990.000' }}">
                            <label class="form-label small">Deskripsi/Fitur</label>
                            <textarea class="form-control form-control-sm" name="features_pro" rows="3">{{ $settings['features_pro'] ?? "5 Cabang Toko\nFitur Manufaktur\nBot WhatsApp" }}</textarea>
                        </div>
                    </div>
                    <!-- Business Plan -->
                    <div class="col-md-4 mb-3">
                        <div class="p-3 border rounded">
                            <h6 class="fw-bold text-dark">Paket Business</h6>
                            <label class="form-label small mt-2">Harga / Bulan</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_business" value="{{ $settings['price_business'] ?? 'Rp 499.000' }}">
                            <label class="form-label small">Harga / Tahun</label>
                            <input type="text" class="form-control form-control-sm mb-2" name="price_business_yearly" value="{{ $settings['price_business_yearly'] ?? 'Rp 4.990.000' }}">
                            <label class="form-label small">Deskripsi/Fitur</label>
                            <textarea class="form-control form-control-sm" name="features_business" rows="3">{{ $settings['features_business'] ?? "Unlimited Cabang\nPrioritas Support\nWhite Label" }}</textarea>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Petunjuk Penggunaan -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-book-open text-info me-2"></i>Petunjuk Penggunaan</h5>
                <div class="mb-3">
                    <label class="form-label">Teks Petunjuk Penggunaan</label>
                    <textarea class="form-control" name="user_guide_text" rows="4" placeholder="Masukkan langkah-langkah penggunaan di sini...">{{ $settings['user_guide_text'] ?? '' }}</textarea>
                </div>
                <div class="mb-3">
                    <label class="form-label">Gambar Petunjuk Penggunaan</label>
                    @if(isset($settings['user_guide_image']) && !empty($settings['user_guide_image']))
                        <div class="mb-2">
                            <img src="{{ asset('storage/' . $settings['user_guide_image']) }}" alt="User Guide Image" class="img-thumbnail" style="max-height: 150px;">
                        </div>
                    @endif
                    <input type="file" class="form-control" name="user_guide_image" accept="image/*">
                    <div class="form-text">Upload gambar pendukung petunjuk penggunaan.</div>
                </div>
            </div>

            <!-- Syarat dan Ketentuan -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-file-contract text-danger me-2"></i>Syarat dan Ketentuan</h5>
                <div class="mb-3">
                    <label class="form-label">Isi Syarat & Ketentuan</label>
                    <textarea class="form-control" name="terms_conditions" rows="6" placeholder="Masukkan isi syarat dan ketentuan...">{{ $settings['terms_conditions'] ?? '' }}</textarea>
                    <div class="form-text">Teks ini akan ditampilkan di halaman Syarat & Ketentuan terpisah.</div>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <!-- Contact Information -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-address-book text-warning me-2"></i>Informasi Kontak</h5>
                <div class="mb-3">
                    <label class="form-label">Email Perusahaan</label>
                    <input type="email" class="form-control" name="contact_email" value="{{ $settings['contact_email'] ?? 'hello@tenanta.id' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Nomor WhatsApp/Telepon</label>
                    <input type="text" class="form-control" name="contact_phone" value="{{ $settings['contact_phone'] ?? '+62 812-3456-7890' }}">
                </div>
                <div class="mb-3">
                    <label class="form-label">Alamat Kantor</label>
                    <textarea class="form-control" name="contact_address" rows="2">{{ $settings['contact_address'] ?? 'Gedung Menara Mulia, Jakarta Selatan' }}</textarea>
                </div>
            </div>

            <!-- Payment Information -->
            <div class="custom-card">
                <h5 class="fw-bold mb-4"><i class="fa-solid fa-building-columns text-info me-2"></i>Instruksi Pembayaran</h5>
                <div class="mb-3">
                    <label class="form-label">Detail Rekening Bank / Pembayaran</label>
                    <textarea class="form-control" name="payment_instructions" rows="4" placeholder="Contoh: BCA 123456789 a/n PT Tenanta">{{ $settings['payment_instructions'] ?? "BCA: 1234567890\nMandiri: 0987654321\nA.n PT Tenanta Inovasi" }}</textarea>
                    <div class="form-text">Instruksi ini akan ditampilkan di halaman langganan tenant.</div>
                </div>
            </div>

            <button type="submit" class="btn btn-primary w-100 py-3 fw-bold rounded-3 shadow-sm">
                <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Semua Pengaturan
            </button>
        </div>
    </div>
</form>
@endsection
