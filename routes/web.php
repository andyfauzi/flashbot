<?php

use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Web Routes
|--------------------------------------------------------------------------
*/

Route::get('/debug-php', function () {
    return response()->json(get_loaded_extensions());
});

Route::get('/', function () {
    if (app()->has('current_tenant')) {
        return redirect()->route('dashboard.transaksi.index');
    }
    
    // Ambil data CMS dari tabel landlord_settings
    $settings = [];
    try {
        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
    } catch (\Exception $e) {
        // Abaikan jika migrasi belum dijalankan
    }

    return view('welcome', compact('settings'));
});

Route::get('/debug-log', function() {
    $path = storage_path('logs/laravel.log');
    if (!file_exists($path)) return 'No log file.';
    $lines = file($path);
    return '<pre>' . htmlspecialchars(implode('', array_slice($lines, -100))) . '</pre>';
});


Route::get('/terms', function() {
    return view('legal.tos');
})->name('legal.terms');

Route::get('/privacy', function() {
    return view('legal.privacy');
})->name('legal.privacy');

if (app()->environment('local')) {
    Route::get('/test-dispatch', function() {
        $start = microtime(true);
        \App\Jobs\ProcessWhatsAppMessageJob::dispatch('personal', []);
        return microtime(true) - $start;
    });
}

use Illuminate\Support\Facades\Auth;

// Tenant login — hanya untuk subdomain (namatoko.tenanta.id/login)
Route::get('/login', function (\Illuminate\Http\Request $request) {
    if (Auth::check()) {
        return redirect()->route('dashboard.transaksi.index');
    }
    
    // Jika login diakses melalui domain utama (bukan subdomain tenant) dan tidak ada konteks tenant
    if (!app()->has('current_tenant')) {
        // Alihkan ke landing page atau sa-access
        return redirect('/');
    }
    
    return view('auth.login');
})->name('login');

Route::post('/login', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    if (Auth::attempt($credentials)) {
        $request->session()->regenerate();
        return redirect()->intended('/dashboard/transaksi')->with('sukses', 'Login berhasil!');
    }

    return back()->withErrors([
        'email' => 'Email atau password salah!',
    ])->onlyInput('email');
})->middleware('throttle:5,1');

// =============================================
// SUPER ADMIN SECRET LOGIN (/sa-access)
// URL ini tidak ada tautannya di halaman manapun
// =============================================
Route::get('/sa-access', function () {
    if (Auth::check() && Auth::user()->is_super_admin) {
        return redirect()->route('superadmin.index');
    }
    Auth::logout();
    return view('auth.sa-login');
})->name('sa.login');

Route::post('/sa-access', function (\Illuminate\Http\Request $request) {
    $credentials = $request->validate([
        'email'    => 'required|email',
        'password' => 'required',
    ]);

    // Verifikasi manual langsung ke landlord DB
    // Menghindari potensi masalah koneksi dari multi-tenant middleware
    $user = \Illuminate\Support\Facades\DB::connection('landlord')
        ->table('users')
        ->where('email', $credentials['email'])
        ->first();

    if ($user && $user->is_super_admin && \Illuminate\Support\Facades\Hash::check($credentials['password'], $user->password)) {
        Auth::loginUsingId($user->id);
        $request->session()->regenerate();
        return redirect()->route('superadmin.index')->with('sukses', 'Selamat datang, Super Admin!');
    }

    return back()->withErrors(['email' => 'Akses ditolak.'])->onlyInput('email');
})->middleware('throttle:5,1')->name('sa.login.post');

Route::get('/logout', function (\Illuminate\Http\Request $request) {
    $isSuperAdmin = Auth::check() && Auth::user()->is_super_admin;
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    if ($isSuperAdmin) {
        return redirect('/sa-access')->with('sukses', 'Logout berhasil!');
    }
    
    // Redirect ke landing page utama jika logout
    return redirect('/')->with('sukses', 'Logout berhasil!');
})->name('logout');

require __DIR__.'/chatbot.php';

// =============================================
// FORCE PASSWORD CHANGE (Auth Required)
// =============================================
Route::middleware(['auth'])->group(function () {
    Route::get('/password/force-change', [\App\Http\Controllers\Auth\PasswordController::class, 'showForceChangeForm'])->name('password.force-change');
    Route::post('/password/force-change', [\App\Http\Controllers\Auth\PasswordController::class, 'update'])->name('password.force-change.update');
});
// =============================================
// POS (Point of Sale) KASIR
// =============================================
Route::middleware(['auth', 'active.subscription', 'permission:akses_pos'])->prefix('pos')->group(function () {
    Route::get('/', [\App\Http\Controllers\PosController::class, 'index'])->name('pos.index');
    Route::post('/store', [\App\Http\Controllers\PosController::class, 'store'])->name('pos.store');
    Route::get('/print/{pesanan}', [\App\Http\Controllers\PosController::class, 'printReceipt'])->name('pos.print');
});

// =============================================
// JADWAL PESANAN (PRE-ORDER)
// =============================================
Route::middleware(['auth', 'active.subscription', 'permission:akses_pos'])->prefix('dashboard/preorder')->group(function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'index'])->name('dashboard.preorder.index');
    Route::put('/{pesanan}/ongkir', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'setOngkir'])->name('dashboard.preorder.ongkir');
    Route::put('/{pesanan}/dp', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'setDp'])->name('dashboard.preorder.dp');
    Route::put('/{pesanan}/lunas', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'lunas'])->name('dashboard.preorder.lunas');
    Route::put('/{pesanan}/selesai', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'selesai'])->name('dashboard.preorder.selesai');
    Route::delete('/{pesanan}/batal', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'batal'])->name('dashboard.preorder.batal');
    Route::post('/{pesanan}/notifikasi-siap', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'kirimNotifikasiSiap'])->name('dashboard.preorder.notif_siap');
    Route::post('/{pesanan}/sync-xendit', [\App\Http\Controllers\Dashboard\PreOrderController::class, 'syncXendit'])->name('dashboard.preorder.sync_xendit');
});

// =============================================
// PRODUKSI / MANUFAKTUR
// =============================================
Route::middleware(['auth', 'active.subscription', 'permission:akses_hpp'])->prefix('dashboard/produksi')->group(function () {
    Route::get('/', [\App\Http\Controllers\Dashboard\ProduksiController::class, 'index'])->name('dashboard.produksi.index');
    Route::post('/store', [\App\Http\Controllers\Dashboard\ProduksiController::class, 'store'])->name('dashboard.produksi.store');
    Route::post('/validasi', [\App\Http\Controllers\Dashboard\ProduksiController::class, 'validasiSelesai'])->name('dashboard.produksi.validasi');
});

// =============================================
// KALKULATOR HPP & RESEP
// =============================================
Route::middleware(['auth', 'active.subscription', 'permission:akses_hpp'])->prefix('dashboard/hpp')->group(function () {
    // Bahan Baku
    Route::get('/bahan', [\App\Http\Controllers\Dashboard\HppController::class, 'indexBahanBaku'])->name('dashboard.hpp.bahan.index');
    Route::post('/bahan', [\App\Http\Controllers\Dashboard\HppController::class, 'storeBahanBaku'])->name('dashboard.hpp.bahan.store');
    Route::put('/bahan/{bahan}', [\App\Http\Controllers\Dashboard\HppController::class, 'updateBahanBaku'])->name('dashboard.hpp.bahan.update');
    Route::delete('/bahan/{bahan}', [\App\Http\Controllers\Dashboard\HppController::class, 'destroyBahanBaku'])->name('dashboard.hpp.bahan.destroy');
    Route::post('/bahan/{bahan}/restock', [\App\Http\Controllers\Dashboard\HppController::class, 'restockBahanBaku'])->name('dashboard.hpp.bahan.restock');
    Route::post('/bahan/{bahan}/koreksi', [\App\Http\Controllers\Dashboard\HppController::class, 'koreksiStokBahanBaku'])->name('dashboard.hpp.bahan.koreksi');
    Route::post('/bahan/{bahan}/rusak', [\App\Http\Controllers\Dashboard\HppController::class, 'laporBahanRusak'])->name('dashboard.hpp.bahan.rusak');

    // Kalkulator HPP
    Route::get('/kalkulator', [\App\Http\Controllers\Dashboard\HppController::class, 'indexKalkulator'])->name('dashboard.hpp.kalkulator.index');
    Route::post('/kalkulator/{varian}/resep', [\App\Http\Controllers\Dashboard\HppController::class, 'simpanResep'])->name('dashboard.hpp.resep.store');
    Route::delete('/kalkulator/resep/{resep}', [\App\Http\Controllers\Dashboard\HppController::class, 'hapusResep'])->name('dashboard.hpp.resep.destroy');
    Route::put('/kalkulator/{varian}/konfigurasi', [\App\Http\Controllers\Dashboard\HppController::class, 'updateKonfigurasiHarga'])->name('dashboard.hpp.konfigurasi');
    Route::put('/kalkulator/{varian}/rekomendasi', [\App\Http\Controllers\Dashboard\HppController::class, 'terapkanHargaRekomendasi'])->name('dashboard.hpp.rekomendasi');
});

// =============================================
// ARUS KAS & SHIFT
// =============================================
Route::middleware(['auth', 'active.subscription'])->prefix('dashboard')->group(function () {
    // Shift (Untuk Kasir)
    Route::post('/shift/buka', [\App\Http\Controllers\Dashboard\ShiftController::class, 'buka'])->name('dashboard.shift.buka');
    Route::post('/shift/tutup', [\App\Http\Controllers\Dashboard\ShiftController::class, 'tutup'])->name('dashboard.shift.tutup');
    Route::post('/shift/pengeluaran', [\App\Http\Controllers\Dashboard\ShiftController::class, 'pengeluaranKasir'])->name('dashboard.shift.pengeluaran');

    // Buku Kas (Khusus admin / keuangan)
    Route::middleware(['permission:akses_kas'])->group(function () {
        Route::get('/cash-flow', [\App\Http\Controllers\Dashboard\CashFlowController::class, 'index'])->name('dashboard.cash_flow.index');
        Route::post('/cash-flow', [\App\Http\Controllers\Dashboard\CashFlowController::class, 'store'])->name('dashboard.cash_flow.store');
        Route::delete('/cash-flow/{cashFlow}', [\App\Http\Controllers\Dashboard\CashFlowController::class, 'destroy'])->name('dashboard.cash_flow.destroy');

        // Riwayat Transaksi
        Route::get('/transaksi', [\App\Http\Controllers\Dashboard\TransaksiController::class, 'index'])->name('dashboard.transaksi.index');
        Route::post('/transaksi/{id}/cancel', [\App\Http\Controllers\Dashboard\TransaksiController::class, 'cancel'])->name('dashboard.transaksi.cancel');

        // UI Mode Toggle
        Route::post('/toggle-ui-mode', [\App\Http\Controllers\Dashboard\UserController::class, 'toggleUiMode'])->name('dashboard.ui_mode.toggle');
    });

    // Manajemen Pengguna & Hak Akses
    Route::middleware(['permission:akses_karyawan'])->group(function () {
        Route::resource('users', \App\Http\Controllers\Dashboard\UserController::class)->names([
            'index' => 'dashboard.users.index',
            'store' => 'dashboard.users.store',
            'update' => 'dashboard.users.update',
            'destroy' => 'dashboard.users.destroy',
        ])->except(['create', 'edit', 'show']);
        
        // Pengaturan Toko
        Route::get('/pengaturan/toko', [\App\Http\Controllers\Dashboard\IdentitasTokoController::class, 'index'])->name('dashboard.pengaturan.toko');
        Route::post('/pengaturan/toko', [\App\Http\Controllers\Dashboard\IdentitasTokoController::class, 'update'])->name('dashboard.pengaturan.toko.update');

        // Manajemen Meja & Reservasi
        Route::resource('meja', \App\Http\Controllers\Dashboard\MejaController::class)->names('dashboard.meja')->except(['show']);
        Route::resource('reservasi', \App\Http\Controllers\Dashboard\ReservasiController::class)->names('dashboard.reservasi')->except(['show']);
    });
    
    // UI Mode Toggle
    Route::post('/toggle-ui-mode', [\App\Http\Controllers\Dashboard\UserController::class, 'toggleUiMode'])->name('dashboard.ui_mode.toggle');
});

// Billing & Checkout — HARUS di luar active.subscription agar bisa diakses walau belum aktif
Route::middleware(['auth'])->prefix('dashboard')->group(function () {
    Route::get('/billing', [\App\Http\Controllers\PaymentController::class, 'index'])->name('dashboard.billing.index');
    Route::post('/billing/checkout', [\App\Http\Controllers\PaymentController::class, 'checkout'])->name('dashboard.billing.checkout');
});



// Fallback route for storage files when the symlink is broken or missing on Windows
Route::get('/storage/{path}', function ($path) {
    $filePath = storage_path('app/public/' . $path);
    if (!file_exists($filePath)) {
        abort(404);
    }
    return response()->file($filePath);
})->where('path', '.*');

// =============================================
// CUSTOMER PORTAL (Self-Order)
// =============================================
Route::middleware(['portal.active'])->group(function () {
    // Self-Service Dine-In
    Route::get('/meja/{meja}/pesan', [\App\Http\Controllers\Chatbot\PortalController::class, 'dineIn'])->name('portal.dine_in');

    Route::get('/portal', [\App\Http\Controllers\Chatbot\PortalController::class, 'index'])->name('portal.index');
    Route::post('/portal/order', [\App\Http\Controllers\Chatbot\PortalController::class, 'store'])
        ->name('portal.store')
        ->middleware('throttle:portal-order');
});

// =============================================
// PLATFORM SUPER ADMIN (Landlord Control Panel)
// Protected by: Auth + SuperAdminOnly middleware (is_super_admin flag + IP whitelist)
// =============================================
Route::prefix('super-admin')
    ->middleware(['auth', 'super.admin', 'throttle:60,1'])
    ->group(function () {
        Route::get('/', [\App\Http\Controllers\SuperAdminController::class, 'index'])->name('superadmin.index');
        Route::post('/store', [\App\Http\Controllers\SuperAdminController::class, 'store'])->name('superadmin.store');
        Route::post('/{id}/toggle', [\App\Http\Controllers\SuperAdminController::class, 'toggleActive'])->name('superadmin.toggle');
        Route::post('/{id}/update-plan', [\App\Http\Controllers\SuperAdminController::class, 'updatePlan'])->name('superadmin.update_plan');
        Route::post('/{id}/toggle-broadcast', [\App\Http\Controllers\SuperAdminController::class, 'toggleBroadcast'])->name('superadmin.toggle_broadcast');
        Route::delete('/{id}', [\App\Http\Controllers\SuperAdminController::class, 'destroy'])->name('superadmin.destroy');

        // Global Settings
        Route::post('/settings', [\App\Http\Controllers\SuperAdminController::class, 'updateSettings'])->name('superadmin.settings.update');

        // Landing Page CMS
        Route::get('/landing-page', [\App\Http\Controllers\LandingPageSettingController::class, 'index'])->name('superadmin.landing_page');
        Route::post('/landing-page', [\App\Http\Controllers\LandingPageSettingController::class, 'update'])->name('superadmin.landing_page.update');

        // Pengaturan Meta WhatsApp Landlord
        Route::get('/meta', [\App\Http\Controllers\SuperAdminController::class, 'showMetaSettings'])->name('superadmin.meta');
        Route::post('/meta-settings', [\App\Http\Controllers\SuperAdminController::class, 'updateMetaSettings'])->name('superadmin.meta.update');
        
        // Pengaturan Midtrans Landlord
        Route::get('/midtrans', [\App\Http\Controllers\SuperAdminController::class, 'showMidtransSettings'])->name('superadmin.midtrans');
        Route::post('/midtrans-settings', [\App\Http\Controllers\SuperAdminController::class, 'updateMidtransSettings'])->name('superadmin.midtrans.update');
        
        // Toggle Payment Gateway (Per-tenant)
        Route::post('/{id}/toggle-payment-gateway', [\App\Http\Controllers\SuperAdminController::class, 'togglePaymentGateway'])->name('superadmin.toggle_payment_gateway');
    });


// =============================================
// GOOGLE OAUTH REGISTRATION & SIGN-IN
// =============================================
    Route::get('/auth/google', [\App\Http\Controllers\GoogleAuthController::class, 'redirectToGoogle'])->name('auth.google');
    Route::get('/auth/google/callback', [\App\Http\Controllers\GoogleAuthController::class, 'handleGoogleCallback']);
    Route::get('/auth/google/cancel', [\App\Http\Controllers\GoogleAuthController::class, 'cancelRegistration'])->name('auth.google.cancel');
    Route::post('/auth/google/complete-registration', [\App\Http\Controllers\GoogleAuthController::class, 'completeRegistration'])->name('auth.google.complete');

Route::get('/auth/google/provisioning', function (\Illuminate\Http\Request $request) {
    \App\Services\TenantManager::switchToLandlord();
    $tenant = \App\Models\Tenant::findOrFail($request->tenant_id);
    $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? $request->getHost();
    $scheme  = $request->getScheme();
    $port    = $request->getPort();
    $portStr = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
    $loginUrl = $scheme . '://' . $tenant->subdomain . '.' . $appHost . $portStr . '/login';
    return view('auth.provisioning', compact('tenant', 'loginUrl'));
})->name('auth.google.provisioning');

Route::get('/migrate-broadcast', function() {
    \Illuminate\Support\Facades\Schema::create('chatbot_broadcasts', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->id();
        $table->string('judul');
        $table->text('isi_pesan');
        $table->string('media_url')->nullable();
        $table->string('media_type')->nullable();
        $table->enum('status', ['draft', 'dikirim'])->default('draft');
        $table->integer('total_penerima')->default(0);
        $table->string('target_filter')->default('all');
        $table->string('meta_template_name')->nullable();
        $table->timestamps();
    });

    if (!\Illuminate\Support\Facades\Schema::hasColumn('identitas_tokos', 'is_broadcast_approved')) {
        \Illuminate\Support\Facades\Schema::table('identitas_tokos', function (\Illuminate\Database\Schema\Blueprint $table) {
            $table->boolean('is_broadcast_approved')->default(false);
        });
    }

    return 'Broadcast Migration OK';
});

