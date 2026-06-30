# Panduan Konfigurasi Cloudflare Turnstile

Dokumen ini memuat panduan lengkap tentang cara mengatur dan mengaktifkan integrasi perlindungan bot (Cloudflare Turnstile) untuk formulir Checkout dan Reservasi di aplikasi Flashbot.

## 1. Mendaftar dan Mendapatkan Kredensial (Key)
Untuk mengganti mode pengujian ke mode *Production*, Anda perlu mendapatkan Site Key dan Secret Key resmi dari Cloudflare:

1. Kunjungi dan login ke [Dashboard Cloudflare Turnstile](https://dash.cloudflare.com/?to=/:account/turnstile).
2. Navigasi ke menu **Turnstile**.
3. Klik tombol **Add Widget**.
4. Pada bagian **Widget name**, masukkan nama untuk mengidentifikasi widget ini (misal: `Flashbot Portal`).
5. Pada bagian **Hostname Management**, klik **Add Hostnames** dan masukkan domain aplikasi Anda tempat portal ini akan diakses (misal: `domainanda.com`). Anda bisa memasukkan localhost untuk tahap pengembangan lokal.
6. Pilih jenis widget pengamanan. Disarankan memilih **Managed** agar pengunjung manusia tidak perlu repot menyelesaiakan tantangan secara manual.
7. Klik **Create**.
8. Salin **Site Key** dan **Secret Key** yang diberikan oleh Cloudflare.

## 2. Pengaturan di File Konfigurasi `.env`
Buka file `.env` di folder utama (root) proyek Flashbot Anda. Tambahkan dua baris berikut (bisa diletakkan di baris paling bawah):

```env
TURNSTILE_SITE_KEY=masukkan_site_key_anda_disini
TURNSTILE_SECRET_KEY=masukkan_secret_key_anda_disini
```

**Penting:**
- Selama variabel `.env` di atas kosong atau tidak diatur, sistem otomatis akan menggunakan *Dummy Key* yang berfungsi khusus untuk pengujian lokal. Ini ditandai dengan munculnya teks peringatan merah "Hanya untuk pengujian" di layar.
- Mengisi kedua variabel di `.env` akan langsung mengubah widget menjadi mode *Live / Production* dan menghilangkan teks peringatan tersebut.

## 3. Penjelasan Sistem (Bagi Developer)
Bila diperlukan modifikasi atau debugging ke depannya, berikut ringkasan bagaimana Turnstile diimplementasikan pada sistem saat ini:

- **Frontend (`resources/views/chatbot/portal.blade.php`):**
  - Pemanggilan skrip eksternal Cloudflare `<script src="https://challenges.cloudflare.com/turnstile/v0/api.js" async defer></script>`.
  - Injeksi widget turnstile ke dalam elemen form via `<div class="cf-turnstile" data-sitekey="{{ env('TURNSTILE_SITE_KEY', '1x00000000000000000000AA') }}"></div>`.
  - Logika JS yang mengekstrak nilai input tersembunyi (`cf-turnstile-response`) saat tombol disubmit dan menyertakannya ke payload AJAX.

- **Backend (`app/Http/Controllers/Chatbot/PortalController.php`):**
  - Fungsi `store` (checkout pesanan) dan `submitReservasi` mencegat *request* dan mencari input `cf-turnstile-response`.
  - Sistem melakukan pemanggilan verifikasi HTTP POST di *backend* ke API Cloudflare (`https://challenges.cloudflare.com/turnstile/v0/siteverify`).
  - Bila skor validasi gagal, API akan segera mengembalikan *response* status `403 Forbidden` dan menolak transaksi tersebut.

---
*Catatan ini dibuat secara otomatis sebagai bagian dari dokumentasi pembaruan keamanan pemesanan Flashbot.*
