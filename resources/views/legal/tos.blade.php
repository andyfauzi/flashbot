<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Syarat dan Ketentuan - Tenanta.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card border-0 shadow-sm rounded-4 p-5">
            <h1 class="fw-bold mb-4">Syarat dan Ketentuan Layanan (Terms of Service)</h1>
            <p class="text-muted">Terakhir Diperbarui: {{ date('d F Y') }}</p>
            <hr>

            @php
                $dynamicTerms = \App\Models\LandlordSetting::get('terms_conditions');
            @endphp

            @if($dynamicTerms)
                <div class="mb-4 text-secondary" style="line-height: 1.8;">
                    {!! nl2br(e($dynamicTerms)) !!}
                </div>
            @else
            <h4>1. Penerimaan Syarat</h4>
            <p>Dengan mengakses dan menggunakan platform Tenanta.id SaaS, Anda menyetujui untuk terikat oleh Syarat dan Ketentuan ini. Jika Anda tidak setuju dengan ketentuan apa pun, Anda dilarang menggunakan layanan kami.</p>

            <h4>2. Layanan Berlangganan</h4>
            <p>Tenanta.id menyediakan layanan kasir (POS), chatbot WhatsApp, dan ERP sistem secara berlangganan (Subscription). Paket dapat dibayar secara bulanan atau tahunan sesuai tagihan yang diterbitkan oleh sistem payment gateway kami (Midtrans).</p>

            <h4>3. Batasan Penggunaan</h4>
            <p>Anda dilarang menggunakan layanan Tenanta.id untuk aktivitas ilegal, penipuan, atau melanggar hukum yang berlaku di Republik Indonesia. Pelanggaran terhadap hal ini akan mengakibatkan pemutusan layanan secara sepihak tanpa pengembalian dana (refund).</p>

            <h4>4. Penghentian Layanan</h4>
            <p>Sistem akan memberikan masa tenggang (grace period) selama 3 hari jika pembayaran Anda kedaluwarsa. Setelah itu, akses ke dashboard akan diblokir hingga pembayaran diselesaikan.</p>
            <h4>5. Kepatuhan Undang-Undang Pelindungan Data Pribadi (UU PDP)</h4>
            <p>Sesuai dengan UU PDP No. 27 Tahun 2022, Anda (sebagai <i>Data Controller</i>) setuju untuk menggunakan data pelanggan Anda secara bertanggung jawab. Tenanta.id (sebagai <i>Data Processor</i>) akan menerapkan langkah-langkah keamanan seperti enkripsi dan Penyensoran Otomatis (Data Masking) terhadap data pelanggan yang berumur lebih dari 30 hari pasca-transaksi untuk melindungi privasi pelanggan akhir.</p>
            
            <h4>8. Refund dan Pembatalan Layanan</h4>
            <h5>8.1 Kebijakan Umum</h5>
            <p>Seluruh biaya yang telah dibayarkan oleh Pelanggan untuk penggunaan Layanan pada prinsipnya bersifat final dan tidak dapat dikembalikan (non-refundable), kecuali ditentukan lain dalam Ketentuan ini atau diwajibkan oleh peraturan perundang-undangan yang berlaku.</p>
            
            <h5>8.2 Kondisi Pengajuan Refund</h5>
            <p>Pelanggan dapat mengajukan permohonan pengembalian dana apabila terjadi salah satu kondisi berikut:</p>
            <ul>
                <li>a. Terjadi pembayaran ganda atas transaksi yang sama;</li>
                <li>b. Layanan tidak dapat digunakan sama sekali akibat kesalahan sistem yang sepenuhnya disebabkan oleh Penyedia Layanan;</li>
                <li>c. Pembayaran telah berhasil dilakukan namun akun atau layanan belum diaktifkan oleh Penyedia Layanan dan belum pernah digunakan oleh Pelanggan.</li>
            </ul>

            <h5>8.3 Kondisi yang Tidak Memenuhi Syarat Refund</h5>
            <p>Pengembalian dana tidak berlaku dalam kondisi sebagai berikut:</p>
            <ul>
                <li>a. Pelanggan memutuskan untuk menghentikan penggunaan layanan setelah akun diaktifkan;</li>
                <li>b. Pelanggan tidak menggunakan layanan selama masa berlangganan;</li>
                <li>c. Gangguan layanan yang disebabkan oleh pihak ketiga, termasuk namun tidak terbatas pada WhatsApp, Meta Platforms, Inc., penyedia layanan cloud, penyedia internet, payment gateway, atau penyedia layanan teknologi lainnya;</li>
                <li>d. Akun dibatasi, ditangguhkan, atau dinonaktifkan akibat tindakan Pelanggan yang melanggar hukum, kebijakan WhatsApp, atau Ketentuan Layanan ini;</li>
                <li>e. Kesalahan penggunaan, konfigurasi, atau pengelolaan akun yang dilakukan oleh Pelanggan;</li>
                <li>f. Biaya yang telah dibayarkan untuk periode layanan yang telah berjalan, baik sebagian maupun seluruhnya.</li>
            </ul>

            <h5>8.4 Prosedur Pengajuan Refund</h5>
            <p>Pelanggan wajib mengajukan permohonan refund secara tertulis melalui kanal dukungan resmi Penyedia Layanan paling lambat 7 (tujuh) hari kalender sejak tanggal transaksi.<br>
            Permohonan refund sekurang-kurangnya harus memuat:</p>
            <ul>
                <li>a. Nama Pelanggan;</li>
                <li>b. Identitas akun yang digunakan;</li>
                <li>c. Bukti pembayaran;</li>
                <li>d. Alasan permohonan refund;</li>
                <li>e. Informasi rekening tujuan pengembalian dana.</li>
            </ul>

            <h5>8.5 Verifikasi dan Persetujuan</h5>
            <p>Penyedia Layanan berhak melakukan pemeriksaan dan verifikasi terhadap seluruh informasi yang diberikan oleh Pelanggan.<br>
            Penyedia Layanan berhak menerima atau menolak permohonan refund berdasarkan hasil verifikasi yang dilakukan. Keputusan Penyedia Layanan terkait permohonan refund bersifat final sepanjang tidak bertentangan dengan peraturan perundang-undangan yang berlaku.</p>

            <h5>8.6 Pengembalian Dana</h5>
            <p>Apabila permohonan refund disetujui, pengembalian dana akan diproses dalam waktu paling lama 14 (empat belas) Hari Kerja sejak persetujuan diberikan.<br>
            Penyedia Layanan berhak mengurangi jumlah dana yang dikembalikan sebesar biaya administrasi, biaya transaksi payment gateway, biaya perbankan, pajak, atau biaya lain yang telah dikenakan oleh pihak ketiga dan tidak dapat dipulihkan oleh Penyedia Layanan.</p>

            <h5>8.7 Penghentian Layanan</h5>
            <p>Pengajuan refund yang telah disetujui dapat mengakibatkan penghentian akses Pelanggan terhadap seluruh atau sebagian Layanan. Setelah proses refund selesai, Penyedia Layanan berhak menonaktifkan akun dan menghapus akses Pelanggan sesuai dengan kebijakan retensi data yang berlaku.</p>
            
            <h5>8.8 Lisensi dan Biaya Pihak Ketiga</h5>
            <p>Pelanggan memahami bahwa sebagian biaya berlangganan digunakan untuk pembelian atau penggunaan lisensi, infrastruktur server, nomor WhatsApp, API, dan layanan pihak ketiga lainnya yang tidak dapat dibatalkan. Oleh karena itu, biaya yang telah digunakan untuk penyediaan layanan tersebut tidak dapat dimintakan pengembalian dana.</p>
            @endif

            <div class="alert alert-info mt-4">
                <h5 class="alert-heading fw-bold">Ketentuan Integrasi WhatsApp</h5>
                <p class="mb-0">Chatbot Tenanta.id menggunakan library unofficial Whatsapp API Indonesia dan <strong>tidak berafiliasi dengan Meta atau WhatsApp Inc.</strong> Namun demikian, kami menyediakan untuk tiap tenant konfigurasi untuk melakukan pendaftaran Meta API secara mandiri melalui pengaturan toko masing-masing.</p>
            </div>

            <div class="mt-5 text-center">
                <a href="/" class="btn btn-primary rounded-pill px-4">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
