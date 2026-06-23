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
                            </div>
                        </div>
                    </div>

                    <!-- Jam Operasional Toko -->
                    <div class="card border border-primary shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-primary text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-clock me-2"></i> Jam Operasional Toko</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <label for="jam_buka" class="form-label fw-bold">Jam Buka</label>
                                    <input type="time" class="form-control @error('jam_buka') is-invalid @enderror" id="jam_buka" name="jam_buka" value="{{ old('jam_buka', $identitas->jam_buka ? \Carbon\Carbon::parse($identitas->jam_buka)->format('H:i') : '') }}">
                                    @error('jam_buka') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-md-6 mt-3 mt-md-0">
                                    <label for="jam_tutup" class="form-label fw-bold">Jam Tutup</label>
                                    <input type="time" class="form-control @error('jam_tutup') is-invalid @enderror" id="jam_tutup" name="jam_tutup" value="{{ old('jam_tutup', $identitas->jam_tutup ? \Carbon\Carbon::parse($identitas->jam_tutup)->format('H:i') : '') }}">
                                    @error('jam_tutup') <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>
                                <div class="col-12 mt-2">
                                    <small class="text-muted">Jika diisi, pesanan yang masuk di luar jam ini akan otomatis diproses/dijadwalkan keesokan paginya saat toko buka kembali. Berlaku untuk pesanan ambil di toko maupun pesan antar (delivery).</small>
                                </div>
                            </div>
                        </div>
                    </div>
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
                    <!-- Pengaturan Landing Page Portal -->
                    <div class="card border border-success shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-success text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-globe me-2"></i> Pengaturan Landing Page Portal</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-4">
                                <label for="deskripsi_toko" class="form-label fw-bold text-dark">Deskripsi Tempat / Tentang Kami</label>
                                <textarea class="form-control @error('deskripsi_toko') is-invalid @enderror" id="deskripsi_toko" name="deskripsi_toko" rows="3" placeholder="Tuliskan cerita singkat atau suasana tempat Anda di sini...">{{ old('deskripsi_toko', $identitas->deskripsi_toko ?? '') }}</textarea>
                                @error('deskripsi_toko') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="hero_image" class="form-label fw-bold text-dark">Gambar Banner Utama (Hero Image)</label>
                                @if(isset($identitas->hero_image_path) && $identitas->hero_image_path)
                                    <div class="mb-2">
                                        <img src="{{ asset('storage/' . $identitas->hero_image_path) }}" alt="Hero Image" class="img-thumbnail" style="max-height: 150px; width: 100%; object-fit: cover;">
                                    </div>
                                @endif
                                <input class="form-control @error('hero_image') is-invalid @enderror" type="file" id="hero_image" name="hero_image" accept="image/jpeg, image/png, image/jpg, image/webp">
                                <div class="form-text text-muted">Gambar ini akan menjadi background paling atas di portal Anda. Resolusi disarankan: 1920x1080 (Landscape). Maks 5MB.</div>
                                @error('hero_image') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="galeri" class="form-label fw-bold text-dark">Galeri Suasana Tempat (Maksimal 6 Foto)</label>
                                @if(isset($identitas->galeri_paths) && is_array($identitas->galeri_paths) && count($identitas->galeri_paths) > 0)
                                    <div class="d-flex flex-wrap gap-2 mb-2">
                                        @foreach($identitas->galeri_paths as $galeri)
                                            <img src="{{ asset('storage/' . $galeri) }}" alt="Galeri" class="img-thumbnail" style="height: 80px; width: 80px; object-fit: cover;">
                                        @endforeach
                                    </div>
                                @endif
                                <input class="form-control @error('galeri.*') is-invalid @enderror" type="file" id="galeri" name="galeri[]" multiple accept="image/jpeg, image/png, image/jpg, image/webp">
                                <div class="form-text text-muted">Unggah beberapa foto suasana tempat untuk menarik pengunjung. Jika Anda mengunggah ulang, foto lama akan tertimpa. Maks 3MB per file.</div>
                                @error('galeri.*') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-4">
                                <label for="kontak_portal" class="form-label fw-bold text-dark">Informasi Kontak Portal</label>
                                <textarea class="form-control @error('kontak_portal') is-invalid @enderror" id="kontak_portal" name="kontak_portal" rows="4" placeholder="Contoh:&#10;Email: halo@toko.com&#10;Alamat: Jl. Sudirman No 123&#10;WhatsApp: 08123456789">{{ old('kontak_portal', $identitas->kontak_portal ?? '') }}</textarea>
                                <div class="form-text text-muted">Informasi ini akan ditampilkan pada bagian 'Kontak' di halaman landing page pelanggan.</div>
                                @error('kontak_portal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-0">
                                <label for="syarat_ketentuan_portal" class="form-label fw-bold text-dark">Syarat & Ketentuan Portal / Reservasi</label>
                                <textarea class="form-control @error('syarat_ketentuan_portal') is-invalid @enderror" id="syarat_ketentuan_portal" name="syarat_ketentuan_portal" rows="4" placeholder="Tuliskan syarat dan ketentuan pemesanan atau reservasi toko Anda di sini...">{{ old('syarat_ketentuan_portal', $identitas->syarat_ketentuan_portal ?? '') }}</textarea>
                                <div class="form-text text-muted">Informasi ini akan ditampilkan pada bagian 'Syarat & Ketentuan' di halaman landing page pelanggan.</div>
                                @error('syarat_ketentuan_portal') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
