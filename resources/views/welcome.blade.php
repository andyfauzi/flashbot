<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Tenanta.id SaaS Platform</title>
    
    <link rel="icon" type="image/png" href="{{ asset('img/tenanta.png') }}?v=4">
    <link rel="apple-touch-icon" href="{{ asset('img/tenanta.png') }}?v=4">

    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Outfit:wght@300;400;600;800&family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    
    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">

    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --secondary: #0f172a;
            --accent: #10b981;
            --text-dark: #1e293b;
            --text-light: #64748b;
            --bg-light: #f8fafc;
        }

        body {
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background-color: var(--bg-light);
            overflow-x: hidden;
        }

        h1, h2, h3, h4, h5, h6, .navbar-brand {
            font-family: 'Outfit', sans-serif;
        }

        /* Glassmorphism Navbar */
        .navbar {
            background: rgba(255, 255, 255, 0.8) !important;
            backdrop-filter: blur(10px);
            -webkit-backdrop-filter: blur(10px);
            border-bottom: 1px solid rgba(255,255,255,0.3);
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.05);
        }

        .navbar-brand {
            font-weight: 800;
            font-size: 1.5rem;
            color: var(--primary) !important;
        }

        .btn-custom {
            background-color: var(--primary);
            color: white;
            border-radius: 50px;
            padding: 10px 24px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
        }

        .btn-custom:hover {
            background-color: var(--primary-hover);
            transform: translateY(-2px);
            box-shadow: 0 10px 20px rgba(79, 70, 229, 0.2);
            color: white;
        }

        /* Hero Section */
        .hero-section {
            padding: 160px 0 100px;
            background: radial-gradient(circle at top right, rgba(79, 70, 229, 0.1), transparent 40%),
                        radial-gradient(circle at bottom left, rgba(16, 185, 129, 0.05), transparent 40%);
            min-height: 100vh;
            display: flex;
            align-items: center;
        }

        .hero-title {
            font-size: 4rem;
            font-weight: 800;
            line-height: 1.1;
            margin-bottom: 24px;
            background: linear-gradient(135deg, var(--secondary), var(--primary));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-subtitle {
            font-size: 1.25rem;
            color: var(--text-light);
            margin-bottom: 40px;
            font-weight: 400;
            line-height: 1.6;
        }

        .hero-image-wrapper {
            position: relative;
            z-index: 1;
        }

        .hero-image {
            border-radius: 24px;
            box-shadow: 0 30px 60px rgba(15, 23, 42, 0.1);
            border: 8px solid white;
            transform: perspective(1000px) rotateY(-5deg);
            transition: all 0.5s ease;
        }

        .hero-image:hover {
            transform: perspective(1000px) rotateY(0deg);
        }

        /* Pricing Section */
        .pricing-section {
            padding: 100px 0;
            background-color: white;
        }

        .pricing-card {
            border: 1px solid #e2e8f0;
            border-radius: 24px;
            padding: 40px 30px;
            transition: all 0.3s ease;
            height: 100%;
            background: white;
        }

        .pricing-card:hover {
            box-shadow: 0 20px 40px rgba(0,0,0,0.08);
            transform: translateY(-10px);
        }

        .pricing-card.popular {
            border: 2px solid var(--primary);
            position: relative;
        }

        .popular-badge {
            position: absolute;
            top: -15px;
            left: 50%;
            transform: translateX(-50%);
            background: var(--primary);
            color: white;
            padding: 5px 15px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }

        .price-amount {
            font-size: 2.5rem;
            font-weight: 800;
            color: var(--secondary);
            font-family: 'Outfit', sans-serif;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 30px 0;
        }

        .feature-list li {
            margin-bottom: 15px;
            color: var(--text-dark);
            display: flex;
            align-items: center;
        }

        .feature-list li i {
            color: var(--accent);
            margin-right: 10px;
        }

        /* Footer */
        .footer {
            background-color: var(--secondary);
            color: white;
            padding: 60px 0 30px;
        }

        .footer-title {
            color: white;
            font-weight: 700;
            margin-bottom: 20px;
        }

        .contact-item {
            margin-bottom: 15px;
            color: #94a3b8;
            display: flex;
            align-items: flex-start;
        }

        .contact-item i {
            margin-top: 5px;
            margin-right: 15px;
            color: var(--primary);
        }
    </style>
</head>
<body>

    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg fixed-top py-3">
        <div class="container">
            <a class="navbar-brand" href="#">
                <i class="fa-solid fa-bolt me-2"></i>Tenanta.id
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#home">Beranda</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#pricing">Harga</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link fw-medium" href="#contact">Kontak</a>
                    </li>
                </ul>
                <div class="d-flex gap-2">
                    @auth
                        @if(Auth::user()->is_super_admin)
                            <a href="{{ route('superadmin.index') }}" class="btn btn-custom">Dashboard Admin</a>
                        @else
                            <a href="{{ route('pos.index') }}" class="btn btn-outline-dark rounded-pill px-4">Kasir POS</a>
                        @endif
                    @else
                        <a href="#pricing" class="btn btn-custom rounded-pill px-4">Lihat Pilihan Paket</a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <section id="home" class="hero-section">
        <div class="container">
            <div class="row align-items-center">
                <div class="col-lg-6 mb-5 mb-lg-0 pe-lg-5">
                    <div class="badge bg-primary bg-opacity-10 text-primary px-3 py-2 rounded-pill mb-3 fw-semibold">
                        #1 Aplikasi Kasir Cloud Terbaik
                    </div>
                    <h1 class="hero-title" style="font-size: {{ $settings['hero_title_size'] ?? '4rem' }};">{{ $settings['hero_title'] ?? 'Platform SaaS Kasir Pintar' }}</h1>
                    <p class="hero-subtitle">{{ $settings['hero_subtitle'] ?? 'Kelola puluhan cabang toko Anda dalam satu pintu dengan teknologi cerdas.' }}</p>
                    <div class="d-flex gap-3">
                        <a href="{{ $settings['cta_link'] ?? '#pricing' }}" class="btn btn-custom btn-lg px-5">{{ $settings['cta_text'] ?? 'Mulai Sekarang' }}</a>
                    </div>
                </div>
                <div class="col-lg-6">
                    <div class="hero-image-wrapper">
                        <!-- Dashboard Image -->
                        @php
                            $heroImage = $settings['hero_image'] ?? null;
                            $imageUrl = $heroImage ? asset('storage/' . $heroImage) : 'https://images.unsplash.com/photo-1551288049-bebda4e38f71?auto=format&fit=crop&q=80&w=1000';
                        @endphp
                        <img src="{{ $imageUrl }}" alt="Dashboard Preview" class="img-fluid hero-image">
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Section -->
    <section id="pricing" class="pricing-section">
        <div class="container">
            <div class="text-center mb-5 pb-3">
                <h2 class="fw-bold" style="font-size: 3rem;">Pilih Paket Terbaik Anda</h2>
                <p class="text-muted fs-5">Skalakan bisnis Anda tanpa batasan teknologi.</p>
            </div>

            <div class="row g-4 justify-content-center">
                <!-- Starter Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card">
                        <h4 class="fw-bold text-success mb-3">Starter</h4>
                        <div class="price-amount mb-4">{{ $settings['price_starter'] ?? 'Rp 99.000' }} <span class="fs-6 text-muted fw-normal">/bulan</span></div>
                        <hr class="text-muted opacity-25">
                        <ul class="feature-list">
                            @foreach(explode("\n", $settings['features_starter'] ?? "1 Cabang Toko\nFitur Kasir Dasar\nLaporan Standar") as $feature)
                                @if(trim($feature))
                                    <li><i class="fa-solid fa-check-circle"></i> {{ trim($feature) }}</li>
                                @endif
                            @endforeach
                            @if(($settings['show_package_menus_on_pricing'] ?? '1') == '1' && isset($packageMenus))
                                <div class="collapse" id="collapseStarterMenusHome">
                                    <ul class="feature-list mb-2 mt-0">
                                    @foreach($packageMenus as $menu)
                                        <li>
                                            @if($menu->starter_enabled)
                                                <i class="fa-solid fa-check-circle text-success"></i> <span class="text-dark">{{ $menu->menu_label }}</span>
                                            @else
                                                <i class="fa-solid fa-xmark text-muted"></i> <span class="text-muted text-decoration-line-through">{{ $menu->menu_label }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                                <div class="text-center mb-3">
                                    <a data-bs-toggle="collapse" href="#collapseStarterMenusHome" role="button" aria-expanded="false" aria-controls="collapseStarterMenusHome" class="text-success text-decoration-none fw-bold small collapse-toggle-btn">
                                        Lihat Semua Fitur <i class="fa-solid fa-chevron-down"></i>
                                    </a>
                                </div>
                            @endif
                            <li>
                                <i class="fa-solid fa-users text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_karyawan_starter'] ?? 2) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_starter'] ?? 2) }} Akun Karyawan
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-robot text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_wa_starter'] ?? 1000) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_starter'] ?? 1000), 0, ',', '.') }} Pesan Bot WA/bln
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-mobile-screen text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_device_starter'] ?? 1) >= 999 ? 'Unlimited' : ($settings['limit_device_starter'] ?? 1) }} Device WA
                                </span>
                            </li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-3">
                            <a href="{{ route('auth.google', ['plan' => 'starter', 'trial' => '0']) }}" class="btn btn-success w-100 rounded-pill fw-bold py-2">Daftar Starter (Bulan)</a>
                            <a href="{{ route('auth.google', ['plan' => 'starter', 'trial' => '0', 'cycle' => 'yearly']) }}" class="btn btn-outline-success w-100 rounded-pill fw-bold py-2">Daftar 1 Tahun ({{ $settings['price_starter_yearly'] ?? 'Rp 990.000' }})</a>
                            <a href="{{ route('auth.google', ['plan' => 'starter', 'trial' => '1']) }}" class="btn btn-light text-success w-100 rounded-pill fw-bold py-2 border">Uji Coba Gratis 15 Hari</a>
                        </div>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card popular">
                        <div class="popular-badge">Paling Populer</div>
                        <h4 class="fw-bold text-primary mb-3">Pro</h4>
                        <div class="price-amount mb-4">{{ $settings['price_pro'] ?? 'Rp 199.000' }} <span class="fs-6 text-muted fw-normal">/bulan</span></div>
                        <hr class="text-muted opacity-25">
                        <ul class="feature-list">
                            @foreach(explode("\n", $settings['features_pro'] ?? "5 Cabang Toko\nFitur Manufaktur\nBot WhatsApp") as $feature)
                                @if(trim($feature))
                                    <li><i class="fa-solid fa-check-circle"></i> {{ trim($feature) }}</li>
                                @endif
                            @endforeach
                            @if(($settings['show_package_menus_on_pricing'] ?? '1') == '1' && isset($packageMenus))
                                <div class="collapse" id="collapseProMenusHome">
                                    <ul class="feature-list mb-2 mt-0">
                                    @foreach($packageMenus as $menu)
                                        <li>
                                            @if($menu->pro_enabled)
                                                <i class="fa-solid fa-check-circle text-success"></i> <span class="text-dark">{{ $menu->menu_label }}</span>
                                            @else
                                                <i class="fa-solid fa-xmark text-muted"></i> <span class="text-muted text-decoration-line-through">{{ $menu->menu_label }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                                <div class="text-center mb-3">
                                    <a data-bs-toggle="collapse" href="#collapseProMenusHome" role="button" aria-expanded="false" aria-controls="collapseProMenusHome" class="text-primary text-decoration-none fw-bold small collapse-toggle-btn">
                                        Lihat Semua Fitur <i class="fa-solid fa-chevron-down"></i>
                                    </a>
                                </div>
                            @endif
                            <li>
                                <i class="fa-solid fa-users text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_karyawan_pro'] ?? 10) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_pro'] ?? 10) }} Akun Karyawan
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-robot text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_wa_pro'] ?? 5000) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_pro'] ?? 5000), 0, ',', '.') }} Pesan Bot WA/bln
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-mobile-screen text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_device_pro'] ?? 3) >= 999 ? 'Unlimited' : ($settings['limit_device_pro'] ?? 3) }} Device WA
                                </span>
                            </li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-3">
                            <a href="{{ route('auth.google', ['plan' => 'pro', 'trial' => '0']) }}" class="btn btn-custom w-100 rounded-pill fw-bold py-2">Daftar Pro (Bulan)</a>
                            <a href="{{ route('auth.google', ['plan' => 'pro', 'trial' => '0', 'cycle' => 'yearly']) }}" class="btn btn-outline-primary w-100 rounded-pill fw-bold py-2">Daftar 1 Tahun ({{ $settings['price_pro_yearly'] ?? 'Rp 1.990.000' }})</a>
                            <a href="{{ route('auth.google', ['plan' => 'pro', 'trial' => '1']) }}" class="btn btn-light text-primary w-100 rounded-pill fw-bold py-2 border">Uji Coba Gratis 30 Hari</a>
                        </div>
                    </div>
                </div>

                <!-- Business Plan -->
                <div class="col-lg-4 col-md-6">
                    <div class="pricing-card border-dark border-opacity-10">
                        <h4 class="fw-bold text-dark mb-3">Business</h4>
                        <div class="price-amount mb-4">{{ $settings['price_business'] ?? 'Rp 499.000' }} <span class="fs-6 text-muted fw-normal">/bulan</span></div>
                        <hr class="text-muted opacity-25">
                        <ul class="feature-list">
                            @foreach(explode("\n", $settings['features_business'] ?? "Unlimited Cabang\nPrioritas Support\nWhite Label") as $feature)
                                @if(trim($feature))
                                    <li><i class="fa-solid fa-check-circle"></i> {{ trim($feature) }}</li>
                                @endif
                            @endforeach
                            @if(($settings['show_package_menus_on_pricing'] ?? '1') == '1' && isset($packageMenus))
                                <div class="collapse" id="collapseBusinessMenusHome">
                                    <ul class="feature-list mb-2 mt-0">
                                    @foreach($packageMenus as $menu)
                                        <li>
                                            @if($menu->business_enabled)
                                                <i class="fa-solid fa-check-circle text-success"></i> <span class="text-dark">{{ $menu->menu_label }}</span>
                                            @else
                                                <i class="fa-solid fa-xmark text-muted"></i> <span class="text-muted text-decoration-line-through">{{ $menu->menu_label }}</span>
                                            @endif
                                        </li>
                                    @endforeach
                                    </ul>
                                </div>
                                <div class="text-center mb-3">
                                    <a data-bs-toggle="collapse" href="#collapseBusinessMenusHome" role="button" aria-expanded="false" aria-controls="collapseBusinessMenusHome" class="text-dark text-decoration-none fw-bold small collapse-toggle-btn">
                                        Lihat Semua Fitur <i class="fa-solid fa-chevron-down"></i>
                                    </a>
                                </div>
                            @endif
                            <li>
                                <i class="fa-solid fa-users text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_karyawan_business'] ?? 999) >= 999 ? 'Unlimited' : ($settings['limit_karyawan_business'] ?? 999) }} Akun Karyawan
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-robot text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_wa_business'] ?? 999999) >= 999999 ? 'Unlimited' : number_format((int)($settings['limit_wa_business'] ?? 999999), 0, ',', '.') }} Pesan Bot WA/bln
                                </span>
                            </li>
                            <li>
                                <i class="fa-solid fa-mobile-screen text-primary"></i> 
                                <span class="text-dark">
                                    {{ ($settings['limit_device_business'] ?? 10) >= 999 ? 'Unlimited' : ($settings['limit_device_business'] ?? 10) }} Device WA
                                </span>
                            </li>
                        </ul>
                        <div class="d-flex flex-column gap-2 mt-3">
                            <a href="{{ route('auth.google', ['plan' => 'business', 'trial' => '0']) }}" class="btn btn-dark w-100 rounded-pill fw-bold py-2">Daftar Business (Bulan)</a>
                            <a href="{{ route('auth.google', ['plan' => 'business', 'trial' => '0', 'cycle' => 'yearly']) }}" class="btn btn-outline-dark w-100 rounded-pill fw-bold py-2">Daftar 1 Tahun ({{ $settings['price_business_yearly'] ?? 'Rp 4.990.000' }})</a>
                            <a href="{{ route('auth.google', ['plan' => 'business', 'trial' => '1']) }}" class="btn btn-light text-dark w-100 rounded-pill fw-bold py-2 border">Uji Coba Gratis 30 Hari</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if(!empty($settings['user_guide_text']) || !empty($settings['user_guide_image']))
    <!-- User Guide Section -->
    <section class="py-5 bg-white border-top">
        <div class="container">
            <div class="text-center mb-5">
                <h2 class="fw-bold" style="font-size: 2.5rem;">Petunjuk Penggunaan</h2>
                <p class="text-muted fs-5">Langkah mudah memulai bisnis dengan Tenanta.id</p>
            </div>
            <div class="row align-items-center">
                @if(!empty($settings['user_guide_image']))
                <div class="col-lg-6 mb-4 mb-lg-0">
                    <img src="{{ asset('storage/' . $settings['user_guide_image']) }}" class="img-fluid rounded-4 shadow" alt="Petunjuk Penggunaan">
                </div>
                <div class="col-lg-6">
                @else
                <div class="col-lg-12 text-center">
                @endif
                    <div class="fs-5 text-secondary" style="line-height: 1.8;">
                        {!! nl2br(e($settings['user_guide_text'] ?? '')) !!}
                    </div>
                </div>
            </div>
        </div>
    </section>
    @endif

    <!-- Footer -->
    <footer id="contact" class="footer">
        <div class="container">
            <div class="row">
                <div class="col-lg-4 mb-4">
                    <h3 class="footer-title"><i class="fa-solid fa-bolt text-primary me-2"></i>Tenanta.id</h3>
                    <p class="text-secondary opacity-75">Platform SaaS Kasir Pintar terdepan di Indonesia. Mengelola ribuan transaksi dengan kecepatan cahaya.</p>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Hubungi Kami</h5>
                    <div class="contact-item">
                        <i class="fa-solid fa-envelope"></i>
                        <span>{{ $settings['contact_email'] ?? 'hello@tenanta.id' }}</span>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-phone"></i>
                        <span>{{ $settings['contact_phone'] ?? '+62 812-3456-7890' }}</span>
                    </div>
                    <div class="contact-item">
                        <i class="fa-solid fa-location-dot"></i>
                        <span>{{ nl2br($settings['contact_address'] ?? 'Gedung Menara Mulia, Jakarta Selatan') }}</span>
                    </div>
                </div>
                <div class="col-lg-4 mb-4">
                    <h5 class="footer-title">Informasi Pembayaran</h5>
                    <div class="bg-dark bg-opacity-25 p-3 rounded text-light font-monospace" style="font-size: 0.9rem;">
                        {!! nl2br(e($settings['payment_instructions'] ?? "BCA: 1234567890\nMandiri: 0987654321\nA.n PT Tenanta Inovasi")) !!}
                    </div>
                </div>
            </div>
            <div class="border-top border-secondary mt-5 pt-4 text-center text-secondary opacity-75">
                &copy; {{ date('Y') }} Tenanta.id SaaS. All rights reserved.
            </div>
        </div>
    </footer>

    <!-- Google Onboarding Step 2 Modal -->
    @if(session('show_google_step2'))
    <div class="modal fade show d-block" id="googleOnboardingModal" tabindex="-1" style="background: rgba(0,0,0,0.5);" aria-modal="true" role="dialog">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 rounded-4 shadow-lg">
                <div class="modal-header border-0 bg-light rounded-top-4 p-4 pb-3 d-flex justify-content-between align-items-center">
                    <h5 class="modal-title fw-bold text-primary m-0"><i class="fa-solid fa-store me-2"></i>Lengkapi Detail Toko</h5>
                    <a href="{{ route('auth.google.cancel') }}" class="btn-close" aria-label="Close"></a>
                </div>
                <div class="modal-body p-4 pt-3">
                    <p class="text-muted mb-4">Satu langkah lagi! Halo <strong>{{ session('google_reg_name') }}</strong>, silakan isi nama dan alamat web toko Anda.</p>
                    
                    <form action="{{ route('auth.google.complete') }}" method="POST" id="formOnboarding">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Lengkap Owner</label>
                            <input type="text" name="owner_name" class="form-control form-control-lg @error('owner_name') is-invalid @enderror" value="{{ old('owner_name', session('google_reg_name')) }}" placeholder="Nama Anda" required>
                            @error('owner_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Nama Toko / Bisnis</label>
                            <input type="text" name="store_name" class="form-control form-control-lg @error('store_name') is-invalid @enderror" value="{{ old('store_name') }}" placeholder="Contoh: Toko Kue Budi" required>
                            @error('store_name')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Alamat Toko</label>
                            <textarea name="store_address" class="form-control @error('store_address') is-invalid @enderror" rows="2" placeholder="Alamat lengkap toko Anda" required>{{ old('store_address') }}</textarea>
                            @error('store_address')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">Jenis Layanan F&B</label>
                            <select name="jenis_layanan" class="form-select form-select-lg @error('jenis_layanan') is-invalid @enderror" required>
                                <option value="" disabled {{ old('jenis_layanan') ? '' : 'selected' }}>-- Pilih Jenis Layanan --</option>
                                <option value="dine_in" {{ old('jenis_layanan') == 'dine_in' ? 'selected' : '' }}>Hanya Dine-in (Makan di Tempat)</option>
                                <option value="take_away" {{ old('jenis_layanan') == 'take_away' ? 'selected' : '' }}>Hanya Take Away / Delivery</option>
                                <option value="keduanya" {{ old('jenis_layanan') == 'keduanya' ? 'selected' : '' }}>Melayani Keduanya</option>
                            </select>
                            <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;"><i class="fa-solid fa-info-circle me-1"></i>Pilihan ini menentukan fitur dashboard yang akan aktif.</small>
                            @error('jenis_layanan')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="row">
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Nomor WhatsApp</label>
                                <input type="text" name="whatsapp_number" class="form-control form-control-lg @error('whatsapp_number') is-invalid @enderror" value="{{ old('whatsapp_number') }}" placeholder="0812xxxx" required>
                                @error('whatsapp_number')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div class="col-md-12 mb-3">
                                <label class="form-label fw-semibold">Subdomain</label>
                                <div class="input-group input-group-lg flex-nowrap">
                                    <input type="text" name="subdomain" class="form-control @error('subdomain') is-invalid @enderror" style="min-width: 150px;" value="{{ old('subdomain') }}" placeholder="tokobudi" required>
                                    <span class="input-group-text text-muted bg-light border-start-0">.{{ request()->getHost() === 'localhost' ? 'localhost' : request()->getHost() }}</span>
                                </div>
                                <small class="text-muted mt-1 d-block" style="font-size: 0.8rem;"><i class="fa-solid fa-info-circle me-1"></i>Hanya huruf, angka, dan dash (-).</small>
                                @error('subdomain')
                                    <div class="invalid-feedback d-block">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        <div class="form-check mb-4 mt-2">
                            <input class="form-check-input @error('terms_accepted') is-invalid @enderror" type="checkbox" name="terms_accepted" id="termsAccepted" required>
                            <label class="form-check-label text-muted" for="termsAccepted" style="font-size: 0.9rem;">
                                Saya menyetujui <a href="{{ route('legal.terms') }}" target="_blank">Syarat & Ketentuan</a> dan <a href="{{ route('legal.privacy') }}" target="_blank">Kebijakan Privasi</a> yang berlaku, termasuk tunduk pada kepatuhan UU Pelindungan Data Pribadi (PDP) No. 27 Tahun 2022.
                            </label>
                            @error('terms_accepted')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="d-flex gap-2">
                            <a href="{{ route('auth.google.cancel') }}" class="btn btn-light btn-lg w-50 fw-bold border d-flex align-items-center justify-content-center" style="text-decoration: none;">Batal</a>
                            <button type="submit" class="btn btn-custom btn-lg w-50" id="btnSelesai">
                                Buat Toko <i class="fa-solid fa-arrow-right ms-2"></i>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
        document.getElementById('formOnboarding').addEventListener('submit', function() {
            var btn = document.getElementById('btnSelesai');
            btn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i>Mempersiapkan...';
            btn.disabled = true;
        });
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.querySelectorAll('.collapse-toggle-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                setTimeout(() => {
                    const isExpanded = this.getAttribute('aria-expanded') === 'true';
                    if(isExpanded) {
                        this.innerHTML = 'Sembunyikan Fitur <i class="fa-solid fa-chevron-up"></i>';
                    } else {
                        this.innerHTML = 'Lihat Semua Fitur <i class="fa-solid fa-chevron-down"></i>';
                    }
                }, 50);
            });
        });
    </script>
</body>
</html>
