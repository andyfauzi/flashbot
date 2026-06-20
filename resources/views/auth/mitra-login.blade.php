<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Tenanta.id</title>
    <!-- Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
    <style>
        body { font-family: 'Plus Jakarta Sans', sans-serif; background-color: #f8f9fa; }
    </style>
</head>
<body>
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg rounded-4" style="max-width: 400px; width: 100%;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-store fa-3x text-primary mb-3"></i>
                <h4 class="fw-bold" style="font-family: var(--font-heading);">Tenanta.id</h4>
                <p class="text-muted small">Silakan masuk ke akun Anda.</p>
            </div>

            @if(session('sukses'))
                <div class="alert alert-success border-0 shadow-sm rounded-3 small">
                    <i class="fa-solid fa-check-circle me-1"></i> {{ session('sukses') }}
                </div>
            @endif

            <form action="{{ route('sales.login.post') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-bold small">Email Akses</label>
                    <input type="email" name="email" class="form-control form-control-lg bg-light border-0 @error('email') is-invalid @enderror" value="{{ old('email') }}" required autofocus placeholder="Masukkan email Anda">
                    @error('email')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-bold small">Password</label>
                    <input type="password" name="password" class="form-control form-control-lg bg-light border-0" required placeholder="Masukkan password">
                </div>

                <button type="submit" class="btn btn-primary btn-lg w-100 rounded-pill fw-bold shadow-sm">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk Dashboard
                </button>
            </form>
            
            <div class="text-center mt-4">
                <a href="{{ url('/') }}" class="text-decoration-none text-muted small"><i class="fa-solid fa-arrow-left me-1"></i> Kembali ke Beranda</a>
            </div>
        </div>
    </div>
</div>
</body>
</html>
