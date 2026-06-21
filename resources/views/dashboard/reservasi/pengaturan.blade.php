@extends('layouts.app')

@section('title', 'Pengaturan Reservasi & Operasional')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Reservasi & Operasional</h1>
        <p class="text-muted mb-0">Atur jam buka toko dan kebijakan Uang Muka (DP) Reservasi Meja.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('dashboard.reservasi.pengaturan.simpan') }}" method="POST">
                    @csrf
                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-money-bill-wave text-success me-2"></i> Pengaturan DP Reservasi</h5>
                    
                    <div class="card bg-light border-0 mb-3">
                        <div class="card-body">
                            <div class="form-check form-switch form-switch-lg mb-2" style="transform: scale(1.1); margin-left: 0.2rem;">
                                <input class="form-check-input" type="checkbox" role="switch" id="wajib_dp_reservasi" name="wajib_dp_reservasi" value="1" {{ old('wajib_dp_reservasi', $identitas->wajib_dp_reservasi ?? false) ? 'checked' : '' }} onchange="document.getElementById('dp_settings').style.display = this.checked ? 'block' : 'none'">
                                <label class="form-check-label fw-bold ms-1" for="wajib_dp_reservasi">Wajibkan Uang Muka (DP) untuk Reservasi</label>
                            </div>
                            <small class="text-muted d-block mt-1">Jika aktif, pelanggan wajib membayar DP untuk bisa mereservasi meja via Chatbot.</small>
                        </div>
                    </div>

                    <div id="dp_settings" style="display: {{ old('wajib_dp_reservasi', $identitas->wajib_dp_reservasi ?? false) ? 'block' : 'none' }};">
                        <div class="mb-4">
                            <label for="nominal_dp_reservasi" class="form-label fw-semibold">Nominal DP Reservasi (Rp)</label>
                            <div class="input-group">
                                <span class="input-group-text bg-light border-end-0">Rp</span>
                                <input type="number" class="form-control border-start-0 ps-0 @error('nominal_dp_reservasi') is-invalid @enderror" id="nominal_dp_reservasi" name="nominal_dp_reservasi" value="{{ old('nominal_dp_reservasi', $identitas->nominal_dp_reservasi ? intval($identitas->nominal_dp_reservasi) : 50000) }}" min="0" step="1000">
                            </div>
                            <div class="form-text">Biaya tetap/flat yang harus dibayar sebagai jaminan reservasi meja.</div>
                            @error('nominal_dp_reservasi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <hr class="mb-4">

                    <h5 class="fw-bold mb-3"><i class="fa-solid fa-hourglass-start text-warning me-2"></i> Aturan Waktu Reservasi</h5>
                    <div class="mb-4">
                        <label for="minimal_jam_reservasi" class="form-label fw-semibold">Batas Minimum Reservasi (H- Jam)</label>
                        <div class="input-group">
                            <input type="number" class="form-control @error('minimal_jam_reservasi') is-invalid @enderror" id="minimal_jam_reservasi" name="minimal_jam_reservasi" value="{{ old('minimal_jam_reservasi', $identitas->minimal_jam_reservasi ?? 2) }}" min="0" max="24" step="1">
                            <span class="input-group-text bg-light">Jam Sebelumnya</span>
                        </div>
                        <div class="form-text">Contoh: Jika diisi '2', maka pelanggan hanya bisa memesan meja untuk jam yang minimal berjarak 2 jam dari waktu saat ini. Isi '0' jika membolehkan reservasi mendadak.</div>
                        @error('minimal_jam_reservasi') <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                    </div>

                    <div class="text-end mt-4">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill fw-medium">
                            <i class="fa-solid fa-save me-1"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
