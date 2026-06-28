<!-- Modal Manual PO -->
<div class="modal fade" id="modalManualPO" tabindex="-1" aria-hidden="true" data-bs-backdrop="static">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
        <div class="modal-content border-0 shadow-lg">
            <div class="modal-header border-0 bg-primary text-white">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-pen-to-square me-2"></i> Input Pre-Order Manual</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <form action="{{ route('dashboard.preorder.store_manual') }}" method="POST" id="formManualPO">
                    @csrf
                    
                    <div class="row g-4">
                        <!-- Left Column: Customer & Delivery Info -->
                        <div class="col-lg-5">
                            <div class="card border-0 shadow-sm rounded-4 h-100">
                                <div class="card-body">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-user me-2"></i>Data Pelanggan</h6>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Nama Pelanggan <span class="text-danger">*</span></label>
                                        <input type="text" name="nama_penerima" class="form-control" required placeholder="Contoh: Budi">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">No. WhatsApp</label>
                                        <input type="text" name="nomor_wa" class="form-control" placeholder="Contoh: 08123456789">
                                    </div>
                                    
                                    <h6 class="fw-bold text-primary mb-3 mt-4"><i class="fa-solid fa-truck me-2"></i>Pengambilan</h6>
                                    
                                    <div class="row g-2 mb-3">
                                        <div class="col-6">
                                            <label class="form-label fw-bold small text-muted">Tanggal <span class="text-danger">*</span></label>
                                            <input type="date" name="tanggal_diambil" class="form-control" required value="{{ date('Y-m-d') }}">
                                        </div>
                                        <div class="col-6">
                                            <label class="form-label fw-bold small text-muted">Jam <span class="text-danger">*</span></label>
                                            <input type="time" name="jam_diambil" class="form-control" required value="10:00">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Tipe Pengiriman <span class="text-danger">*</span></label>
                                        <select name="tipe_pengiriman" class="form-select" id="tipePengirimanManual" onchange="toggleAlamatManual()" required>
                                            <option value="ambil_sendiri">Ambil Sendiri (Takeaway)</option>
                                            <option value="kurir_toko">Diantar (Kurir Toko)</option>
                                        </select>
                                    </div>
                                    
                                    <div class="mb-3" id="groupAlamatManual" style="display: none;">
                                        <label class="form-label fw-bold small text-muted">Alamat Lengkap <span class="text-danger">*</span></label>
                                        <textarea name="alamat_penerima" id="inputAlamatManual" class="form-control" rows="2" placeholder="Jalan..."></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-muted">Uang Muka (DP) Rp</label>
                                        <input type="number" name="uang_muka" class="form-control fw-bold text-success" value="0" min="0" step="1000">
                                        <small class="text-muted">Biarkan 0 jika belum DP.</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Right Column: Product Selection & Cart -->
                        <div class="col-lg-7">
                            <div class="card border-0 shadow-sm rounded-4 h-100 d-flex flex-column">
                                <div class="card-body d-flex flex-column">
                                    <h6 class="fw-bold text-primary mb-3"><i class="fa-solid fa-utensils me-2"></i>Daftar Pesanan</h6>
                                    
                                    <!-- Product Adder -->
                                    <div class="p-3 bg-white border rounded-3 mb-3">
                                        <div class="row row-cols-3 g-2 mb-3" style="max-height: 250px; overflow-y: auto;" id="gridProdukManual">
                                            @foreach($semuaProduk as $p)
                                                <div class="col">
                                                    <div class="card h-100 border product-card-manual shadow-sm" style="cursor: pointer; transition: 0.2s;" onclick="selectProdukManual(this, '{{ $p->id }}', {{ json_encode($p) }})" id="cardProduk_{{ $p->id }}">
                                                       @if($p->foto)
                                                           <img src="{{ asset('storage/' . $p->foto) }}" class="card-img-top object-fit-cover" alt="{{ $p->nama }}" style="height: 80px;">
                                                       @else
                                                           <div class="card-img-top bg-light d-flex align-items-center justify-content-center text-secondary" style="height: 80px;">
                                                               <i class="fa-solid fa-image fa-2x opacity-25"></i>
                                                           </div>
                                                       @endif
                                                       <div class="card-body p-2 text-center d-flex flex-column justify-content-center">
                                                           <div class="fw-bold small lh-sm mb-1" style="font-size: 0.8rem;">{{ $p->nama }}</div>
                                                           <div class="text-primary fw-bold" style="font-size: 0.75rem;">Rp {{ number_format($p->harga, 0, ',', '.') }}</div>
                                                       </div>
                                                    </div>
                                                </div>
                                            @endforeach
                                        </div>
                                        <input type="hidden" id="selectedProdukManualId" value="">
                                        <input type="hidden" id="selectedProdukManualJson" value="">
                                        
                                        <div class="p-2 border rounded bg-light" id="optionsContainerManual" style="display: none;">
                                            <div class="d-flex justify-content-between align-items-center mb-2 border-bottom pb-2">
                                                <span class="fw-bold text-dark" id="selectedProdukNameLabel">Pilih Varian & Add-on</span>
                                                <button type="button" class="btn btn-sm btn-outline-secondary py-0" onclick="resetProdukSelection()">Batal</button>
                                            </div>
                                            <div class="row g-2 align-items-end">
                                                <div class="col-md-6" id="varianContainer" style="display:none;">
                                                    <label class="form-label fw-bold small text-muted">Varian</label>
                                                    <select id="selectVarianManual" class="form-select"></select>
                                                </div>
                                                <div class="col-md-3">
                                                    <label class="form-label fw-bold small text-muted">Qty</label>
                                                    <input type="number" id="inputQtyManual" class="form-control text-center fw-bold" value="1" min="1">
                                                </div>
                                                <div class="col-md-3 text-end">
                                                    <button type="button" class="btn btn-primary w-100 fw-bold" onclick="addManualItem()">
                                                        <i class="fa-solid fa-plus"></i> Add
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Addons Container -->
                                            <div id="addonContainer" class="mt-3" style="display:none;">
                                                <label class="form-label fw-bold small text-muted d-block border-bottom pb-1 mb-2">Add-ons Tambahan</label>
                                                <div id="addonCheckboxes" class="d-flex flex-wrap gap-2"></div>
                                            </div>
                                            
                                            <div class="mt-3">
                                                <input type="text" id="inputCatatanManual" class="form-control form-control-sm" placeholder="Catatan opsional (pedas, dll)...">
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Cart List -->
                                    <div class="flex-grow-1 overflow-auto bg-light rounded-3 p-2 border" style="min-height: 200px; max-height: 300px;">
                                        <div id="manualCartEmpty" class="text-center text-muted py-5">
                                            <i class="fa-solid fa-basket-shopping fs-3 mb-2 opacity-50"></i><br>
                                            Belum ada produk ditambahkan
                                        </div>
                                        <div id="manualCartList" class="d-flex flex-column gap-2"></div>
                                    </div>
                                    
                                    <div class="mt-3 pt-3 border-top d-flex justify-content-between align-items-center">
                                        <span class="fw-bold text-muted">Total Biaya Barang:</span>
                                        <h4 class="fw-bold text-primary mb-0" id="manualCartTotal">Rp 0</h4>
                                    </div>
                                    
                                    <!-- Hidden input area for submitted form -->
                                    <div id="hiddenItemsArea"></div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer border-0 bg-white">
                <button type="button" class="btn btn-light rounded-pill px-4 fw-bold" data-bs-dismiss="modal">Batal</button>
                <button type="button" class="btn btn-primary rounded-pill px-5 fw-bold shadow-sm" onclick="submitManualPO()">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Pesanan PO
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    let manualCart = [];
    
    function toggleAlamatManual() {
        const val = document.getElementById('tipePengirimanManual').value;
        const group = document.getElementById('groupAlamatManual');
        const input = document.getElementById('inputAlamatManual');
        if (val === 'ambil_sendiri') {
            group.style.display = 'none';
            input.required = false;
        } else {
            group.style.display = 'block';
            input.required = true;
        }
    }
    
    function selectProdukManual(element, produkId, produkObj) {
        // Remove highlight from all cards
        document.querySelectorAll('.product-card-manual').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary-subtle');
            card.classList.add('border');
        });
        
        // Add highlight to selected
        element.classList.remove('border');
        element.classList.add('border-primary', 'bg-primary-subtle');
        
        // Set hidden inputs
        document.getElementById('selectedProdukManualId').value = produkId;
        document.getElementById('selectedProdukManualJson').value = JSON.stringify(produkObj);
        
        // Update Label
        document.getElementById('selectedProdukNameLabel').textContent = "Pilihan: " + produkObj.nama;
        
        loadProdukDetails(produkObj);
    }
    
    function resetProdukSelection() {
        document.querySelectorAll('.product-card-manual').forEach(card => {
            card.classList.remove('border-primary', 'bg-primary-subtle');
            card.classList.add('border');
        });
        document.getElementById('selectedProdukManualId').value = '';
        document.getElementById('selectedProdukManualJson').value = '';
        document.getElementById('optionsContainerManual').style.display = 'none';
        document.getElementById('inputQtyManual').value = 1;
        document.getElementById('inputCatatanManual').value = '';
    }
    
    function loadProdukDetails(produk) {
        document.getElementById('optionsContainerManual').style.display = 'block';
        
        const varianContainer = document.getElementById('varianContainer');
        const selectVarian = document.getElementById('selectVarianManual');
        const addonContainer = document.getElementById('addonContainer');
        const addonCheckboxes = document.getElementById('addonCheckboxes');
        
        // Reset
        varianContainer.style.display = 'none';
        selectVarian.innerHTML = '';
        addonContainer.style.display = 'none';
        addonCheckboxes.innerHTML = '';
        
        // Load Varians
        if (produk.varians && produk.varians.length > 0) {
            varianContainer.style.display = 'block';
            produk.varians.forEach(v => {
                const opt = document.createElement('option');
                opt.value = v.id;
                const harga = Number(v.harga || 0);
                opt.textContent = `${v.nama_varian} (+Rp ${harga.toLocaleString('id-ID')})`;
                opt.setAttribute('data-harga', harga);
                opt.setAttribute('data-nama', v.nama_varian);
                selectVarian.appendChild(opt);
            });
        }
        
        // Load Addons
        if (produk.addons && produk.addons.length > 0) {
            addonContainer.style.display = 'block';
            produk.addons.forEach(a => {
                const id = `addon_${a.id}`;
                const harga = Number(a.harga || 0);
                addonCheckboxes.innerHTML += `
                    <div class="form-check form-check-inline border rounded px-2 py-1 bg-white mb-0 shadow-sm">
                        <input class="form-check-input ms-0 me-1 manual-addon-checkbox" type="checkbox" id="${id}" value="${a.id}" data-nama="${a.nama_addon}" data-harga="${harga}">
                        <label class="form-check-label small" style="cursor:pointer" for="${id}">${a.nama_addon} (+${harga.toLocaleString('id-ID')})</label>
                    </div>
                `;
            });
        }
    }
    
    function addManualItem() {
        const produkJson = document.getElementById('selectedProdukManualJson').value;
        if (!produkJson) return alert('Pilih produk dari daftar terlebih dahulu!');
        
        const produk = JSON.parse(produkJson);
        const qty = parseInt(document.getElementById('inputQtyManual').value) || 1;
        const catatan = document.getElementById('inputCatatanManual').value;
        
        let item = {
            id: 'item_' + Date.now(),
            produk_id: produk.id,
            nama_produk: produk.nama,
            qty: qty,
            catatan: catatan,
            harga_satuan: produk.harga,
            varian_id: null,
            nama_varian: null,
            addons: [],
            addons_text: '',
            subtotal: 0
        };
        
        // Check Varian
        const varianContainer = document.getElementById('varianContainer');
        if (varianContainer.style.display === 'block') {
            const selectVarian = document.getElementById('selectVarianManual');
            const optVarian = selectVarian.options[selectVarian.selectedIndex];
            if(optVarian){
                item.varian_id = optVarian.value;
                item.nama_varian = optVarian.getAttribute('data-nama');
                item.harga_satuan = parseFloat(optVarian.getAttribute('data-harga'));
            }
        }
        
        let addonsTotal = 0;
        let addonsName = [];
        // Check Addons
        const checkboxes = document.querySelectorAll('.manual-addon-checkbox:checked');
        checkboxes.forEach(cb => {
            item.addons.push(cb.value);
            const hargaAddon = parseFloat(cb.getAttribute('data-harga'));
            addonsTotal += hargaAddon;
            addonsName.push(cb.getAttribute('data-nama'));
        });
        
        if(addonsName.length > 0) {
            item.addons_text = addonsName.join(', ');
        }
        
        item.subtotal = (item.harga_satuan + addonsTotal) * qty;
        
        manualCart.push(item);
        
        resetProdukSelection();
        
        renderManualCart();
    }
    
    function removeManualItem(id) {
        manualCart = manualCart.filter(i => i.id !== id);
        renderManualCart();
    }
    
    function renderManualCart() {
        const list = document.getElementById('manualCartList');
        const empty = document.getElementById('manualCartEmpty');
        const totalEl = document.getElementById('manualCartTotal');
        
        list.innerHTML = '';
        let total = 0;
        
        if (manualCart.length === 0) {
            empty.style.display = 'block';
        } else {
            empty.style.display = 'none';
            manualCart.forEach(item => {
                total += item.subtotal;
                
                let varianHtml = item.nama_varian ? `<div class="small text-primary fw-semibold"><i class="fa-solid fa-tag me-1"></i>Varian: ${item.nama_varian}</div>` : '';
                let addonHtml = item.addons_text ? `<div class="small text-muted"><i class="fa-solid fa-plus me-1"></i>Add-on: ${item.addons_text}</div>` : '';
                let noteHtml = item.catatan ? `<div class="small text-warning"><i class="fa-solid fa-note-sticky me-1"></i>${item.catatan}</div>` : '';
                
                list.innerHTML += `
                    <div class="d-flex justify-content-between align-items-center bg-white p-2 rounded-3 border shadow-sm">
                        <div>
                            <div class="fw-bold">${item.nama_produk} <span class="badge bg-secondary ms-1 rounded-pill">${item.qty}x</span></div>
                            ${varianHtml}
                            ${addonHtml}
                            ${noteHtml}
                        </div>
                        <div class="text-end">
                            <div class="fw-bold mb-1 text-dark">Rp ${item.subtotal.toLocaleString('id-ID')}</div>
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" onclick="removeManualItem('${item.id}')"><i class="fa-solid fa-trash"></i></button>
                        </div>
                    </div>
                `;
            });
        }
        
        totalEl.textContent = 'Rp ' + total.toLocaleString('id-ID');
    }
    
    function submitManualPO() {
        if (manualCart.length === 0) {
            alert('Daftar pesanan kosong! Tambahkan minimal 1 produk.');
            return;
        }
        
        const form = document.getElementById('formManualPO');
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }
        
        const hiddenArea = document.getElementById('hiddenItemsArea');
        hiddenArea.innerHTML = '';
        
        manualCart.forEach(item => {
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'items[]';
            input.value = JSON.stringify(item);
            hiddenArea.appendChild(input);
        });
        
        form.submit();
    }
</script>
