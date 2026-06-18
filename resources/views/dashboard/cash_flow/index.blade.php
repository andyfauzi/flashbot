@extends('layouts.app')

@section('title', 'Buku Kas & Laporan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="fw-bold m-0"><i class="fa-solid fa-file-invoice-dollar me-2 text-primary"></i>Buku Kas (Arus Kas)</h3>
        
        <form action="{{ route('dashboard.cash_flow.index') }}" method="GET" class="d-flex gap-2">
            <input type="month" name="bulan" class="form-control" value="{{ $bulan }}" onchange="this.form.submit()">
        </form>
    </div>

    <!-- Ringkasan Keuangan -->
    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="card bg-success text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-white-50 fw-bold">Total Pemasukan</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalPemasukan, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-danger text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-white-50 fw-bold">Total Pengeluaran</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($totalPengeluaran, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card bg-primary text-white h-100 shadow-sm border-0">
                <div class="card-body">
                    <h6 class="text-white-50 fw-bold">Laba / Saldo Bersih</h6>
                    <h3 class="fw-bold mb-0">Rp {{ number_format($labaBersih, 0, ',', '.') }}</h3>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- Form Tambah Transaksi Kas -->
        <div class="col-md-4 mb-4">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-plus-circle me-2 text-primary"></i>Catat Transaksi Manual</h6>
                </div>
                <div class="card-body">
                    <form action="{{ route('dashboard.cash_flow.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label">Tanggal</label>
                            <input type="date" name="tanggal" class="form-control" value="{{ date('Y-m-d') }}" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Tipe Transaksi</label>
                            <select name="tipe" class="form-select" required>
                                <option value="in">Pemasukan (Uang Masuk)</option>
                                <option value="out">Pengeluaran (Uang Keluar)</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Kategori</label>
                            <select id="kategori_select" name="kategori" class="form-select mb-2" required>
                                <option value="Gaji Karyawan">Gaji Karyawan</option>
                                <option value="Listrik & Air">Listrik & Air</option>
                                <option value="Sewa Tempat">Sewa Tempat</option>
                                <option value="Belanja Bahan Baku">Belanja Bahan Baku</option>
                                <option value="Lainnya">Lainnya (Ketik Sendiri)</option>
                            </select>
                            <input type="text" name="kategori" id="kat_lain" class="form-control d-none mb-2" placeholder="Tulis kategori..." disabled>
                        </div>

                        <!-- Opsi Khusus Belanja Bahan Baku -->
                        <div id="bahan_baku_options" class="p-3 bg-light rounded border mb-3 d-none">
                            <p class="mb-2 fw-bold text-success"><i class="fa-solid fa-box me-1"></i> Sinkronisasi Stok Bahan</p>
                            <div class="mb-2">
                                <label class="form-label small">Pilih Bahan Baku (Opsional)</label>
                                <select name="bahan_baku_id" id="bahan_baku_id" class="form-select form-select-sm">
                                    <option value="">-- Pilih Jika Ingin Menambah Stok --</option>
                                    @if(isset($bahanBaku))
                                        @foreach($bahanBaku as $b)
                                            <option value="{{ $b->id }}">{{ $b->nama_bahan }} (Stok: {{ $b->stok }} {{ $b->satuan }})</option>
                                        @endforeach
                                    @endif
                                </select>
                            </div>
                            <div class="mb-0">
                                <label class="form-label small">Qty Beli Tambahan</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" step="0.01" id="cfQtyInput" class="form-control" placeholder="Contoh: 10" min="0">
                                    <select id="cfUnitSelect" class="form-select" style="max-width: 140px;" disabled></select>
                                </div>
                                <div id="cfCustomGroup" class="input-group input-group-sm mt-2 d-none">
                                    <span class="input-group-text small bg-light">Isi per Karton/Dos:</span>
                                    <input type="number" step="0.01" id="cfCustomMultiplier" class="form-control" value="1" min="1">
                                </div>
                                <input type="hidden" name="qty_beli" id="cfQtyReal">
                                <small class="text-success mt-1 d-block fw-bold" id="cfHelper"></small>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Nominal (Rp)</label>
                            <input type="number" name="nominal" class="form-control" placeholder="Contoh: 500000" min="0" required>
                        </div>
                        <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan" class="form-control" rows="2" placeholder="Catatan opsional..."></textarea>
                        </div>
                        <button type="submit" class="btn btn-primary w-100 fw-bold">Simpan Transaksi</button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Transaksi Kas -->
        <div class="col-md-8">
            <div class="card shadow-sm border-0">
                <div class="card-header bg-white">
                    <h6 class="fw-bold m-0"><i class="fa-solid fa-table-list me-2 text-primary"></i>Riwayat Arus Kas ({{ \Carbon\Carbon::parse($bulan)->translatedFormat('F Y') }})</h6>
                </div>
                <div class="card-body p-0">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle mb-0">
                            <thead class="table-light">
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Kategori</th>
                                    <th>Keterangan</th>
                                    <th>Kasir/Admin</th>
                                    <th class="text-end">Nominal</th>
                                    <th></th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse($cashFlows as $cf)
                                <tr>
                                    <td>{{ $cf->tanggal->format('d/m/Y') }}</td>
                                    <td>
                                        <span class="badge {{ $cf->tipe == 'in' ? 'bg-success-subtle text-success' : 'bg-danger-subtle text-danger' }}">
                                            <i class="fa-solid {{ $cf->tipe == 'in' ? 'fa-arrow-down' : 'fa-arrow-up' }} me-1"></i> {{ $cf->kategori }}
                                        </span>
                                    </td>
                                    <td><small class="text-muted">{{ $cf->keterangan ?? '-' }}</small></td>
                                    <td><small>{{ $cf->user->name ?? 'Sistem' }}</small></td>
                                    <td class="text-end fw-bold {{ $cf->tipe == 'in' ? 'text-success' : 'text-danger' }}">
                                        {{ $cf->tipe == 'in' ? '+' : '-' }} Rp {{ number_format($cf->nominal, 0, ',', '.') }}
                                    </td>
                                    <td class="text-end">
                                        <form action="{{ route('dashboard.cash_flow.destroy', $cf->id) }}" method="POST" onsubmit="return confirm('Hapus data kas ini? Total akan berubah.')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm text-danger"><i class="fa-solid fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="text-center py-4 text-muted">Belum ada data arus kas di bulan ini.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.getElementById('kategori_select').addEventListener('change', function() {
        var katLain = document.getElementById('kat_lain');
        var bbOptions = document.getElementById('bahan_baku_options');
        var bbId = document.getElementById('bahan_baku_id');
        var bbQty = document.getElementById('qty_beli');

        // Toggle Lainnya
        if(this.value == 'Lainnya') {
            katLain.classList.remove('d-none');
            katLain.removeAttribute('disabled');
            this.removeAttribute('name');
        } else {
            katLain.classList.add('d-none');
            katLain.setAttribute('disabled', 'disabled');
            this.setAttribute('name', 'kategori');
        }

        // Toggle Bahan Baku
        if(this.value == 'Belanja Bahan Baku') {
            bbOptions.classList.remove('d-none');
        } else {
            bbOptions.classList.add('d-none');
            bbId.value = '';
            document.getElementById('cfQtyInput').value = '';
            document.getElementById('cfQtyReal').value = '';
            document.getElementById('cfHelper').innerHTML = '';
        }
    });

    const cfInput = document.getElementById('cfQtyInput');
    const cfSelect = document.getElementById('cfUnitSelect');
    const cfReal = document.getElementById('cfQtyReal');
    const cfHelper = document.getElementById('cfHelper');
    const cfCustomGroup = document.getElementById('cfCustomGroup');
    const cfCustomMultiplier = document.getElementById('cfCustomMultiplier');
    let currentBaseSatuan = '';

    function calculateCf() {
        const val = parseFloat(cfInput.value) || 0;
        let multiplier = 1;
        
        if (cfSelect.value === 'custom') {
            cfCustomGroup.classList.remove('d-none');
            multiplier = parseFloat(cfCustomMultiplier.value) || 1;
        } else {
            cfCustomGroup.classList.add('d-none');
            multiplier = parseFloat(cfSelect.value) || 1;
        }

        const total = val * multiplier;
        cfReal.value = total;
        
        if (val > 0 && currentBaseSatuan) {
            cfHelper.innerHTML = `<i class="fa-solid fa-arrow-right-arrow-left me-1"></i> Disimpan sebagai: ${total} ${currentBaseSatuan}`;
        } else {
            cfHelper.innerHTML = '';
        }
    }

    cfInput.addEventListener('input', calculateCf);
    cfSelect.addEventListener('change', calculateCf);
    cfCustomMultiplier.addEventListener('input', calculateCf);

    // Validasi Qty Beli wajib jika Bahan dipilih dan set dropdown konversi
    document.getElementById('bahan_baku_id').addEventListener('change', function() {
        if(this.value != '') {
            cfInput.setAttribute('required', 'required');
            cfSelect.removeAttribute('disabled');
            cfCustomMultiplier.value = '1';
            
            // Ambil text option yang dipilih untuk mencari satuan
            const text = this.options[this.selectedIndex].text;
            
            if(text.includes('gram)')) {
                currentBaseSatuan = 'gram';
                cfSelect.innerHTML = `<option value="1">Gram (g)</option><option value="1000">Kilogram (Kg)</option><option value="custom">Custom (Dos/Sak)</option>`;
            } else if(text.includes('ml)')) {
                currentBaseSatuan = 'ml';
                cfSelect.innerHTML = `<option value="1">Mililiter (ml)</option><option value="1000">Liter (L)</option><option value="custom">Custom (Karton)</option>`;
            } else {
                currentBaseSatuan = 'pcs';
                cfSelect.innerHTML = `<option value="1">Pieces (pcs)</option><option value="custom">Karton/Dos/Pack</option>`;
            }
            calculateCf();
        } else {
            cfInput.removeAttribute('required');
            cfSelect.setAttribute('disabled', 'disabled');
            cfSelect.innerHTML = '';
            currentBaseSatuan = '';
            cfHelper.innerHTML = '';
            cfCustomGroup.classList.add('d-none');
        }
    });
</script>
@endsection
