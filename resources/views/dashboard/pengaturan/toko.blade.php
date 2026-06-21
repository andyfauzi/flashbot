@extends('layouts.app')

@section('title', 'Pengaturan Toko')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Toko</h1>
        <p class="text-muted mb-0">Kelola identitas dan info struk toko Anda.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('dashboard.pengaturan.toko.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <div class="mb-4">
                        <label for="nama_toko" class="form-label fw-bold">Nama Toko <span class="text-danger">*</span></label>
                        <input type="text" class="form-control @error('nama_toko') is-invalid @enderror" id="nama_toko" name="nama_toko" value="{{ old('nama_toko', $identitas->nama_toko ?? '') }}" required>
                        @error('nama_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="alamat_toko" class="form-label fw-bold">Alamat Toko</label>
                        <textarea class="form-control @error('alamat_toko') is-invalid @enderror" id="alamat_toko" name="alamat_toko" rows="3">{{ old('alamat_toko', $identitas->alamat_toko ?? '') }}</textarea>
                        @error('alamat_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="nomor_telepon" class="form-label fw-bold">Nomor Telepon</label>
                        <input type="text" class="form-control @error('nomor_telepon') is-invalid @enderror" id="nomor_telepon" name="nomor_telepon" value="{{ old('nomor_telepon', $identitas->nomor_telepon ?? '') }}">
                        @error('nomor_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="pesan_footer" class="form-label fw-bold">Pesan Bawah (Footer) Struk</label>
                        <textarea class="form-control @error('pesan_footer') is-invalid @enderror" id="pesan_footer" name="pesan_footer" rows="3" placeholder="Terima kasih atas kunjungan Anda!">{{ old('pesan_footer', $identitas->pesan_footer ?? '') }}</textarea>
                        <div class="form-text">Pesan yang akan dicetak di bagian paling bawah struk kasir.</div>
                        @error('pesan_footer') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label for="logo" class="form-label fw-bold">Logo Toko (Opsional)</label>
                        @if(isset($identitas->logo_path) && $identitas->logo_path)
                            <div class="mb-2">
                                <img src="{{ asset('storage/' . $identitas->logo_path) }}" alt="Logo Toko" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        @endif
                        <input class="form-control @error('logo') is-invalid @enderror" type="file" id="logo" name="logo" accept="image/jpeg, image/png, image/jpg">
                        <div class="form-text">Gambar akan otomatis dicetak hitam-putih di struk printer thermal. Maks 2MB.</div>
                        @error('logo') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <!-- Metode Pembayaran Chatbot (Sangat Privat) -->
                    <div class="card border border-warning shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-warning text-dark border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-lock me-2"></i> Metode Pembayaran Chatbot (Sangat Privat)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="nomor_rekening" class="form-label fw-bold text-dark">Informasi Nomor Rekening (Untuk Transfer)</label>
                                <textarea class="form-control @error('nomor_rekening') is-invalid @enderror" id="nomor_rekening" name="nomor_rekening" rows="3" placeholder="Contoh:&#10;BCA: 123456789 a/n Ninsky Bakery&#10;Mandiri: 987654321 a/n Ninsky Bakery">{{ old('nomor_rekening', $identitas->nomor_rekening ?? '') }}</textarea>
                                <div class="form-text text-muted">Detail ini akan dibaca secara privat oleh AI Chatbot dan dikirimkan saat pelanggan menanyakan nomor rekening/transfer.</div>
                                @error('nomor_rekening') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="qris" class="form-label fw-bold text-dark">File QRIS Toko (Gambar)</label>
                                @if(isset($identitas->qris_path) && $identitas->qris_path)
                                    <div class="mb-3">
                                        <img src="{{ asset('storage/' . $identitas->qris_path) }}" alt="QRIS Toko" class="img-thumbnail" style="max-height: 200px;">
                                    </div>
                                @endif
                                <input class="form-control @error('qris') is-invalid @enderror" type="file" id="qris" name="qris" accept="image/jpeg, image/png, image/jpg">
                                <div class="form-text text-muted">Maks 2MB. Gambar QRIS ini akan dikirimkan otomatis oleh bot jika pelanggan memilih metode QRIS atau menanyakan kode pembayaran.</div>
                                @error('qris') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>




                    <!-- Pengaturan Model Bisnis -->
                    <div class="card border border-info shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-info text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-store-slash me-2"></i> Pengaturan Model Bisnis (Dine-in / Take Away)</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-3">
                                <label for="jenis_layanan" class="form-label fw-bold text-dark">Jenis Layanan F&B</label>
                                <select class="form-select @error('jenis_layanan') is-invalid @enderror" id="jenis_layanan" name="jenis_layanan" required>
                                    <option value="dine_in" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'dine_in' ? 'selected' : '' }}>Hanya Dine-in (Makan di Tempat)</option>
                                    <option value="take_away" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'take_away' ? 'selected' : '' }}>Hanya Take Away / Delivery</option>
                                    <option value="keduanya" {{ old('jenis_layanan', $identitas->jenis_layanan ?? 'keduanya') == 'keduanya' ? 'selected' : '' }}>Melayani Keduanya</option>
                                </select>
                                <div class="form-text text-muted">Mengubah jenis layanan akan menyesuaikan fitur yang muncul di Dashboard (seperti Manajemen Meja dan Reservasi).</div>
                                @error('jenis_layanan') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                        </div>
                    </div>



                    <!-- Pilihan Tema Warna -->
                    <div class="card border border-primary shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-primary text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-palette me-2"></i> Pengaturan Tema Warna</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6 mb-3 mb-md-0">
                                    <label for="tema_portal" class="form-label fw-bold text-dark">Tema Warna Portal Customer</label>
                                    <select class="form-select @error('tema_portal') is-invalid @enderror" id="tema_portal" name="tema_portal" required>
                                        <option value="cool" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'cool' ? 'selected' : '' }}>Cool (Harmoni Violet & Slate)</option>
                                        <option value="warm" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'warm' ? 'selected' : '' }}>Warm (Energetik Oranye & Kuning)</option>
                                        <option value="kalem" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'kalem' ? 'selected' : '' }}>Kalem (Pastel Hijau & Tosca)</option>
                                    </select>
                                    <div class="form-text text-muted">Mempengaruhi warna tombol dan gradien header pada Portal Pemesanan Mandiri.</div>
                                    @error('tema_portal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="col-md-6">
                                    <label for="tema_desktop" class="form-label fw-bold text-dark">Tema Warna Dashboard Desktop</label>
                                    <select class="form-select @error('tema_desktop') is-invalid @enderror" id="tema_desktop" name="tema_desktop" required>
                                        <option value="cool" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'cool' ? 'selected' : '' }}>Cool (Harmoni Violet & Slate)</option>
                                        <option value="warm" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'warm' ? 'selected' : '' }}>Warm (Energetik Oranye & Kuning)</option>
                                        <option value="kalem" {{ old('tema_desktop', $identitas->tema_desktop ?? 'cool') == 'kalem' ? 'selected' : '' }}>Kalem (Pastel Hijau & Tosca)</option>
                                    </select>
                                    <div class="form-text text-muted">Mempengaruhi aksen warna utama di seluruh dashboard admin dan kasir offline POS.</div>
                                    @error('tema_desktop') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="text-end">
                        <button type="submit" class="btn btn-primary px-4 rounded-pill">
                            <i class="fa-solid fa-save me-2"></i> Simpan Perubahan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')

@endsection
