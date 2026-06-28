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
                          <input type="number" name="harga" id="inputHargaPokok" class="form-control form-control-premium" value="{{ old('harga', isset($produk) ? (int)$produk->harga : '') }}" required placeholder="Contoh: 50000" min="0">
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
                      <div class="col-md-12 mb-3">
                          <div class="card bg-light border-0">
                              <div class="card-body py-2">
                                  <div class="form-check form-switch mt-2">
                                      <input class="form-check-input" type="checkbox" name="is_bundle" id="isBundleToggle" value="1" style="transform: scale(1.3); margin-left: -2em;" 
                                          {{ old('is_bundle', $produk->is_bundle ?? false) ? 'checked' : '' }}>
                                      <label class="form-check-label ms-2 fw-bold text-primary" for="isBundleToggle">Jadikan sebagai Menu Paket (Bundling)</label>
                                  </div>
                                  <small class="text-muted d-block mt-1">Menu Paket memungkinkan Anda menggabungkan beberapa produk/varian menjadi satu harga paket khusus.</small>
                              </div>
                          </div>
                      </div>
                  </div>

                  <div class="card bg-info border-0 mb-4" id="bundleItemsSection" style="display: none; --bs-bg-opacity: .1;">
                      <div class="card-body">
                          <div class="d-flex justify-content-between align-items-center mb-3">
                              <h5 class="fw-bold mb-0 text-primary"><i class="fa-solid fa-boxes-stacked me-2"></i>Isi Paket Bundling</h5>
                              <button type="button" class="btn btn-sm btn-primary rounded-pill px-3" onclick="tambahBundleItem()">
                                  <i class="fa-solid fa-plus"></i> Tambah Isi Paket
                              </button>
                          </div>
                          <div class="table-responsive">
                              <table class="table table-bordered align-middle bg-white" id="tableBundle">
                                  <thead class="bg-light">
                                      <tr>
                                          <th>Pilih Produk / Varian</th>
                                          <th style="width: 150px;">Qty (Jumlah)</th>
                                          <th style="width: 80px;" class="text-center">Aksi</th>
                                      </tr>
                                  </thead>
                                  <tbody>
                                      @php
                                          $bundleItems = old('bundle_items', isset($produk) && $produk->bundleItems ? $produk->bundleItems->toArray() : []);
                                      @endphp
                                      
                                      @forelse($bundleItems as $idx => $bItem)
                                      <tr class="bundle-row">
                                          <td>
                                              <input type="hidden" name="bundle_items[{{ $idx }}][id]" value="{{ $bItem['id'] ?? '' }}">
                                              <select name="bundle_items[{{ $idx }}][varian_id]" class="form-select" required>
                                                  <option value="">-- Pilih Varian Produk --</option>
                                                  @if(isset($allVarians))
                                                      @foreach($allVarians as $av)
                                                          <option value="{{ $av->id }}" 
                                                              data-harga="{{ $av->harga > 0 ? $av->harga : ($av->produk->harga ?? 0) }}" 
                                                              data-hpp="{{ $av->hpp ?? 0 }}"
                                                              {{ (isset($bItem['produk_varian_id']) && $bItem['produk_varian_id'] == $av->id) || (isset($bItem['varian_id']) && $bItem['varian_id'] == $av->id) ? 'selected' : '' }}>
                                                              {{ $av->produk->nama }} - {{ $av->nama_varian }}
                                                          </option>
                                                      @endforeach
                                                  @endif
                                              </select>
                                          </td>
                                          <td>
                                              <input type="number" name="bundle_items[{{ $idx }}][qty]" class="form-control text-center bundle-qty" value="{{ $bItem['qty'] ?? 1 }}" required min="1">
                                          </td>
                                          <td class="text-center">
                                              <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBundleItem(this)">
                                                  <i class="fa-solid fa-times"></i>
                                              </button>
                                          </td>
                                      </tr>
                                      @empty
                                      <tr id="emptyBundleRow">
                                          <td colspan="3" class="text-center text-muted py-3">Belum ada isi paket. Klik Tambah Isi Paket.</td>
                                      </tr>
                                      @endforelse
                                  </tbody>
                              </table>
                          </div>
                          
                          <!-- KALKULATOR PROFIT BUNDLING -->
                          <div class="card border-0 mt-3 shadow-sm" style="background-color: #f8f9fa;">
                              <div class="card-body">
                                  <h6 class="fw-bold text-success mb-3"><i class="fa-solid fa-calculator me-2"></i>Kalkulator Profit Bundling</h6>
                                  <div class="row text-center">
                                      <div class="col-md-3 mb-2">
                                          <div class="small text-muted">Total Harga Jual Normal</div>
                                          <div class="fw-bold fs-5" id="calcHargaNormal">Rp 0</div>
                                      </div>
                                      <div class="col-md-3 mb-2">
                                          <div class="small text-muted">Total HPP / Modal</div>
                                          <div class="fw-bold fs-5 text-danger" id="calcTotalHpp">Rp 0</div>
                                      </div>
                                      <div class="col-md-3 mb-2">
                                          <div class="small text-muted">Harga Paket Bundling</div>
                                          <div class="fw-bold fs-5 text-primary" id="calcHargaPaket">Rp 0</div>
                                      </div>
                                      <div class="col-md-3 mb-2">
                                          <div class="small text-muted">Estimasi Keuntungan</div>
                                          <div class="fw-bold fs-5 text-success" id="calcProfit">Rp 0</div>
                                      </div>
                                  </div>
                                  <hr class="my-2">
                                  <div class="d-flex justify-content-between align-items-center">
                                      <small class="text-muted" id="calcDiskonText">Diskon yang diberikan ke pembeli: Rp 0 (0%)</small>
                                      <div>
                                          <span class="badge bg-info rounded-pill me-2" id="calcDiskonBadge">Diskon: 0%</span>
                                          <span class="badge bg-success rounded-pill" id="calcMarginBadge">Margin: 0%</span>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <!-- END KALKULATOR -->
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

                <div class="card bg-light border-0 mb-4" id="promoPaketSection">
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

                <!-- Bundle items section moved to top -->

                <hr class="my-4">

                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="fw-bold mb-0">Varian Produk</h5>
                    <button type="button" class="btn btn-sm btn-outline-primary rounded-pill px-3" onclick="tambahVarian()">
                        <i class="fa-solid fa-plus"></i> Tambah Varian
                    </button>
                </div>
                
                <div class="alert alert-info py-2" style="font-size: 13px;" id="varianAlertText">
                    <i class="fa-solid fa-circle-info me-2"></i>
                    Masukkan Varian/Ukuran di sini (contoh: "Ukuran S", "Kotak Besar", "Kecil + Kartu Ucapan").
                    Jika produk ini tidak memiliki varian (All Size) atau ini adalah Menu Paket, cukup biarkan 1 varian dengan nama "Regular" (Otomatis hidden dari pembeli).
                </div>

                <div class="table-responsive" id="varianTableSection">
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
    @php
        $bundleItemsCount = isset($bundleItems) ? count($bundleItems) : (isset($produk) && $produk->bundleItems ? $produk->bundleItems->count() : 0);
    @endphp
    let bundleIndex = {{ $bundleItemsCount }};

    document.addEventListener('DOMContentLoaded', function() {
        const bundleToggle = document.getElementById('isBundleToggle');
        const inputHargaPokok = document.getElementById('inputHargaPokok');
        
        function toggleBundleSections() {
            const isBundle = bundleToggle.checked;
            const promoSection = document.getElementById('promoPaketSection');
            const bundleSection = document.getElementById('bundleItemsSection');
            const varianAlert = document.getElementById('varianAlertText');
            
            if (isBundle) {
                promoSection.style.display = 'none';
                bundleSection.style.display = 'block';
                varianAlert.innerHTML = '<i class="fa-solid fa-circle-info me-2"></i> Menu Paket Bundling: Varian di bawah ini (biasanya "Regular") hanya sebagai data referensi. Isi paketnya ditentukan di bagian atas.';
                
                // Set first varian to Regular if none exists
                const firstVarian = document.querySelector('input[name="varians[0][nama]"]');
                if (firstVarian && firstVarian.value === '') {
                    firstVarian.value = 'Regular';
                }
                hitungProfitBundling(); // Hitung awal
            } else {
                promoSection.style.display = 'block';
                bundleSection.style.display = 'none';
                varianAlert.innerHTML = '<i class="fa-solid fa-circle-info me-2"></i> Masukkan Varian/Ukuran di sini (contoh: "Ukuran S", "Kotak Besar", "Kecil + Kartu Ucapan"). Jika produk ini tidak memiliki varian (All Size), cukup buat 1 varian dengan nama "Regular" atau "All Size".';
            }
        }

        if(bundleToggle) {
            bundleToggle.addEventListener('change', toggleBundleSections);
            toggleBundleSections(); // Init on load
        }
        
        if (inputHargaPokok) {
            inputHargaPokok.addEventListener('input', hitungProfitBundling);
        }
        
        // Delegasi event untuk perubahan select dan qty
        document.getElementById('tableBundle').addEventListener('change', function(e) {
            if (e.target.tagName === 'SELECT' || e.target.tagName === 'INPUT') {
                hitungProfitBundling();
            }
        });
        document.getElementById('tableBundle').addEventListener('input', function(e) {
            if (e.target.tagName === 'INPUT') {
                hitungProfitBundling();
            }
        });
    });
    
    function hitungProfitBundling() {
        let totalHargaNormal = 0;
        let totalHpp = 0;
        
        const rows = document.querySelectorAll('#tableBundle tbody .bundle-row');
        rows.forEach(row => {
            const select = row.querySelector('select');
            const qtyInput = row.querySelector('.bundle-qty');
            if (select && select.value !== '' && qtyInput) {
                const option = select.options[select.selectedIndex];
                const harga = parseFloat(option.getAttribute('data-harga')) || 0;
                const hpp = parseFloat(option.getAttribute('data-hpp')) || 0;
                const qty = parseInt(qtyInput.value) || 1;
                
                totalHargaNormal += (harga * qty);
                totalHpp += (hpp * qty);
            }
        });
        
        const hargaPaket = parseFloat(document.getElementById('inputHargaPokok').value) || 0;
        const profit = hargaPaket - totalHpp;
        
        const diskon = totalHargaNormal - hargaPaket;
        const persentaseDiskon = totalHargaNormal > 0 ? Math.abs(diskon / totalHargaNormal * 100).toFixed(1) : 0;
        
        let margin = 0;
        if (hargaPaket > 0) margin = (profit / hargaPaket * 100).toFixed(1);
        else if (totalHpp > 0) margin = (profit / totalHpp * 100).toFixed(1);
        
        const formatRp = (angka) => 'Rp ' + Math.abs(angka).toLocaleString('id-ID');
        
        document.getElementById('calcHargaNormal').innerText = formatRp(totalHargaNormal);
        document.getElementById('calcTotalHpp').innerText = formatRp(totalHpp);
        document.getElementById('calcHargaPaket').innerText = formatRp(hargaPaket);
        document.getElementById('calcProfit').innerText = (profit < 0 ? '-' : '') + formatRp(profit);
        
        let diskonText = '';
        if (diskon > 0) {
            diskonText = `<span class="text-success fw-bold"><i class="fa-solid fa-tag me-1"></i> Diskon untuk pembeli: ${formatRp(diskon)} (${persentaseDiskon}%)</span>`;
        } else if (diskon < 0) {
            diskonText = `<span class="text-danger"><i class="fa-solid fa-triangle-exclamation me-1"></i> Harga paket lebih MAHAL ${formatRp(diskon)} dari beli satuan!</span>`;
        } else {
            diskonText = `<span class="text-muted"><i class="fa-solid fa-equals me-1"></i> Harga paket SAMA dengan harga beli satuan.</span>`;
        }
        
        document.getElementById('calcDiskonText').innerHTML = diskonText;
        
        let badgeDiskon = document.getElementById('calcDiskonBadge');
        if (diskon > 0) {
            badgeDiskon.innerText = `Diskon: ${persentaseDiskon}%`;
            badgeDiskon.className = 'badge bg-info rounded-pill me-2';
        } else if (diskon < 0) {
            badgeDiskon.innerText = `Markup: ${persentaseDiskon}%`;
            badgeDiskon.className = 'badge bg-danger rounded-pill me-2';
        } else {
            badgeDiskon.innerText = `Diskon: 0%`;
            badgeDiskon.className = 'badge bg-secondary rounded-pill me-2';
        }

        document.getElementById('calcMarginBadge').innerText = `Margin: ${margin}%`;
        
        if (profit < 0) {
            document.getElementById('calcProfit').className = 'fw-bold fs-5 text-danger';
            document.getElementById('calcMarginBadge').className = 'badge bg-danger rounded-pill';
        } else {
            document.getElementById('calcProfit').className = 'fw-bold fs-5 text-success';
            document.getElementById('calcMarginBadge').className = 'badge bg-success rounded-pill';
        }
    }

    function tambahBundleItem() {
        const emptyRow = document.getElementById('emptyBundleRow');
        if (emptyRow) emptyRow.remove();

        const tbody = document.querySelector('#tableBundle tbody');
        const tr = document.createElement('tr');
        tr.className = 'bundle-row';
        tr.innerHTML = `
            <td>
                <input type="hidden" name="bundle_items[${bundleIndex}][id]" value="">
                <select name="bundle_items[${bundleIndex}][varian_id]" class="form-select" required>
                    <option value="">-- Pilih Varian Produk --</option>
                    @if(isset($allVarians))
                        @foreach($allVarians as $av)
                            <option value="{{ $av->id }}"
                                data-harga="{{ $av->harga > 0 ? $av->harga : ($av->produk->harga ?? 0) }}" 
                                data-hpp="{{ $av->hpp ?? 0 }}">
                                {{ $av->produk->nama }} - {{ $av->nama_varian }}
                            </option>
                        @endforeach
                    @endif
                </select>
            </td>
            <td>
                <input type="number" name="bundle_items[${bundleIndex}][qty]" class="form-control text-center bundle-qty" value="1" required min="1">
            </td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger" onclick="hapusBundleItem(this)">
                    <i class="fa-solid fa-times"></i>
                </button>
            </td>
        `;
        tbody.appendChild(tr);
        bundleIndex++;
    }

    function hapusBundleItem(btn) {
        btn.closest('tr').remove();
        const tbody = document.querySelector('#tableBundle tbody');
        if (tbody.querySelectorAll('.bundle-row').length === 0) {
            tbody.innerHTML = '<tr id="emptyBundleRow"><td colspan="3" class="text-center text-muted py-3">Belum ada isi paket. Klik Tambah Isi Paket.</td></tr>';
        }
        hitungProfitBundling();
    }

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
