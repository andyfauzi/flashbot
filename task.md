# Task List: Sinkronisasi DP Reservasi dengan Pesanan

## 1. Analisis & Perencanaan Struktur Data
- [ ] Periksa relasi antara tabel `reservasis` dan `pesanans` di database.
- [ ] Tentukan bagaimana pesanan akan dikaitkan dengan reservasi (misalnya melalui nomor telepon pelanggan, `meja_id`, atau menambahkan kolom `reservasi_id` pada tabel `pesanans`).
- [ ] Pahami alur pembayaran DP reservasi dan penyimpanannya.

## 2. Modifikasi Backend & Logika Pemesanan
- [ ] **Kasir (POS)**: Ubah logika pembuatan pesanan agar otomatis mengecek apakah meja/pelanggan terkait memiliki reservasi aktif di hari dan jam tersebut. Jika ya, tarik nominal DP secara otomatis.
- [ ] **Portal / Chatbot**: Jika pelanggan memesan via portal menggunakan nomor WhatsApp yang sama dengan data reservasi, otomatis terapkan DP ke keranjang pesanan.
- [ ] Hitung ulang total bayar dengan mengurangkan uang DP dari total tagihan pesanan.

## 3. Pembaruan UI & Cetak Struk
- [ ] **Halaman Kasir (POS)**: Tampilkan informasi atau *badge* "DP Reservasi Tersedia: Rp X" saat kasir memilih meja atau memasukkan data pelanggan.
- [ ] **Cetak Struk (PDF & Thermal)**: Tampilkan baris potongan "Uang Muka (DP) Reservasi: - Rp X" sebelum Total Bayar, sehingga tagihan akhir menjadi akurat.
- [ ] **Dashboard Admin**: Tampilkan kaitan/link antara detail pesanan dan detail reservasi.

## 4. Pengujian & Verifikasi
- [ ] Lakukan simulasi pembuatan reservasi dengan DP.
- [ ] Buat pesanan (Dine-in) untuk meja yang sama, lalu pastikan DP terpotong dengan benar.
- [ ] Cetak struk dan pastikan nominalnya tercatat dengan baik.
- [ ] Pastikan bahwa DP tidak ditarik *dua kali* untuk pesanan yang berbeda.

## 5. Manajemen Paket & Langganan Tenant (Super Admin)
- [ ] **Modifikasi UI Modal**: Hapus/gantikan komponen *switch button* (Batasan Fitur Aktif) pada modal "Atur Paket & Fitur" di halaman Super Admin karena sudah tidak relevan (akses fitur seharusnya otomatis mengikuti Paket Langganan).
- [ ] **Logika Super Admin**: Berikan kebebasan (*full override*) kepada Super Admin untuk mengubah status Paket Langganan (Plan) dan mengatur/memperpanjang Masa Aktif Paket (Masa Berlaku) secara bebas di modal tersebut.
- [ ] **Pembaruan Fitur Tenant**: Pastikan saat Super Admin menyimpan perubahan paket (misal dari Free ke Business), sistem secara otomatis menyesuaikan *feature flags* (hak akses fitur) tenant tersebut di database tanpa perlu *switch button* manual.
- [ ] **Validasi Akses**: Verifikasi di setiap akses fitur (POS, Chatbot, dll) bahwa hak akses fitur dievaluasi langsung berdasarkan tipe paket yang telah diatur oleh Super Admin dan tenggat waktu berlakunya paket.
-[ ]***masa trial** pastikan masa trial tidak menggangu data keuangan super admin
-[ ]***buatkan skema untuk plan gratis*** perencanaan jika busines plan tidak laku,busines plan akan di hapus dan digantikan dengan pro plan dengan akses ke semua menu
- [ ] **Analisis Lingkungan Server**: Lakukan pengecekan dan analisis mendalam untuk mencari tahu mengapa manajemen paket tenant di server lokal (localhost) memiliki perilaku atau sinkronisasi data yang berbeda dibandingkan saat dijalankan di server *live/production*. (Cek *database migration state*, *cache*, dan versi *codebase*).
-[ ] super admin bisa membatasi jumlah pendaftar melalui dashboard
