<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\LandlordHelpGuide;

class PanduanTenantSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        // Hapus panduan yang sudah ada agar tidak duplikat
        LandlordHelpGuide::truncate();

        $guides = [
            // ==========================================
            // KASIR & PENJUALAN
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menggunakan Kasir POS untuk menerima pesanan?',
                'jawaban' => "<b>Langkah-langkah Menggunakan Kasir (POS):</b>\n1. Buka menu <b>Kasir & Penjualan</b> > <b>Kasir (POS)</b> di sidebar kiri.\n2. Di sebelah kiri, Anda akan melihat daftar produk Anda. Klik pada produk yang ingin dibeli pelanggan untuk memindahkannya ke keranjang.\n3. Anda dapat mencari produk melalui kolom pencarian di bagian atas atau memfilternya berdasarkan kategori.\n4. Di keranjang pesanan (sebelah kanan), Anda dapat menambah jumlah kuantitas dengan menekan ikon (+) atau (-) pada tiap item.\n5. Jika pelanggan memiliki diskon atau ingin memasukkan ongkir (jika ada), silakan atur di opsi yang tersedia.\n6. Setelah pesanan sesuai, klik tombol <b>Bayar</b> (atau proses tagihan).\n7. Pilih metode pembayaran yang digunakan (Cash, QRIS, Transfer, dll).\n8. Masukkan jumlah uang yang diterima (jika tunai) dan klik <b>Simpan Transaksi</b>.\n9. Transaksi selesai, dan Anda dapat memilih untuk mencetak struk secara otomatis.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara melihat dan mengelola Riwayat Transaksi?',
                'jawaban' => "<b>Langkah-langkah Melihat Riwayat Transaksi:</b>\n1. Buka menu <b>Kasir & Penjualan</b> > <b>Riwayat Transaksi</b>.\n2. Anda akan melihat daftar semua transaksi yang pernah terjadi. Anda dapat memfilter transaksi berdasarkan rentang tanggal atau metode pembayaran.\n3. Untuk membatalkan transaksi yang salah (void), temukan transaksi tersebut, lalu klik tombol <b>Batal (Cancel)</b> pada opsi (titik tiga) di ujung kanan baris tersebut.\n4. Membatalkan transaksi akan secara otomatis mengembalikan stok produk yang telah berkurang dan mengoreksi laporan pendapatan.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara memproses Jadwal Pesanan (Pre-Order / Reservasi Makanan)?',
                'jawaban' => "<b>Langkah-langkah Memproses Pesanan Jadwal / Pre-Order:</b>\n1. Buka menu <b>Kasir & Penjualan</b> > <b>Jadwal Pesanan</b>.\n2. Menu ini berisi daftar pesanan pelanggan yang dilakukan via Chatbot WhatsApp atau reservasi yang masuk ke sistem dengan metode ambil nanti (Pick-Up / Delivery).\n3. Saat pesanan baru masuk, statusnya biasanya 'Menunggu Pembayaran' atau 'Diproses'.\n4. Klik <b>Detail</b> pada pesanan untuk melihat rincian barang yang dipesan.\n5. Jika pelanggan sudah membayar DP, Anda dapat memasukkan jumlah DP melalui tombol <b>Set DP</b>.\n6. Jika makanan/barang sudah selesai disiapkan, klik <b>Kirim Notifikasi Siap</b> agar sistem mengirim pesan WhatsApp otomatis ke pelanggan bahwa pesanannya siap diambil.\n7. Setelah pesanan selesai diambil dan dilunasi, klik <b>Selesai</b>.",
            ],

            // ==========================================
            // DINE-IN & RESERVASI
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara mengelola Manajemen Meja untuk Dine-in?',
                'jawaban' => "<b>Langkah-langkah Mengatur Manajemen Meja:</b>\n1. Buka menu <b>Dine-in & Reservasi</b> > <b>Manajemen Meja</b>.\n2. Klik tombol <b>Tambah Meja</b>.\n3. Masukkan Nomor atau Nama Meja (Contoh: Meja 01, VIP 01).\n4. Tentukan kapasitas meja (jumlah maksimal orang) dan simpan.\n5. Sistem akan secara otomatis membuat kode QR khusus untuk meja tersebut. Anda dapat mengunduh dan mencetak QR Code tersebut untuk ditempel di meja.\n6. Pelanggan yang duduk di meja tersebut dapat memindai (scan) QR Code menggunakan HP mereka untuk melakukan Self-Order tanpa harus memanggil pelayan.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengelola Jadwal Reservasi?',
                'jawaban' => "<b>Langkah-langkah Mengatur Jadwal Reservasi:</b>\n1. Buka menu <b>Dine-in & Reservasi</b> > <b>Jadwal Reservasi</b>.\n2. Menu ini memungkinkan Anda mencatat pelanggan yang ingin melakukan booking tempat/meja di tanggal tertentu.\n3. Klik <b>Tambah Reservasi</b>, lalu masukkan Nama Pelanggan, Nomor HP, Tanggal/Waktu Booking, Jumlah Orang, dan pilih Meja yang akan dibooking.\n4. Meja yang telah dibooking akan ditandai agar tidak digunakan oleh tamu lain pada jam tersebut.",
            ],

            // ==========================================
            // PRODUK & INVENTORI
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menambah Kategori Produk?',
                'jawaban' => "<b>Langkah-langkah Menambah Kategori Produk:</b>\n1. Buka menu <b>Produk & Inventori</b> > <b>Kategori Produk</b>.\n2. Klik tombol <b>Tambah Kategori</b> di kanan atas.\n3. Masukkan nama kategori (Contoh: Minuman Dingin, Makanan Berat, Snack).\n4. Simpan. Kategori ini nantinya sangat berguna agar Kasir dan Pelanggan di Chatbot mudah mencari kelompok produk Anda.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara memasukkan Produk Baru dan Varian?',
                'jawaban' => "<b>Langkah-langkah Menambah Produk:</b>\n1. Buka menu <b>Produk & Inventori</b> > <b>Produk & Varian</b>.\n2. Klik tombol <b>Tambah Produk</b>.\n3. Masukkan Informasi Dasar: Nama Produk, Kategori, Deskripsi singkat, dan unggah Gambar (Sangat disarankan agar tampilan di Chatbot/Portal lebih menarik).\n4. Pada bagian <b>Varian</b>: Walaupun produk Anda tidak memiliki varian ukuran, Anda <b>wajib mengisi setidaknya 1 varian standar</b> (misalnya bernama 'Regular').\n5. Tentukan Harga Jual dan Harga Modal (HPP - Opsional).\n6. Aktifkan saklar <b>Kelola Stok</b> jika Anda ingin sistem secara otomatis memotong stok saat barang terjual. Masukkan stok awal.\n7. Klik <b>Simpan</b>.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara menggunakan fitur Pengelolaan Stok?',
                'jawaban' => "<b>Langkah-langkah Mengelola Stok Produk:</b>\n1. Buka menu <b>Produk & Inventori</b> > <b>Pengelolaan Stok</b>.\n2. Halaman ini berfokus pada manajemen jumlah fisik barang jadi Anda.\n3. Jika barang menipis dan Anda ingin menambah stok, cari produk lalu klik tombol <b>Restock / In</b>. Masukkan jumlah stok masuk dan catatannya.\n4. Jika ada produk yang rusak/basi/hilang, gunakan tombol <b>Adjustment / Rusak</b> untuk mengurangi stok secara akurat di sistem tanpa membukukan pendapatan.",
            ],

            // ==========================================
            // PRODUKSI & HPP
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menggunakan Kalkulator HPP & Resep Dapur?',
                'jawaban' => "<b>Langkah-langkah Menggunakan Fitur Produksi (F&B / Manufaktur):</b>\nSistem ini bisa menghitung modal makanan Anda dengan sangat detail berdasarkan gramasi bahan.\n\n<b>Tahap 1: Masukkan Master Bahan Baku</b>\n1. Buka menu <b>Produksi & HPP</b> > <b>Master Bahan Baku</b>.\n2. Daftarkan semua bahan dasar Anda. Contoh: Daging Ayam (Beli: Rp 40.000 / Kg), Gula Pasir (Beli: Rp 15.000 / Kg).\n3. Pastikan satuan dasar pembelian terisi benar (Gram, Kg, Liter, Pcs).\n\n<b>Tahap 2: Rakit Resep di Kalkulator HPP</b>\n1. Buka menu <b>Produksi & HPP</b> > <b>Kalkulator HPP</b>.\n2. Anda akan melihat daftar Produk/Varian Anda. Klik ikon <b>Hitung (Kalkulator)</b> di salah satu varian.\n3. Tambahkan bahan baku yang dibutuhkan untuk membuat 1 porsi produk tersebut (Misal: 1 porsi Ayam Bakar = 200 gram Daging Ayam, 10 gram Kecap, dst).\n4. Sistem akan otomatis menghitung Harga Pokok Penjualan (HPP) total untuk porsi tersebut secara real-time!\n\n<b>Tahap 3: Produksi Dapur (Pemotongan Stok Bahan)</b>\n1. Masuk ke <b>Produksi & HPP</b> > <b>Produksi Dapur</b>.\n2. Setiap kali dapur selesai memasak 1 Batch (Misal memasak 50 Porsi Ayam Bakar sekaligus), catat di sini.\n3. Sistem akan secara cerdas <b>menambah 50 stok barang jadi Ayam Bakar</b> Anda di inventori, dan secara otomatis <b>memotong stok mentah Daging Ayam dan Kecap</b> di gudang sesuai takaran resep yang Anda buat tadi!",
            ],

            // ==========================================
            // KEUANGAN & LAPORAN
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menggunakan Buku Kas & Membaca Laporan?',
                'jawaban' => "<b>Langkah-langkah Mengelola Buku Kas & Keuangan:</b>\n1. Buka menu <b>Keuangan & Laporan</b> > <b>Buku Kas & Laporan</b>.\n2. Di bagian atas, Anda dapat melihat ringkasan Pendapatan Kotor, Laba Kotor (Pendapatan - HPP), dan Total Pengeluaran hari/bulan ini.\n3. Seluruh pemasukan dari Kasir otomatis tercatat di sini. Namun, jika Anda memiliki <b>Pengeluaran Operasional</b> (seperti beli gas, bayar listrik, uang kebersihan), Anda dapat meng-klik tombol <b>Catat Pengeluaran Baru</b>.\n4. Masukkan nominal pengeluaran dan keterangannya.\n5. Anda bisa memfilter dan mengunduh (Export Excel/PDF) laporan bulanan untuk memantau performa toko secara berkala.",
            ],

            // ==========================================
            // CHATBOT & WHATSAPP
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menghubungkan Nomor WhatsApp (Device) dengan Tenanta.id?',
                'jawaban' => "<b>Langkah-langkah Menghubungkan Device WhatsApp Anda:</b>\n1. Buka menu <b>Chatbot & WhatsApp</b> > <b>Pengaturan Device</b>.\n2. Anda dapat mendaftarkan nomor HP yang akan menjadi bot Anda.\n3. Klik tombol untuk memunculkan QR Code (Prosesnya sama persis seperti menghubungkan WA Web).\n4. Buka WhatsApp di HP toko Anda > Pilih <b>Linked Devices (Perangkat Taut)</b> > Scan QR yang ada di layar komputer Anda.\n5. Tunggu hingga statusnya berubah menjadi 'Connected'. Sekarang nomor Anda sudah bisa menerima pesanan otomatis!",
            ],
            [
                'pertanyaan' => 'Apa itu Dashboard Chatbot dan Riwayat Pesan?',
                'jawaban' => "<b>Cara Memantau Chatbot:</b>\n1. Menu <b>Dashboard Chatbot</b> menampilkan grafik statistik jumlah pesan yang masuk, keluar, serta sisa kuota pengiriman pesan jika Anda menggunakan layanan berbatas.\n2. Menu <b>Riwayat Pesan</b> menampilkan log aktivitas obrolan antara Robot (Sistem Tenanta) dengan pelanggan secara live.\n3. Jika Anda merasa bot salah merespon, Anda bisa memantau dan mengambil alih obrolan secara langsung dari ponsel Anda.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara melakukan Broadcast Promosi WhatsApp?',
                'jawaban' => "<b>Langkah-langkah Mengirim Broadcast:</b>\n1. Buka menu <b>Chatbot & WhatsApp</b> > <b>Broadcast Promosi</b>.\n2. Fitur ini memungkinkan Anda mengirimkan promo massal (Blast) ke nomor-nomor pelanggan yang sudah tersimpan di database Anda.\n3. Buat Kampanye Baru, pilih target pelanggan (Misalnya: Pelanggan yang pesan minggu lalu).\n4. Tulis pesan promosi Anda, tambahkan gambar poster promo jika ada.\n5. Klik kirim, sistem akan perlahan-lahan mengirimkannya melalui nomor bot Anda secara bertahap untuk menghindari blokir spam dari WhatsApp.",
            ],

            // ==========================================
            // PENGATURAN SISTEM
            // ==========================================
            [
                'pertanyaan' => 'Bagaimana cara menambah Hak Akses Karyawan (Admin/Kasir/Dapur)?',
                'jawaban' => "<b>Langkah-langkah Menambah Akun Karyawan:</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Hak Akses Karyawan</b>.\n2. Klik tombol <b>Tambah Akun</b>.\n3. Masukkan Nama, Email, dan Password untuk karyawan Anda.\n4. Pada kolom <b>Akses Peran (Role)</b>, centang akses apa saja yang mereka miliki (Misalnya: Kasir hanya bisa buka menu Kasir, Dapur hanya bisa buka menu Produksi).\n5. Simpan. Karyawan Anda sekarang bisa login di URL Tenant Anda (namatoko.tenanta.id/login) menggunakan email dan password tersebut.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara mengubah Identitas Toko (Logo, Alamat)?',
                'jawaban' => "<b>Langkah-langkah Mengubah Profil Toko:</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Identitas Toko</b>.\n2. Di sini Anda bisa mengunggah Logo Toko, mengganti nama toko, deskripsi, alamat lengkap, hingga jam operasional.\n3. Pastikan jam operasional diisi dengan benar, karena chatbot WhatsApp akan membatasi orderan pelanggan jika toko Anda sedang berstatus 'Tutup'.",
            ],
            [
                'pertanyaan' => 'Bagaimana cara menggunakan Manajemen Kurir untuk pesan antar (Delivery)?',
                'jawaban' => "<b>Langkah-langkah Mengelola Kurir Internal:</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Manajemen Kurir</b>.\n2. Daftarkan nama, pelat nomor, dan nomor WhatsApp kurir atau driver toko Anda.\n3. Saat ada pesanan yang masuk dengan metode Delivery, Anda bisa menugaskan pesanan tersebut ke salah satu kurir yang Anda daftarkan.\n4. Sistem dapat mengirimkan notifikasi otomatis ke nomor WhatsApp kurir berisi rincian alamat tujuan dan barang yang harus diantar.",
            ],
        ];

        $urutan = 1;
        foreach ($guides as $guide) {
            LandlordHelpGuide::create([
                'pertanyaan' => $guide['pertanyaan'],
                'jawaban'    => nl2br($guide['jawaban']), // Simpan dengan format line break HTML agar rapi
                'urutan'     => $urutan++,
            ]);
        }
    }
}
