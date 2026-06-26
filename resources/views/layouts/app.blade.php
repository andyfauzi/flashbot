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

    <!-- LEFT: Hamburger (mobile) + Brand -->
    <div class="d-flex align-items-center gap-2">
        <button class="nav-icon-btn d-md-none" type="button" data-bs-toggle="offcanvas" data-bs-target="#sidebarOffcanvas" style="background:rgba(255,255,255,0.07); border:1px solid rgba(255,255,255,0.09); color:rgba(255,255,255,0.7);">
            <i data-lucide="menu" style="width:18px;height:18px;"></i>
        </button>

        <a class="navbar-brand m-0" href="{{ route('chatbot.dashboard') }}">
            @if(isset($identitasToko) && $identitasToko->logo_path)
                <img src="{{ asset('storage/' . $identitasToko->logo_path) }}" alt="Logo" style="height:30px; width:30px; object-fit:cover; border-radius:8px; border:1.5px solid rgba(255,255,255,0.15);">
            @else
                <div class="brand-logo-wrap">
                    <i class="fa-solid fa-bolt" style="color:#fff; font-size:14px;"></i>
                </div>
            @endif
            <span class="brand-name d-none d-sm-block">
                {{ isset($identitasToko) ? strtoupper($identitasToko->nama_toko) : 'TENANTA.ID' }}
            </span>
        </a>
    </div>

    <!-- RIGHT: Actions -->
    <div class="d-flex align-items-center gap-2">

        <!-- UI Mode Toggle -->
        @auth
        <form action="{{ route('dashboard.ui_mode.toggle') }}" method="POST" class="m-0">
            @csrf
            <button type="submit" class="nav-icon-btn" title="Ubah Tampilan">
                <i data-lucide="{{ auth()->user()->ui_mode === 'grid' ? 'list' : 'layout-grid' }}" style="width:16px;height:16px;"></i>
            </button>
        </form>
        @endauth

        <!-- Refresh -->
        <button onclick="window.location.reload(true)" class="nav-icon-btn" title="Refresh Halaman">
            <i data-lucide="refresh-cw" style="width:16px;height:16px;"></i>
        </button>

        <!-- Status Gateway Badge -->
        <div class="d-none d-sm-flex">
            @if($isConnected)
                <div class="status-badge online">
                    <div class="pulse-dot"></div>
                    <span>{{ strtoupper($gatewayStatus['gateway'] ?? 'WA') }} Connected</span>
                </div>
            @else
                <div class="status-badge offline">
                    <div class="pulse-dot"></div>
                    <span>Disconnected</span>
                </div>
            @endif
        </div>

        @auth
            @php
                $activeShift = null;
                if (app()->has('current_tenant') && auth()->check()) {
                    try {
                        $activeShift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
                    } catch (\Exception $e) {}
                }
            @endphp

            @if($activeShift)
                <!-- Pengeluaran -->
                <button type="button" class="btn-pengeluaran d-none d-md-inline-flex" data-bs-toggle="modal" data-bs-target="#pengeluaranModal">
                    <i data-lucide="banknote" style="width:15px;height:15px;"></i>
                    <span>Pengeluaran</span>
                </button>
                <!-- Tutup Shift CTA -->
                <button type="button" class="btn-tutup-shift" data-bs-toggle="modal" data-bs-target="#tutupShiftModal">
                    <i data-lucide="lock" style="width:15px;height:15px;"></i>
                    <span class="d-none d-sm-inline">Tutup Shift</span>
                </button>
            @endif

            <!-- Divider -->
            <div style="width:1px; height:28px; background:rgba(255,255,255,0.12); margin:0 4px;"></div>

            <!-- User Dropdown -->
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
            @if(isset($identitasToko) && $identitasToko->logo_path)
                <img src="{{ asset('storage/' . $identitasToko->logo_path) }}" alt="Logo" style="height: 24px; object-fit: contain;" class="me-1" loading="lazy">
            @else
                <i class="fa-solid fa-store text-primary"></i>
            @endif
            {{ isset($identitasToko) ? strtoupper($identitasToko->nama_toko) : 'TENANTA.ID' }}
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="offcanvas" aria-label="Close"></button>
    </div>
    <div class="offcanvas-body flex-column pt-2 px-0 {{ (auth()->user() && auth()->user()->ui_mode === 'grid') ? 'grid-mode-menu' : '' }}">
        @include('layouts.sidebar_menu', ['prefix' => 'Mobile'])
    </div>
</div>

<div class="d-flex min-vh-100">
    <!-- Sidebar Container (Desktop) -->
    <div class="sidebar-premium sidebar-sticky d-none d-md-block flex-shrink-0">
        <div class="pt-4 px-0 {{ (auth()->user() && auth()->user()->ui_mode === 'grid') ? 'grid-mode-menu' : '' }}">
            @include('layouts.sidebar_menu', ['prefix' => 'Desktop'])
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

@auth
@if(isset($activeShift) && $activeShift)
<!-- Modal Pengeluaran Kasir -->
<div class="modal fade" id="pengeluaranModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('dashboard.shift.pengeluaran') }}" method="POST" class="modal-content" onsubmit="return preventDoubleSubmit(this);">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i data-lucide="arrow-right-left" class="text-warning me-2"></i>Catat Pengeluaran Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label">Nominal (Rp)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" name="nominal" class="form-control" placeholder="Contoh: 15000" required>
                </div>
                <div class="mb-3">
                    <label class="form-label">Keterangan / Keperluan</label>
                    <input type="text" name="keterangan" class="form-control" placeholder="Beli es batu dadakan" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-warning w-100 fw-bold">Simpan Pengeluaran</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tutup Shift -->
<div class="modal fade" id="tutupShiftModal" tabindex="-1">
    <div class="modal-dialog">
        <form action="{{ route('dashboard.shift.tutup') }}" method="POST" class="modal-content" onsubmit="return preventDoubleSubmit(this);">
            @csrf
            <div class="modal-header">
                <h5 class="modal-title"><i data-lucide="lock" class="text-danger me-2"></i>Tutup Shift Kasir</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="alert alert-info">
                    <ul class="mb-0">
                        <li>Modal Awal: <strong>Rp {{ number_format($activeShift->modal_awal, 0, ',', '.') }}</strong></li>
                        <li>Penjualan Tunai: <strong>Rp {{ number_format($activeShift->total_penjualan_tunai, 0, ',', '.') }}</strong></li>
                        <li>Pengeluaran: <strong>Rp {{ number_format($activeShift->pengeluaran_kasir, 0, ',', '.') }}</strong></li>
                    </ul>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-bold text-danger">Total Uang Fisik yang Ada di Laci Sekarang (Rp)</label>
                    <input type="text" inputmode="numeric" pattern="[0-9]*" name="uang_fisik" class="form-control form-control-lg border-danger" placeholder="Hitung uang tunai Anda..." required>
                    <small class="text-muted">Masukkan jumlah asli sesuai fisik uang, sistem akan mencocokkannya secara otomatis.</small>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-danger w-100 fw-bold">Kunci Laci & Tutup Shift</button>
            </div>
        </form>
    </div>
</div>
@endif
@endauth

<!-- Bottom Navigation (Mobile Only) -->
<nav class="bottom-nav d-md-none fixed-bottom bg-white border-top py-2 d-flex justify-content-around shadow-sm" style="z-index: 1040;">
    <a href="{{ route('chatbot.dashboard') }}" class="text-center text-decoration-none {{ request()->routeIs('chatbot.dashboard') ? 'text-primary' : 'text-secondary' }}">
        <i data-lucide="home" class="d-block mx-auto mb-1" style="width: 20px; height: 20px;"></i>
        <span style="font-size: 0.7rem; font-weight: 500;">Home</span>
    </a>
    <a href="{{ route('pos.index') }}" class="text-center text-decoration-none {{ request()->routeIs('pos.*') ? 'text-primary' : 'text-secondary' }}">
        <i data-lucide="banknote" class="d-block mx-auto mb-1" style="width: 20px; height: 20px;"></i>
        <span style="font-size: 0.7rem; font-weight: 500;">Kasir</span>
    </a>
    <!-- FAB (Floating Action Button) -->
    <a href="{{ route('dashboard.preorder.index') }}" class="text-center text-decoration-none">
        <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center shadow-lg mx-auto" style="width: 50px; height: 50px; margin-top: -25px; border: 4px solid #FFFCFA;">
            <i data-lucide="calendar-check" style="width: 24px; height: 24px;"></i>
        </div>
        <span class="text-secondary" style="font-size: 0.7rem; font-weight: 500;">Pesanan</span>
    </a>
    <a href="{{ route('chatbot.grup') }}" class="text-center text-decoration-none {{ request()->routeIs('chatbot.grup*') ? 'text-primary' : 'text-secondary' }}">
        <i data-lucide="users" class="d-block mx-auto mb-1" style="width: 20px; height: 20px;"></i>
        <span style="font-size: 0.7rem; font-weight: 500;">Grup</span>
    </a>
    <a href="#" class="text-center text-decoration-none text-secondary">
        <i data-lucide="settings" class="d-block mx-auto mb-1" style="width: 20px; height: 20px;"></i>
        <span style="font-size: 0.7rem; font-weight: 500;">Setelan</span>
    </a>
</nav>

<!-- Padding helper agar konten tidak tertutup bottom nav -->
<div class="d-md-none" style="height: 70px;"></div>
<!-- Idle Detection Modal -->
@auth
<div class="modal fade" id="idleModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4">
            <div class="modal-body text-center p-5">
                <i data-lucide="clock" class="text-warning mb-3" style="width: 64px; height: 64px;"></i>
                <h4 class="fw-bold mb-3">Sesi Akan Berakhir</h4>
                <p class="text-secondary mb-4">Sesi Anda akan berakhir dalam <span id="idleCountdown" class="fw-bold text-danger">120</span> detik karena tidak ada aktivitas.</p>
                <div class="d-flex gap-3 justify-content-center">
                    <button type="button" class="btn btn-outline-secondary px-4 rounded-3" onclick="window.location.href='{{ route('logout') }}'">Logout</button>
                    <button type="button" class="btn btn-primary px-4 rounded-3" onclick="extendSession()">Tetap Login</button>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let idleTime = 0;
    let countdownTime = 120;
    let idleInterval;
    let countdownInterval;
    
    // 18 minutes = 1080 seconds
    const maxIdleTime = 1080;

    function resetTimer() {
        idleTime = 0;
        if (countdownInterval) {
            clearInterval(countdownInterval);
            countdownInterval = null;
            const modal = bootstrap.Modal.getInstance(document.getElementById('idleModal'));
            if (modal) modal.hide();
        }
    }

    function timerIncrement() {
        idleTime++;
        if (idleTime > maxIdleTime && !countdownInterval) {
            // Show modal and start countdown
            countdownTime = 120;
            document.getElementById('idleCountdown').innerText = countdownTime;
            const idleModal = new bootstrap.Modal(document.getElementById('idleModal'));
            idleModal.show();

            countdownInterval = setInterval(() => {
                countdownTime--;
                document.getElementById('idleCountdown').innerText = countdownTime;
                if (countdownTime <= 0) {
                    window.location.href = "{{ route('login') }}";
                }
            }, 1000);
        }
    }

    function extendSession() {
        fetch('/api/heartbeat', {
            method: 'POST',
            headers: {
                'X-CSRF-TOKEN': '{{ csrf_token() }}',
                'Accept': 'application/json'
            }
        }).then(response => {
            if (response.ok) {
                resetTimer();
            } else {
                window.location.href = "{{ route('login') }}";
            }
        }).catch(() => {
            window.location.href = "{{ route('login') }}";
        });
    }

    // Bind events
    document.addEventListener('mousemove', resetTimer);
    document.addEventListener('keypress', resetTimer);
    document.addEventListener('scroll', resetTimer);
    document.addEventListener('click', resetTimer);

    // Increment timer every second
    idleInterval = setInterval(timerIncrement, 1000);

    // Double Submit Protection untuk UX Kasir
    function preventDoubleSubmit(form) {
        const btn = form.querySelector('button[type="submit"]');
        if (btn) {
            btn.disabled = true;
            btn.innerHTML = '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span> Memproses...';
        }
        return true;
    }
</script>
@endauth

    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
      lucide.createIcons({
          attrs: {
              'stroke-width': 1.5,
              'width': 20,
              'height': 20
          }
      });
    </script>
    @yield('scripts')
</body>
</html>