@extends('layouts.superadmin')

@section('title', 'Konfigurasi Midtrans Landlord')

@section('styles')
<style>
    .header-panel {
        background: linear-gradient(135deg, #0284c7 0%, #0369a1 100%);
        border-radius: 20px;
        padding: 30px;
        color: #fff;
        margin-bottom: 24px;
        box-shadow: 0 10px 30px rgba(2, 132, 199, 0.2);
    }
    .custom-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 24px;
    }
</style>
@endsection

@section('content')
<div class="header-panel">
    <h3 class="fw-bold mb-1"><i class="fa-solid fa-credit-card me-2"></i> Konfigurasi Midtrans Landlord</h3>
    <p class="text-white-50 mb-0">Pengaturan payment gateway untuk tagihan langganan tenant ke Flashbot</p>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row">
    <div class="col-lg-8 mx-auto mb-4">
        <div class="custom-card">
            <h5 class="fw-bold mb-4">Kredensial API Midtrans</h5>
            <form action="{{ route('superadmin.midtrans.update') }}" method="POST">
                @csrf
                <div class="form-check form-switch mb-4 p-3 bg-light rounded-3 border">
                    <input class="form-check-input ms-0 me-2" type="checkbox" name="midtrans_is_production" id="midtransProductionMode" value="1" {{ ($midtransIsProduction ?? '0') == '1' ? 'checked' : '' }}>
                    <label class="form-check-label fw-bold text-dark" for="midtransProductionMode">Mode Production (Live)</label>
                    <small class="d-block text-muted ms-5 mt-1">Matikan untuk Sandbox Mode (Testing)</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-semibold">Server Key</label>
                    <input type="text" name="midtrans_server_key" class="form-control font-monospace" placeholder="SB-Mid-server-..." value="{{ $midtransServerKey ?? '' }}">
                </div>
                
                <div class="mb-4">
                    <label class="form-label fw-semibold">Client Key</label>
                    <input type="text" name="midtrans_client_key" class="form-control font-monospace" placeholder="SB-Mid-client-..." value="{{ $midtransClientKey ?? '' }}">
                    <small class="text-muted mt-2 d-block"><i class="fa-solid fa-circle-info me-1"></i>Client Key dibutuhkan untuk memunculkan popup pembayaran (Snap JS) di halaman tagihan tenant.</small>
                </div>
                
                <hr>
                <div class="alert alert-info border-0 rounded-3">
                    <h6 class="fw-bold mb-1"><i class="fa-solid fa-bell me-2"></i>Pengaturan Webhook Midtrans</h6>
                    <p class="mb-0 small">Pastikan Anda telah mengatur Notification URL di dashboard Midtrans (Settings > Notification) mengarah ke:</p>
                    <code class="d-block mt-2 bg-white p-2 rounded text-dark font-monospace border">{{ url('/api/webhook/midtrans') }}</code>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-3"><i class="fa-solid fa-save me-2"></i>Simpan Konfigurasi Midtrans</button>
            </form>
        </div>
    </div>
</div>
@endsection
