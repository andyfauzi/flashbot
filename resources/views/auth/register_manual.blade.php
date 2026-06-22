<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Toko Baru - Flashbot</title>
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f4f7f6; margin: 0; padding: 20px; }
        .container { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1); max-width: 600px; margin: 40px auto; }
        h2 { color: #333; margin-top: 0; margin-bottom: 20px; text-align: center; }
        .form-group { margin-bottom: 15px; }
        label { display: block; margin-bottom: 5px; color: #555; font-weight: bold; }
        input[type="text"], input[type="email"], select, textarea { width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 4px; box-sizing: border-box; }
        .btn { display: block; width: 100%; padding: 10px; background-color: #28a745; color: white; border: none; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; }
        .btn:hover { background-color: #218838; }
        .error { color: #dc3545; font-size: 14px; margin-top: 5px; }
        .alert-error { background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin-bottom: 20px; }
        .input-group { display: flex; align-items: center; }
        .input-group-text { padding: 10px; background-color: #e9ecef; border: 1px solid #ccc; border-left: none; border-radius: 0 4px 4px 0; color: #495057; }
        .input-group input { border-radius: 4px 0 0 4px; border-right: none; }
    </style>
</head>
<body>
    <div class="container">
        <h2>Pendaftaran Toko Baru</h2>
        
        @if($errors->any())
            <div class="alert-error">
                <ul style="margin: 0; padding-left: 20px;">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('auth.register.submit') }}" method="POST">
            @csrf
            <input type="hidden" name="plan" value="{{ $plan }}">
            <input type="hidden" name="trial" value="{{ $trial }}">

            <div class="form-group">
                <label>Nama Pemilik</label>
                <input type="text" name="owner_name" value="{{ old('owner_name') }}" required placeholder="Nama lengkap Anda">
            </div>

            <div class="form-group">
                <label>Email Pemilik</label>
                <input type="email" name="email" value="{{ old('email') }}" required placeholder="Email aktif Anda">
            </div>

            <div class="form-group">
                <label>Nama Toko</label>
                <input type="text" name="store_name" value="{{ old('store_name') }}" required placeholder="Nama bisnis / toko Anda">
            </div>

            <div class="form-group">
                <label>Subdomain (Alamat Dashboard)</label>
                <div class="input-group">
                    <input type="text" name="subdomain" value="{{ old('subdomain') }}" required placeholder="namatoko" pattern="[a-z0-9]+">
                    <span class="input-group-text">.tenanta.id</span>
                </div>
                <small style="color: #6c757d;">Hanya huruf kecil dan angka, tanpa spasi.</small>
            </div>

            <div class="form-group">
                <label>Nomor WhatsApp</label>
                <input type="text" name="whatsapp_number" value="{{ old('whatsapp_number') }}" required placeholder="Contoh: 08123456789">
            </div>

            <div class="form-group">
                <label>Alamat Toko</label>
                <textarea name="store_address" rows="3" required placeholder="Alamat lengkap toko Anda">{{ old('store_address') }}</textarea>
            </div>

            <div class="form-group">
                <label>Jenis Layanan</label>
                <select name="jenis_layanan" required>
                    <option value="">-- Pilih Jenis Layanan --</option>
                    <option value="dine_in" {{ old('jenis_layanan') == 'dine_in' ? 'selected' : '' }}>Dine In (Makan di tempat)</option>
                    <option value="take_away" {{ old('jenis_layanan') == 'take_away' ? 'selected' : '' }}>Take Away (Bawa pulang)</option>
                    <option value="keduanya" {{ old('jenis_layanan') == 'keduanya' ? 'selected' : '' }}>Keduanya</option>
                </select>
            </div>

            <div class="form-group">
                <label>Skala Bisnis (Estimasi Pelanggan per Hari)</label>
                <select name="skala_bisnis" required>
                    <option value="">-- Pilih Skala Bisnis --</option>
                    <option value="1-50" {{ old('skala_bisnis') == '1-50' ? 'selected' : '' }}>1 - 50 Pelanggan</option>
                    <option value="51-100" {{ old('skala_bisnis') == '51-100' ? 'selected' : '' }}>51 - 100 Pelanggan</option>
                    <option value="101-500" {{ old('skala_bisnis') == '101-500' ? 'selected' : '' }}>101 - 500 Pelanggan</option>
                    <option value=">500" {{ old('skala_bisnis') == '>500' ? 'selected' : '' }}>Lebih dari 500 Pelanggan</option>
                </select>
            </div>

            <div class="form-group" style="margin-top: 20px;">
                <label style="font-weight: normal; display: flex; align-items: center; cursor: pointer;">
                    <input type="checkbox" name="terms_accepted" value="1" required style="margin-right: 10px;">
                    Saya menyetujui <a href="{{ route('legal.terms') }}" target="_blank" style="color: #007bff; text-decoration: none;">Syarat & Ketentuan yang berlaku</a>.
                </label>
            </div>

            @if(env('RECAPTCHA_SITE_KEY'))
            <div class="form-group" style="margin-top: 20px;">
                <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                @error('g-recaptcha-response')
                    <div style="color: #dc3545; font-size: 14px; margin-top: 5px;">{{ $message }}</div>
                @enderror
            </div>
            @endif

            <button type="submit" class="btn">Daftar Sekarang</button>
            
            <div style="text-align: center; margin: 20px 0; color: #6c757d; position: relative;">
                <hr style="border: 0; border-top: 1px solid #e0e0e0; position: absolute; width: 100%; top: 50%; transform: translateY(-50%); margin: 0; z-index: 1;">
                <span style="background: white; padding: 0 10px; position: relative; z-index: 2; font-size: 0.9em; color: #999;">ATAU</span>
            </div>

            <a href="{{ route('auth.google', ['plan' => $plan ?? 'business', 'trial' => $trial ?? '1']) }}" style="display: block; width: 100%; padding: 12px; background-color: #fff; color: #444; border: 1px solid #ccc; border-radius: 4px; font-size: 16px; cursor: pointer; font-weight: bold; text-align: center; text-decoration: none; box-sizing: border-box; box-shadow: 0 1px 2px rgba(0,0,0,0.05); transition: background-color 0.2s;">
                <img src="https://www.gstatic.com/firebasejs/ui/2.0.0/images/auth/google.svg" alt="Google" style="width: 20px; vertical-align: middle; margin-right: 8px;">
                Mulai Trial dengan Google
            </a>

            <div style="text-align: center; margin-top: 25px;">
                <a href="/" style="color: #6c757d; text-decoration: none; font-size: 0.95em;">&larr; Kembali ke Beranda</a>
            </div>
        </form>
    </div>
</body>
</html>
