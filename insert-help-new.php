<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$newGuides = [
    [
        'pertanyaan' => 'Bagaimana cara mengatur Zona Waktu Toko?',
        'jawaban' => "<b>Langkah-langkah Mengatur Zona Waktu:</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Identitas Toko</b>.\n2. Gulir ke bawah hingga menemukan bagian <b>Zona Waktu Lokasi Toko</b>.\n3. Pilih zona waktu yang sesuai dengan lokasi toko Anda:\n   - <b>WIB</b> (Asia/Jakarta) → untuk Sumatera, Jawa, Kalimantan Barat &amp; Tengah\n   - <b>WITA</b> (Asia/Makassar) → untuk Bali, NTB, NTT, Kalimantan Selatan &amp; Timur, Sulawesi\n   - <b>WIT</b> (Asia/Jayapura) → untuk Maluku dan Papua\n4. Klik <b>Simpan Identitas Toko</b>.\n\n<b>Mengapa ini penting?</b>\nZona waktu yang benar memastikan:\n• Bot AI menyebutkan waktu yang tepat saat pelanggan memesan\n• Jam operasional toko (buka/tutup) dihitung berdasarkan jam lokal Anda\n• Timestamp pesanan, struk, dan laporan akurat sesuai waktu setempat",
    ],
    [
        'pertanyaan' => 'Bagaimana cara mengubah Tema Warna Portal dan Tampilan Dashboard?',
        'jawaban' => "<b>Langkah-langkah Mengubah Tema Portal:</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Pengaturan Landing Page / Portal</b>.\n2. Pada bagian <b>Pengaturan Tema Warna Portal</b>, pilih salah satu tema:\n   - <b>Cool (Harmoni Violet &amp; Slate)</b> → Tampilan elegan biru-ungu, cocok untuk kafe atau toko modern\n   - <b>Warm (Energetik Oranye &amp; Kuning)</b> → Tampilan hangat dan bersemangat, cocok untuk restoran atau warung\n   - <b>Kalem (Pastel Hijau &amp; Tosca)</b> → Tampilan alami dan segar, cocok untuk toko herbal, salad, atau makanan sehat\n3. Klik <b>Simpan Pengaturan Portal</b>. Tema akan langsung berubah secara real-time.\n\n<b>Langkah-langkah Mengubah Tema Dashboard:</b>\n1. Masih di halaman yang sama, gulir ke bagian <b>Tema Dashboard Admin</b>.\n2. Pilih tema tampilan antarmuka admin panel Anda.\n3. Simpan. Perubahan akan langsung terlihat.\n\n💡 Tip: Anda juga bisa mengunggah gambar latar (Hero Image) di halaman yang sama untuk memperindah tampilan beranda portal pelanggan Anda.",
    ],
    [
        'pertanyaan' => 'Apa perbedaan Mode AI (Gemini) dan Mode Manual? Bagaimana cara menggantinya?',
        'jawaban' => "<b>Perbedaan Mode Chatbot:</b>\n\n<b>Mode AI (Gemini)</b>\n• Bot akan merespons pelanggan secara cerdas menggunakan kecerdasan buatan (AI) dari Google Gemini\n• Bot bisa memahami percakapan bebas, menjawab pertanyaan di luar katalog, dan memandu pemesanan secara alami\n• Bot dapat menangani: Pesan produk, lihat katalog, buat reservasi, batalkan pesanan, ubah detail pesanan\n• Memerlukan API Key Gemini dari akun Google AI Studio Anda\n\n<b>Mode Manual</b>\n• Bot merespons berdasarkan perintah kata kunci yang sudah ditentukan (contoh: ketik 'katalog', 'order', 'status order')\n• Lebih stabil dan tidak bergantung pada kuota API eksternal\n• Lebih cepat dan konsisten untuk alur pemesanan sederhana\n\n<b>Cara Mengaktifkan Mode AI:</b>\n1. Pastikan Anda sudah punya <b>API Key Gemini</b> dari <a href='https://aistudio.google.com' target='_blank'>Google AI Studio</a> (gratis)\n2. Buka menu <b>Chatbot &amp; WhatsApp</b> > <b>Pengaturan AI</b>\n3. Paste API Key Anda di kolom yang tersedia, lalu simpan\n4. Pastikan variabel USE_GEMINI_AI=true sudah aktif (tanya admin teknis jika perlu)\n\n<b>Catatan Penting tentang Kuota AI:</b>\n• Paket gratis Google AI Studio memiliki batas penggunaan harian\n• Jika kuota habis, bot <b>otomatis beralih ke Mode Manual</b> dan memberitahu pelanggan dengan pesan ramah\n• Untuk menghindari kuota cepat habis: Kurangi percakapan basa-basi, arahkan pelanggan langsung ke katalog",
    ],
    [
        'pertanyaan' => 'Bagaimana alur pelanggan memesan melalui Portal Web (Pemesanan Online)?',
        'jawaban' => "<b>Alur Pemesanan Pelanggan via Portal Web:</b>\n\n<b>Akses Portal:</b>\nPelanggan mengunjungi alamat portal Anda (biasanya: <i>namatoko.tenanta.id</i> atau URL khusus yang Anda bagikan).\n\n<b>Langkah Pemesanan:</b>\n1. Pelanggan membuka portal dan melihat halaman beranda dengan foto toko, deskripsi, dan tombol mulai pesan\n2. Klik <b>Pesan Sekarang</b> atau langsung menuju halaman Katalog\n3. Pelanggan memilih produk, memilih varian (jika ada), dan menentukan jumlah\n4. Produk masuk ke <b>keranjang belanja</b>. Pelanggan dapat terus menambah produk\n5. Setelah selesai memilih, klik <b>Lihat Keranjang</b> > <b>Checkout</b>\n6. Isi form checkout: Nama, Nomor HP (untuk notifikasi WA), Alamat Pengiriman atau pilih Ambil Sendiri, Tanggal Pengiriman, Metode Pembayaran\n7. Klik <b>Buat Pesanan</b>\n8. Sistem otomatis membuat nomor order dan mengirim notifikasi ke grup admin Anda via WhatsApp\n9. Admin menyetujui pesanan (ketik '!setuju-order [nomor]' di grup WA atau klik di dashboard)\n10. Sistem mengirim konfirmasi + notifikasi ke nomor HP pelanggan\n\n<b>Cara mengaktifkan Portal:</b>\nPortal aktif secara otomatis jika toko Anda sudah terverifikasi. Bagikan URL portal ke pelanggan via sosial media, WhatsApp, atau pasang di bio Instagram!",
    ],
    [
        'pertanyaan' => 'Bagaimana alur pelanggan membuat Reservasi melalui Portal Web?',
        'jawaban' => "<b>Alur Reservasi via Portal Web:</b>\n\n<b>Syarat:</b> Fitur reservasi hanya tersedia jika Anda mengaktifkan layanan <b>Dine-in</b> di menu Pengaturan Sistem > Identitas Toko > Jenis Layanan.\n\n<b>Langkah Reservasi Pelanggan:</b>\n1. Pelanggan membuka portal Anda dan klik menu <b>Reservasi</b>\n2. Pelanggan mengisi form: Nama lengkap, Nomor HP, Tanggal &amp; Waktu reservasi, Jumlah tamu (pax), Catatan khusus (opsional)\n3. Jika toko Anda mewajibkan <b>Uang Muka (DP)</b>, sistem akan menampilkan instruksi pembayaran DP beserta nomor rekening toko\n4. Klik <b>Ajukan Reservasi</b>\n5. Admin menerima notifikasi reservasi baru via grup WhatsApp\n6. Admin memverifikasi ketersediaan meja dan mengkonfirmasi via dashboard (<b>Dine-in &amp; Reservasi</b> > <b>Jadwal Reservasi</b>)\n7. Sistem otomatis mengirim pesan konfirmasi ke nomor HP pelanggan\n\n<b>Cara Mengatur Reservasi di Dashboard:</b>\n1. Buka <b>Pengaturan Sistem</b> > <b>Identitas Toko</b>\n2. Aktifkan jenis layanan <b>Dine-in</b> atau <b>Keduanya</b>\n3. Atur <b>Minimal Jam Reservasi</b> (berapa jam sebelumnya pelanggan harus reservasi)\n4. Atur apakah <b>DP Wajib</b> dibayar atau tidak",
    ],
    [
        'pertanyaan' => 'Bagaimana cara mengelola Katalog dan Tampilan Portal dari Dashboard?',
        'jawaban' => "<b>Mengelola Katalog yang Tampil di Portal:</b>\n\n1. Buka menu <b>Produk &amp; Inventori</b> > <b>Produk &amp; Varian</b>\n2. Pastikan produk yang ingin ditampilkan di portal memiliki status <b>Aktif</b> (toggle hijau)\n3. Tambahkan foto produk yang menarik karena gambar produk akan langsung tampil di katalog portal\n4. Isi deskripsi produk yang informatif agar pelanggan mudah memilih\n5. Produk yang stoknya habis (stok = 0) akan otomatis ditandai 'Habis' di portal\n\n<b>Mengelola Tampilan Portal (Landing Page):</b>\n1. Buka menu <b>Pengaturan Sistem</b> > <b>Pengaturan Landing Page / Portal</b>\n2. Di sini Anda bisa mengatur:\n   - <b>Tema warna</b> portal (Cool/Warm/Kalem)\n   - <b>Hero Image</b> → gambar latar beranda (disarankan 1920x1080px)\n   - <b>Deskripsi Toko</b> → teks sambutan yang muncul di beranda\n   - <b>Galeri Foto</b> → foto-foto toko/produk unggulan untuk tampilan profil\n   - <b>Informasi Kontak</b> → alamat, email, WhatsApp yang tampil di portal\n   - <b>Syarat &amp; Ketentuan</b> → aturan pemesanan yang dapat dibaca pelanggan\n3. Klik <b>Simpan Pengaturan Portal</b> untuk menerapkan perubahan\n\n<b>Melihat Portal dari Sudut Pandang Pelanggan:</b>\nKlik ikon mata (👁) atau tautan 'Lihat Portal' yang ada di bagian atas dashboard untuk preview tampilan portal Anda secara langsung.",
    ],
];

$inserted = 0;
try {
    foreach ($newGuides as $guide) {
        $exists = \App\Models\LandlordHelpGuide::where('pertanyaan', $guide['pertanyaan'])->exists();
        if (!$exists) {
            \App\Models\LandlordHelpGuide::create([
                'pertanyaan' => $guide['pertanyaan'],
                'jawaban'    => nl2br($guide['jawaban']),
                'urutan'     => \App\Models\LandlordHelpGuide::max('urutan') + 1,
            ]);
            $inserted++;
            echo "Ditambahkan: {$guide['pertanyaan']}\n";
        } else {
            echo "Sudah ada (dilewati): {$guide['pertanyaan']}\n";
        }
    }
    echo "\nSelesai! {$inserted} panduan baru berhasil ditambahkan.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
