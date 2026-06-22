<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tenanta.id | Sistem Kasir POS & WhatsApp Chatbot SaaS</title>
    <!-- Bootstrap 5 & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        :root {
            --bg-dark: #090d16;
            --card-bg: rgba(30, 41, 59, 0.4);
            --border-color: rgba(255, 255, 255, 0.08);
            --accent-purple: #8b5cf6;
            --accent-cyan: #06b6d4;
            --text-muted: #94a3b8;
        }

        body {
            font-family: 'Plus Jakarta Sans', sans-serif;
            background-color: var(--bg-dark);
            color: #f8fafc;
            overflow-x: hidden;
        }

        /* Hero Background Gradient Circles */
        .gradient-circle-1 {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 50vw;
            height: 50vw;
            background: radial-gradient(circle, rgba(139, 92, 246, 0.15) 0%, rgba(9, 13, 22, 0) 70%);
            z-index: -1;
            filter: blur(80px);
        }

        .gradient-circle-2 {
            position: absolute;
            top: 40%;
            right: -10%;
            width: 40vw;
            height: 40vw;
            background: radial-gradient(circle, rgba(6, 182, 212, 0.1) 0%, rgba(9, 13, 22, 0) 70%);
            z-index: -1;
            filter: blur(80px);
        }

        /* Glassmorphism Navbar */
        .navbar-custom {
            background: rgba(9, 13, 22, 0.7);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            padding: 18px 0;
        }

        .logo-text {
            font-size: 1.5rem;
            font-weight: 800;
            background: linear-gradient(135deg, #fff 0%, #a78bfa 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        /* Glass Cards */
        .glass-card {
            background: var(--card-bg);
            backdrop-filter: blur(16px);
            border: 1px solid var(--border-color);
            border-radius: 20px;
            padding: 32px;
            box-shadow: 0 20px 40px rgba(0, 0, 0, 0.3);
            transition: all 0.3s ease;
        }

        .glass-card:hover {
            transform: translateY(-5px);
            border-color: rgba(139, 92, 246, 0.3);
        }

        .hero-title {
            font-size: 3.5rem;
            font-weight: 800;
            line-height: 1.2;
            background: linear-gradient(135deg, #fff 30%, #cbd5e1 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        @media (max-width: 768px) {
            .hero-title {
                font-size: 2.5rem;
            }
        }

        .hero-subtitle {
            color: var(--text-muted);
            font-size: 1.15rem;
            line-height: 1.6;
        }

        /* Google button */
        .btn-google {
            background: #fff;
            color: #1e293b;
            font-weight: 700;
            border: none;
            border-radius: 12px;
            padding: 14px 28px;
            display: inline-flex;
            align-items: center;
            gap: 12px;
            box-shadow: 0 4px 20px rgba(255, 255, 255, 0.1);
            transition: all 0.2s ease;
            text-decoration: none;
        }

        .btn-google:hover {
            background: #f1f5f9;
            transform: translateY(-2px);
            color: #1e293b;
        }

        /* Features Section */
        .feature-icon {
            font-size: 2rem;
            background: linear-gradient(135deg, var(--accent-purple) 0%, var(--accent-cyan) 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            margin-bottom: 20px;
            display: inline-block;
        }

        /* Pricing Card */
        .pricing-card {
            border: 1px solid var(--border-color);
            background: rgba(15, 23, 42, 0.6);
            border-radius: 24px;
            padding: 40px 30px;
            text-align: center;
            position: relative;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .pricing-card.popular {
            border: 2px solid var(--accent-purple);
            background: rgba(30, 41, 59, 0.6);
        }

        .pricing-card.popular::before {
            content: "POPULER";
            position: absolute;
            top: 20px;
            right: -30px;
            background: var(--accent-purple);
            color: #fff;
            font-size: 0.75rem;
            font-weight: 800;
            padding: 4px 30px;
            transform: rotate(45deg);
        }

        .price {
            font-size: 2.5rem;
            font-weight: 800;
            margin: 20px 0;
            color: #fff;
        }

        .price span {
            font-size: 1rem;
            font-weight: 500;
            color: var(--text-muted);
        }

        .pricing-card ul {
            list-style: none;
            padding: 0;
            margin: 30px 0;
            text-align: left;
        }

        .pricing-card ul li {
            margin-bottom: 12px;
            font-size: 0.95rem;
            color: #cbd5e1;
        }

        .pricing-card ul li i {
            color: var(--accent-cyan);
            margin-right: 8px;
        }

        /* Input Custom Style */
        .form-control-custom {
            background: rgba(15, 23, 42, 0.8);
            border: 1px solid var(--border-color);
            color: #fff;
            border-radius: 10px;
            padding: 12px;
        }

        .form-control-custom:focus {
            background: rgba(15, 23, 42, 0.9);
            border-color: var(--accent-purple);
            color: #fff;
            box-shadow: none;
        }
    </style>
</head>
<body>

    <div class="gradient-circle-1"></div>
    <div class="gradient-circle-2"></div>

    <!-- Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark navbar-custom sticky-top">
        <div class="container">
            <a class="navbar-brand d-flex align-items-center gap-2" href="#">
                <span class="logo-text">⚡ TENANTA.ID SaaS</span>
            </a>
            <div class="ms-auto">
                <a href="#pricing" class="btn btn-outline-light rounded-pill px-4 btn-sm d-none d-sm-inline-block">Lihat Harga</a>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="container py-5 mt-4">
        <div class="row align-items-center g-5">
            <div class="col-lg-6 text-start">
                <div class="badge bg-primary bg-opacity-20 text-info px-3 py-2 rounded-pill mb-3 fw-bold border border-info border-opacity-30">
                    🚀 ERA BARU APLIKASI KASIR SAAS
                </div>
                <h1 class="hero-title mb-4">Satu Sistem Pintar Untuk Semua Cabang Toko Anda</h1>
                <p class="hero-subtitle mb-5">
                    Kelola POS Penjualan, Keuangan Kasir, Resep Bahan Baku (HPP) otomatis, hingga WhatsApp Chatbot AI dalam satu platform terintegrasi. Buat akun toko Anda dalam hitungan detik.
                </p>
                <div class="d-flex flex-wrap gap-3">
                    <a href="{{ route('auth.google', ['plan' => 'business', 'trial' => '1']) }}" class="btn-google">
                        <img src="https://lh3.googleusercontent.com/COxitS7y0g8sHTxZ97_ypgFJnifh7pI5vy45GEwVm2xJ9ed573037g7515R7lk279w" alt="google" style="width: 22px;">
                        Mulai Trial 30 Hari via Google
                    </a>
                </div>
            </div>
            <div class="col-lg-6">
                <!-- Visual Dashboard Showcase -->
                <div class="glass-card p-2 border-opacity-20 position-relative">
                    <img src="https://illustrations.popsy.co/white/web-design.svg" alt="dashboard-mockup" class="img-fluid rounded-4" style="filter: drop-shadow(0 20px 30px rgba(139, 92, 246, 0.2));">
                </div>
            </div>
        </div>
    </div>

    <!-- Features Section -->
    <div class="container py-5 my-5">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Fitur Unggulan Platform</h2>
            <p class="text-muted">Semua yang Anda butuhkan untuk mengelola dan melipatgandakan omset bisnis Anda</p>
        </div>
        <div class="row g-4">
            <div class="col-md-4">
                <div class="glass-card h-100">
                    <i class="fa-solid fa-cash-register feature-icon"></i>
                    <h5 class="fw-bold">Kasir POS & Pre-Order</h5>
                    <p class="text-muted mb-0">Transaksi kasir kilat, print struk, kelola stok realtime, dan jadwal pesanan pre-order pelanggan dalam satu antarmuka.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card h-100">
                    <i class="fa-brands fa-whatsapp feature-icon"></i>
                    <h5 class="fw-bold">WhatsApp Chatbot & AI</h5>
                    <p class="text-muted mb-0">Bot pintar yang membalas otomatis chat pelanggan, cek stok barang, hingga menerima pesanan orderan via WhatsApp 24 jam non-stop.</p>
                </div>
            </div>
            <div class="col-md-4">
                <div class="glass-card h-100">
                    <i class="fa-solid fa-calculator feature-icon"></i>
                    <h5 class="fw-bold">Kalkulator HPP & Resep</h5>
                    <p class="text-muted mb-0">Hitung Harga Pokok Penjualan (HPP) otomatis berdasarkan resep bahan baku. Kurangi stok bahan secara otomatis setiap ada penjualan produk.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Pricing Section -->
    <div id="pricing" class="container py-5 my-4">
        <div class="text-center mb-5">
            <h2 class="fw-bold">Pilihan Paket Langganan</h2>
            <p class="text-muted">Investasi terbaik untuk digitalisasi bisnis Anda. Pilih paket yang sesuai kebutuhan.</p>
        </div>
        
        <div class="row g-4 justify-content-center">
            <!-- Starter -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <h5 class="fw-bold text-white">Starter Plan</h5>
                    <div class="price">Rp 99k <span>/ bulan</span></div>
                    <ul class="text-start">
                        <li><i class="fa-solid fa-circle-check"></i> 1 Toko / Subdomain</li>
                        <li><i class="fa-solid fa-circle-check"></i> Point of Sale (POS) Kasir</li>
                        <li><i class="fa-solid fa-circle-check"></i> Manajemen Stok & Produk</li>
                        <li class="text-muted"><i class="fa-solid fa-circle-xmark text-danger"></i> WhatsApp Chatbot</li>
                        <li class="text-muted"><i class="fa-solid fa-circle-xmark text-danger"></i> Kalkulator HPP & Resep</li>
                    </ul>
                </div>
            </div>

            <!-- Pro -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card popular">
                    <h5 class="fw-bold text-white">Pro Plan</h5>
                    <div class="price">Rp 199k <span>/ bulan</span></div>
                    <ul class="text-start">
                        <li><i class="fa-solid fa-circle-check"></i> 1 Toko / Subdomain</li>
                        <li><i class="fa-solid fa-circle-check"></i> Point of Sale (POS) Kasir</li>
                        <li><i class="fa-solid fa-circle-check"></i> Manajemen Stok & Produk</li>
                        <li><i class="fa-solid fa-circle-check"></i> WhatsApp Chatbot & Auto-Reply</li>
                        <li><i class="fa-solid fa-circle-check"></i> Kalkulator HPP & Resep</li>
                    </ul>
                </div>
            </div>

            <!-- Business -->
            <div class="col-lg-4 col-md-6">
                <div class="pricing-card">
                    <h5 class="fw-bold text-white">Business Plan</h5>
                    <div class="price">Rp 299k <span>/ bulan</span></div>
                    <ul class="text-start">
                        <li><i class="fa-solid fa-circle-check"></i> 1 Toko / Subdomain</li>
                        <li><i class="fa-solid fa-circle-check"></i> Semua Fitur Pro</li>
                        <li><i class="fa-solid fa-circle-check"></i> AI Agent Integration (Gemini AI)</li>
                        <li><i class="fa-solid fa-circle-check"></i> Prioritas CS Support 24/7</li>
                        <li><i class="fa-solid fa-circle-check"></i> Custom Fitur request</li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Single Call To Action Button -->
        <div class="row mt-5">
            <div class="col-12 text-center">
                <a href="{{ route('auth.google', ['plan' => 'business', 'trial' => '1']) }}" class="btn btn-primary btn-lg rounded-pill px-5 py-3 fw-bold shadow-lg" style="background: var(--accent-purple); border: none; font-size: 1.15rem;">
                    <i class="fa-solid fa-rocket me-2"></i> Mulai Trial Gratis 30 Hari (Akses Semua Fitur)
                </a>
                <p class="text-muted mt-3 small"><i class="fa-solid fa-shield-check me-1"></i> Tanpa kartu kredit. Bisa dibatalkan kapan saja.</p>
            </div>
        </div>
    </div>

    <!-- Registration Modal Step 2 -->
    @if(session('show_google_step2'))
        <div class="modal fade" id="googleStep2Modal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1" aria-labelledby="googleStep2ModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <form action="{{ route('auth.google.complete') }}" method="POST" class="modal-content text-start text-dark shadow-lg border-0" style="border-radius: 20px;">
                    @csrf
                    <div class="modal-header border-0 pb-0">
                        <h5 class="modal-title fw-bold" id="googleStep2ModalLabel">
                            <i class="fa-solid fa-store text-primary me-2"></i>Sedikit Lagi! Lengkapi Toko Anda
                        </h5>
                    </div>
                    <div class="modal-body py-4">
                        <div class="alert alert-info border-0 rounded-3 mb-4">
                            <i class="fa-solid fa-circle-info me-1"></i> Terhubung dengan: <strong>{{ session('google_reg_email') }}</strong>
                        </div>
                        
                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Nama Toko / Bisnis</label>
                            <input type="text" name="store_name" class="form-control py-2.5 rounded-3 @error('store_name') is-invalid @enderror" placeholder="Contoh: Brownies Ninsky" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-bold text-secondary">Alamat Web Subdomain</label>
                            <div class="input-group">
                                <input type="text" name="subdomain" class="form-control py-2.5 rounded-3 @error('subdomain') is-invalid @enderror" placeholder="ninskybrownies" required>
                                <span class="input-group-text text-muted bg-light border-start-0 font-monospace">.localhost</span>
                            </div>
                            <small class="text-muted">Hanya boleh huruf, angka, dan dash (-). Contoh: <code>ninskybrownies</code></small>
                            @error('subdomain')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>
                    </div>
                    <div class="modal-footer border-0 pt-0">
                        <button type="submit" class="btn btn-primary w-100 py-3 rounded-3 fw-bold" style="background: var(--accent-purple); border: none;">
                            <i class="fa-solid fa-circle-nodes me-2"></i>Inisialisasi Toko Baru Saya
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Bootstrap Bundle JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    @if(session('show_google_step2'))
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                var myModal = new bootstrap.Modal(document.getElementById('googleStep2Modal'));
                myModal.show();
            });
        </script>
    @endif
</body>
</html>
