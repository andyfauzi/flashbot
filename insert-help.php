<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

try {
    $guide1 = new \App\Models\LandlordHelpGuide();
    $guide1->pertanyaan = "Bagaimana fitur Nomor Antrian bekerja?";
    $guide1->jawaban = "Sistem secara otomatis akan memberikan nomor antrian untuk setiap pesanan baru yang masuk, baik dari Portal Pelanggan, Kasir (POS), maupun Chatbot WhatsApp. Nomor antrian ini mulai dari 1 dan akan otomatis direset ulang setiap hari (jam 00:00). Anda dapat melihat nomor antrian di halaman Kasir (POS), di Cetakan Struk, dan notifikasi ke pelanggan.";
    $guide1->urutan = \App\Models\LandlordHelpGuide::max('urutan') + 1;
    $guide1->save();
    
    $guide2 = new \App\Models\LandlordHelpGuide();
    $guide2->pertanyaan = "Bagaimana cara menentukan tanggal dan jam pengambilan Pre-Order?";
    $guide2->jawaban = "Di halaman Kasir (POS), saat Anda mengaktifkan opsi 'Jadikan Pre-Order (Ambil Nanti)', kolom 'Tanggal Pengambilan' akan otomatis terisi dengan tanggal dan waktu saat ini. Anda dapat mengklik kolom tersebut untuk mengubah hari dan memilih jam pengambilan spesifik sesuai keinginan pelanggan. Informasi waktu ini akan terlihat di bagian dapur/pre-order.";
    $guide2->urutan = \App\Models\LandlordHelpGuide::max('urutan') + 1;
    $guide2->save();
    
    echo "Success inserting help guides.";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage();
}
