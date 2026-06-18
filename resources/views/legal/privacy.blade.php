<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kebijakan Privasi - Tenanta.id</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container py-5">
        <div class="card border-0 shadow-sm rounded-4 p-5">
            <h1 class="fw-bold mb-4">Kebijakan Privasi (Privacy Policy)</h1>
            <p class="text-muted">Terakhir Diperbarui: {{ date('d F Y') }}</p>
            <hr>
            
            <h4>1. Informasi yang Kami Kumpulkan</h4>
            <p>Kami mengumpulkan informasi pendaftaran seperti nama, email (via Google OAuth), dan detail bisnis Anda. Kami juga menyimpan data operasional aplikasi (transaksi, produk) di dalam isolasi database tenant Anda masing-masing.</p>

            <h4>2. Penggunaan Data</h4>
            <p>Data yang kami kumpulkan hanya digunakan untuk menyediakan, memelihara, dan meningkatkan kualitas layanan SaaS Tenanta.id. Kami tidak akan pernah menjual data transaksi pelanggan Anda kepada pihak ketiga mana pun.</p>

            <h4>3. Keamanan Data</h4>
            <p>Aplikasi ini memisahkan setiap data tenant secara fisik pada level database (Database-per-tenant architecture) untuk memastikan keamanan dan isolasi tingkat tinggi.</p>

            <h4>4. Retensi dan Penghapusan Data (Hak Subjek Data)</h4>
            <p>Berdasarkan UU Pelindungan Data Pribadi (UU PDP) No. 27 Tahun 2022, Anda memiliki hak penuh atas data Anda. Anda dapat meminta penghapusan permanen database toko Anda dengan mengirimkan email ke layanan support kami. Selain itu, sistem kami secara otomatis melakukan <b>Penyensoran Data (Data Masking)</b> terhadap nama, alamat, dan nomor WhatsApp pelanggan akhir Anda, serta riwayat obrolan AI yang usianya telah melebihi 30 hari pasca-transaksi untuk mencegah potensi kebocoran data historis.</p>

            <h4>5. Perlindungan Hak Kekayaan Intelektual (Resep & Formula)</h4>
            <p>Kami memahami bahwa takaran bumbu, formula, dan resep (BOM) adalah aset bisnis (Hak Kekayaan Intelektual) yang sangat berharga bagi Tenant. Oleh karena itu, seluruh kuantitas dan takaran resep yang Anda masukkan ke dalam sistem Tenanta.id <b>disandikan secara kuat (Encrypted)</b> di tingkat database. Pihak Tenanta.id maupun Super Admin pusat tidak dapat membaca atau menduplikasi resep rahasia Anda.</p>

            <div class="mt-5 text-center">
                <a href="/" class="btn btn-primary rounded-pill px-4">Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</body>
</html>
