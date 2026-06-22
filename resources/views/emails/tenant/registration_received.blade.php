<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pendaftaran Toko Sedang Diproses</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Terima Kasih Telah Mendaftar!</h2>
        
        <p>Halo {{ $tenantRequest->owner_name }},</p>
        
        <p>Terima kasih telah mendaftarkan toko <strong>{{ $tenantRequest->store_name }}</strong> di Flashbot. Kami telah menerima permohonan Anda dan saat ini sedang dalam proses peninjauan oleh tim kami.</p>
        
        <p>Proses peninjauan ini biasanya memakan waktu maksimal 1x24 jam kerja. Kami akan mengirimkan email pemberitahuan selanjutnya setelah permohonan Anda disetujui atau jika ada informasi tambahan yang kami perlukan.</p>
        
        <p>Detail Pendaftaran:</p>
        <ul>
            <li><strong>Nama Toko:</strong> {{ $tenantRequest->store_name }}</li>
            <li><strong>Subdomain Pilihan:</strong> {{ $tenantRequest->subdomain }}.tenanta.id (atau domain utama sistem)</li>
            <li><strong>Jenis Layanan:</strong> {{ $tenantRequest->jenis_layanan }}</li>
        </ul>
        
        <p>Jika Anda memiliki pertanyaan, jangan ragu untuk membalas email ini.</p>
        
        <p>Salam hangat,<br>
        Tim Flashbot</p>
    </div>
</body>
</html>
