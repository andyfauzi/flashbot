@extends('layouts.app')

@section('title', 'Login Mitra Sales')

@section('content')
<div class="container d-flex align-items-center justify-content-center min-vh-100">
    <div class="card border-0 shadow-lg rounded-4" style="max-width: 400px; width: 100%;">
        <div class="card-body p-5">
            <div class="text-center mb-4">
                <i class="fa-solid fa-handshake fa-3x text-primary mb-3"></i>
                <h4 class="fw-bold" style="font-family: var(--font-heading);">Mitra Sales</h4>
                <p class="text-muted small">Silakan masuk untuk memantau performa dan komisi penjualan Anda.</p>
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
@endsection
