@extends('layouts.app')

@section('title', isset($produk) ? 'Edit Produk' : 'Tambah Produk')

@section('content')
<div class="container-fluid">
    <div class="mb-4">
        <a href="{{ route('chatbot.produk.index') }}" class="btn btn-outline-secondary rounded-pill px-3 mb-2">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
        <h2 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading);">
            {{ isset($produk) ? 'Edit Produk: ' . $produk->nama : 'Tambah Produk Baru' }}
        </h2>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-4">
            <form action="{{ isset($produk) ? route('chatbot.produk.update', $produk->id) : route('chatbot.produk.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                @if(isset($produk))
                    @method('PUT')
                @endif

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kode Produk</label>
                        <input type="text" name="kode" class="form-control form-control-premium" value="{{ old('kode', $produk->kode ?? $autoKode ?? '') }}" required placeholder="Contoh: PRD-0001">
                        <small class="text-muted mt-1 d-block">Kode unik untuk mengidentifikasi produk</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Nama Produk</label>
                        <input type="text" name="nama" class="form-control form-control-premium" value="{{ old('nama', $produk->nama ?? '') }}" required placeholder="Contoh: Brownies Panggang">
                    </div>
                </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Kategori Produk</label>
                        <select name="kategori_id" class="form-select form-control-premium" required>
                            <option value="">-- Pilih Kategori --</option>
                            @foreach($kategoris as $kategori)
                                <option value="{{ $kategori->id }}" {{ old('kategori_id', $produk->kategori_id ?? '') == $kategori->id ? 'selected' : '' }}>
                                    {{ $kategori->nama }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                  <div class="row">
                      <div class="col-md-4 mb-3">
                          <label class="form-label fw-bold">Harga Pokok (Rp)</label>
                          <input type="number" name="harga" class="form-control form-control-premium" value="{{ old('harga', isset($produk) ? (int)$produk->harga : '') }}" required placeholder="Contoh: 50000" min="0">
                      </div>
                      <div class="col-md-3 mb-3">
                          <label class="form-label fw-bold">Tipe Produksi</label>
                          <div class="form-check form-switch mt-2">
                              <input class="form-check-input" type="checkbox" name="is_made_to_order" value="1" style="transform: scale(1.3); margin-left: -2em;" 
                                  {{ old('is_made_to_order', $produk->is_made_to_order ?? false) ? 'checked' : '' }}>
                              <label class="form-check-label ms-2" title="Jika aktif, bahan baku akan dipotong saat dipesan.">Made-to-Order</label>
                          </div>
                      </div>
                      <div class="col-md-3 mb-3">
                          <label class="form-label fw-bold">Menu Favorit</label>
                          <div class="form-check form-switch mt-2">
                              <input class="form-check-input" type="checkbox" name="is_favorite" value="1" style="transform: scale(1.3); margin-left: -2em;" 
                                  {{ old('is_favorite', $produk->is_favorite ?? false) ? 'checked' : '' }}>
                              <label class="form-check-label ms-2" title="Produk favorit akan ditampilkan di bagian atas Landing Page.">Jadikan Favorit</label>
                          </div>
                      </div>
                      <div class="col-md-4 mb-3">
                          <label class="form-label fw-bold">Status Aktif</label>
                          <div class="form-check form-switch mt-2">
                              <input class="form-check-input" type="checkbox" name="aktif" value="1" style="transform: scale(1.3); margin-left: -2em;" 
                                  {{ old('aktif', $produk->aktif ?? true) ? 'checked' : '' }}>
                              <label class="form-check-label ms-2">Tampilkan untuk pelanggan</label>
                          </div>
                      </div>
                  </div>

                <div class="row">
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Foto Utama Produk (Opsional)</label>
                        <input type="file" name="foto" class="form-control form-control-premium" accept="image/*">
                        @if(isset($produk) && $produk->foto)
                            <div class="mt-2">
                                <img src="{{ asset('storage/' . $produk->foto) }}" alt="Foto Produk" class="img-thumbnail" style="max-height: 100px;">
                            </div>
                        @endif
                        <small class="text-muted">Format: JPG, PNG. Maks 2MB. Gambar ini akan dikirim bot jika tidak ada foto spesifik varian.</small>
                    </div>
                    <div class="col-md-6 mb-3">
                        <label class="form-label fw-bold">Deskripsi Tambahan</label>
                        <textarea name="deskripsi" class="form-control form-control-premium" rows="3" placeholder="Informasi tambahan terkait produk">{{ old('deskripsi', $produk->deskripsi ?? '') }}</textarea>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Size Chart / Keterangan Varian (Opsional)</label>
                    <textarea name="size_chart" class="form-control form-control-premium" rows="3" placeholder="Contoh: S = 50x70cm, M = 52x72cm">{{ old('size_chart', $produk->size_chart ?? '') }}</textarea>
                    <small class="text-muted">Penjelasan mengenai ukuran atau tipe varian. Teks ini akan dikirim oleh bot sebelum pelanggan memilih varian.</small>
                </div>

                <div class="card bg-light border-0 mb-4">
                    <div class="card-body">
                        <h6 class="fw-bold"><i class="fa-solid fa-tags text-warning me-2"></i>Harga Grosir / Promo Paket (Opsional)</h6>
                        <p class="text-muted small mb-3">Isi bagian ini jika produk memiliki sistem paket promo. Contoh: Harga normal Rp 4.000, "Beli 3 seharga Rp 10.000".</p>
                        <div class="row">
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Minimal Pembelian (Qty)</label>
                                <input type="number" name="promo_min_qty" class="form-control" value="{{ old('promo_min_qty', $produk->promo_min_qty ?? '') }}" placeholder="Contoh: 3" min="2">
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label fw-semibold" style="font-size: 13px;">Harga Promo Paket (Total untuk Qty di samping)</label>
                                <input type="number" name="promo_harga" class="form-control" value="{{ old('promo_harga', isset($produk) && $produk->promo_harga ? (int)$produk->promo_harga : '') }}" placeholder="Contoh: 10000" min="0">
                            </div>
                        </div>
                    </div>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Varian Produk</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="tambahVarian()">
                        <i class="fa-solid fa-plus"></i> Tambah Varian
                    </button>
                </div>
                
                <div class="alert alert-info py-2" style="font-size: 13px;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Masukkan Varian/Ukuran di sini (contoh: "Ukuran S", "Kotak Besar", "Kecil + Kartu Ucapan").
                    Jika produk ini tidak memiliki varian (All Size), cukup buat 1 variis dengan nama "Regular" atau "All Size".
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tableVarian">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Varian / Ukuran</th>
                                <th>Foto Spesifik (Opsional)</th>
                                <th style="width: 80px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $varians = old('varians', isset($produk) ? $produk->varians->toArray() : [['nama' => 'Regular', 'stok' => 0]]);
                            @endphp
                            
                            @foreach($varians as $idx => $varian)
                            <tr class="varian-row">
                                <td>
                                    <input type="hidden" name="varians[{{ $idx }}][id]" value="{{ $varian['id'] ?? '' }}">
                                    <input type="text" name="varians[{{ $idx }}][nama]" class="form-control" value="{{ $varian['nama'] ?? $varian['nama_varian'] ?? '' }}" required placeholder="Contoh: S / Regular">
                                </td>
                                <td>
                                    <input type="file" name="varians[{{ $idx }}][foto]" class="form-control" accept="image/*">
                                    @if(isset($varian['foto']) && $varian['foto'])
                                        <div class="mt-1">
                                            <a href="{{ asset('storage/' . $varian['foto']) }}" target="_blank" class="badge bg-info text-decoration-none">Lihat Foto</a>
                                        </div>
                                    @endif
                                </td>
                                <td>
                                    <input type="hidden" name="varians[{{ $idx }}][stok]" value="{{ $varian['stok'] ?? 0 }}">
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusVarian(this)">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Menu Tambahan / Add-ons (Opsional)</h5>
                    <button type="button" class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="tambahAddon()">
                        <i class="fa-solid fa-plus"></i> Tambah Add-on
                    </button>
                </div>
                
                <div class="alert alert-success py-2" style="font-size: 13px;">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Masukkan opsi tambahan (contoh: "Kartu Ucapan", "Lilin", "Pita"). Biarkan kosong jika tidak ada tambahan.
                </div>

                <div class="table-responsive">
                    <table class="table table-bordered align-middle" id="tableAddon">
                        <thead class="bg-light">
                            <tr>
                                <th>Nama Tambahan</th>
                                <th>Harga (Rp)</th>
                                <th class="text-center">Butuh Teks?</th>
                                <th style="width: 80px;" class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $addons = old('addons', isset($produk) ? $produk->addons->toArray() : []);
                            @endphp
                            
                            @forelse($addons as $idx => $addon)
                            <tr class="addon-row">
                                <td>
                                    <input type="hidden" name="addons[{{ $idx }}][id]" value="{{ $addon['id'] ?? '' }}">
                                    <input type="text" name="addons[{{ $idx }}][nama_addon]" class="form-control" value="{{ $addon['nama_addon'] ?? '' }}" required placeholder="Contoh: Kartu Ucapan">
                                </td>
                                <td>
                                    <input type="number" name="addons[{{ $idx }}][harga]" class="form-control" value="{{ isset($addon['harga']) ? (int)$addon['harga'] : 0 }}" required min="0" placeholder="Contoh: 5000">
                                </td>
                                <td class="text-center align-middle">
                                    <div class="form-check d-flex justify-content-center">
                                        <input class="form-check-input" type="checkbox" name="addons[{{ $idx }}][butuh_teks]" value="1" {{ !empty($addon['butuh_teks']) ? 'checked' : '' }}>
                                    </div>
                                </td>
                                <td class="text-center">
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusAddon(this)">
                                        <i class="fa-solid fa-times"></i>
                                    </button>
                                </td>
                            </tr>
                            @empty
                            <tr id="emptyAddonRow">
                                <td colspan="4" class="text-center text-muted py-3">Belum ada menu tambahan.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <div class="d-flex justify-content-end mt-4">
                    <button type="submit" class="btn btn-premium btn-premium-brand px-5 py-2 fw-bold">
                        <i class="fa-solid fa-save me-1"></i> Simpan Data Produk
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let varianIndex = {{ count($varians) }};

    function tambahVarian() {
        const tbody = document.querySelector('#tableVarian tbody');
        const tr = document.createElement('tr');
        tr.className = 'varian-row';
        tr.innerHTML = `
            <td>
                <input type="hidden" name="varians[${varianIndex}][id]" value="">
                <input type="text" name="varians[${varianIndex}][nama]" class="form-control" required placeholder="Contoh: S / Regular">
            </td>
            <td>
                <input type="file" name="varians[${varianIndex}][foto]" class="form-control" accept="image/*">
                <input type="hidden" name="varians[${varianIndex}][stok]" value="0">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusVarian(this)">
                    <i class="fa-solid fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        varianIndex++;
    }

    function hapusVarian(btn) {
        const tbody = document.querySelector('#tableVarian tbody');
        if (tbody.querySelectorAll('.varian-row').length > 1) {
            btn.closest('tr').remove();
        } else {
            Swal.fire('Oops', 'Minimal harus ada 1 varian produk!', 'warning');
        }
    }

    let addonIndex = {{ count($addons) }};

    function tambahAddon() {
        const emptyRow = document.getElementById('emptyAddonRow');
        if (emptyRow) emptyRow.remove();

        const tbody = document.querySelector('#tableAddon tbody');
        const tr = document.createElement('tr');
        tr.className = 'addon-row';
        tr.innerHTML = `
            <td>
                <input type="hidden" name="addons[${addonIndex}][id]" value="">
                <input type="text" name="addons[${addonIndex}][nama_addon]" class="form-control" required placeholder="Contoh: Kartu Ucapan">
            </td>
            <td>
                <input type="number" name="addons[${addonIndex}][harga]" class="form-control" required min="0" value="0">
            </td>
            <td class="text-center align-middle">
                <div class="form-check d-flex justify-content-center">
                    <input class="form-check-input" type="checkbox" name="addons[${addonIndex}][butuh_teks]" value="1">
                </div>
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusAddon(this)">
                    <i class="fa-solid fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        addonIndex++;
    }

    function hapusAddon(btn) {
        btn.closest('tr').remove();
        const tbody = document.querySelector('#tableAddon tbody');
        if (tbody.querySelectorAll('.addon-row').length === 0) {
            tbody.innerHTML = '<tr id="emptyAddonRow"><td colspan="4" class="text-center text-muted py-3">Belum ada menu tambahan.</td></tr>';
        }
    }
</script>
@endsection
