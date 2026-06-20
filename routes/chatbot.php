<?php

use App\Http\Controllers\Chatbot\DashboardController;
use App\Http\Controllers\Chatbot\GrupController;
use App\Http\Controllers\Chatbot\WebhookController;
use App\Http\Controllers\Chatbot\SettingsController;
use Illuminate\Support\Facades\Route;

// =============================================
// WEBHOOK META WHATSAPP (tidak perlu login)
// =============================================
// GET  → verifikasi webhook dari Meta Developer Console
// POST → menerima pesan masuk dari Meta
Route::middleware(['throttle:120,1', 'verify.webhook:auto'])->group(function () {
    Route::get('/webhook/whatsapp',  [WebhookController::class, 'verifikasi']);
    Route::post('/webhook/whatsapp', [WebhookController::class, 'terima'])
        ->withoutMiddleware([\App\Http\Middleware\VerifyCsrfToken::class]);
});

// =============================================
// DASHBOARD CHATBOT (Proteksi dengan middleware session)
// =============================================
Route::prefix('chatbot')->middleware('auth')->group(function () {
    // =============================================
    // BROADCAST PROMOSI
    // =============================================
    Route::get('/broadcast', [\App\Http\Controllers\Chatbot\BroadcastController::class, 'index'])->name('chatbot.broadcast.index');
    Route::get('/broadcast/create', [\App\Http\Controllers\Chatbot\BroadcastController::class, 'create'])->name('chatbot.broadcast.create');
    Route::post('/broadcast', [\App\Http\Controllers\Chatbot\BroadcastController::class, 'store'])->name('chatbot.broadcast.store');
    
    // =============================================
    // RUTE YANG BISA DIAKSES OLEH ADMIN & USER (DEVICE)
    // =============================================
    Route::get('/device',          [\App\Http\Controllers\Chatbot\DeviceController::class, 'index'])->name('chatbot.device.index');
    Route::post('/device',         [\App\Http\Controllers\Chatbot\DeviceController::class, 'store'])->name('chatbot.device.store');
    Route::post('/device/settings',[\App\Http\Controllers\Chatbot\DeviceController::class, 'updateSettings'])->name('chatbot.device.settings.update');
    Route::post('/device/{device}/default', [\App\Http\Controllers\Chatbot\DeviceController::class, 'setAsDefault'])->name('chatbot.device.default');
    Route::delete('/device/{device}', [\App\Http\Controllers\Chatbot\DeviceController::class, 'destroy'])->name('chatbot.device.hapus');
    Route::get('/device/status/{session}', [\App\Http\Controllers\Chatbot\DeviceController::class, 'statusQr'])->name('chatbot.device.status');
    Route::post('/device/{device}/reconnect', [\App\Http\Controllers\Chatbot\DeviceController::class, 'reconnect'])->name('chatbot.device.reconnect');
    Route::post('/device/{device}/disconnect', [\App\Http\Controllers\Chatbot\DeviceController::class, 'disconnect'])->name('chatbot.device.disconnect');
    Route::post('/device/{device}/sapaan', [\App\Http\Controllers\Chatbot\DeviceController::class, 'simpanSapaan'])->name('chatbot.device.sapaan');

    // =============================================
    // RUTE YANG MEMBUTUHKAN PERMISSION KHUSUS
    // =============================================
    Route::middleware('permission:akses_hpp')->group(function () {
        Route::post('produk/{id}/duplicate', [\App\Http\Controllers\Chatbot\ProdukController::class, 'duplicate'])->name('chatbot.produk.duplicate');
        Route::resource('produk', \App\Http\Controllers\Chatbot\ProdukController::class)->names([
            'index'   => 'chatbot.produk.index',
            'create'  => 'chatbot.produk.create',
            'store'   => 'chatbot.produk.store',
            'edit'    => 'chatbot.produk.edit',
            'update'  => 'chatbot.produk.update',
            'destroy' => 'chatbot.produk.destroy',
        ]);
        Route::resource('kategori', \App\Http\Controllers\Chatbot\KategoriController::class)->names([
            'index'   => 'chatbot.kategori.index',
            'create'  => 'chatbot.kategori.create',
            'store'   => 'chatbot.kategori.store',
            'edit'    => 'chatbot.kategori.edit',
            'update'  => 'chatbot.kategori.update',
            'destroy' => 'chatbot.kategori.destroy',
        ]);
    });

    Route::middleware('permission:akses_hpp')->group(function () {
        Route::get('/stok', [\App\Http\Controllers\Dashboard\StokController::class, 'index'])->name('chatbot.stok.index');
        Route::post('/stok/update', [\App\Http\Controllers\Dashboard\StokController::class, 'updateBulk'])->name('chatbot.stok.update');
    });

    // =============================================
    // RUTE YANG HANYA BISA DIAKSES ADMIN
    // =============================================
    Route::middleware(\App\Http\Middleware\AdminMiddleware::class)->group(function () {
        Route::get('/',               [DashboardController::class, 'index']) ->name('chatbot.dashboard');
        Route::get('/users',          [DashboardController::class, 'users']) ->name('chatbot.users');
        Route::get('/pesan',          [DashboardController::class, 'pesan']) ->name('chatbot.pesan');
        Route::get('/menu',           [DashboardController::class, 'menu'])  ->name('chatbot.menu');
        Route::post('/menu',          [DashboardController::class, 'menuSimpan'])->name('chatbot.menu.simpan');
        Route::put('/menu/{menu}',    [DashboardController::class, 'menuUpdate'])->name('chatbot.menu.update');
        Route::delete('/menu/{menu}', [DashboardController::class, 'menuHapus'])->name('chatbot.menu.hapus');



        Route::post('/kirim',         [DashboardController::class, 'kirim']) ->name('chatbot.kirim');

        // Settings Meta API
        Route::middleware(['permission:kelola_integrasi'])->group(function () {
            Route::get('/settings',       [SettingsController::class, 'index'])  ->name('chatbot.settings');
            Route::post('/settings',      [SettingsController::class, 'update']) ->name('chatbot.settings.update');
        });

        // Manajemen Admin & User Sistem
        Route::get('/system-users',          [\App\Http\Controllers\Chatbot\SystemUserController::class, 'index']) ->name('chatbot.system_users.index');
        Route::post('/system-users',         [\App\Http\Controllers\Chatbot\SystemUserController::class, 'store']) ->name('chatbot.system_users.store');
        Route::put('/system-users/{user}',   [\App\Http\Controllers\Chatbot\SystemUserController::class, 'update'])->name('chatbot.system_users.update');
        Route::delete('/system-users/{user}',[\App\Http\Controllers\Chatbot\SystemUserController::class, 'destroy'])->name('chatbot.system_users.destroy');

        // Manajemen Kurir
        Route::get('/kurir',          [\App\Http\Controllers\Dashboard\KurirController::class, 'index']) ->name('chatbot.kurir.index');
        Route::post('/kurir',         [\App\Http\Controllers\Dashboard\KurirController::class, 'store']) ->name('chatbot.kurir.store');
        Route::put('/kurir/{kurir}',   [\App\Http\Controllers\Dashboard\KurirController::class, 'update'])->name('chatbot.kurir.update');
        Route::delete('/kurir/{kurir}',[\App\Http\Controllers\Dashboard\KurirController::class, 'destroy'])->name('chatbot.kurir.destroy');

        // Grup
        Route::get('/grup',                  [GrupController::class, 'index']) ->name('chatbot.grup');
        Route::post('/grup/broadcast',       [GrupController::class, 'broadcast'])->name('chatbot.grup.broadcast');
        Route::post('/grup/{grupId}/abaikan',[GrupController::class, 'abaikan'])->name('chatbot.grup.abaikan');
        Route::get('/grup/{grupId}',         [GrupController::class, 'detail'])->name('chatbot.grup.detail');
        Route::post('/grup/{grupId}/setadmin',[GrupController::class, 'setAdmin'])->name('chatbot.grup.setadmin');
        Route::post('/grup/{grupId}/unsetadmin',[GrupController::class, 'unsetAdmin'])->name('chatbot.grup.unsetadmin');
        Route::post('/grup/{grupId}/kirim',  [GrupController::class, 'kirim'])->name('chatbot.grup.kirim');
        Route::post('/grup/{grupId}/pengaturan', [GrupController::class, 'simpanPengaturan'])->name('chatbot.grup.pengaturan.simpan');
        Route::post('/grup/{grupId}/autoreply',  [GrupController::class, 'simpanAutoReply'])->name('chatbot.grup.autoreply.simpan');
        Route::put('/grup/{grupId}/autoreply/{id}', [GrupController::class, 'updateAutoReply'])->name('chatbot.grup.autoreply.update');
        Route::delete('/grup/{grupId}/autoreply/{id}', [GrupController::class, 'hapusAutoReply'])->name('chatbot.grup.autoreply.hapus');
        Route::get('/grup/{grupId}/admin',   [GrupController::class, 'admin'])->name('chatbot.grup.admin');
        Route::post('/grup/{grupId}/admin',  [GrupController::class, 'adminTambah'])->name('chatbot.grup.admin.tambah');
        Route::delete('/grup/{grupId}/admin/{nomorAdmin}', [GrupController::class, 'adminHapus'])->name('chatbot.grup.admin.hapus');
        Route::post('/grup/{grupId}/whitelist', [GrupController::class, 'toggleWhitelist'])->name('chatbot.grup.whitelist');
    });
});