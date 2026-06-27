<div class="accordion accordion-flush bg-transparent" id="accordion{{ $prefix }}">
    
    <!-- ============================================== -->
    <!-- 👑 SUPER ADMIN (Landlord) -->
    <!-- ============================================== -->
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
</style>
