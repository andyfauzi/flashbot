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
- [ ] **Pencetakan Struk**: Memastikan struk pembayaran dapat dicetak ke printer kasir lokal meskipun offline.
- [ ] **Sinkronisasi Transaksi (Online Kembali)**: Memastikan semua transaksi yang terjadi saat offline otomatis dikirim ke server pusat (tersinkronisasi) setelah koneksi internet kembali pulih.

---

## 2. Pengujian Cloudflare (Tugas Berikutnya)

Pengujian ini akan dilakukan pada tahap selanjutnya untuk memastikan aplikasi dapat diakses secara publik dan aman menggunakan jaringan Cloudflare.

### Skenario Uji Cloudflare:

- [ ] **Setup Cloudflare Tunnel / DNS**: Memastikan konfigurasi tunnel atau DNS dari server lokal/hosting ke Cloudflare berjalan tanpa masalah.
- [ ] **Aksesibilitas Eksternal**: Memastikan aplikasi Flashbot dapat diakses dari internet luar melalui domain yang dikelola Cloudflare.
- [ ] **Pengujian SSL/HTTPS**: Memastikan semua lalu lintas web terenkripsi dengan aman dan sertifikat SSL dari Cloudflare aktif.
- [ ] **Uji Kinerja & Caching**: Memastikan aset-aset statis (gambar, CSS, JS) di-cache secara efisien oleh Edge Network Cloudflare.
- [ ] **Fungsi Web Socket (Opsional)**: Jika aplikasi kasir memerlukan sinkronisasi real-time via WebSocket, pastikan lalu lintas WebSocket diizinkan dan berjalan lancar melalui proksi Cloudflare.
