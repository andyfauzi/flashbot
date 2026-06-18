@extends('layouts.app')

@section('title', 'Manufaktur & Produksi Harian')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-industry me-2"></i> Produksi Harian (Dapur)
        </h2>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row">
        <!-- Form Produksi -->
        <div class="col-md-5">
            <div class="card card-premium mb-4">
                <div class="card-header bg-white pb-0 border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fa-solid fa-clipboard-check me-2 text-primary"></i> Form Produksi</h5>
                    <p class="text-muted small">Input jumlah barang yang selesai diproduksi hari ini. Stok produk jadi akan bertambah, dan bahan baku akan terpotong sesuai resep.</p>
                </div>
                <div class="card-body px-4 pb-4">
                    <form action="{{ route('dashboard.produksi.store') }}" method="POST">
                        @csrf
                        <div class="mb-3">
                            <label class="form-label fw-bold">Pilih Produk (Made-to-Stock)</label>
                            <select class="form-select form-select-lg form-control-premium" name="produk_varian_id" id="produkSelect" required>
                                <option value="">-- Pilih Produk Jadi --</option>
                                @foreach($produks as $produk)
                                    @foreach($produk->varians as $varian)
                                        <option value="{{ $varian->id }}" data-resep="{{ json_encode($varian->resep->map(function($r) { return ['nama' => $r->bahanBaku->nama_bahan, 'qty' => $r->qty_dipakai, 'satuan' => $r->bahanBaku->satuan]; })) }}">
                                            {{ $produk->nama }} {{ $varian->nama_varian !== 'All Size' ? ' - ' . $varian->nama_varian : '' }}
                                        </option>
                                    @endforeach
                                @endforeach
                            </select>
                        </div>
                        
                        <div class="mb-4">
                            <label class="form-label fw-bold">Jumlah Diproduksi (Qty)</label>
                            <input type="number" name="qty_produksi" id="qtyInput" class="form-control form-control-lg form-control-premium" min="1" value="1" required>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-bold" onclick="return confirm('Apakah Anda yakin data produksi sudah benar? Proses ini akan memotong stok bahan baku secara permanen.')">
                            <i class="fa-solid fa-industry me-2"></i> Eksekusi Produksi
                        </button>
                    </form>
                </div>
            </div>
        </div>

        <!-- Preview Estimasi Potongan Bahan Baku -->
        <div class="col-md-7">
            <div class="card card-premium">
                <div class="card-header bg-white pb-0 border-0 pt-4 px-4">
                    <h5 class="fw-bold"><i class="fa-solid fa-calculator me-2 text-warning"></i> Estimasi Bahan Terpakai</h5>
                </div>
                <div class="card-body px-4 pb-4">
                    <div id="previewResep" class="text-center p-5 text-muted bg-light rounded-3">
                        <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Pilih produk di sebelah kiri untuk melihat rincian bahan baku yang akan dipotong otomatis.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Baris Baru untuk Produk Sedang Diproses (Dapur) & Validasi Gudang -->
    <div class="row mt-4">
        <div class="col-12">
            <div class="card card-premium">
                <div class="card-header bg-white pb-0 border-0 pt-4 px-4 d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold"><i class="fa-solid fa-hourglass-half me-2 text-info"></i> Produk Sedang Diproses di Dapur</h5>
                    <span class="badge bg-info text-dark fw-bold">Menunggu Validasi Gudang</span>
                </div>
                <div class="card-body px-4 pb-4">
                    <div class="table-responsive">
                        <table class="table table-hover align-middle border">
                            <thead class="bg-light">
                                <tr>
                                    <th>Produk / Varian</th>
                                    <th class="text-center">Jumlah Sedang Diproses</th>
                                    <th class="text-center">Aksi Gudang</th>
                                </tr>
                            </thead>
                            <tbody>
                                @php $hasProses = false; @endphp
                                @foreach($produks as $produk)
                                    @foreach($produk->varians as $varian)
                                        @if($varian->stok_proses_dapur > 0)
                                            @php $hasProses = true; @endphp
                                            <tr>
                                                <td>
                                                    <span class="fw-bold text-dark">{{ $produk->nama }}</span>
                                                    @if($varian->nama_varian !== 'All Size')
                                                        <span class="badge bg-secondary ms-1">{{ $varian->nama_varian }}</span>
                                                    @endif
                                                </td>
                                                <td class="text-center fw-bold text-info">
                                                    {{ $varian->stok_proses_dapur }} pcs
                                                </td>
                                                <td class="text-center">
                                                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 btn-validasi" 
                                                        data-id="{{ $varian->id }}"
                                                        data-nama="{{ $produk->nama }}{{ $varian->nama_varian !== 'All Size' ? ' - ' . $varian->nama_varian : '' }}"
                                                        data-max="{{ $varian->stok_proses_dapur }}">
                                                        <i class="fa-solid fa-square-check me-1"></i> Validasi Gudang
                                                    </button>
                                                </td>
                                            </tr>
                                        @endif
                                    @endforeach
                                @endforeach

                                @if(!$hasProses)
                                    <tr>
                                        <td colspan="3" class="text-center py-4 text-muted">
                                            <i class="fa-solid fa-circle-info me-1"></i> Tidak ada produk yang sedang diproses di dapur saat ini.
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Validasi Gudang -->
<div class="modal fade" id="modalValidasi" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('dashboard.produksi.validasi') }}" method="POST">
                @csrf
                <input type="hidden" name="produk_varian_id" id="valVarianId">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-circle-check text-success me-2"></i> Validasi Hasil Produksi</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold d-block">Produk / Varian</label>
                        <div class="p-2 bg-light rounded fw-bold text-dark" id="valNamaProduk">-</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold d-block mb-2">Tindakan Validasi</label>
                        <div class="row g-2">
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="tindakan" id="tindakanSelesai" value="selesai" checked autocomplete="off">
                                <label class="btn btn-outline-success w-100 py-3 d-flex flex-column align-items-center" for="tindakanSelesai">
                                    <i class="fa-solid fa-check-circle fa-xl mb-2"></i>
                                    <span class="fw-bold">Selesai (Siap Jual)</span>
                                    <small class="text-muted d-block mt-1">Stok siap jual bertambah</small>
                                </label>
                            </div>
                            <div class="col-6">
                                <input type="radio" class="btn-check" name="tindakan" id="tindakanWaste" value="waste" autocomplete="off">
                                <label class="btn btn-outline-danger w-100 py-3 d-flex flex-column align-items-center" for="tindakanWaste">
                                    <i class="fa-solid fa-trash-can fa-xl mb-2"></i>
                                    <span class="fw-bold">Gagal / Waste</span>
                                    <small class="text-muted d-block mt-1">Produk cacat/dibuang</small>
                                </label>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold">Jumlah Validasi (Pcs)</label>
                        <div class="input-group">
                            <input type="number" name="qty_validasi" id="valQtyInput" class="form-control form-control-lg text-center fw-bold" min="1" required>
                            <span class="input-group-text bg-light fw-bold" id="valMaxLabel">/ Max 0 pcs</span>
                        </div>
                        <small class="text-muted mt-1 d-block">Masukkan jumlah yang lolos quality control atau yang terbuang.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Simpan Validasi</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const produkSelect = document.getElementById('produkSelect');
        const qtyInput = document.getElementById('qtyInput');
        const previewResep = document.getElementById('previewResep');

        function updatePreview() {
            const selectedOption = produkSelect.options[produkSelect.selectedIndex];
            if (!selectedOption.value) {
                previewResep.innerHTML = `
                    <div class="text-center p-5 text-muted bg-light rounded-3">
                        <i class="fa-solid fa-receipt fa-3x mb-3 text-secondary"></i>
                        <p class="mb-0">Pilih produk di sebelah kiri untuk melihat rincian bahan baku yang akan dipotong otomatis.</p>
                    </div>`;
                return;
            }

            const resepData = JSON.parse(selectedOption.getAttribute('data-resep') || '[]');
            const qty = parseInt(qtyInput.value) || 1;

            if (resepData.length === 0) {
                previewResep.innerHTML = `
                    <div class="alert alert-info">
                        <i class="fa-solid fa-info-circle me-2"></i> Produk ini tidak memiliki resep HPP. Sistem hanya akan menambahkan stok produk jadi tanpa memotong bahan baku.
                    </div>`;
                return;
            }

            let html = `
                <div class="table-responsive">
                    <table class="table table-hover align-middle border">
                        <thead class="bg-light">
                            <tr>
                                <th>Bahan Baku</th>
                                <th>Kebutuhan per Pcs</th>
                                <th>Total Dipotong (x${qty})</th>
                            </tr>
                        </thead>
                        <tbody>
            `;

            resepData.forEach(item => {
                const total = item.qty * qty;
                html += `
                    <tr>
                        <td class="fw-bold text-dark">${item.nama}</td>
                        <td class="text-muted">${item.qty} ${item.satuan}</td>
                        <td class="text-danger fw-bold"><i class="fa-solid fa-arrow-trend-down me-1"></i> -${total} ${item.satuan}</td>
                    </tr>
                `;
            });

            html += `</tbody></table></div>`;
            previewResep.innerHTML = html;
        }

        produkSelect.addEventListener('change', updatePreview);
        qtyInput.addEventListener('input', updatePreview);

        // Handling modal trigger
        const btnValidasis = document.querySelectorAll('.btn-validasi');
        const modalValidasi = new bootstrap.Modal(document.getElementById('modalValidasi'));
        
        btnValidasis.forEach(btn => {
            btn.addEventListener('click', function() {
                const id = this.getAttribute('data-id');
                const nama = this.getAttribute('data-nama');
                const max = parseInt(this.getAttribute('data-max')) || 0;

                document.getElementById('valVarianId').value = id;
                document.getElementById('valNamaProduk').textContent = nama;
                document.getElementById('valQtyInput').value = max;
                document.getElementById('valQtyInput').max = max;
                document.getElementById('valMaxLabel').textContent = `/ Max ${max} pcs`;

                modalValidasi.show();
            });
        });
    });
</script>
@endpush
@endsection
