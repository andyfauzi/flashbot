<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pengaturan Landing Page | Tenanta.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-gradient: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
            --accent-color: #4f46e5;
            --accent-gradient: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
            --accent-hover: #4338ca;
            --card-shadow: 0 10px 40px -10px rgba(0,0,0,0.08);
            --border-radius: 20px;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: #f1f5f9;
            color: #0f172a;
            min-vh: 100vh;
        }

        .header-panel {
            background: var(--bg-gradient);
            position: relative;
            overflow: hidden;
            border-bottom-left-radius: 32px;
            border-bottom-right-radius: 32px;
            padding: 50px 20px;
            color: #fff;
            box-shadow: 0 20px 40px rgba(15, 23, 42, 0.1);
            margin-bottom: 30px;
        }

        .header-panel::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -10%;
            width: 400px;
            height: 400px;
            background: radial-gradient(circle, rgba(79,70,229,0.4) 0%, rgba(0,0,0,0) 70%);
            border-radius: 50%;
            opacity: 0.8;
            pointer-events: none;
        }

        .custom-card {
            background: #fff;
            border: 1px solid rgba(226, 232, 240, 0.6);
            border-radius: var(--border-radius);
            box-shadow: var(--card-shadow);
            padding: 28px;
            margin-bottom: 24px;
            transition: all 0.3s ease;
        }

        .custom-card:hover {
            box-shadow: 0 15px 35px -10px rgba(0,0,0,0.1);
        }

        .form-label {
            font-weight: 600;
            color: #475569;
        }
        
        .form-control, .form-select {
            border-radius: 12px;
            padding: 12px 16px;
            border: 1px solid #cbd5e1;
            background-color: #f8fafc;
            transition: all 0.2s ease;
        }
        
        .form-control:focus, .form-select:focus {
            background-color: #fff;
            border-color: var(--accent-color);
            box-shadow: 0 0 0 4px rgba(79, 70, 229, 0.1);
        }
        
        .btn-primary {
            background: var(--accent-gradient);
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: linear-gradient(135deg, #4338ca 0%, #4f46e5 100%);
            transform: translateY(-2px);
            box-shadow: 0 12px 25px rgba(79, 70, 229, 0.4);
        }
    </style>
</head>
<body>

    <!-- Header Panel -->
    <div class="header-panel">
        <div class="container">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="fw-bold mb-1"><i class="fa-solid fa-server me-2"></i>Tenanta.id Super Admin</h1>
                    <p class="text-white-50 mb-0">Pengaturan Tampilan & Konten Landing Page</p>
                </div>
                <div class="d-flex align-items-center gap-3">
                    <span class="badge bg-light text-dark py-2 px-3 fw-bold"><i class="fa-solid fa-earth-asia me-1"></i> Landlord Mode</span>
                    <a href="{{ route('logout') }}" class="btn btn-outline-light btn-sm fw-bold rounded-pill px-3">
                        <i class="fa-solid fa-right-from-bracket me-1"></i> Logout
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Navigation Tabs -->
    <div class="container mb-4">
        <ul class="nav nav-pills nav-fill bg-white p-2 rounded-4 shadow-sm">
            <li class="nav-item">
                <a class="nav-link text-dark fw-bold rounded-3" href="{{ route('superadmin.index') }}">
                    <i class="fa-solid fa-users-gear me-2"></i> Manajemen Tenant
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link active fw-bold rounded-3" href="{{ route('superadmin.landing_page') }}">
                    <i class="fa-solid fa-earth-americas me-2"></i> Pengaturan Landing Page
                </a>
            </li>
        </ul>
    </div>

    <div class="container mb-5">
        @if(session('sukses'))
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
                <i class="fa-solid fa-circle-check me-2"></i> {{ session('sukses') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        @endif

        <form action="{{ route('superadmin.landing_page.update') }}" method="POST">
            @csrf
            
            <div class="row">
                <div class="col-lg-8">
                    <!-- Hero Section -->
                    <div class="custom-card">
                        <h5 class="fw-bold mb-4"><i class="fa-solid fa-heading text-primary me-2"></i>Bagian Utama (Hero)</h5>
                        <div class="mb-3">
                            <label class="form-label">Judul Besar (Hero Title)</label>
                            <input type="text" class="form-control" name="hero_title" value="{{ $settings['hero_title'] ?? 'Platform SaaS Kasir Pintar' }}" required>
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
                                    <label class="form-label small">Deskripsi/Fitur</label>
                                    <textarea class="form-control form-control-sm" name="features_business" rows="3">{{ $settings['features_business'] ?? "Unlimited Cabang\nPrioritas Support\nWhite Label" }}</textarea>
                                </div>
                            </div>
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
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
