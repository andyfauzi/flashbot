<!DOCTYPE html>
<html>
<head>
    <style>
        body {
            font-family: Arial, sans-serif;
            line-height: 1.6;
            color: #333;
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
        }
        .header {
            background-color: #0d6efd;
            color: white;
            padding: 20px;
            text-align: center;
            border-radius: 8px 8px 0 0;
        }
        .content {
            background-color: #f8f9fa;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-top: none;
            border-radius: 0 0 8px 8px;
        }
        .details {
            background-color: white;
            padding: 15px;
            border-radius: 5px;
            margin: 20px 0;
            border: 1px solid #e9ecef;
        }
        .detail-row {
            display: flex;
            justify-content: space-between;
            margin-bottom: 10px;
            border-bottom: 1px solid #f1f3f5;
            padding-bottom: 5px;
        }
        .footer {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
            color: #6c757d;
        }
        .btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #0d6efd;
            color: white;
            text-decoration: none;
            border-radius: 5px;
            margin-top: 20px;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>Pembayaran Berhasil!</h2>
    </div>
    <div class="content">
        <p>Halo,</p>
        <p>Terima kasih! Pembayaran Anda untuk berlangganan layanan di <strong>Tenanta.id</strong> telah kami terima. Akun Anda sekarang sudah aktif.</p>
        
        <div class="details">
            <div class="detail-row">
                <strong>ID Pesanan:</strong>
                <span>{{ $payment->order_id }}</span>
            </div>
            <div class="detail-row">
                <strong>Paket Berlangganan:</strong>
                <span>{{ ucfirst($payment->plan_name) }}</span>
            </div>
            <div class="detail-row">
                <strong>Total Pembayaran:</strong>
                <span>Rp {{ number_format($payment->amount, 0, ',', '.') }}</span>
            </div>
            <div class="detail-row">
                <strong>Waktu Pembayaran:</strong>
                <span>{{ $payment->paid_at->format('d M Y, H:i') }} WIB</span>
            </div>
            <div class="detail-row">
                <strong>Subdomain Toko:</strong>
                <span>{{ $tenant->subdomain }}</span>
            </div>
        </div>

        <p>Silakan klik tombol di bawah ini untuk masuk ke *dashboard* utama toko Anda dan mulai mengelola operasional Anda.</p>
        
        <div style="text-align: center;">
            @php
                $protocol = request()->isSecure() ? 'https://' : 'http://';
                $appDomain = env('APP_DOMAIN', 'tenanta.id');
                $dashboardUrl = $protocol . $tenant->subdomain . '.' . $appDomain;
            @endphp
            <a href="{{ $dashboardUrl }}" class="btn">Buka Dashboard Toko</a>
        </div>
    </div>
    <div class="footer">
        <p>Email ini dikirim secara otomatis oleh sistem. Harap tidak membalas email ini.</p>
        <p>&copy; {{ date('Y') }} Tenanta.id - Platform Kasir & Manajemen Toko</p>
    </div>
</body>
</html>
