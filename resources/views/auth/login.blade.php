<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login {{ app()->bound('current_tenant') ? ($identitasToko->nama_toko ?? 'Toko') : 'Tenanta.id Platform' }}</title>
    <!-- Bootstrap 5 & FontAwesome CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@300;400;500;600;700;800&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <style>
        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Nunito', sans-serif;
        }

        .login-wrapper {
            background: #FAF7F4;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 2rem 0;
        }

        .login-card {
            border-radius: 20px;
            overflow: hidden;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        
        .login-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 1rem 3rem rgba(0,0,0,.175)!important;
        }

        .form-control {
            border-radius: 14px;
            background-color: #ffffff;
            border: 1px solid #F4E4D4;
            min-height: 48px;
        }
        
        .form-control:focus {
            background-color: #fff;
            border-color: #D97757;
            box-shadow: 0 0 0 4px rgba(217, 119, 87, 0.15);
        }

        .btn-login {
            background-color: #D97757;
            border: none;
            border-radius: 14px;
            min-height: 48px;
            transition: all 0.3s ease;
        }

        .btn-login:hover {
            background-color: #c76747;
            transform: scale(1.02);
            box-shadow: 0 8px 20px rgba(217, 119, 87, 0.2);
        }
    </style>
</head>
<body>

<div class="login-wrapper">
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-5 col-lg-4">
                
                {{-- Alert --}}
                @if(session('sukses'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-check-circle me-2"></i> {{ session('sukses') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif
                @if(session('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                {{-- Card Login --}}
                <div class="card border-0 shadow-lg login-card">
                    <div class="card-body p-5">
                        <div class="text-center mb-4">
                            @if(app()->bound('current_tenant'))
                                @if(isset($identitasToko) && $identitasToko->logo_path)
                                    <img src="{{ asset('storage/' . $identitasToko->logo_path) }}" alt="Logo" class="img-fluid mb-3" style="max-height: 100px; object-fit: contain;">
                                @else
                                    <div class="mb-3 d-flex justify-content-center">
                                        <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                                            <i class="fa-solid fa-store text-primary fs-1"></i>
                                        </div>
                                    </div>
                                @endif
                                <h3 class="fw-bold mb-1" style="color: #3A3A3A; font-family: 'Poppins', sans-serif;">{{ strtoupper($identitasToko->nama_toko ?? 'TENANTA.ID') }}</h3>
                                <p class="text-muted small">Sistem Manajemen Chatbot Terpadu</p>
                            @else
                                <div class="mb-3 d-flex justify-content-center">
                                    <div class="bg-dark rounded-circle d-flex align-items-center justify-content-center shadow" style="width: 80px; height: 80px;">
                                        <i class="fa-solid fa-server text-white fs-1"></i>
                                    </div>
                                </div>
                                <h3 class="fw-bold mb-1" style="color: #3A3A3A; font-family: 'Poppins', sans-serif;">TENANTA.ID SUPER ADMIN</h3>
                                <p class="text-muted small">Landlord Control Panel</p>
                            @endif
                        </div>

                        <form action="{{ route('login') }}" method="POST">
                            @csrf

                            {{-- Email --}}
                            <div class="mb-4 form-floating">
                                <input 
                                    type="email" 
                                    class="form-control @error('email') is-invalid @enderror"
                                    id="email" 
                                    name="email"
                                    value="{{ old('email') }}"
                                    placeholder="admin@example.com"
                                    required
                                >
                                <label for="email"><i class="fas fa-envelope text-muted me-2"></i>Email Address</label>
                                @error('email')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Password --}}
                            <div class="mb-4 form-floating">
                                <input 
                                    type="password" 
                                    class="form-control @error('password') is-invalid @enderror"
                                    id="password" 
                                    name="password"
                                    placeholder="Password"
                                    required
                                >
                                <label for="password"><i class="fas fa-lock text-muted me-2"></i>Password</label>
                                @error('password')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            {{-- Submit Button --}}
                            <button type="submit" class="btn btn-login w-100 py-3 fw-bold text-white shadow mb-3">
                                <i class="fa-solid fa-right-to-bracket me-2"></i> Masuk ke Dashboard
                            </button>
                            @if(!app()->bound('current_tenant'))
                                <div class="text-center mt-3">
                                    <a href="{{ route('auth.google') }}" class="btn btn-outline-dark w-100 py-2 fw-semibold" style="border-radius: 14px;">
                                        <i class="fa-brands fa-google me-2 text-danger"></i> Daftar Tenant / Akun Baru
                                    </a>
                                </div>
                            @endif
                        </form>

                    </div>

                    {{-- Footer --}}
                    <div class="card-footer bg-white border-0 text-center py-4">
                        <small class="text-muted fw-semibold">
                            &copy; {{ date('Y') }} Abu Zayyan Tech
                        </small>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
