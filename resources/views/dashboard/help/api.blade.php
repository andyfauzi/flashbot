@extends('layouts.app')

@section('title', 'Panduan API')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Panduan Integrasi API</h2>
            <p class="text-muted mb-0">Langkah-langkah mendaftar dan mendapatkan API Key untuk fitur premium.</p>
        </div>
        <a href="{{ route('dashboard.help') }}" class="btn btn-light border px-4 rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    <div class="row">
        <div class="col-lg-10 col-xl-8 mx-auto">
            
            <!-- Midtrans -->
            <div class="card border border-info shadow-sm rounded-4 mb-4">
                <div class="card-header bg-info text-dark border-0 pt-3 pb-2 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-credit-card me-2"></i> 1. Mendaftar Midtrans (Payment Gateway)</h5>
                </div>
                <div class="card-body p-4">
                    <p>Midtrans memungkinkan toko Anda menerima pembayaran otomatis via QRIS, Virtual Account, GoPay, dan lainnya.</p>
                    <ol class="mb-4">
                        <li><strong>Daftar Akun:</strong> Kunjungi <a href="https://dashboard.midtrans.com/register" target="_blank" class="fw-bold">Midtrans Register</a> dan isi data diri serta informasi bisnis Anda.</li>
                        <li><strong>Lengkapi Dokumen:</strong> Anda akan diminta mengunggah foto KTP, NPWP (opsional untuk perorangan), dan informasi rekening bank pencairan dana.</li>
                        <li><strong>Persetujuan:</strong> Tunggu proses verifikasi dari tim Midtrans (biasanya memakan waktu 1-3 hari kerja).</li>
                        <li><strong>Mode Production:</strong> Setelah disetujui, pastikan <em>toggle</em> di sudut layar Anda diubah dari <strong>Sandbox</strong> menjadi <strong>Production</strong>.</li>
                    </ol>

                    <h6 class="fw-bold"><i class="fa-solid fa-key text-warning me-1"></i> Cara Mengambil API Key:</h6>
                    <ul class="mb-0 text-muted">
                        <li>Di Dashboard Midtrans Anda, pilih menu <strong>Settings</strong> di bagian kiri bawah.</li>
                        <li>Klik <strong>Access Keys</strong>.</li>
                        <li>Salin <strong>Client Key</strong> dan <strong>Server Key</strong> yang ada di bagian <em>Production</em>.</li>
                        <li>Masukkan kedua kunci tersebut ke aplikasi ini di menu <strong>Pengaturan Pembayaran > Midtrans</strong>. Pastikan Anda mencentang opsi "Gunakan Mode Production/Live".</li>
                    </ul>
                </div>
            </div>

            <!-- Xendit -->
            <div class="card border border-primary shadow-sm rounded-4 mb-4">
                <div class="card-header bg-primary text-white border-0 pt-3 pb-2 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-money-bill-transfer me-2"></i> 2. Mendaftar Xendit (Payment Alternatif)</h5>
                </div>
                <div class="card-body p-4">
                    <p>Xendit adalah alternatif sistem pembayaran yang sangat handal dengan fitur invoicing yang canggih.</p>
                    <ol class="mb-4">
                        <li><strong>Daftar Akun:</strong> Kunjungi <a href="https://dashboard.xendit.co/register" target="_blank" class="fw-bold text-primary">Xendit Register</a>.</li>
                        <li><strong>Aktivasi Akun:</strong> Lakukan verifikasi email dan lengkapi profil bisnis Anda (KTP & Buku Tabungan).</li>
                        <li><strong>Go Live:</strong> Klik tombol "Aktifkan Akun / Go Live" di dashboard dan tunggu persetujuan tim Xendit.</li>
                    </ol>

                    <h6 class="fw-bold"><i class="fa-solid fa-key text-warning me-1"></i> Cara Mengambil API Key:</h6>
                    <ul class="mb-0 text-muted">
                        <li>Di Dashboard Xendit, masuk ke menu <strong>Settings</strong> > <strong>API Keys</strong>.</li>
                        <li>Klik tombol <strong>Generate Secret Key</strong>.</li>
                        <li>Beri nama kunci dan berikan izin (<em>Permissions</em>) ke fitur pembayaran (<strong>Money In / Invoices</strong> set ke <code>Write/Read</code>).</li>
                        <li>Salin kunci rahasia yang muncul (berawalan <code>xnd_production_...</code>). <strong class="text-danger">Salin segera, karena Xendit hanya akan menampilkannya satu kali!</strong></li>
                        <li>Masukkan kunci tersebut ke menu <strong>Pengaturan Pembayaran > Xendit</strong>.</li>
                    </ul>
                </div>
            </div>

            <!-- Gemini -->
            <div class="card border border-success shadow-sm rounded-4 mb-4">
                <div class="card-header bg-success text-white border-0 pt-3 pb-2 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fa-solid fa-brain me-2"></i> 3. Google Gemini API Key (AI Chatbot)</h5>
                </div>
                <div class="card-body p-4">
                    <p>Membawa API Key sendiri (BYOK) memungkinkan Anda mengatur penggunaan AI secara mandiri tanpa membebani kuota sistem pusat.</p>
                    <ol class="mb-0">
                        <li>Kunjungi <a href="https://aistudio.google.com/app/apikey" target="_blank" class="fw-bold text-success">Google AI Studio</a> menggunakan akun Google Anda.</li>
                        <li>Di panel sebelah kiri, klik menu <strong>Get API key</strong> atau <strong>Create API key</strong>.</li>
                        <li>Pilih tombol <strong>Create API key in new project</strong>.</li>
                        <li>Google akan memunculkan sebuah teks panjang yang diawali dengan huruf <code>AIzaSy...</code>. Inilah API Key Anda.</li>
                        <li>Salin kunci tersebut dan masukkan di menu <strong>Device & Bot > Kotak Google Gemini API</strong>.</li>
                    </ol>
                </div>
            </div>

            <!-- Meta WhatsApp -->
            <div class="card border border-dark shadow-sm rounded-4 mb-5">
                <div class="card-header bg-dark text-white border-0 pt-3 pb-2 rounded-top-4">
                    <h5 class="fw-bold mb-0"><i class="fa-brands fa-whatsapp me-2"></i> 4. Meta WhatsApp Cloud API</h5>
                </div>
                <div class="card-body p-4">
                    <p>Beralih dari Gateway Sistem (Baileys) ke jalur koneksi WhatsApp yang 100% resmi dan anti-banned dari Meta (Facebook).</p>
                    
                    <h6 class="fw-bold text-dark mt-4">A. Mendaftar Aplikasi</h6>
                    <ol class="mb-4">
                        <li>Kunjungi <a href="https://developers.facebook.com/" target="_blank" class="fw-bold text-dark">Meta for Developers</a> dan login menggunakan Facebook Anda.</li>
                        <li>Klik <strong>My Apps</strong>, lalu tombol <strong>Create App</strong>.</li>
                        <li>Pilih tipe <strong>Business</strong> dan beri nama aplikasi Anda.</li>
                        <li>Di <em>Dashboard App</em>, cari kartu <strong>WhatsApp</strong>, lalu klik <strong>Set Up</strong>.</li>
                    </ol>

                    <h6 class="fw-bold text-dark mt-3">B. Konfigurasi Nomor & Token</h6>
                    <ul class="mb-0 text-muted">
                        <li>Buka <strong>WhatsApp > API Setup</strong>. Tambahkan nomor telepon toko Anda (tidak boleh terdaftar di WA/WA Business biasa).</li>
                        <li>Salin <strong>Phone Number ID</strong> yang tertera.</li>
                        <li>Untuk token, kunjungi <a href="https://business.facebook.com/settings" target="_blank" class="text-dark">Meta Business Settings</a>. Masuk ke <strong>Users > System Users</strong>.</li>
                        <li>Buat <em>System User</em> baru, lalu klik <strong>Generate New Token</strong>.</li>
                        <li>Pilih aplikasi Anda, atur kedaluwarsa ke <strong>Never</strong> (Tidak Pernah), centang <code>whatsapp_business_messaging</code>, dan hasilkan token permanen (<code>EAALx...</code>).</li>
                        <li>Masukkan <em>Phone Number ID</em>, <em>Permanent Access Token</em>, dan <em>Webhook Token</em> (kata sandi buatan Anda sendiri) ke menu <strong>Device & Bot > Gateway WhatsApp</strong>.</li>
                    </ul>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
