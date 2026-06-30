# Catatan Pengujian Flashbot

Dokumen ini berisi daftar skenario pengujian untuk fitur Kasir Offline dan rencana pengujian integrasi Cloudflare untuk tugas berikutnya.

## 1. Pengujian Menu Kasir Offline

Pengujian ini bertujuan untuk memastikan fitur kasir dapat berjalan dengan baik dan lancar meskipun tanpa koneksi internet.

### Skenario Uji Kasir Offline:

- [x] **Akses Halaman Kasir**: Memastikan halaman kasir tetap dapat diakses saat tidak ada koneksi internet (PWA/Cache berjalan dengan baik).
- [ ] **Sinkronisasi Data Awal**: Memastikan data produk, harga, dan pelanggan telah berhasil diunduh dan tersimpan di penyimpanan lokal (Local Storage/IndexedDB) saat masih online.
- [ ] **Pencarian Produk**: Memastikan pencarian produk berfungsi cepat menggunakan data lokal.
- [x] **Proses Transaksi**: 
  - [x] Memastikan produk bisa ditambahkan ke keranjang.
  - [ ] Memastikan kalkulasi harga, diskon, dan pajak (jika ada) berjalan akurat.
  - [x] Memastikan pembayaran bisa diproses dan transaksi berhasil disimpan secara lokal.
- [x] **Pencetakan Struk**: Memastikan struk pembayaran dapat dicetak ke printer kasir lokal meskipun offline.
- [x] **Sinkronisasi Transaksi (Online Kembali)**: Memastikan semua transaksi yang terjadi saat offline otomatis dikirim ke server pusat (tersinkronisasi) setelah koneksi internet kembali pulih.

---

## 2. Pengujian Cloudflare Turnstile

Pengujian ini memastikan sistem keamanan anti-bot dari Cloudflare (Turnstile) telah terpasang dan berfungsi dengan baik pada formulir yang rawan diserang bot (misal: halaman Reservasi/Pemesanan).

### Skenario Uji Cloudflare Turnstile:

- [x] **Tampilan Widget**: Memastikan widget Turnstile (kotak verifikasi "Verify you are human") muncul dengan sempurna di halaman formulir pemesanan.
- [x] **Validasi Gagal (Bot)**: Memastikan formulir tidak dapat dikirim (atau ditolak oleh backend) jika *challenge* dari Turnstile diabaikan atau gagal.
- [x] **Validasi Sukses (Manusia)**: Memastikan proses *checkout* atau pemesanan berhasil dikirim ke server ketika widget Turnstile telah sukses diverifikasi (centang hijau).
- [x] **Integrasi Localhost**: Memastikan Turnstile dapat digunakan untuk proses pengetesan di lingkungan *development* (`localhost`) dengan menggunakan *dummy key* atau pengakuan *hostname* lokal.
