@extends('layouts.app')

@section('title', 'Pengaturan Landing Page')

@section('content')
<div class="d-flex justify-content-between align-items-center mb-4">
    <div>
        <h1 class="h3 mb-0 text-gray-800">Pengaturan Landing Page</h1>
        <p class="text-muted mb-0">Kelola tampilan, tema, dan konten portal publik Anda.</p>
    </div>
</div>

<div class="row">
    <div class="col-lg-8">
        <div class="card border-0 shadow-sm rounded-4">
            <div class="card-body p-4">
                <form action="{{ route('dashboard.pengaturan.landing_page.update') }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    
                    <!-- Pilihan Tema Warna Portal -->
                    <div class="card border border-primary shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-primary text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-palette me-2"></i> Pengaturan Tema Warna Portal</h5>
                        </div>
                        <div class="card-body p-4">
                            <div class="mb-0">
                                <label for="tema_portal" class="form-label fw-bold text-dark">Tema Warna Portal Customer</label>
                                <select class="form-select @error('tema_portal') is-invalid @enderror" id="tema_portal" name="tema_portal" required>
                                    <option value="cool" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'cool' ? 'selected' : '' }}>Cool (Harmoni Violet & Slate)</option>
                                    <option value="warm" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'warm' ? 'selected' : '' }}>Warm (Energetik Oranye & Kuning)</option>
                                    <option value="kalem" {{ old('tema_portal', $identitas->tema_portal ?? 'cool') == 'kalem' ? 'selected' : '' }}>Kalem (Pastel Hijau & Tosca)</option>
                                </select>
                                <div class="form-text text-muted">Mempengaruhi warna tombol dan gradien header pada Portal Pemesanan Mandiri.</div>
                                @error('tema_portal') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Pengaturan Landing Page Portal -->
                    <div class="card border border-success shadow-sm rounded-4 mb-4 bg-light">
                        <div class="card-header bg-success text-white border-bottom-0 py-3 rounded-top-4">
                            <h5 class="fw-bold mb-0"><i class="fa-solid fa-globe me-2"></i> Pengaturan Konten Portal</h5>
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
                            <i class="fa-solid fa-save me-2"></i> Simpan Pengaturan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
