<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Informasi Pendaftaran Toko</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #f44336;">Informasi Pendaftaran Toko</h2>
        
        <p>Halo {{ $tenantRequest->owner_name }},</p>
        
        <p>Terima kasih atas ketertarikan Anda untuk mendaftarkan toko <strong>{{ $tenantRequest->store_name }}</strong> di Flashbot.</p>
        
        <p>Setelah meninjau permohonan Anda, dengan berat hati kami sampaikan bahwa saat ini kami <strong>belum dapat menyetujui</strong> pendaftaran toko Anda. Hal ini bisa terjadi karena berbagai alasan, seperti ketidaksesuaian data atau hal lainnya terkait kebijakan layanan kami.</p>
        
        <p>Jika Anda merasa ini adalah sebuah kesalahan atau ingin mengetahui lebih lanjut, silakan balas email ini untuk menghubungi tim dukungan kami.</p>
        
        <p>Terima kasih atas pengertian Anda.<br>
        Salam,<br>
        Tim Flashbot</p>
    </div>
</body>
</html>
