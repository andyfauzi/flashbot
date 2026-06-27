@extends('layouts.app')

@section('title', 'Master Bahan Baku')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-boxes-stacked me-2"></i> Master Bahan Baku
        </h2>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalBahanBaku">
            <i class="fa-solid fa-plus me-1"></i> Tambah Bahan
        </button>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No</th>
                            <th>Nama Bahan</th>
                            <th>Kategori</th>
                            <th class="text-end text-primary">Stok Aktual</th>
                            <th>Satuan Beli</th>
                            <th class="text-end">Harga Beli</th>
                            <th class="text-end">Qty Beli</th>
                            <th class="text-end text-success">Harga / Satuan Terkecil</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($bahan as $index => $b)
                        <tr>
                            <td class="ps-4">{{ $index + 1 }}</td>
                            <td class="fw-bold">{{ $b->nama_bahan }}</td>
                            <td>
                                @if($b->kategori == 'packaging')
                                    <span class="badge bg-warning text-dark"><i class="fa-solid fa-box-open me-1"></i> Packaging</span>
                                @else
                                    <span class="badge bg-primary text-white"><i class="fa-solid fa-leaf me-1"></i> Bahan Baku</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold text-primary">{{ number_format($b->stok, 2, ',', '.') }} {{ $b->satuan }}</td>
                            <td>{{ $b->satuan }}</td>
                            <td class="text-end">Rp {{ number_format($b->harga_beli, 0, ',', '.') }}</td>
                            <td class="text-end">{{ $b->qty_beli }} {{ $b->satuan }}</td>
                            <td class="text-end fw-bold text-success">Rp {{ number_format($b->harga_per_unit, 2, ',', '.') }} / {{ $b->satuan }}</td>
                            <td class="text-center pe-4" style="min-width: 180px;">
                                <button class="btn btn-sm btn-outline-info me-1" title="Koreksi Stok Awal" onclick="koreksiStok({{ $b->id }}, '{{ $b->nama_bahan }}', '{{ $b->satuan }}', {{ $b->stok }})">
                                    <i class="fa-solid fa-scale-balanced"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-success me-1" title="Restock / Tambah Stok" onclick="restockBahan({{ $b->id }}, '{{ $b->nama_bahan }}', '{{ $b->satuan }}')">
                                    <i class="fa-solid fa-plus"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-warning me-1" title="Lapor Bahan Rusak/Susut" onclick="rusakBahan({{ $b->id }}, '{{ $b->nama_bahan }}', '{{ $b->satuan }}', {{ $b->stok }})">
                                    <i class="fa-solid fa-heart-crack"></i>
                                </button>
                                <button class="btn btn-sm btn-outline-primary me-1" title="Edit Master" onclick="editBahan({{ $b->id }}, '{{ $b->nama_bahan }}', '{{ $b->kategori }}', '{{ $b->satuan }}', {{ $b->harga_beli }}, {{ $b->qty_beli }})">
                                    <i class="fa-solid fa-edit"></i>
                                </button>
                                <form action="{{ route('dashboard.hpp.bahan.destroy', $b->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus bahan baku ini?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Master">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="7" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fa-3x mb-3 text-light"></i>
                                <h5>Belum ada data bahan baku</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit -->
<div class="modal fade" id="modalBahanBaku" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formBahanBaku" action="{{ route('dashboard.hpp.bahan.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-box text-primary me-2"></i> Tambah Bahan Baku</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bahan / Item</label>
                        <input type="text" name="nama_bahan" id="nama_bahan" class="form-control" placeholder="Contoh: Tepung Terigu / Paper Cup" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kategori</label>
                        <select name="kategori" id="kategori" class="form-select" required>
                            <option value="bahan_baku">Bahan Baku (Bahan Makanan/Minuman)</option>
                            <option value="packaging">Packaging (Gelas, Sedotan, Plastik)</option>
                        </select>
                    </div>
                    <div class="row">
                        @php
                            $uniqueAwal = $konversis->pluck('satuan_awal')->unique();
                            $uniqueAkhir = $konversis->pluck('satuan_akhir')->unique();
                            $allUnits = $uniqueAwal->merge($uniqueAkhir)->unique();
                        @endphp
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Kuantitas per Beli</label>
                            <div class="input-group">
                                <input type="number" step="0.01" id="qty_beli_input" class="form-control" placeholder="Contoh: 1000" required>
                                <select id="qty_beli_unit" class="form-select" style="max-width: 140px;">
                                    @foreach($allUnits as $unit)
                                        <option value="{{ $unit }}">{{ $unit }}</option>
                                    @endforeach
                                    <option value="custom">Custom (Dos/Pack)</option>
                                </select>
                            </div>
                            <div id="qty_beli_custom_group" class="input-group mt-2 d-none">
                                <span class="input-group-text small bg-light">Isi per Karton/Dos:</span>
                                <input type="number" step="0.01" id="qty_beli_custom_multiplier" class="form-control" value="1" min="1">
                            </div>
                            <input type="hidden" name="qty_beli" id="qty_beli">
                            <small class="text-success mt-1 d-block fw-bold" id="qty_beli_helper"></small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Satuan Terkecil</label>
                            <select name="satuan" id="satuan" class="form-select" required>
                                @foreach($allUnits as $unit)
                                    <option value="{{ $unit }}">{{ $unit }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Harga Beli (Total)</label>
                        <input type="number" name="harga_beli" id="harga_beli" class="form-control" placeholder="Contoh: 15000" required min="0">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Restock -->
<div class="modal fade" id="modalRestock" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formRestock" action="" method="POST">
                @csrf
                <div class="modal-header bg-success-subtle border-bottom-0">
                    <h5 class="modal-title fw-bold text-success"><i class="fa-solid fa-cart-plus me-2"></i> Restock <span id="restockNamaBahan"></span></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-warning mb-3">
                        <i class="fa-solid fa-circle-info me-2"></i> Pengeluaran kas akan dicatat otomatis.
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Qty Beli Tambahan</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="restockInput" class="form-control form-control-lg" required min="0.01">
                            <select id="restockUnit" class="form-select" style="max-width: 140px;"></select>
                        </div>
                        <div id="restockCustomGroup" class="input-group mt-2 d-none">
                            <span class="input-group-text small bg-light">Isi per Karton/Dos:</span>
                            <input type="number" step="0.01" id="restockCustomMultiplier" class="form-control" value="1" min="1">
                        </div>
                        <input type="hidden" name="qty_beli" id="restockQtyReal">
                        <small class="text-success mt-1 d-block fw-bold" id="restockHelper"></small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Harga Beli Total (Rp)</label>
                        <input type="number" name="harga_beli" class="form-control form-control-lg" required min="0">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Beli & Catat Kas</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Bahan Rusak -->
<div class="modal fade" id="modalRusak" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formRusak" action="" method="POST">
                @csrf
                <div class="modal-header bg-warning-subtle border-bottom-0">
                    <h5 class="modal-title fw-bold text-warning-emphasis"><i class="fa-solid fa-heart-crack me-2"></i> Lapor Bahan Rusak/Susut</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-danger mb-3">
                        Stok Saat Ini: <strong id="rusakStokSekarang"></strong> <span id="rusakSatuanCurrent"></span>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Rusak/Susut (<span id="rusakSatuan"></span>)</label>
                        <input type="number" step="0.01" name="qty_rusak" id="qty_rusak" class="form-control form-control-lg border-danger" required min="0.01">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Keterangan / Alasan</label>
                        <input type="text" name="alasan" class="form-control" placeholder="Contoh: Susu basi, tumpah, dll" required>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-danger fw-bold px-4">Susutkan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Koreksi Stok -->
<div class="modal fade" id="modalKoreksi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formKoreksi" action="" method="POST">
                @csrf
                <div class="modal-header bg-info-subtle border-bottom-0">
                    <h5 class="modal-title fw-bold text-info-emphasis"><i class="fa-solid fa-scale-balanced me-2"></i> Koreksi Stok Aktual</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="alert alert-info mb-3 small">
                        <i class="fa-solid fa-circle-info me-1"></i> Gunakan ini untuk menyesuaikan stok saat pertama kali menggunakan aplikasi (Stok Opname). <strong>Aksi ini TIDAK akan dicatat ke Arus Kas.</strong>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nama Bahan</label>
                        <input type="text" id="koreksiNamaBahan" class="form-control" disabled>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Stok Aktual di Gudang</label>
                        <div class="input-group">
                            <input type="number" step="0.01" id="koreksiInput" class="form-control form-control-lg border-info" required min="0">
                            <select id="koreksiUnit" class="form-select border-info" style="max-width: 140px;"></select>
                        </div>
                        <div id="koreksiCustomGroup" class="input-group mt-2 d-none">
                            <span class="input-group-text small bg-light border-info">Isi per Karton/Dos:</span>
                            <input type="number" step="0.01" id="koreksiCustomMultiplier" class="form-control border-info" value="1" min="1">
                        </div>
                        <input type="hidden" name="stok_aktual" id="koreksiQtyReal">
                        <small class="text-info mt-1 d-block fw-bold" id="koreksiHelper"></small>
                        <small class="text-muted mt-1 d-block">Stok sebelumnya di sistem: <span id="koreksiStokLama"></span></small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold text-white px-4">Simpan Stok</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    let modalBahanBakuInitialized = false;

    function editBahan(id, nama, kategori, satuan, harga_beli, qty_beli) {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-edit text-primary me-2"></i> Edit Bahan Baku';
        document.getElementById('formBahanBaku').action = '/dashboard/hpp/bahan/' + id;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('nama_bahan').value = nama;
        document.getElementById('kategori').value = kategori;
        document.getElementById('satuan').value = satuan;
        document.getElementById('harga_beli').value = harga_beli;
        
        // Handle unit calculator inputs
        // For editing, we set the input unit to the base unit itself (e.g., Gram -> gram), so multiplier is 1
        const unitSelect = document.getElementById('qty_beli_unit');
        unitSelect.value = satuan; // since our static select has 'gram', 'ml', 'pcs', 'butir', etc.
        document.getElementById('qty_beli_input').value = qty_beli;
        updateModalBahanBakuCalculator();

        new bootstrap.Modal(document.getElementById('modalBahanBaku')).show();
    }

    function getConversionOptions(baseSatuan) {
        if (baseSatuan === 'gram') return `<option value="1">Gram (g)</option><option value="1000">Kilogram (Kg)</option><option value="custom">Custom (Dos/Sak)</option>`;
        if (baseSatuan === 'ml') return `<option value="1">Mililiter (ml)</option><option value="1000">Liter (L)</option><option value="19000">Galon</option><option value="custom">Custom (Karton)</option>`;
        return `<option value="1">${baseSatuan.charAt(0).toUpperCase() + baseSatuan.slice(1)}</option><option value="custom">Karton/Dos/Pack</option>`;
    }

    function initConversion(inputId, selectId, realId, helperId, customGroupId, customInputId, baseSatuan) {
        const input = document.getElementById(inputId);
        const select = document.getElementById(selectId);
        const real = document.getElementById(realId);
        const helper = document.getElementById(helperId);
        const customGroup = document.getElementById(customGroupId);
        const customInput = document.getElementById(customInputId);

        input.dataset.baseSatuan = baseSatuan;
        select.innerHTML = getConversionOptions(baseSatuan);
        
        if (input.dataset.initialized) {
            input.dispatchEvent(new Event('input'));
            return;
        }
        input.dataset.initialized = 'true';

        function calculate() {
            const currentBaseSatuan = input.dataset.baseSatuan;
            const val = parseFloat(input.value) || 0;
            let multiplier = 1;
            
            if (select.value === 'custom') {
                customGroup.classList.remove('d-none');
                multiplier = parseFloat(customInput.value) || 1;
            } else {
                customGroup.classList.add('d-none');
                multiplier = parseFloat(select.value) || 1;
            }

            const total = val * multiplier;
            real.value = total;
            
            if (val > 0) {
                helper.innerHTML = `<i class="fa-solid fa-check me-1"></i> Disimpan sebagai: ${total} ${currentBaseSatuan}`;
            } else {
                helper.innerHTML = '';
            }
        }

        input.addEventListener('input', calculate);
        select.addEventListener('change', calculate);
        customInput.addEventListener('input', calculate);
        calculate(); // initial calculation
    }

    function restockBahan(id, nama, satuan) {
        document.getElementById('restockNamaBahan').innerText = nama;
        document.getElementById('formRestock').action = '/dashboard/hpp/bahan/' + id + '/restock';
        document.getElementById('restockInput').value = '';
        document.getElementById('restockCustomMultiplier').value = '1';
        initConversion('restockInput', 'restockUnit', 'restockQtyReal', 'restockHelper', 'restockCustomGroup', 'restockCustomMultiplier', satuan);
        new bootstrap.Modal(document.getElementById('modalRestock')).show();
    }

    function rusakBahan(id, nama, satuan, stok) {
        document.getElementById('rusakStokSekarang').innerText = stok;
        document.getElementById('rusakSatuan').innerText = satuan;
        document.getElementById('rusakSatuanCurrent').innerText = satuan;
        document.getElementById('qty_rusak').setAttribute('max', stok);
        document.getElementById('formRusak').action = '/dashboard/hpp/bahan/' + id + '/rusak';
        new bootstrap.Modal(document.getElementById('modalRusak')).show();
    }

    function koreksiStok(id, nama, satuan, stok) {
        document.getElementById('koreksiNamaBahan').value = nama;
        document.getElementById('koreksiStokLama').innerText = stok + ' ' + satuan;
        document.getElementById('formKoreksi').action = '/dashboard/hpp/bahan/' + id + '/koreksi';
        document.getElementById('koreksiInput').value = stok;
        document.getElementById('koreksiCustomMultiplier').value = '1';
        initConversion('koreksiInput', 'koreksiUnit', 'koreksiQtyReal', 'koreksiHelper', 'koreksiCustomGroup', 'koreksiCustomMultiplier', satuan);
        new bootstrap.Modal(document.getElementById('modalKoreksi')).show();
    }

    document.getElementById('modalBahanBaku').addEventListener('hidden.bs.modal', function () {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-box text-primary me-2"></i> Tambah Bahan Baku';
        document.getElementById('formBahanBaku').action = '{{ route("dashboard.hpp.bahan.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('formBahanBaku').reset();
        document.getElementById('satuan').dispatchEvent(new Event('change'));
        document.getElementById('qty_beli_input').dispatchEvent(new Event('input'));
    });

    const dbKonversis = @json($konversis);
    const unitMapping = {
        'custom': { base: 'custom', multiplier: 'custom' }
    };

    // Build fallback for 1:1 conversions (e.g., Gram -> Gram, Pcs -> Pcs)
    const uniqueAwal = [...new Set(dbKonversis.map(k => k.satuan_awal))];
    const uniqueAkhir = [...new Set(dbKonversis.map(k => k.satuan_akhir))];
    const allUniqueUnits = [...new Set([...uniqueAwal, ...uniqueAkhir])];
    
    allUniqueUnits.forEach(unit => {
        unitMapping[unit] = { base: unit, multiplier: 1 };
    });

    // Overwrite with actual conversions from database
    dbKonversis.forEach(k => {
        unitMapping[k.satuan_awal] = { base: k.satuan_akhir, multiplier: parseFloat(k.nilai_konversi) };
    });

    function updateModalBahanBakuCalculator() {
        const input = document.getElementById('qty_beli_input');
        const unit = document.getElementById('qty_beli_unit');
        const real = document.getElementById('qty_beli');
        const helper = document.getElementById('qty_beli_helper');
        const customGroup = document.getElementById('qty_beli_custom_group');
        const customMultiplier = document.getElementById('qty_beli_custom_multiplier');
        const satuanSelect = document.getElementById('satuan');

        const val = parseFloat(input.value) || 0;
        const mapping = unitMapping[unit.value];
        let multiplier = 1;
        let baseSatuanText = '';

        if (mapping.base === 'custom') {
            customGroup.classList.remove('d-none');
            multiplier = parseFloat(customMultiplier.value) || 1;
            satuanSelect.disabled = false; // Let user choose base unit
            baseSatuanText = satuanSelect.options[satuanSelect.selectedIndex].text;
        } else {
            customGroup.classList.add('d-none');
            multiplier = mapping.multiplier;
            satuanSelect.value = mapping.base; // Auto-select base unit
            satuanSelect.disabled = true; // Lock the base unit
            baseSatuanText = satuanSelect.options[satuanSelect.selectedIndex].text;
        }

        const total = val * multiplier;
        real.value = total;

        if (val > 0) {
            helper.innerHTML = `<i class="fa-solid fa-check me-1"></i> Disimpan sebagai: ${total} ${baseSatuanText}`;
        } else {
            helper.innerHTML = '';
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Init modal bahan baku
        document.getElementById('qty_beli_input').addEventListener('input', updateModalBahanBakuCalculator);
        document.getElementById('qty_beli_unit').addEventListener('change', updateModalBahanBakuCalculator);
        document.getElementById('qty_beli_custom_multiplier').addEventListener('input', updateModalBahanBakuCalculator);
        document.getElementById('satuan').addEventListener('change', updateModalBahanBakuCalculator); // In case they change it when custom
        
        // Remove disabled attribute from satuan before submitting form
        document.getElementById('formBahanBaku').addEventListener('submit', function() {
            document.getElementById('satuan').disabled = false;
        });
    });
</script>
@endsection
