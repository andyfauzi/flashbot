@extends('layouts.app')

@section('title', 'Kasir / POS')

@section('styles')
<style>
    .product-card {
        cursor: pointer;
        transition: transform 0.1s, box-shadow 0.1s;
    }
    .product-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 4px 12px rgba(0,0,0,0.1);
    }
    .product-card.disabled {
        opacity: 0.5;
        cursor: not-allowed;
    }
    .cart-item {
        background: #f8fafc;
        border-radius: 8px;
        padding: 10px;
        margin-bottom: 10px;
    }
    
    /* Z-index fix for topbar to appear below the lock overlay */
    .z-3 { z-index: 1050; }
    
    /* List View Styles */
    #productContainer.view-list .product-wrapper {
        width: 100%;
        margin-bottom: 0.5rem;
    }
    #productContainer.view-list .product-card {
        flex-direction: row;
        height: auto !important;
    }
    #productContainer.view-list .card-img-top {
        width: 100px;
        height: 100px !important;
        border-radius: var(--bs-card-inner-border-radius) 0 0 var(--bs-card-inner-border-radius);
    }
    #productContainer.view-list .card-body {
        display: flex;
        align-items: center;
        justify-content: space-between;
        text-align: left !important;
        padding: 1rem;
    }
    #productContainer.view-list .card-body-left {
        flex-grow: 1;
    }
    #productContainer.view-list .card-body-right {
        text-align: right;
        min-width: 120px;
    }
    #productContainer.view-list .product-title {
        height: auto !important;
        margin-bottom: 0 !important;
    }
</style>
@endsection

@section('content')

@if(!$activeShift)
<!-- OVERLAY BLOKIR POS KARENA BELUM BUKA SHIFT -->
<div class="position-fixed top-0 start-0 w-100 h-100 bg-dark bg-opacity-75 z-3 d-flex align-items-center justify-content-center" style="z-index: 1050 !important;">
    <div class="card p-4 text-center shadow-lg" style="max-width: 400px;">
        <i class="fa-solid fa-lock fa-3x text-warning mb-3"></i>
        <h4 class="fw-bold">Kasir Terkunci</h4>
        <p class="text-muted">Anda belum membuka shift kasir. Silakan masukkan modal awal laci untuk memulai transaksi.</p>
        <form action="{{ route('dashboard.shift.buka') }}" method="POST">
            @csrf
            <div class="mb-3 text-start">
                <label class="form-label">Modal Awal / Uang Kembalian (Rp)</label>
                <input type="number" name="modal_awal" class="form-control" placeholder="Contoh: 100000" min="0" required>
            </div>
            <button type="submit" class="btn btn-primary w-100 fw-bold">
                <i class="fa-solid fa-key me-2"></i> Buka Shift & Mulai POS
            </button>
        </form>
    </div>
</div>
@endif

<div class="container-fluid {{ !$activeShift ? 'pe-none opacity-50' : '' }}">
    <div class="row">
        <!-- Kolom Kiri: Produk & Kategori -->
        <div class="col-md-8">
            <div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-3">
                <h4 class="fw-bold mb-2 mb-md-0" id="mainTitle"><i class="fa-solid fa-tags me-2"></i>Pilih Kategori</h4>
                <div class="d-flex gap-2">
                    <button class="btn btn-outline-secondary btn-sm d-none" id="btnBackToCategory" onclick="showCategories()">
                        <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                    </button>
                    <div class="btn-group d-none" id="viewToggleGroup">
                        <button class="btn btn-outline-primary btn-sm active" id="btnGrid" onclick="setViewMode('grid')"><i class="fa-solid fa-border-all"></i></button>
                        <button class="btn btn-outline-primary btn-sm" id="btnList" onclick="setViewMode('list')"><i class="fa-solid fa-list"></i></button>
                    </div>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="mb-4">
                <div class="input-group shadow-sm" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-card);">
                    <span class="input-group-text bg-white border-0 text-muted px-3"><i class="fa-solid fa-search"></i></span>
                    <input type="text" id="posSearch" class="form-control border-0 ps-0" placeholder="Cari nama menu..." oninput="searchProducts(this.value)">
                </div>
            </div>
            
            <!-- Category View -->
            <div class="row g-3" id="categoryContainer">
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center shadow-sm product-card" onclick="showProducts('all', 'Semua Menu')">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                            <i class="fa-solid fa-layer-group fs-2 text-primary mb-2"></i>
                            <h6 class="fw-bold mb-0">Semua Menu</h6>
                        </div>
                    </div>
                </div>
                @foreach($kategoris as $kat)
                <div class="col-md-3 col-6">
                    <div class="card h-100 text-center shadow-sm product-card" onclick="showProducts('{{ $kat->id }}', '{{ addslashes($kat->nama) }}')">
                        <div class="card-body d-flex flex-column align-items-center justify-content-center py-4">
                            @if($kat->foto)
                                <img src="{{ asset('storage/' . $kat->foto) }}" alt="{{ $kat->nama }}" class="mb-2" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px; border: 1px solid #eee;">
                            @else
                                <i class="fa-solid fa-folder-open fs-2 text-warning mb-2"></i>
                            @endif
                            <h6 class="fw-bold mb-0">{{ $kat->nama }}</h6>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>

            <!-- Product View (Hidden initially) -->
            <div class="row g-3 d-none" id="productContainer">
                @foreach($produks as $produk)
                    @php
                        $punyaVarian = $produk->varians->count() > 1;
                    @endphp
                    @if($punyaVarian)
                        @foreach($produk->varians as $varian)
                            @php
                                $stokTersedia = $produk->is_made_to_order ? 9999 : $varian->stok;
                                $hargaVarian = $varian->harga ? $varian->harga : $produk->harga;
                                $namaTampil = $produk->nama . ' - ' . $varian->nama_varian;
                            @endphp
                            <div class="col-md-3 product-wrapper" data-kategori="{{ $produk->kategori_id }}">
                                <div class="card h-100 product-card {{ ($stokTersedia <= 0 && !$produk->is_made_to_order) ? 'disabled' : '' }}" 
                                     data-id="{{ $produk->id }}"
                                     data-nama="{{ $produk->nama }}"
                                     data-harga="{{ $produk->harga }}"
                                     data-varian-id="{{ $varian->id }}"
                                     data-varian-nama="{{ $varian->nama_varian }}"
                                     data-varian-harga="{{ $hargaVarian }}"
                                     data-addons='{{ json_encode($produk->addons) }}'
                                     data-promo-min="{{ $produk->promo_min_qty ?? '' }}"
                                     data-promo-harga="{{ $produk->promo_harga ?? '' }}"
                                     data-stok="{{ $stokTersedia }}"
                                     data-is-made-to-order="{{ $produk->is_made_to_order ? '1' : '0' }}">
                                    @if($varian->foto)
                                        <img src="{{ asset('storage/' . $varian->foto) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="{{ $namaTampil }}">
                                    @elseif($produk->foto)
                                        <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="{{ $namaTampil }}">
                                    @else
                                        <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 120px;">
                                            <i class="fa-solid fa-image fs-1"></i>
                                        </div>
                                    @endif
                                    <div class="card-body text-center p-3">
                                        <div class="card-body-left">
                                            <h6 class="fw-bold mb-1 product-title" style="font-size: 14px; height: 35px; overflow:hidden;">{{ $namaTampil }}</h6>
                                            <small class="text-muted">
                                                @if($produk->is_made_to_order)
                                                    Made-to-Order
                                                @else
                                                    Sisa Stok: {{ $stokTersedia }}
                                                @endif
                                            </small>
                                        </div>
                                        <div class="card-body-right">
                                            <div class="text-success fw-bold mt-2 mt-md-0">Rp {{ number_format($hargaVarian, 0, ',', '.') }}</div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    @else
                        @php
                            $varian = $produk->varians->first();
                            $stokTersedia = $produk->is_made_to_order ? 9999 : ($varian ? $varian->stok : $produk->stok);
                            $hargaVarian = ($varian && $varian->harga) ? $varian->harga : $produk->harga;
                            $varianId = $varian ? $varian->id : null;
                            $varianNama = $varian ? $varian->nama_varian : '';
                        @endphp
                        <div class="col-md-3 product-wrapper" data-kategori="{{ $produk->kategori_id }}">
                            <div class="card h-100 product-card {{ ($stokTersedia <= 0 && !$produk->is_made_to_order) ? 'disabled' : '' }}" 
                                 data-id="{{ $produk->id }}"
                                 data-nama="{{ $produk->nama }}"
                                 data-harga="{{ $produk->harga }}"
                                 data-varian-id="{{ $varianId }}"
                                 data-varian-nama="{{ $varianNama }}"
                                 data-varian-harga="{{ $hargaVarian }}"
                                 data-addons='{{ json_encode($produk->addons) }}'
                                 data-promo-min="{{ $produk->promo_min_qty ?? '' }}"
                                 data-promo-harga="{{ $produk->promo_harga ?? '' }}"
                                 data-stok="{{ $stokTersedia }}"
                                 data-is-made-to-order="{{ $produk->is_made_to_order ? '1' : '0' }}">
                                @if($produk->foto)
                                    <img src="{{ asset('storage/' . $produk->foto) }}" class="card-img-top object-fit-cover" style="height: 120px;" alt="{{ $produk->nama }}">
                                @else
                                    <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 120px;">
                                        <i class="fa-solid fa-image fs-1"></i>
                                    </div>
                                @endif
                                <div class="card-body text-center p-3">
                                    <div class="card-body-left">
                                        <h6 class="fw-bold mb-1 product-title" style="font-size: 14px; height: 35px; overflow:hidden;">{{ $produk->nama }}</h6>
                                        <small class="text-muted">
                                            @if($produk->is_made_to_order)
                                                Made-to-Order
                                            @else
                                                Sisa Stok: {{ $stokTersedia }}
                                            @endif
                                        </small>
                                    </div>
                                    <div class="card-body-right">
                                        <div class="text-success fw-bold mt-2 mt-md-0">Rp {{ number_format($hargaVarian, 0, ',', '.') }}</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        </div>

        <!-- Kolom Kanan: Keranjang Kasir -->
        <div class="col-md-4">
            <div class="card shadow-sm h-100 border-0">
                <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                    <h5 class="fw-bold"><i class="fa-solid fa-cart-shopping me-2"></i>Keranjang Kasir</h5>
                </div>
                <div class="card-body d-flex flex-column">
                    
                    <!-- Form Pelanggan -->
                    <div class="mb-3">
                        <input type="text" id="nama_penerima" class="form-control mb-2" placeholder="Nama Pelanggan (Opsional)">
                        <input type="text" id="nomor_wa" class="form-control" placeholder="Nomor WA Pelanggan (Opsional, cth: 0812...)">
                        <small class="text-muted" style="font-size: 11px;">Isi untuk mengirimkan struk via WhatsApp</small>
                    </div>
                    
                    <!-- Cart List -->
                    <div id="cartList" class="flex-grow-1 overflow-auto" style="min-height: 200px; max-height: 350px;">
                        <div class="text-center text-muted mt-5" id="emptyCart">
                            <i class="fa-solid fa-basket-shopping fs-1 mb-2"></i>
                            <p>Keranjang kosong</p>
                        </div>
                    </div>

                    <!-- Total & Checkout -->
                    <div class="mt-3 border-top pt-3">
                        <div class="d-flex justify-content-between mb-2">
                            <span class="fs-5">Total Bayar:</span>
                            <span class="fs-5 fw-bold text-primary" id="totalBayarText">Rp 0</span>
                        </div>

                        <select id="metode_pembayaran" class="form-select mb-3" onchange="toggleKembalian()">
                            <option value="cash">Tunai / Cash</option>
                            <option value="qris">QRIS</option>
                            <option value="transfer">Transfer Bank</option>
                        </select>

                        <div class="mb-3" id="uangDiterimaContainer">
                            <label class="form-label text-secondary fw-semibold" style="font-size: 13px;">Uang Diterima (Rp):</label>
                            <input type="number" id="uang_diterima" class="form-control form-control-sm border-primary" placeholder="Contoh: 50000" min="0" oninput="hitungKembalian()">
                        </div>
                        <div class="d-flex justify-content-between mb-3 text-success fw-bold d-none" id="kembalianContainer">
                            <span class="fs-6">Kembalian:</span>
                            <span class="fs-6" id="kembalianText">Rp 0</span>
                        </div>

                        <div class="form-check form-switch mb-3">
                            <input class="form-check-input" type="checkbox" id="is_preorder" onchange="togglePreorderInputs()">
                            <label class="form-check-label fw-bold text-primary" for="is_preorder">Jadikan Pre-Order (Ambil Nanti)</label>
                        </div>
                        
                        <div id="preorder_inputs" class="d-none mb-3 bg-light p-2 rounded">
                            <label class="form-label" style="font-size: 13px;">Tanggal Pengambilan:</label>
                            <input type="date" id="tanggal_diambil" class="form-control form-control-sm mb-2">
                            <label class="form-label" style="font-size: 13px;">Uang Muka / DP (Rp):</label>
                            <input type="number" id="uang_muka" class="form-control form-control-sm" placeholder="Isi 0 jika belum bayar DP" min="0">
                        </div>

                        <button class="btn btn-success w-100 py-3 fw-bold fs-5" id="btnBayar" onclick="prosesPembayaran()" disabled>
                            <i class="fa-solid fa-check-circle me-2"></i> BAYAR SEKARANG
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Pilih Opsi Produk -->
<div class="modal fade" id="modalOpsi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold" id="modalOpsiTitle">Opsi Produk</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="opsiFormContainer">
                    <!-- Form opsi will be injected here -->
                </div>
            </div>
            <div class="modal-footer border-0">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary fw-bold px-4" id="btnSubmitOpsi">Tambahkan ke Keranjang</button>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    let cart = {};
    let modalVarian;
    let currentTotal = 0;

    document.addEventListener('DOMContentLoaded', function() {
        // Initialize cart
        try {
            cart = JSON.parse(localStorage.getItem('pos_cart')) || {};
            // Clean up any corrupt items loaded from local storage
            let cartChanged = false;
            for (let id in cart) {
                let item = cart[id];
                if (!item || !item.id || item.nama === null || item.nama === undefined || item.nama === 'null' || isNaN(item.harga) || item.harga === null) {
                    delete cart[id];
                    cartChanged = true;
                }
            }
            if (cartChanged) {
                localStorage.setItem('pos_cart', JSON.stringify(cart));
            }
        } catch (e) {
            console.error('Error parsing pos_cart from localStorage', e);
            cart = {};
            localStorage.removeItem('pos_cart');
        }

        @if(isset($editOrder) && $editOrder)
            // Auto-fill cart from Voided Order
            cart = {};
            @foreach($editOrder->items as $item)
                @php
                    $cartKey = $item->produk_id . '_' . ($item->produk_varian_id ?? '0');
                @endphp
                cart["{{ $cartKey }}"] = {
                    id: "{{ $item->produk_id }}",
                    varian_id: "{{ $item->produk_varian_id }}",
                    nama: `{!! addslashes($item->produk->nama) !!}` + ("{{ $item->produkVarian ? ' - ' . addslashes($item->produkVarian->nama_varian) : '' }}"),
                    harga: {{ $item->harga_satuan }},
                    qty: {{ $item->jumlah }}
                };
            @endforeach
            saveCart();
            
            // Populate inputs
            document.getElementById('nama_penerima').value = "{!! addslashes($editOrder->nama_penerima) !!}";
            document.getElementById('nomor_wa').value = "{!! addslashes($editOrder->nomor_wa) !!}";
            document.getElementById('metode_pembayaran').value = "{{ $editOrder->metode_pembayaran }}";
            
            // Remove edit_order from URL without reloading
            window.history.replaceState({}, document.title, "{{ route('pos.index') }}");
        @endif

        // Event listener untuk klik produk di keranjang (Card Produk)
        document.querySelectorAll('.product-card').forEach(card => {
            card.addEventListener('click', function() {
                if (this.classList.contains('disabled')) return;
                
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const varianId = this.getAttribute('data-varian-id');
                const varianNama = this.getAttribute('data-varian-nama');
                const varianHarga = parseInt(this.getAttribute('data-varian-harga'));
                const addonsJson = this.getAttribute('data-addons') || '[]';
                const promoMin = parseInt(this.getAttribute('data-promo-min')) || null;
                const promoHarga = parseFloat(this.getAttribute('data-promo-harga')) || null;
                const stokTersedia = parseInt(this.getAttribute('data-stok'));
                const isMadeToOrder = this.getAttribute('data-is-made-to-order') === '1';

                if (stokTersedia <= 0 && !isMadeToOrder) {
                    Swal.fire('Stok Habis', 'Produk ini sedang kosong.', 'warning');
                    return;
                }

                let addons = [];
                try {
                    addons = JSON.parse(addonsJson);
                } catch (e) {
                    console.error("Gagal mem-parsing JSON", e);
                }

                if (addons.length > 0) {
                    // Tampilkan modal opsi addons
                    document.getElementById('modalOpsiTitle').innerText = (varianNama && varianNama !== 'All Size') ? `${nama} (${varianNama})` : nama;
                    let html = '<form id="formOpsiProduk">';
                    
                    html += '<h6 class="fw-bold mb-2">Menu Tambahan (Opsional):</h6><div class="list-group mb-3">';
                    addons.forEach((a) => {
                        const hargaAddon = parseInt(a.harga);
                        html += `
                            <label class="list-group-item d-flex justify-content-between align-items-center">
                                <div>
                                    <input class="form-check-input me-2 addon-checkbox" type="checkbox" name="addonSelect[]" value='${JSON.stringify({id: a.id, nama: a.nama_addon, harga: hargaAddon})}'>
                                    ${a.nama_addon}
                                </div>
                                <span class="badge bg-success">+ Rp ${formatRupiah(hargaAddon)}</span>
                            </label>
                        `;
                    });
                    html += '</div></form>';
                    document.getElementById('opsiFormContainer').innerHTML = html;
                    
                    // Setup submit button
                    const btnSubmit = document.getElementById('btnSubmitOpsi');
                    btnSubmit.onclick = function() {
                        let selectedAddons = [];
                        const checkboxes = document.querySelectorAll('.addon-checkbox:checked');
                        checkboxes.forEach(cb => {
                            selectedAddons.push(JSON.parse(cb.value));
                        });

                        addToCart(id, nama, varianHarga, stokTersedia, varianId, varianNama, selectedAddons, promoMin, promoHarga, isMadeToOrder);
                    };

                    new bootstrap.Modal(document.getElementById('modalOpsi')).show();
                } else {
                    // Langsung tambah ke cart jika tidak ada addons
                    addToCart(id, nama, varianHarga, stokTersedia, varianId, varianNama, [], promoMin, promoHarga, isMadeToOrder);
                }
            });
        });
    });

    function setViewMode(mode) {
        const container = document.getElementById('productContainer');
        const btnGrid = document.getElementById('btnGrid');
        const btnList = document.getElementById('btnList');

        if (mode === 'list') {
            container.classList.add('view-list');
            btnList.classList.add('active');
            btnGrid.classList.remove('active');
        } else {
            container.classList.remove('view-list');
            btnGrid.classList.add('active');
            btnList.classList.remove('active');
        }
    }

    function showCategories() {
        document.getElementById('categoryContainer').classList.remove('d-none');
        document.getElementById('productContainer').classList.add('d-none');
        document.getElementById('mainTitle').innerHTML = '<i class="fa-solid fa-tags me-2"></i>Pilih Kategori';
        document.getElementById('btnBackToCategory').classList.add('d-none');
        document.getElementById('viewToggleGroup').classList.add('d-none');
    }

    function showProducts(kategoriId, kategoriNama) {
        // Kosongkan search box saat masuk kategori
        document.getElementById('posSearch').value = '';
        
        document.getElementById('categoryContainer').classList.add('d-none');
        document.getElementById('productContainer').classList.remove('d-none');
        document.getElementById('mainTitle').innerHTML = '<i class="fa-solid fa-cash-register me-2"></i>' + kategoriNama;
        document.getElementById('btnBackToCategory').classList.remove('d-none');
        document.getElementById('viewToggleGroup').classList.remove('d-none');

        const products = document.querySelectorAll('.product-wrapper');
        products.forEach(p => {
            if (kategoriId === 'all') {
                p.style.display = 'block';
            } else {
                if (p.getAttribute('data-kategori') === kategoriId.toString()) {
                    p.style.display = 'block';
                } else {
                    p.style.display = 'none';
                }
            }
        });
    }

    function searchProducts(keyword) {
        keyword = keyword.toLowerCase();
        
        if (keyword.length > 0) {
            document.getElementById('categoryContainer').classList.add('d-none');
            document.getElementById('productContainer').classList.remove('d-none');
            document.getElementById('mainTitle').innerHTML = '<i class="fa-solid fa-search me-2"></i>Hasil Pencarian';
            document.getElementById('btnBackToCategory').classList.remove('d-none');
            document.getElementById('viewToggleGroup').classList.remove('d-none');
        } else {
            showCategories();
            return;
        }

        const products = document.querySelectorAll('.product-wrapper');
        products.forEach(p => {
            const card = p.querySelector('.product-card');
            const nama = (card.getAttribute('data-nama') || '').toLowerCase();
            const varianNama = (card.getAttribute('data-varian-nama') || '').toLowerCase();
            
            if (nama.includes(keyword) || varianNama.includes(keyword)) {
                p.style.display = 'block';
            } else {
                p.style.display = 'none';
            }
        });
    }

    function addToCart(id, nama, harga, stokMax, varianId, varianNama, addons = [], promoMin = null, promoHarga = null, isMadeToOrder = false) {
        const modalEl = document.getElementById('modalOpsi');
        if (modalEl) {
            const modalInstance = bootstrap.Modal.getInstance(modalEl);
            if(modalInstance) modalInstance.hide();
        }
        
        let addonHargaTotal = 0;
        let addonNames = [];
        let addonIds = [];
        addons.forEach(a => {
            addonHargaTotal += parseInt(a.harga);
            addonNames.push(a.nama);
            addonIds.push(a.id);
        });

        // Gunakan gabungan ID, varian_id, dan addons sebagai kunci keranjang
        const addonKeyPart = addonIds.length > 0 ? `-${addonIds.join('-')}` : '';
        const cartKey = varianId ? `${id}-${varianId}${addonKeyPart}` : `${id}${addonKeyPart}`;
        
        let namaTampil = (varianId && varianNama && varianNama !== 'All Size') ? `${nama} (${varianNama})` : nama;
        if (addonNames.length > 0) {
            namaTampil += ` <br><small class="text-muted">+ ${addonNames.join(', ')}</small>`;
        }

        const hargaFinal = harga + addonHargaTotal;

        if (!cart[cartKey]) {
            cart[cartKey] = { 
                id: id, 
                varian_id: varianId, 
                nama: namaTampil, 
                harga: hargaFinal, 
                qty: 1, 
                stokMax: stokMax, 
                oriNama: nama, 
                oriVarianNama: varianNama,
                addons: addons,
                promoMin: promoMin,
                promoHarga: promoHarga,
                baseHarga: harga, // Harga pokok tanpa addon
                addonHargaTotal: addonHargaTotal, // Total harga addon
                isMadeToOrder: isMadeToOrder
            };
        } else {
            if (isMadeToOrder || cart[cartKey].qty < stokMax) {
                cart[cartKey].qty++;
            } else {
                Swal.fire('Stok Habis', 'Maksimal pembelian untuk produk ini adalah ' + stokMax, 'warning');
            }
        }
        saveCart();
        renderCart();
    }

    function kurangiQty(id) {
        if (cart[id].qty > 1) {
            cart[id].qty--;
        } else {
            delete cart[id];
        }
        saveCart();
        renderCart();
    }

    function tambahQty(id) {
        if (cart[id].isMadeToOrder || cart[id].qty < cart[id].stokMax) {
            cart[id].qty++;
            saveCart();
            renderCart();
        } else {
            Swal.fire('Stok Habis', 'Maksimal pembelian untuk produk ini adalah ' + cart[id].stokMax, 'warning');
        }
    }

    function hapusItem(id) {
        delete cart[id];
        saveCart();
        renderCart();
    }

    function formatRupiah(angka) {
        return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR', minimumFractionDigits: 0 }).format(angka);
    }

    function renderCart() {
        const cartList = document.getElementById('cartList');
        const emptyCart = document.getElementById('emptyCart');
        const totalBayarText = document.getElementById('totalBayarText');
        const btnBayar = document.getElementById('btnBayar');
        
        let html = '';
        
        let total = 0;
        let count = 0;
        let cartChanged = false;

        for (let id in cart) {
            let item = cart[id];
            
            // Validate item
            if (!item || !item.id || item.nama === null || item.nama === undefined || item.nama === 'null' || isNaN(item.harga) || item.harga === null) {
                delete cart[id];
                cartChanged = true;
                continue;
            }
            
            // Fallback untuk keranjang lama yang tersimpan di localStorage
            if (item.baseHarga === undefined) {
                item.baseHarga = item.harga;
                item.addonHargaTotal = 0;
            }

            // Perhitungan Promo Bundle (Pilihan A)
            let totalBiayaProduk = 0;
            let promoBadge = '';
            
            if (item.promoMin && item.promoHarga && item.qty >= item.promoMin) {
                let jmlPaket = Math.floor(item.qty / item.promoMin);
                let sisaItem = item.qty % item.promoMin;
                totalBiayaProduk = (jmlPaket * item.promoHarga) + (sisaItem * item.baseHarga);
                promoBadge = `<br><span class="badge bg-warning text-dark" style="font-size: 10px;">Harga Paket Grosir Aktif!</span>`;
            } else {
                totalBiayaProduk = item.baseHarga * item.qty;
            }
            
            let totalBiayaAddon = item.addonHargaTotal * item.qty;
            let subtotal = totalBiayaProduk + totalBiayaAddon;
            
            total += subtotal;
            count++;

            html += `
            <div class="cart-item d-flex justify-content-between align-items-center">
                <div style="width: 50%;">
                    <h6 class="mb-0 fw-bold" style="font-size: 13px;">${item.nama}</h6>
                    <small class="text-muted">${formatRupiah(item.harga)}/pcs</small>
                    ${promoBadge}
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="kurangiQty('${id}')">-</button>
                    <span class="fw-bold">${item.qty}</span>
                    <button class="btn btn-sm btn-outline-secondary px-2 py-0" onclick="tambahQty('${id}')">+</button>
                </div>
                <div class="fw-bold" style="width: 25%; text-align:right; font-size: 13px;">
                    ${formatRupiah(subtotal)}
                </div>
            </div>`;
        }

        if (cartChanged) {
            saveCart();
        }

        currentTotal = total;
        totalBayarText.innerText = formatRupiah(total);
        hitungKembalian();

        if (count > 0) {
            cartList.innerHTML = html;
            emptyCart.style.display = 'none';
            btnBayar.disabled = false;
        } else {
            cartList.innerHTML = emptyCart.outerHTML;
            document.getElementById('emptyCart').style.display = 'block';
            btnBayar.disabled = true;
        }
    }

    function saveCart() {
        localStorage.setItem('pos_cart', JSON.stringify(cart));
    }

    function prosesPembayaran() {
        if (Object.keys(cart).length === 0) return;

        const nama = document.getElementById('nama_penerima').value;
        const nomor_wa = document.getElementById('nomor_wa').value;
        const metode = document.getElementById('metode_pembayaran').value;
        const is_preorder = document.getElementById('is_preorder').checked;
        const tanggal_diambil = document.getElementById('tanggal_diambil').value;
        const uang_muka = document.getElementById('uang_muka').value;

        if (is_preorder && !tanggal_diambil) {
            Swal.fire('Error', 'Tanggal pengambilan harus diisi untuk Pre-Order.', 'error');
            return;
        }
        
        // Format array untuk dikirim
        const cartArray = Object.values(cart).map(item => ({
            id: item.id,
            varian_id: item.varian_id,
            qty: item.qty,
            addons: item.addons
        }));

        const btnBayar = document.getElementById('btnBayar');
        btnBayar.disabled = true;
        btnBayar.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-2"></i> MEMPROSES...';

        fetch("{{ route('pos.store') }}", {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                nama_penerima: nama,
                nomor_wa: nomor_wa,
                metode_pembayaran: metode,
                is_preorder: is_preorder,
                tanggal_diambil: tanggal_diambil,
                uang_muka: uang_muka,
                cart: cartArray
            })
        })
        .then(res => res.json())
        .then(data => {
            if (data.status === 'success') {
                // Tampilkan struk dalam Modal (Pop up)
                document.getElementById('printIframe').src = "{{ url('pos/print') }}/" + data.pesanan_id;
                var printModal = new bootstrap.Modal(document.getElementById('printModal'));
                printModal.show();
            } else {
                Swal.fire('Error', data.message, 'error');
                btnBayar.disabled = false;
                btnBayar.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i> BAYAR SEKARANG';
            }
        })
        .catch(err => {
            Swal.fire('Error', 'Terjadi kesalahan sistem.', 'error');
            btnBayar.disabled = false;
            btnBayar.innerHTML = '<i class="fa-solid fa-check-circle me-2"></i> BAYAR SEKARANG';
        });
    }

    function togglePreorderInputs() {
        const isPreorder = document.getElementById('is_preorder').checked;
        const preorderInputs = document.getElementById('preorder_inputs');
        if (isPreorder) {
            preorderInputs.classList.remove('d-none');
            document.getElementById('uangDiterimaContainer').classList.add('d-none');
            document.getElementById('kembalianContainer').classList.add('d-none');
        } else {
            preorderInputs.classList.add('d-none');
            document.getElementById('tanggal_diambil').value = '';
            document.getElementById('uang_muka').value = '';
            toggleKembalian(); // Restore cash logic
        }
    }

    function toggleKembalian() {
        const isPreorder = document.getElementById('is_preorder').checked;
        if (isPreorder) return; // Skip if preorder is active

        const metode = document.getElementById('metode_pembayaran').value;
        const uangContainer = document.getElementById('uangDiterimaContainer');
        const kembalianContainer = document.getElementById('kembalianContainer');

        if (metode === 'cash') {
            uangContainer.classList.remove('d-none');
            hitungKembalian();
        } else {
            uangContainer.classList.add('d-none');
            kembalianContainer.classList.add('d-none');
            document.getElementById('uang_diterima').value = '';
        }
    }

    function hitungKembalian() {
        if (document.getElementById('metode_pembayaran').value !== 'cash') return;
        
        const uangDiterima = parseFloat(document.getElementById('uang_diterima').value) || 0;
        const kembalianContainer = document.getElementById('kembalianContainer');
        const kembalianText = document.getElementById('kembalianText');

        if (uangDiterima > 0) {
            kembalianContainer.classList.remove('d-none');
            const kembalian = uangDiterima - currentTotal;
            if (kembalian < 0) {
                kembalianText.innerText = "Kurang " + formatRupiah(Math.abs(kembalian));
                kembalianText.classList.remove('text-success');
                kembalianText.classList.add('text-danger');
            } else {
                kembalianText.innerText = formatRupiah(kembalian);
                kembalianText.classList.remove('text-danger');
                kembalianText.classList.add('text-success');
            }
        } else {
            kembalianContainer.classList.add('d-none');
        }
    }

    function closePrintModal() {
        // Reload halaman untuk mereset kasir setelah selesai transaksi
        window.location.reload();
    }
</script>

<!-- Modal Print (Pop Up) -->
<div class="modal fade" id="printModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 justify-content-center bg-success text-white py-3">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-check-circle me-2"></i>Transaksi Berhasil</h5>
            </div>
            <div class="modal-body p-0 text-center bg-light">
                <!-- Iframe untuk menampilkan struk -->
                <iframe id="printIframe" style="width: 100%; height: 65vh; border: none; background: #fff;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection
