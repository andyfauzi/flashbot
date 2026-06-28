<div class="accordion accordion-flush bg-transparent" id="accordion{{ $prefix }}">
    
    @if(auth()->user() && auth()->user()->isSales())
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingSales{{ $prefix }}">
            <a href="{{ route('sales.dashboard') }}" class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ request()->routeIs('sales.dashboard') ? '' : 'collapsed' }} text-decoration-none" style="display: block;">
                <i data-lucide="handshake" class="me-2 text-primary"></i> Dashboard Sales
            </a>
        </h2>
    </div>
    @endif
    
    @if(!auth()->user() || (!auth()->user()->is_super_admin && !auth()->user()->isSales()))
    <!-- ============================================== -->
    <!-- 🛒 KASIR & PENJUALAN -->
    <!-- ============================================== -->
    @can('akses_pos')
    @php $kasirActive = request()->routeIs('pos.*', 'dashboard.preorder.*', 'dashboard.transaksi.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingKasir{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $kasirActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKasir{{ $prefix }}" aria-expanded="{{ $kasirActive ? 'true' : 'false' }}" aria-controls="collapseKasir{{ $prefix }}">
                <i data-lucide="shopping-cart" class="me-2"></i> Kasir & Penjualan
            </button>
        </h2>
        <div id="collapseKasir{{ $prefix }}" class="accordion-collapse collapse {{ $kasirActive ? 'show' : '' }}" aria-labelledby="headingKasir{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('riwayat_transaksi'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('riwayat_transaksi'); @endphp
                <a href="{{ $hMenu ? route('dashboard.transaksi.index') : '#' }}" class="{{ request()->routeIs('dashboard.transaksi.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Riwayat Transaksi.\', \'info\');"' : '' !!}>
                    <i data-lucide="history"></i><span>Riwayat Transaksi</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('kasir_pos'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('kasir_pos'); @endphp
                <a href="{{ $hMenu ? route('pos.index') : '#' }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Kasir (POS).\', \'info\');"' : '' !!}>
                    <i data-lucide="banknote"></i><span>Kasir (POS)</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('jadwal_pesanan'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('jadwal_pesanan'); @endphp
                <a href="{{ $hMenu ? route('dashboard.preorder.index') : '#' }}" class="sidebar-item {{ Request::is('dashboard/preorder*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Daftar Pesanan.\', \'info\');"' : '' !!}>
                    <i data-lucide="list-ordered"></i><span>Daftar Pesanan</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- ============================================== -->
    <!-- 🛎️ DINE-IN & RESERVASI -->
    <!-- ============================================== -->
    @if(!isset($identitasToko) || $identitasToko->jenis_layanan !== 'take_away')
    @php 
        $dineInActive = request()->routeIs('dashboard.meja.*', 'dashboard.reservasi.*'); 
    @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingDineIn{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $dineInActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDineIn{{ $prefix }}" aria-expanded="{{ $dineInActive ? 'true' : 'false' }}" aria-controls="collapseDineIn{{ $prefix }}">
                <i data-lucide="bell-ring" class="me-2"></i> Dine-in & Reservasi
            </button>
        </h2>
        <div id="collapseDineIn{{ $prefix }}" class="accordion-collapse collapse {{ $dineInActive ? 'show' : '' }}" aria-labelledby="headingDineIn{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('manajemen_meja'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('manajemen_meja'); @endphp
                <a href="{{ $hMenu ? route('dashboard.meja.index') : '#' }}" class="{{ request()->routeIs('dashboard.meja.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Manajemen Meja.\', \'info\');"' : '' !!}>
                    <i data-lucide="layout-grid"></i><span>Manajemen Meja</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('jadwal_reservasi'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('jadwal_reservasi'); @endphp
                <a href="{{ $hMenu ? route('dashboard.reservasi.index') : '#' }}" class="{{ request()->routeIs('dashboard.reservasi.index') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Jadwal Reservasi.\', \'info\');"' : '' !!}>
                    <i data-lucide="calendar-clock"></i><span>Jadwal Reservasi</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                <a href="{{ $hMenu ? route('dashboard.reservasi.pengaturan') : '#' }}" class="{{ request()->routeIs('dashboard.reservasi.pengaturan') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Pengaturan Reservasi.\', \'info\');"' : '' !!}>
                    <i data-lucide="settings-2"></i><span>Pengaturan Operasional & Reservasi</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- 📦 PRODUK & INVENTORI -->
    <!-- ============================================== -->
    <!-- ============================================== -->
    <!-- 📦 PRODUK & INVENTORI -->
    <!-- ============================================== -->
    @if(auth()->user() && (auth()->user()->hasPermission('produk') || auth()->user()->hasPermission('stok')))
    @php $produkActive = request()->routeIs('chatbot.kategori.*', 'chatbot.produk.*', 'chatbot.stok.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingProduk{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $produkActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseProduk{{ $prefix }}" aria-expanded="{{ $produkActive ? 'true' : 'false' }}" aria-controls="collapseProduk{{ $prefix }}">
                <i data-lucide="package-open" class="me-2"></i> Produk & Inventori
            </button>
        </h2>
        <div id="collapseProduk{{ $prefix }}" class="accordion-collapse collapse {{ $produkActive ? 'show' : '' }}" aria-labelledby="headingProduk{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @if(auth()->user() && auth()->user()->hasPermission('produk'))
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('kategori_produk'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('kategori_produk'); @endphp
                <a href="{{ $hMenu ? route('chatbot.kategori.index') : '#' }}" class="{{ request()->routeIs('chatbot.kategori.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Kategori Produk.\', \'info\');"' : '' !!}>
                    <i data-lucide="tags"></i><span>Kategori Produk</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('produk_varian'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('produk_varian'); @endphp
                <a href="{{ $hMenu ? route('chatbot.produk.index') : '#' }}" class="{{ request()->routeIs('chatbot.produk.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Produk & Varian.\', \'info\');"' : '' !!}>
                    <i data-lucide="blocks"></i><span>Produk & Varian</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                @endif
                
                @if(auth()->user() && auth()->user()->hasPermission('stok'))
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('pengelolaan_stok'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('pengelolaan_stok'); @endphp
                <a href="{{ $hMenu ? route('chatbot.stok.index') : '#' }}" class="{{ request()->routeIs('chatbot.stok.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Pengelolaan Stok.\', \'info\');"' : '' !!}>
                    <i data-lucide="boxes"></i><span>Pengelolaan Stok</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- 🍳 PRODUKSI & HPP -->
    <!-- ============================================== -->
    @can('akses_hpp')
    @php $hppActive = request()->routeIs('dashboard.hpp.bahan.*', 'dashboard.hpp.kalkulator.*', 'dashboard.produksi.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingHpp{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $hppActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseHpp{{ $prefix }}" aria-expanded="{{ $hppActive ? 'true' : 'false' }}" aria-controls="collapseHpp{{ $prefix }}">
                <i data-lucide="utensils" class="me-2"></i> Produksi & HPP
            </button>
        </h2>
        <div id="collapseHpp{{ $prefix }}" class="accordion-collapse collapse {{ $hppActive ? 'show' : '' }}" aria-labelledby="headingHpp{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('master_bahan_baku'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('master_bahan_baku'); @endphp
                <a href="{{ $hMenu ? route('dashboard.hpp.bahan.index') : '#' }}" class="{{ request()->routeIs('dashboard.hpp.bahan.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Master Bahan Baku.\', \'info\');"' : '' !!}>
                    <i data-lucide="leaf"></i><span>Master Bahan Baku</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('kalkulator_hpp'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('kalkulator_hpp'); @endphp
                <a href="{{ $hMenu ? route('dashboard.hpp.kalkulator.index') : '#' }}" class="{{ request()->routeIs('dashboard.hpp.kalkulator.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Kalkulator HPP.\', \'info\');"' : '' !!}>
                    <i data-lucide="calculator"></i><span>Kalkulator HPP</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('produksi_dapur'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('produksi_dapur'); @endphp
                <a href="{{ $hMenu ? route('dashboard.produksi.index') : '#' }}" class="{{ request()->routeIs('dashboard.produksi.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Produksi Dapur.\', \'info\');"' : '' !!}>
                    <i data-lucide="factory"></i><span>Produksi Dapur</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                <a href="{{ route('dashboard.hpp.satuan.index') }}" class="{{ request()->routeIs('dashboard.hpp.satuan.*') ? 'active' : '' }}">
                    <i data-lucide="scale"></i><span>Pengaturan Satuan</span>
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- ============================================== -->
    <!-- 💰 KEUANGAN & LAPORAN -->
    <!-- ============================================== -->
    @can('akses_kas')
    @php $keuanganActive = request()->routeIs('dashboard.cash_flow.*', 'dashboard.kalkulator_bisnis.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingKeuangan{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $keuanganActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKeuangan{{ $prefix }}" aria-expanded="{{ $keuanganActive ? 'true' : 'false' }}" aria-controls="collapseKeuangan{{ $prefix }}">
                <i data-lucide="wallet" class="me-2"></i> Keuangan & Laporan
            </button>
        </h2>
        <div id="collapseKeuangan{{ $prefix }}" class="accordion-collapse collapse {{ $keuanganActive ? 'show' : '' }}" aria-labelledby="headingKeuangan{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('buku_kas_laporan'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('buku_kas_laporan'); @endphp
                <a href="{{ $hMenu ? route('dashboard.cash_flow.index') : '#' }}" class="{{ request()->routeIs('dashboard.cash_flow.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Buku Kas & Laporan.\', \'info\');"' : '' !!}>
                    <i data-lucide="receipt"></i><span>Buku Kas & Laporan</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('kalkulator_bisnis'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('kalkulator_bisnis'); @endphp
                <a href="{{ $hMenu ? route('dashboard.kalkulator_bisnis.index') : '#' }}" class="{{ request()->routeIs('dashboard.kalkulator_bisnis.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Kalkulator Bisnis & BEP.\', \'info\');"' : '' !!}>
                    <i data-lucide="calculator"></i><span>Kalkulator Bisnis / BEP</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
            </div>
        </div>
    </div>
    @endcan

    <!-- ============================================== -->
    <!-- 🤖 CHATBOT, WHATSAPP & GRUP -->
    <!-- ============================================== -->
    <!-- ============================================== -->
    <!-- 🤖 CHATBOT, WHATSAPP & GRUP -->
    <!-- ============================================== -->
    @if(auth()->user() && auth()->user()->isAdmin())
    @php $chatbotActive = request()->routeIs('chatbot.dashboard', 'chatbot.pesan', 'chatbot.users', 'chatbot.device*', 'chatbot.grup*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingChatbot{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $chatbotActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseChatbot{{ $prefix }}" aria-expanded="{{ $chatbotActive ? 'true' : 'false' }}" aria-controls="collapseChatbot{{ $prefix }}">
                <i data-lucide="bot" class="me-2"></i> Chatbot & WhatsApp
            </button>
        </h2>
        <div id="collapseChatbot{{ $prefix }}" class="accordion-collapse collapse {{ $chatbotActive ? 'show' : '' }}" aria-labelledby="headingChatbot{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('dashboard_chatbot'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('dashboard_chatbot'); @endphp
                <a href="{{ $hMenu ? route('chatbot.dashboard') : '#' }}" class="{{ request()->routeIs('chatbot.dashboard') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Dashboard Chatbot.\', \'info\');"' : '' !!}>
                    <i data-lucide="pie-chart"></i><span>Dashboard Chatbot</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('riwayat_pesan'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('riwayat_pesan'); @endphp
                <a href="{{ $hMenu ? route('chatbot.pesan') : '#' }}" class="{{ request()->routeIs('chatbot.pesan') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Riwayat Pesan.\', \'info\');"' : '' !!}>
                    <i data-lucide="message-square"></i><span>Riwayat Pesan</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('data_pengguna'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('data_pengguna'); @endphp
                <a href="{{ $hMenu ? route('chatbot.users') : '#' }}" class="{{ request()->routeIs('chatbot.users') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Data Pengguna.\', \'info\');"' : '' !!}>
                    <i data-lucide="users"></i><span>Data Pengguna (Users)</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('dashboard_grup'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('dashboard_grup'); @endphp
                <a href="{{ $hMenu ? route('chatbot.grup') : '#' }}" class="{{ request()->routeIs('chatbot.grup*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Dashboard Grup.\', \'info\');"' : '' !!}>
                    <i data-lucide="users" class="-viewfinder"></i><span>Dashboard Grup</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('pengaturan_device'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('pengaturan_device'); @endphp
                <a href="{{ $hMenu ? route('chatbot.device.index') : '#' }}" class="{{ request()->routeIs('chatbot.device*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Pengaturan Device.\', \'info\');"' : '' !!}>
                    <i data-lucide="smartphone"></i><span>Pengaturan Device</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @if(isset($identitasToko) && $identitasToko->is_broadcast_approved)
                <a href="{{ route('chatbot.broadcast.index') }}" class="{{ request()->routeIs('chatbot.broadcast*') ? 'active' : '' }}">
                    <i data-lucide="bullhorn"></i><span>Broadcast Promosi</span>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- ⚙️ PENGATURAN SISTEM -->
    <!-- ============================================== -->
    @if(auth()->user() && (auth()->user()->isAdmin() || auth()->user()->can('akses_karyawan')))
    @php $pengaturanActive = request()->routeIs('dashboard.users.*', 'dashboard.pengaturan.toko', 'dashboard.billing.*', 'chatbot.system_users.*', 'chatbot.kurir.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingPengaturan{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $pengaturanActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapsePengaturan{{ $prefix }}" aria-expanded="{{ $pengaturanActive ? 'true' : 'false' }}" aria-controls="collapsePengaturan{{ $prefix }}">
                <i data-lucide="settings" class="me-2"></i> Pengaturan Sistem
            </button>
        </h2>
        <div id="collapsePengaturan{{ $prefix }}" class="accordion-collapse collapse {{ $pengaturanActive ? 'show' : '' }}" aria-labelledby="headingPengaturan{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                @can('akses_karyawan')
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('hak_akses_karyawan'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('hak_akses_karyawan'); @endphp
                <a href="{{ $hMenu ? route('dashboard.users.index') : '#' }}" class="{{ request()->routeIs('dashboard.users.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Hak Akses Karyawan.\', \'info\');"' : '' !!}>
                    <i data-lucide="contact"></i><span>Hak Akses Karyawan</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                @endcan
                
                @if(auth()->user() && auth()->user()->isAdmin())
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('identitas_toko'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('identitas_toko'); @endphp
                <a href="{{ $hMenu ? route('dashboard.pengaturan.toko') : '#' }}" class="{{ request()->routeIs('dashboard.pengaturan.toko') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Identitas Toko.\', \'info\');"' : '' !!}>
                    <i data-lucide="store"></i><span>Identitas Toko</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                <a href="{{ $hMenu ? route('dashboard.pengaturan.landing_page') : '#' }}" class="{{ request()->routeIs('dashboard.pengaturan.landing_page') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Pengaturan Landing Page.\', \'info\');"' : '' !!}>
                    <i data-lucide="globe"></i><span>Pengaturan Landing Page</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('tagihan_paket'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('tagihan_paket'); @endphp
                <a href="{{ $hMenu ? route('dashboard.billing.index') : '#' }}" class="{{ request()->routeIs('dashboard.billing.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Tagihan & Paket.\', \'info\');"' : '' !!}>
                    <i data-lucide="credit-card"></i><span>Tagihan & Paket</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('pengaturan_pembayaran'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('pengaturan_pembayaran'); @endphp
                <a href="{{ $hMenu ? route('dashboard.pengaturan.payment') : '#' }}" class="{{ request()->routeIs('dashboard.pengaturan.payment') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Pengaturan Pembayaran.\', \'info\');"' : '' !!}>
                    <i data-lucide="credit-card"></i><span>Pengaturan Pembayaran</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('admin_whatsapp'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('admin_whatsapp'); @endphp
                <a href="{{ $hMenu ? route('chatbot.system_users.index') : '#' }}" class="{{ request()->routeIs('chatbot.system_users.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Admin WhatsApp.\', \'info\');"' : '' !!}>
                    <i data-lucide="shield-check"></i><span>Admin WhatsApp</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                @endif
                
                @if(auth()->user() && auth()->user()->isAdmin() && (!isset($identitasToko) || $identitasToko->jenis_layanan !== 'dine_in'))
                @php $hMenu = \App\Helpers\TenantPlanHelper::hasMenu('manajemen_kurir'); $reqPlan = \App\Helpers\TenantPlanHelper::getMinimumPlan('manajemen_kurir'); @endphp
                <a href="{{ $hMenu ? route('chatbot.kurir.index') : '#' }}" class="{{ request()->routeIs('chatbot.kurir.*') ? 'active' : '' }} {{ !$hMenu ? 'opacity-50' : '' }}" {!! !$hMenu ? 'onclick="event.preventDefault(); Swal.fire(\'Fitur Terkunci\', \'Silakan upgrade ke paket '.$reqPlan.' untuk mengakses Manajemen Kurir.\', \'info\');"' : '' !!}>
                    <i data-lucide="truck"></i><span>Manajemen Kurir</span>
                    @if(!$hMenu) <span class="badge bg-warning text-dark ms-auto" style="font-size: 0.6rem;">{{ $reqPlan }}</span> @endif
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif
    @endif



    <!-- ============================================== -->
    <!-- 👑 SUPER ADMIN (Landlord) -->
    <!-- ============================================== -->
    @if(auth()->user() && auth()->user()->is_super_admin)
    @php $superAdminActive = request()->routeIs('superadmin.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1 mt-3">
        <h2 class="accordion-header" id="headingSuperAdmin{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold text-danger {{ $superAdminActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseSuperAdmin{{ $prefix }}" aria-expanded="{{ $superAdminActive ? 'true' : 'false' }}" aria-controls="collapseSuperAdmin{{ $prefix }}">
                <i data-lucide="crown" class="me-2 text-danger"></i> Super Admin
            </button>
        </h2>
        <div id="collapseSuperAdmin{{ $prefix }}" class="accordion-collapse collapse {{ $superAdminActive ? 'show' : '' }}" aria-labelledby="headingSuperAdmin{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                <a href="{{ route('superadmin.index') }}" class="{{ request()->routeIs('superadmin.index') ? 'active' : '' }}">
                    <i data-lucide="users-gear"></i><span>Daftar Tenant</span>
                </a>
                <a href="{{ route('superadmin.requests') }}" class="{{ request()->routeIs('superadmin.requests') ? 'active' : '' }}">
                    <i data-lucide="clipboard-list"></i><span>Pendaftaran Tenant</span>
                </a>
                <a href="{{ route('superadmin.landing_page') }}" class="{{ request()->routeIs('superadmin.landing_page') ? 'active' : '' }}">
                    <i data-lucide="monitor-play"></i><span>Pengaturan Landing</span>
                </a>
                <a href="{{ route('superadmin.package_menus') }}" class="{{ request()->routeIs('superadmin.package_menus') ? 'active' : '' }}">
                    <i data-lucide="list-checks"></i><span>Paket Tenant (Matrix)</span>
                </a>
                <a href="{{ route('superadmin.help_guides') }}" class="{{ request()->routeIs('superadmin.help_guides') ? 'active' : '' }}">
                    <i data-lucide="book"></i><span>Panduan Tenant</span>
                </a>
                <a href="{{ route('superadmin.meta') }}" class="{{ request()->routeIs('superadmin.meta*') ? 'active' : '' }}">
                    <i data-lucide="message-circle"></i><span>Meta API Pusat</span>
                </a>
                <a href="{{ route('superadmin.midtrans') }}" class="{{ request()->routeIs('superadmin.midtrans*') ? 'active' : '' }}">
                    <i data-lucide="credit-card"></i><span>Midtrans Pusat</span>
                </a>
                <a href="{{ route('superadmin.broadcast') }}" class="{{ request()->routeIs('superadmin.broadcast*') ? 'active' : '' }}">
                    <i data-lucide="send"></i><span>Broadcast & Pesan</span>
                </a>
                <a href="{{ route('superadmin.vouchers.index') }}" class="{{ request()->routeIs('superadmin.vouchers*') ? 'active' : '' }}">
                    <i data-lucide="ticket"></i><span>Voucher Sales</span>
                </a>
                <a href="{{ route('superadmin.finance.index') }}" class="{{ request()->routeIs('superadmin.finance*') ? 'active' : '' }}">
                    <i data-lucide="wallet"></i><span>Laporan Keuangan</span>
                </a>
                <a href="{{ route('superadmin.logs') }}" class="{{ request()->routeIs('superadmin.logs*') ? 'active' : '' }}">
                    <i data-lucide="terminal"></i><span>System Error Logs</span>
                </a>
                <a href="{{ route('superadmin.audits') }}" class="{{ request()->routeIs('superadmin.audits*') ? 'active' : '' }}">
                    <i data-lucide="file-shield"></i><span>Audit Activity Logs</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- Bantuan Widget -->
    @if(auth()->user() && !auth()->user()->is_super_admin)
    <div class="mt-auto px-3 pb-4 pt-3">
        <div class="p-3 rounded-4" style="background: linear-gradient(135deg, rgba(79, 70, 229, 0.2) 0%, rgba(79, 70, 229, 0.05) 100%); border: 1px solid rgba(79, 70, 229, 0.3);">
            <div class="d-flex align-items-center gap-2 mb-2">
                <i data-lucide="headphones" style="color: #A5B4FC; width: 18px; height: 18px;"></i>
                <span style="font-weight: 600; color: #fff; font-size: 13px;">Butuh bantuan?</span>
            </div>
            <p style="font-size: 11px; color: rgba(255,255,255,0.6); margin-bottom: 12px;">Pusat Bantuan & Dokumentasi</p>
            <a href="{{ route('dashboard.help') }}" class="btn btn-sm w-100 d-flex justify-content-between align-items-center" style="background: var(--brand); color: #fff; border-radius: 8px; font-weight: 500; font-size: 12px; padding: 8px 12px;">
                Pusat Bantuan
                <i data-lucide="arrow-right" style="width: 14px; height: 14px;"></i>
            </a>
        </div>
    </div>
    @endif
</div>

<style>
    /* Styling for sidebar accordion to match premium look */
    .sidebar-premium .accordion-button {
        border-radius: 12px;
        transition: all 0.2s ease;
    }
    .sidebar-premium .accordion-button:not(.collapsed) {
        background-color: var(--sidebar-hover) !important;
        box-shadow: none;
    }
    .sidebar-premium .accordion-button:focus {
        box-shadow: none;
    }
    .sidebar-premium .accordion-button::after {
        background-size: 1rem;
        transition: transform 0.2s ease;
    }
    .sidebar-premium .accordion-item {
        margin: 0 10px;
    }
    .sidebar-premium .accordion-body a {
        display: flex;
        align-items: center;
        text-decoration: none;
    }
    .sidebar-premium .accordion-body a.opacity-50 {
        opacity: 0.6 !important;
        cursor: pointer;
    }
    .sidebar-premium .accordion-body a:hover.opacity-50 {
        opacity: 0.8 !important;
        background-color: rgba(255, 193, 7, 0.1);
    }
</style>
