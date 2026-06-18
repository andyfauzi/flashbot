<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembaruan Kata Sandi Wajib | Tenanta.id</title>
    <!-- Bootstrap 5 & Google Fonts -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <style>
        body {
            font-family: 'Inter', sans-serif;
            background-color: #f3f4f6;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .auth-card {
            background: #fff;
            border-radius: 16px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.05);
            padding: 40px;
            width: 100%;
            max-width: 500px;
        }
        .icon-box {
            width: 64px;
            height: 64px;
            background: #fff3cd;
            color: #856404;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 24px;
            font-size: 28px;
        }
    </style>
</head>
<body>

<div class="auth-card">
    <div class="icon-box">
        <i class="fa-solid fa-shield-halved"></i>
    </div>
    
    <h3 class="fw-bold text-center mb-3">Pembaruan Kata Sandi Diperlukan</h3>
    <p class="text-center text-muted mb-4">Demi keamanan akun toko Anda, Anda diwajibkan untuk mengganti kata sandi sementara (default) dengan kata sandi baru yang lebih aman.</p>

    @if ($errors->any())
        <div class="alert alert-danger border-0 rounded-3" style="font-size: 0.9rem;">
            <ul class="mb-0 px-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('password.force-change.update') }}" method="POST">
        @csrf
        <div class="mb-3">
            <label class="form-label fw-semibold">Kata Sandi Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-lock text-muted"></i></span>
                <input type="password" name="password" class="form-control border-start-0 ps-0" placeholder="Minimal 8 karakter" required>
            </div>
            <div class="form-text text-muted small mt-2">
                <i class="fa-solid fa-circle-info me-1"></i> Kata sandi harus <strong>minimal 8 karakter</strong>, mengandung <strong>minimal satu huruf besar</strong> dan <strong>satu angka</strong>.
            </div>
        </div>

        <div class="mb-4">
            <label class="form-label fw-semibold">Konfirmasi Kata Sandi Baru</label>
            <div class="input-group">
                <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-check-double text-muted"></i></span>
                <input type="password" name="password_confirmation" class="form-control border-start-0 ps-0" placeholder="Ketik ulang kata sandi baru" required>
            </div>
        </div>

        <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold rounded-3 mb-3">
            <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Kata Sandi
        </button>

        <div class="text-center">
            <a href="{{ route('logout') }}" class="text-danger text-decoration-none small">
                <i class="fa-solid fa-arrow-right-from-bracket me-1"></i> Keluar
            </a>
        </div>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
