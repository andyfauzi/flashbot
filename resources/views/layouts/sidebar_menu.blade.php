<div class="accordion accordion-flush bg-transparent" id="accordion{{ $prefix }}">
    
    @if(!auth()->user() || !auth()->user()->is_super_admin)
    <!-- ============================================== -->
    <!-- 🛒 KASIR & PENJUALAN -->
    <!-- ============================================== -->
    @if(config('flashbot.features.pos'))
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
                <a href="{{ route('dashboard.transaksi.index') }}" class="{{ request()->routeIs('dashboard.transaksi.*') ? 'active' : '' }}">
                    <i data-lucide="history"></i><span>Riwayat Transaksi</span>
                </a>
                <a href="{{ route('pos.index') }}" class="{{ request()->routeIs('pos.*') ? 'active' : '' }}">
                    <i data-lucide="banknote"></i><span>Kasir (POS)</span>
                </a>
                <a href="{{ route('dashboard.preorder.index') }}" class="{{ request()->routeIs('dashboard.preorder.*') ? 'active' : '' }}">
                    <i data-lucide="calendar-check"></i><span>Jadwal Pesanan</span>
                </a>
            </div>
        </div>
    </div>
    @endcan
    @endif

    <!-- ============================================== -->
    <!-- 🛎️ DINE-IN & RESERVASI -->
    <!-- ============================================== -->
    @if(!isset($identitasToko) || $identitasToko->jenis_layanan !== 'take_away')
    @php $dineInActive = request()->routeIs('dashboard.meja.*', 'dashboard.reservasi.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingDineIn{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $dineInActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseDineIn{{ $prefix }}" aria-expanded="{{ $dineInActive ? 'true' : 'false' }}" aria-controls="collapseDineIn{{ $prefix }}">
                <i data-lucide="bell-ring" class="me-2"></i> Dine-in & Reservasi
            </button>
        </h2>
        <div id="collapseDineIn{{ $prefix }}" class="accordion-collapse collapse {{ $dineInActive ? 'show' : '' }}" aria-labelledby="headingDineIn{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                <a href="{{ route('dashboard.meja.index') }}" class="{{ request()->routeIs('dashboard.meja.*') ? 'active' : '' }}">
                    <i data-lucide="layout-grid"></i><span>Manajemen Meja</span>
                </a>
                <a href="{{ route('dashboard.reservasi.index') }}" class="{{ request()->routeIs('dashboard.reservasi.*') ? 'active' : '' }}">
                    <i data-lucide="calendar-clock"></i><span>Jadwal Reservasi</span>
                </a>
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- 📦 PRODUK & INVENTORI -->
    <!-- ============================================== -->
    @if((auth()->user() && auth()->user()->hasPermission('produk')) || (auth()->user() && auth()->user()->hasPermission('stok')))
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
                <a href="{{ route('chatbot.kategori.index') }}" class="{{ request()->routeIs('chatbot.kategori.*') ? 'active' : '' }}">
                    <i data-lucide="tags"></i><span>Kategori Produk</span>
                </a>
                <a href="{{ route('chatbot.produk.index') }}" class="{{ request()->routeIs('chatbot.produk.*') ? 'active' : '' }}">
                    <i data-lucide="blocks"></i><span>Produk & Varian</span>
                </a>
                @endif
                @if(auth()->user() && auth()->user()->hasPermission('stok'))
                <a href="{{ route('chatbot.stok.index') }}" class="{{ request()->routeIs('chatbot.stok.*') ? 'active' : '' }}">
                    <i data-lucide="boxes"></i><span>Pengelolaan Stok</span>
                </a>
                @endif
            </div>
        </div>
    </div>
    @endif

    <!-- ============================================== -->
    <!-- 🍳 PRODUKSI & HPP -->
    <!-- ============================================== -->
    @if(config('flashbot.features.erp'))
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
                <a href="{{ route('dashboard.hpp.bahan.index') }}" class="{{ request()->routeIs('dashboard.hpp.bahan.*') ? 'active' : '' }}">
                    <i data-lucide="leaf"></i><span>Master Bahan Baku</span>
                </a>
                <a href="{{ route('dashboard.hpp.kalkulator.index') }}" class="{{ request()->routeIs('dashboard.hpp.kalkulator.*') ? 'active' : '' }}">
                    <i data-lucide="calculator"></i><span>Kalkulator HPP</span>
                </a>
                <a href="{{ route('dashboard.produksi.index') }}" class="{{ request()->routeIs('dashboard.produksi.*') ? 'active' : '' }}">
                    <i data-lucide="factory"></i><span>Produksi Dapur</span>
                </a>
            </div>
        </div>
    </div>
    @endcan
    @endif

    <!-- ============================================== -->
    <!-- 💰 KEUANGAN & LAPORAN -->
    <!-- ============================================== -->
    @if(config('flashbot.features.finance'))
    @can('akses_kas')
    @php $keuanganActive = request()->routeIs('dashboard.cash_flow.*'); @endphp
    <div class="accordion-item bg-transparent border-0 mb-1">
        <h2 class="accordion-header" id="headingKeuangan{{ $prefix }}">
            <button class="accordion-button bg-transparent shadow-none px-3 py-2 fw-bold {{ $keuanganActive ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKeuangan{{ $prefix }}" aria-expanded="{{ $keuanganActive ? 'true' : 'false' }}" aria-controls="collapseKeuangan{{ $prefix }}">
                <i data-lucide="wallet" class="me-2"></i> Keuangan & Laporan
            </button>
        </h2>
        <div id="collapseKeuangan{{ $prefix }}" class="accordion-collapse collapse {{ $keuanganActive ? 'show' : '' }}" aria-labelledby="headingKeuangan{{ $prefix }}" data-bs-parent="#accordion{{ $prefix }}">
            <div class="accordion-body p-0 pt-1 pb-2">
                <a href="{{ route('dashboard.cash_flow.index') }}" class="{{ request()->routeIs('dashboard.cash_flow.*') ? 'active' : '' }}">
                    <i data-lucide="receipt"></i><span>Buku Kas & Laporan</span>
                </a>
            </div>
        </div>
    </div>
    @endcan
    @endif

    <!-- ============================================== -->
    <!-- 🤖 CHATBOT, WHATSAPP & GRUP -->
    <!-- ============================================== -->
    @if(config('flashbot.features.chatbot'))
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
                <a href="{{ route('chatbot.dashboard') }}" class="{{ request()->routeIs('chatbot.dashboard') ? 'active' : '' }}">
                    <i data-lucide="pie-chart"></i><span>Dashboard Chatbot</span>
                </a>
                <a href="{{ route('chatbot.pesan') }}" class="{{ request()->routeIs('chatbot.pesan') ? 'active' : '' }}">
                    <i data-lucide="message-square"></i><span>Riwayat Pesan</span>
                </a>
                <a href="{{ route('chatbot.users') }}" class="{{ request()->routeIs('chatbot.users') ? 'active' : '' }}">
                    <i data-lucide="users"></i><span>Data Pengguna (Users)</span>
                </a>
                <a href="{{ route('chatbot.grup') }}" class="{{ request()->routeIs('chatbot.grup*') ? 'active' : '' }}">
                    <i data-lucide="users" class="-viewfinder"></i><span>Dashboard Grup</span>
                </a>
                <a href="{{ route('chatbot.device.index') }}" class="{{ request()->routeIs('chatbot.device*') ? 'active' : '' }}">
                    <i data-lucide="smartphone"></i><span>Pengaturan Device</span>
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
                <a href="{{ route('dashboard.users.index') }}" class="{{ request()->routeIs('dashboard.users.*') ? 'active' : '' }}">
                    <i data-lucide="contact"></i><span>Hak Akses Karyawan</span>
                </a>
                @endcan
                @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('dashboard.pengaturan.toko') }}" class="{{ request()->routeIs('dashboard.pengaturan.toko') ? 'active' : '' }}">
                    <i data-lucide="store"></i><span>Identitas Toko</span>
                </a>
                <a href="{{ route('dashboard.billing.index') }}" class="{{ request()->routeIs('dashboard.billing.*') ? 'active' : '' }}">
                    <i data-lucide="credit-card"></i><span>Tagihan & Paket</span>
                </a>
                @endif
                @if(auth()->user() && auth()->user()->isAdmin())
                <a href="{{ route('chatbot.system_users.index') }}" class="{{ request()->routeIs('chatbot.system_users.*') ? 'active' : '' }}">
                    <i data-lucide="shield-check"></i><span>Admin WhatsApp</span>
                </a>
                @endif
                @if(auth()->user() && auth()->user()->isAdmin() && (!isset($identitasToko) || $identitasToko->jenis_layanan !== 'dine_in'))
                <a href="{{ route('chatbot.kurir.index') }}" class="{{ request()->routeIs('chatbot.kurir.*') ? 'active' : '' }}">
                    <i data-lucide="truck"></i><span>Manajemen Kurir</span>
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
                <a href="{{ route('superadmin.landing_page') }}" class="{{ request()->routeIs('superadmin.landing_page') ? 'active' : '' }}">
                    <i data-lucide="monitor-play"></i><span>Pengaturan Landing</span>
                </a>
                <a href="/superadmin/meta" class="{{ request()->routeIs('superadmin.meta*') ? 'active' : '' }}">
                    <i data-lucide="message-circle"></i><span>Meta API Pusat</span>
                </a>
                <a href="/superadmin/midtrans" class="{{ request()->routeIs('superadmin.midtrans*') ? 'active' : '' }}">
                    <i data-lucide="credit-card"></i><span>Midtrans Landlord</span>
                </a>
            </div>
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
</style>
