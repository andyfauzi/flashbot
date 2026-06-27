@inject('waService', 'App\Services\WhatsAppService')
@php
    $gatewayStatus = $waService->statusGateway();
    $isConnected = isset($gatewayStatus['status']) && $gatewayStatus['status'] === 'connected';
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', isset($identitasToko) ? $identitasToko->nama_toko : 'NINSKY') | Panel Admin</title>
    <!-- PWA Meta Tags -->
    <link rel="manifest" href="{{ asset('manifest.json') }}">
    <meta name="theme-color" content="#0F172A">
    <link rel="icon" type="image/png" href="{{ asset('img/tenanta.png') }}?v=4">
    <link rel="apple-touch-icon" href="{{ asset('img/tenanta.png') }}?v=4">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="black-translucent">
    <meta name="apple-mobile-web-app-title" content="Ninsky">

    <!-- Inter Font Preload (Premium SaaS Typography) -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:ital,opsz,wght@0,14..32,400;0,14..32,500;0,14..32,600;0,14..32,700;0,14..32,800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 & FontAwesome CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Custom Premium Stylesheet -->
    <link href="{{ asset('css/dashboard.css') }}?v={{ filemtime(public_path('css/dashboard.css')) }}" rel="stylesheet">

    @yield('styles')
</head>
<body>

<!-- ======================================================
     NAVBAR PREMIUM — Dark Glass 68px
     ====================================================== -->
<nav class="navbar-premium px-3 px-md-4 d-flex justify-content-between align-items-center" style="height:68px; position:sticky; top:0; z-index:1030;">

    <div class="d-flex align-items-center gap-2">
        <!-- Mobile Toggle -->
        <button class="nav-icon-btn d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.09); color:rgba(255,255,255,0.7);">
            <i data-lucide="menu" style="width:18px;height:18px;"></i>
        </button>
        <!-- Desktop Toggle -->
        <button class="nav-icon-btn d-none d-md-flex align-items-center justify-content-center" type="button" id="toggleDesktopSidebar" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.09); color:rgba(255,255,255,0.7); border-radius: 8px; width: 36px; height: 36px;">
            <i data-lucide="menu" style="width:18px;height:18px;"></i>
        </button>

        <a class="navbar-brand m-0" href="{{ route('superadmin.index') }}">
            <div class="brand-logo-wrap">
                <i class="fa-solid fa-crown" style="color:var(--brand); font-size:16px;"></i>
            </div>
            <span class="brand-name d-none d-sm-block fs-5 ms-1">
                SUPER ADMIN
            </span>
        </a>
    </div>

    <!-- RIGHT: Actions -->
    <div class="d-flex align-items-center gap-2">

        <!-- UI Mode Toggle -->
        @auth
        <form action="{{ route('dashboard.ui_mode.toggle') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="nav-icon-btn d-flex align-items-center justify-content-center" title="Ubah Tampilan" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); width:36px; height:36px; border-radius:8px;">
                <i data-lucide="{{ auth()->user()->ui_mode === 'grid' ? 'list' : 'layout-grid' }}" style="width:18px;height:18px;color:rgba(255,255,255,0.8);"></i>
            </button>
        </form>
        @endauth

        <!-- Refresh -->
        <button onclick="window.location.reload(true)" class="nav-icon-btn d-flex align-items-center justify-content-center" title="Refresh Halaman" style="background:rgba(255,255,255,0.05); border:1px solid rgba(255,255,255,0.1); width:36px; height:36px; border-radius:8px;">
            <i data-lucide="refresh-cw" style="width:16px;height:16px;color:rgba(255,255,255,0.8);"></i>
        </button>

        <!-- Status Gateway Badge Removed for Super Admin -->
        <!-- Shift Buttons Removed for Super Admin -->

        <!-- Divider -->
            <div style="width:1px; height:28px; background:rgba(255,255,255,0.12); margin:0 4px;"></div>

            <!-- User Dropdown -->
            @auth
            <div class="dropdown">
                <div class="navbar-user-chip" data-bs-toggle="dropdown" aria-expanded="false" style="cursor: pointer;">
                    <div class="navbar-user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? auth()->user()->email, 0, 1)) }}
                    </div>
                    <span class="navbar-user-name d-none d-md-block">{{ auth()->user()->name ?? auth()->user()->email }}</span>
                    <i data-lucide="chevron-down" style="width:14px;height:14px;color:rgba(255,255,255,0.45);"></i>
                </div>
                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="min-width: 200px; border-radius: 12px; margin-top: 8px;">
                    <li class="px-3 py-2 border-bottom mb-1">
                        <div class="fw-bold text-dark">{{ auth()->user()->name ?? 'User' }}</div>
                        <div class="text-muted small" style="font-size: 11px;">{{ auth()->user()->email }}</div>
                    </li>
                    <li>
                        <a class="dropdown-item d-flex align-items-center gap-2 py-2 text-danger fw-medium" href="{{ route('logout') }}">
                            <i data-lucide="log-out" style="width:16px;height:16px;"></i>
                            Keluar (Logout)
                        </a>
                    </li>
                </ul>
            </div>
        @endauth
    </div>
</nav>

<!-- Mobile Offcanvas Sidebar -->
<div class="offcanvas offcanvas-start sidebar-premium" tabindex="-1" id="sidebarOffcanvas" aria-labelledby="sidebarOffcanvasLabel">
    <div class="offcanvas-header border-bottom px-4">
        <h5 class="offcanvas-title fw-bold d-flex align-items-center gap-2" id="sidebarOffcanvasLabel" style="font-family: var(--font-heading); color: var(--text-primary);">
            <i class="fa-solid fa-crown text-primary"></i>
            SUPER ADMIN
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-column pt-2 px-0 {{ (auth()->user() && auth()->user()->ui_mode === 'grid') ? 'grid-mode-menu' : '' }}">
        @include('layouts.superadmin_sidebar_menu', ['prefix' => 'Mobile'])
    </div>
</div>

<div class="d-flex min-vh-100">
    <!-- Sidebar Container (Desktop) -->
    <div class="sidebar-premium sidebar-sticky d-none d-md-block flex-shrink-0" id="desktopSidebarContainer">
        <div class="pt-4 px-0 d-flex flex-column h-100 {{ (auth()->user() && auth()->user()->ui_mode === 'grid') ? 'grid-mode-menu' : '' }}">
            @include('layouts.superadmin_sidebar_menu', ['prefix' => 'Desktop'])
        </div>
    </div>

    <!-- Konten Utama -->
    <div class="flex-grow-1 min-vw-0 bg-app py-4 px-3 px-md-4">
            @if(session('sukses'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'success',
                            title: 'Berhasil!',
                            text: '{{ session('sukses') }}',
                            timer: 3000,
                            showConfirmButton: false,
                            toast: true,
                            position: 'top-end',
                            background: '#FAF7F4',
                            color: '#3A3A3A'
                        });
                    });
                </script>
            @endif
            
            @if($errors->any())
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Periksa kembali data Anda',
                            html: '<ul class="text-start mb-0 ps-3" style="color:#64748B;">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul>',
                            confirmButtonColor: '#4F46E5',
                            confirmButtonText: 'Baik, mengerti',
                            background: '#FFFFFF',
                            color: '#0F172A',
                            borderRadius: '20px'
                        });
                    });
                </script>
            @endif

            @if(session('error'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'error',
                            title: 'Terjadi Kesalahan',
                            text: '{{ session('error') }}',
                            confirmButtonColor: '#4F46E5',
                            background: '#FFFFFF',
                            borderRadius: '20px'
                        });
                    });
                </script>
            @endif

            @if(session('warning'))
                <script>
                    document.addEventListener('DOMContentLoaded', function() {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Perhatian',
                            text: '{{ session('warning') }}',
                            confirmButtonColor: '#4F46E5',
                            background: '#FFFFFF',
                            borderRadius: '20px'
                        });
                    });
                </script>
            @endif

            @if(auth()->check() && auth()->user()->must_change_password)
                <div class="alert alert-warning d-flex align-items-center shadow-sm fade show" role="alert" style="border-radius: 12px; border-left: 5px solid #f59e0b;">
                    <i data-lucide="shield-alert" class="me-3 text-warning" style="width: 24px; height: 24px;"></i>
                    <div>
                        <h6 class="alert-heading fw-bold mb-1">Keamanan Akun: Harap Ganti Password Anda!</h6>
                        <p class="mb-0 text-muted small">Anda saat ini masuk menggunakan password sementara. Demi keamanan toko Anda, segera perbarui password Anda (Anda bisa menggantinya di menu Karyawan jika fitur profil belum tersedia).</p>
                    </div>
                </div>
            @endif
            
            <div class="content-wrapper">
                
                @php
                    // Check if there is a global announcement
                    $announcement = '';
                    try {
                        // Accessing landlord database from tenant
                        $announcement = \Illuminate\Support\Facades\DB::connection('landlord')
                            ->table('landlord_settings')
                            ->where('key', 'global_announcement_text')
                            ->value('value');
                    } catch (\Exception $e) {}
                @endphp

                @if(!empty($announcement))
                <div class="container-fluid px-4 pt-4">
                    <div class="alert alert-warning alert-dismissible fade show border-0 shadow-sm rounded-4 d-flex align-items-center" role="alert">
                        <i class="fa-solid fa-bullhorn fa-lg me-3 text-warning-emphasis"></i>
                        <div class="text-dark fw-medium">
                            {!! nl2br(e($announcement)) !!}
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                </div>
                @endif

                @yield('content')
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pulltorefreshjs/0.2.9/index.umd.min.js"></script>
<script>
  // PWA Service Worker Registration
  if ('serviceWorker' in navigator) {
    window.addEventListener('load', () => {
      navigator.serviceWorker.register('/sw.js')
        .then(registration => {
          console.log('ServiceWorker registration successful');
        })
        .catch(err => {
          console.log('ServiceWorker registration failed: ', err);
        });
    });
  }

  // Pull-to-Refresh untuk Mode PWA
  if (typeof PullToRefresh !== 'undefined') {
      PullToRefresh.init({
          mainElement: 'body',
          instructionsPullToRefresh: 'Tarik ke bawah untuk memuat ulang',
          instructionsReleaseToRefresh: 'Lepaskan untuk memuat ulang',
          instructionsRefreshing: 'Memuat ulang...',
          onRefresh: function() {
              window.location.reload(true);
          }
      });
  }
</script>



    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons({
          attrs: {
              'stroke-width': 1.5,
              'width': 20,
              'height': 20
          }
      });

      document.addEventListener('DOMContentLoaded', function() {
          const toggleDesktopBtn = document.getElementById('toggleDesktopSidebar');
          const desktopSidebar = document.getElementById('desktopSidebarContainer');
          
          if (toggleDesktopBtn && desktopSidebar) {
              toggleDesktopBtn.addEventListener('click', function() {
                  desktopSidebar.classList.toggle('d-md-block');
              });
          }
      });
    </script>
    @yield('scripts')
</body>
</html>
