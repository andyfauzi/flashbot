@extends('layouts.guest')

@section('title', 'Petunjuk Penggunaan')

@section('content')
<div class="container py-5 mt-5">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="card border-0 shadow-sm rounded-4">
                <div class="card-body p-5">
                    <h2 class="fw-bold mb-4 text-center">Petunjuk Penggunaan</h2>
                    
                    @if(!empty($settings['user_guide_image']))
                        <div class="text-center mb-5">
                            <img src="{{ asset('storage/' . $settings['user_guide_image']) }}" alt="Ilustrasi Petunjuk Penggunaan" class="img-fluid rounded" style="max-height: 300px; object-fit: cover;">
                        </div>
                    @endif
                    
                    <div class="user-guide-content fs-5" style="line-height: 1.8;">
                        @php
                            $defaultGuide = "1. Daftar dan Lengkapi Profil Toko Anda\n2. Tambahkan Produk atau Layanan di Dashboard\n3. Nikmati kemudahan transaksi dengan POS & Bot WhatsApp Tenanta.id!";
                            $guideText = $settings['user_guide_text'] ?? $defaultGuide;
                        @endphp
                        
                        {!! nl2br(e($guideText)) !!}
                    </div>
                    
                    <div class="mt-5 text-center">
                        <a href="{{ url('/') }}" class="btn btn-outline-primary rounded-pill px-4 py-2">
                            <i class="fa-solid fa-arrow-left me-2"></i> Kembali ke Beranda
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
