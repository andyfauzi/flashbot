@extends('layouts.app')

@section('title', 'Kirim Broadcast')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="h3 mb-0 text-gray-800 fw-bold">Broadcast ke Tenant</h2>
            <p class="text-muted mb-0">Kirim pesan serentak ke semua pemilik toko melalui WhatsApp dan Email.</p>
        </div>
        <a href="{{ route('superadmin.index') }}" class="btn btn-light border rounded-pill px-4">
            <i class="fa-solid fa-arrow-left me-2"></i> Kembali
        </a>
    </div>

    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show rounded-4" role="alert">
            <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card border-0 shadow-sm rounded-4 mb-4">
        <div class="card-body p-4">
            <form action="{{ route('superadmin.broadcast.send') }}" method="POST">
                @csrf
                <div class="row">
                    <div class="col-md-8">
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark">Pesan Broadcast</label>
                            <textarea class="form-control" name="message" rows="8" placeholder="Tuliskan pesan broadcast Anda di sini..." required></textarea>
                            <div class="form-text mt-2 text-muted">
                                <i class="fa-solid fa-info-circle me-1"></i> Pesan ini akan dikirimkan serentak ke semua tenant yang berstatus Aktif.
                            </div>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="card bg-light border-0 rounded-4 p-3 mb-4">
                            <label class="form-label fw-bold text-dark mb-3">Saluran Pengiriman</label>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="channelWA" name="channel[]" value="whatsapp" checked>
                                <label class="form-check-label ms-2" for="channelWA">
                                    <i class="fa-brands fa-whatsapp text-success me-1"></i> WhatsApp (Baileys Landlord)
                                </label>
                            </div>
                            
                            <div class="form-check form-switch mb-3">
                                <input class="form-check-input" type="checkbox" role="switch" id="channelEmail" name="channel[]" value="email" checked>
                                <label class="form-check-label ms-2" for="channelEmail">
                                    <i class="fa-solid fa-envelope text-primary me-1"></i> Email Gateway
                                </label>
                            </div>
                            
                            <div class="alert alert-info border-0 rounded-3 mb-0 small">
                                Sistem akan otomatis mendeteksi nomor HP dan email pemilik tenant dari database.
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary rounded-pill py-2 fw-bold" onclick="return confirm('Anda yakin ingin mengirim broadcast ini ke SEMUA tenant?')">
                                <i class="fa-solid fa-paper-plane me-2"></i> Kirim Broadcast Sekarang
                            </button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
