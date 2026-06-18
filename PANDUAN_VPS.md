# 🚀 Panduan Deployment & Konfigurasi VPS Flashbot

Dokumen ini berisi rangkuman seluruh proses deployment aplikasi Flashbot di VPS, konfigurasi yang digunakan, serta catatan masalah yang pernah terjadi beserta solusinya agar mudah dilacak di masa depan.

---

## 🔐 Kredensial & Akses Penting

### 1. Super Admin (Akses Master)
- **URL Login:** `https://tenanta.id/sa-access` (Disembunyikan untuk keamanan)
- **Email:** `andhyfauzi@gmail.com`
- **Password:** `AdminKuat2026!`
- **Catatan Keamanan:** Login hanya bisa dilakukan jika IP pengguna terdaftar di variabel `SUPER_ADMIN_IPS` dalam file `.env` VPS.

### 2. Database MySQL
- **Database Induk (Landlord):** `db_flashbot`
- **User MySQL:** `flashbot_user`
- **Password MySQL:** `PasswordRahasia123!`
- **Host:** `127.0.0.1`

### 3. Konfigurasi Kunci di `.env` (VPS)
```env
APP_URL=https://tenanta.id
APP_ENV=production

# Database
DB_CONNECTION=landlord
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=db_flashbot
DB_USERNAME=flashbot_user
DB_PASSWORD=PasswordRahasia123!

# Redis / Cache (Penting: Gunakan predis)
CACHE_DRIVER=redis
CACHE_STORE=redis
REDIS_CLIENT=predis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Layanan Eksternal & Webhook
GOOGLE_REDIRECT_URL=https://tenanta.id/auth/google/callback
LARAVEL_WEBHOOK_URL=https://tenanta.id/webhook/whatsapp
BAILEYS_API_URL=http://127.0.0.1:3001
BAILEYS_PORT=3001
WEBHOOK_SECRET=FlashbotSecretKey2026
```

---

## 🛠️ Catatan Masalah yang Terjadi & Solusinya

Selama proses setup, kita menemui beberapa rintangan. Berikut adalah rangkuman penyebab dan cara kita menyelesaikannya:

### 1. Tenant Gagal Terdaftar (Error Database)
- **Gejala:** `Access denied for user 'flashbot_user'@'localhost' to database 'tenant_...'`
- **Penyebab:** User MySQL `flashbot_user` hanya memiliki akses ke `db_flashbot`, sehingga tidak bisa menjalankan perintah `CREATE DATABASE` untuk tenant baru.
- **Solusi yang Diterapkan:** Memberikan hak akses global ke user tersebut.
  ```sql
  GRANT ALL PRIVILEGES ON *.* TO 'flashbot_user'@'localhost' WITH GRANT OPTION;
  FLUSH PRIVILEGES;
  ```

### 2. Login Google Error (Redirect URI Mismatch)
- **Gejala:** Error `400: redirect_uri_mismatch` saat mendaftar dengan Google.
- **Penyebab:** Variabel `GOOGLE_REDIRECT_URL` di `.env` VPS masih mengarah ke `localhost`, sehingga Google menolak permintaan tersebut.
- **Solusi yang Diterapkan:** Mengubahnya ke `https://tenanta.id/auth/google/callback` dan memastikan URI tersebut didaftarkan di Google Cloud Console.

### 3. Tenant Redirect ke `localhost` Setelah Mendaftar
- **Gejala:** Setelah mendaftar, tenant diarahkan ke `namatoko.localhost/login`.
- **Penyebab:** URL subdomain masih di-hardcode ke `.localhost` di controller dan tampilan (view).
- **Solusi yang Diterapkan:** Mengganti hardcode dengan URL dinamis menggunakan `APP_URL` di kode sumber aplikasi.

### 4. Halaman POS Error 500 (Cache Tagging)
- **Gejala:** Error `This cache store does not support tagging` atau `Class "Redis" not found`.
- **Penyebab:** Laravel mencoba menggunakan `phpredis` (ekstensi C) yang tidak terinstall di PHP-FPM VPS.
- **Solusi yang Diterapkan:**
  1. Mengganti client menjadi library murni PHP di `.env`: `REDIS_CLIENT=predis`
  2. Menginstal library: `composer require predis/predis`

### 5. Masalah Sertifikat SSL Wildcard (ZeroSSL / Let's Encrypt Timeout)
- **Gejala:** Kegagalan saat memproses `acme.sh` untuk menerbitkan sertifikat SSL Wildcard `*.tenanta.id`.
- **Penyebab:** Tantangan DNS (DNS Challenge) manual terlalu rumit dan proses pemesanan ke Otoritas Sertifikat (CA) terhambat (Timeout 24 jam).
- **Solusi yang Diterapkan:** Mengganti manajemen DNS (Nameservers) dari Hostinger ke **Cloudflare (Gratis)**. Cloudflare otomatis menangani SSL Wildcard secara instan dan memberikan gembok hijau untuk seluruh subdomain tenant.

### 6. Node.js Baileys Error (ESM vs CommonJS)
- **Gejala:** Error `ERR_REQUIRE_ESM` di `server.js` saat menjalankan service WhatsApp.
- **Penyebab:** Versi terbaru `@whiskeysockets/baileys` (^7.x) sepenuhnya bermigrasi ke ES Module, sementara script lama `server.js` kita menggunakan format CommonJS (`require`).
- **Solusi yang Diterapkan:** Mendowngrade versi Baileys di `package.json` menjadi `^6.7.0` yang stabil dan masih mensupport CommonJS.

### 7. Node.js Port Bentrok (EADDRINUSE)
- **Gejala:** Error `listen EADDRINUSE: address already in use 0.0.0.0:3000` di PM2.
- **Penyebab:** Ada proses Node.js yang tertinggal/nyangkut di port 3000, sementara Laravel kita mengharapkan Baileys berjalan di port 3001.
- **Solusi yang Diterapkan:**
  1. Membunuh semua proses Node.js yang tertinggal: `pm2 kill && killall -9 node`
  2. Menambahkan `BAILEYS_PORT=3001` ke dalam file `.env`
  3. Merestart ulang server menggunakan PM2: `pm2 start ecosystem.config.js`

---

## 🔄 Prosedur Pembaruan (Update) Ke VPS

Jika Anda melakukan perubahan kode di laptop (lokal), ini adalah cara rutin untuk mengupdatenya ke VPS:

**Di Laptop (PowerShell):**
```powershell
git add .
git commit -m "Deskripsi perubahan"
git push
```

**Di VPS (Terminal):**
```bash
cd /var/www/flashbot
git pull
composer install --no-dev --optimize-autoloader
php artisan config:clear
php artisan cache:clear
php artisan view:clear

# Opsional: Jika ada update database
php artisan migrate
```
