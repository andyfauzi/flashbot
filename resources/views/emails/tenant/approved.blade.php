<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Pendaftaran Toko Disetujui</title>
</head>
<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px;">
        <h2 style="color: #4CAF50;">Selamat! Pendaftaran Toko Anda Telah Disetujui</h2>
        
        <p>Halo,</p>
        
        <p>Kabar baik! Pendaftaran toko <strong>{{ $storeName }}</strong> di Flashbot telah kami setujui dan database toko Anda sudah siap digunakan.</p>
        
        <p>Berikut adalah informasi akses masuk (login) untuk dashboard toko Anda:</p>
        
        <div style="background-color: #f9f9f9; padding: 15px; border-left: 4px solid #4CAF50; margin: 20px 0;">
            <p style="margin: 5px 0;"><strong>URL Dashboard:</strong> <a href="{{ $subdomainUrl }}">{{ $subdomainUrl }}</a></p>
            <p style="margin: 5px 0;"><strong>Username / Email:</strong> {{ $email }}</p>
            <p style="margin: 5px 0;"><strong>Password Sementara:</strong> {{ $password }}</p>
        </div>
        
        <p><em>Catatan: Segera ganti password sementara Anda setelah berhasil login pertama kali demi keamanan akun Anda.</em></p>
        
        <p>Jika Anda menemui kendala saat login, jangan ragu untuk menghubungi tim dukungan kami.</p>
        
        <p>Semoga sukses dengan bisnis Anda!<br>
        Salam hangat,<br>
        Tim Flashbot</p>
    </div>
</body>
</html>
