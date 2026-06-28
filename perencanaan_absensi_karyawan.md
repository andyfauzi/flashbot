# Perencanaan Fitur Absensi Karyawan (Draft)

Fitur absensi karyawan ini direncanakan untuk diintegrasikan ke dalam sistem Flashbot menggunakan data `User` (kasir/staf) yang sudah ada di database. Berikut adalah beberapa opsi tingkat keamanan dan mekanisme yang bisa diimplementasikan sesuai kebutuhan Outlet:

## Opsi Mekanisme Absensi
1. **Absensi Sederhana (Simple Button)**
   - Karyawan login ke dashboard kasir.
   - Tersedia tombol **"Check In"** (untuk masuk) dan **"Check Out"** (untuk pulang).
   - Sistem hanya mencatat jam kehadiran (timestamp) berdasarkan waktu server.

2. **Absensi Ketat (Selfie & Validasi Lokasi)**
   - Saat karyawan mengklik tombol Check In/Out, sistem akan meminta akses kamera perangkat (HP/Tablet Kasir).
   - Karyawan wajib mengambil **foto selfie** (langsung dari kamera, tidak bisa upload dari galeri).
   - Sistem juga mencatat **kordinat GPS** (Latitude & Longitude) untuk divalidasi apakah karyawan benar-benar berada di dalam radius outlet (opsional jika lokasi outlet diset di sistem).

3. **Absensi Berbasis Jadwal Shift**
   - Absensi dikunci berdasarkan jadwal `Shift` karyawan.
   - Karyawan tidak bisa Check In jika belum waktunya (atau hanya bisa Check In 30 menit sebelum shift dimulai).
   - Akan ada penanda otomatis (flag) untuk status: *Tepat Waktu, Terlambat, Pulang Cepat, atau Lembur*.

## Kebutuhan Teknis (Technical Requirements)
- Pembuatan tabel database baru: `absensi` (karyawan_id, tanggal, jam_masuk, jam_pulang, foto_masuk, foto_pulang, lokasi, status).
- Penambahan menu "Absensi" di sidebar kasir.
- Halaman "Laporan Kehadiran" khusus untuk level Owner/Superadmin untuk mengekspor (Excel/PDF) rekap kehadiran bulanan.

*Status: Menunggu keputusan Owner untuk level ketatnya absensi sebelum eksekusi koding dilakukan.*
