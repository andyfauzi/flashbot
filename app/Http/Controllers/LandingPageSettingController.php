<?php

namespace App\Http\Controllers;

use App\Models\LandlordSetting;
use Illuminate\Http\Request;

class LandingPageSettingController extends Controller
{
    /**
     * Tampilkan form pengaturan landing page.
     */
    public function index()
    {
        // Ambil semua pengaturan dan jadikan key-value pair array
        $settings = LandlordSetting::pluck('value', 'key')->toArray();
        
        // Ganti null dengan string kosong agar form tidak merender nilai default
        foreach ($settings as $key => $value) {
            if ($value === null) {
                $settings[$key] = '';
            }
        }

        // Jika terms_conditions kosong, isi dengan teks default
        if (empty($settings['terms_conditions'])) {
            $settings['terms_conditions'] = "1. Penerimaan Syarat\nDengan mengakses dan menggunakan platform Tenanta.id SaaS, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini. Jika Anda tidak setuju dengan ketentuan apa pun, Anda dilarang menggunakan layanan kami.\n\n2. Layanan Berlangganan\nTenanta.id menyediakan layanan kasir (POS), chatbot WhatsApp, dan ERP sistem secara berlangganan (Subscription). Paket dapat dibayar secara bulanan atau tahunan sesuai tagihan yang diterbitkan oleh sistem payment gateway kami (Midtrans).\n\n3. Batasan Penggunaan\nAnda dilarang menggunakan layanan Tenanta.id untuk aktivitas ilegal, penipuan, atau melanggar hukum yang berlaku di Republik Indonesia. Pelanggaran terhadap hal ini akan mengakibatkan pemutusan layanan secara sepihak tanpa pengembalian dana (refund).\n\n4. Penghentian Layanan\nSistem akan memberikan masa tenggang (grace period) selama 3 hari jika pembayaran Anda kedaluwarsa. Setelah itu, akses ke dashboard akan diblokir hingga pembayaran diselesaikan.\n\n5. Kepatuhan Undang-Undang Pelindungan Data Pribadi (UU PDP)\nSesuai dengan UU PDP No. 27 Tahun 2022, Anda (sebagai Data Controller) setuju untuk menggunakan data pelanggan Anda secara bertanggung jawab. Tenanta.id (sebagai Data Processor) akan menerapkan langkah-langkah keamanan seperti enkripsi dan Penyensoran Otomatis (Data Masking) terhadap data pelanggan yang berumur lebih dari 30 hari pasca-transaksi untuk melindungi privasi pelanggan akhir.\n\n6. Refund dan Pembatalan Layanan\n6.1 Kebijakan Umum\nSeluruh biaya yang telah dibayarkan oleh Pelanggan untuk penggunaan Layanan pada prinsipnya bersifat final dan tidak dapat dikembalikan (non-refundable), kecuali ditentukan lain dalam Ketentuan ini atau diwajibkan oleh peraturan perundang-undangan yang berlaku.\n\n6.2 Kondisi Pengajuan Refund\nPelanggan dapat mengajukan permohonan pengembalian dana apabila terjadi salah satu kondisi berikut:\na. Terjadi pembayaran ganda atas transaksi yang sama;\nb. Layanan tidak dapat digunakan sama sekali akibat kesalahan sistem yang sepenuhnya disebabkan oleh Penyedia Layanan;\nc. Pembayaran telah berhasil dilakukan namun akun atau layanan belum diaktifkan oleh Penyedia Layanan dan belum pernah digunakan oleh Pelanggan.\n\n6.3 Kondisi yang Tidak Memenuhi Syarat Refund\nPengembalian dana tidak berlaku dalam kondisi sebagai berikut:\na. Pelanggan memutuskan untuk menghentikan penggunaan layanan setelah akun diaktifkan;\nb. Pelanggan tidak menggunakan layanan selama masa berlangganan;\nc. Gangguan layanan yang disebabkan oleh pihak ketiga, termasuk namun tidak terbatas pada WhatsApp, Meta Platforms, Inc., penyedia layanan cloud, penyedia internet, payment gateway, atau penyedia layanan teknologi lainnya;\nd. Akun dibatasi, ditangguhkan, atau dinonaktifkan akibat tindakan Pelanggan yang melanggar hukum, kebijakan WhatsApp, atau Ketentuan Layanan ini;\ne. Kesalahan penggunaan, konfigurasi, atau pengelolaan akun yang dilakukan oleh Pelanggan;\nf. Biaya yang telah dibayarkan untuk periode layanan yang telah berjalan, baik sebagian maupun seluruhnya.\n\n6.4 Prosedur Pengajuan Refund\nPelanggan wajib mengajukan permohonan refund secara tertulis melalui kanal dukungan resmi Penyedia Layanan paling lambat 7 (tujuh) hari kalender sejak tanggal transaksi.\nPermohonan refund sekurang-kurangnya harus memuat:\na. Nama Pelanggan;\nb. Identitas akun yang digunakan;\nc. Bukti pembayaran;\nd. Alasan permohonan refund;\ne. Informasi rekening tujuan pengembalian dana.\n\n6.5 Verifikasi dan Persetujuan\nPenyedia Layanan berhak melakukan pemeriksaan dan verifikasi terhadap seluruh informasi yang diberikan oleh Pelanggan.\nPenyedia Layanan berhak menerima atau menolak permohonan refund berdasarkan hasil verifikasi yang dilakukan. Keputusan Penyedia Layanan terkait permohonan refund bersifat final sepanjang tidak bertentangan dengan peraturan perundang-undangan yang berlaku.\n\n6.6 Pengembalian Dana\nApabila permohonan refund disetujui, pengembalian dana akan diproses dalam waktu paling lama 14 (empat belas) Hari Kerja sejak persetujuan diberikan.\nPenyedia Layanan berhak mengurangi jumlah dana yang dikembalikan sebesar biaya administrasi, biaya transaksi payment gateway, biaya perbankan, pajak, atau biaya lain yang telah dikenakan oleh pihak ketiga dan tidak dapat dipulihkan oleh Penyedia Layanan.\n\n6.7 Penghentian Layanan\nPengajuan refund yang telah disetujui dapat mengakibatkan penghentian akses Pelanggan terhadap seluruh atau sebagian Layanan. Setelah proses refund selesai, Penyedia Layanan berhak menonaktifkan akun dan menghapus akses Pelanggan sesuai dengan kebijakan retensi data yang berlaku.\n\n6.8 Lisensi dan Biaya Pihak Ketiga\nPelanggan memahami bahwa sebagian biaya berlangganan digunakan untuk pembelian atau penggunaan lisensi, infrastruktur server, nomor WhatsApp, API, dan layanan pihak ketiga lainnya yang tidak dapat dibatalkan. Oleh karena itu, biaya yang telah digunakan untuk penyediaan layanan tersebut tidak dapat dimintakan pengembalian dana.";
        }

        return view('superadmin.landing-page.index', compact('settings'));
    }

    /**
     * Simpan pembaruan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'hero_image', 'user_guide_image']);

        // Handle image upload
        if ($request->hasFile('hero_image')) {
            $request->validate([
                'hero_image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $path = $request->file('hero_image')->store('landing', 'public');
            LandlordSetting::set('hero_image', $path);
        }

        if ($request->hasFile('user_guide_image')) {
            $request->validate([
                'user_guide_image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $path = $request->file('user_guide_image')->store('landing', 'public');
            LandlordSetting::set('user_guide_image', $path);
        }

        foreach ($data as $key => $value) {
            LandlordSetting::set($key, $value);
        }

        return redirect()->route('superadmin.landing_page')->with('sukses', 'Pengaturan Landing Page berhasil diperbarui!');
    }
}
