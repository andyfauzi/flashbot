@extends('layouts.app')

@section('title', 'Kalkulator HPP & Auto-Pricing')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-calculator me-2"></i> Kalkulator HPP & Auto-Pricing
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
        <div class="col-12">
            <div class="card shadow-sm border-0 mb-4">
                <div class="card-body">
                    <p class="mb-0 text-muted">Fitur ini membantu Anda menghitung Harga Pokok Penjualan (HPP) setiap varian produk secara otomatis berdasarkan resep. Anda dapat menyetel persentase keuntungan (margin) untuk mendapatkan Harga Jual Rekomendasi.</p>
                </div>
            </div>
        </div>
    </div>

    @forelse($produks as $produk)
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white border-bottom-0 pt-4 pb-0">
                <h4 class="fw-bold text-primary mb-1">{{ $produk->nama }}</h4>
                <p class="text-muted small"><i class="fa-solid fa-tag me-1"></i>Kategori: {{ $produk->kategori->nama_kategori ?? '-' }}</p>
            </div>
            <div class="card-body pt-2">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle">
                        <thead class="table-light">
                            <tr>
                                <th width="20%">Varian</th>
                                <th width="25%">Resep & Bahan</th>
                                <th width="15%">Konfigurasi</th>
                                <th width="20%">Kalkulasi HPP & Margin</th>
                                <th width="20%">Status & Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($produk->varians as $v)
                            <tr>
                                <td>
                                    <strong>{{ $v->nama_varian }}</strong>
                                    
                                    @php
                                        $maxPorsi = -1;
                                        if($v->resep->count() > 0) {
                                            foreach($v->resep as $r) {
                                                $bahan = $r->bahanBaku;
                                                if($bahan && $r->qty_dipakai > 0) {
                                                    $porsiBahanIni = floor($bahan->stok / $r->qty_dipakai);
                                                    if($maxPorsi == -1 || $porsiBahanIni < $maxPorsi) {
                                                        $maxPorsi = $porsiBahanIni;
                                                    }
                                                }
                                            }
                                        }
                                    @endphp

                                    @if($maxPorsi >= 0)
                                        <div class="mt-2">
                                            <span class="badge {{ $maxPorsi < 5 ? 'bg-danger' : 'bg-success' }}">
                                                <i class="fa-solid fa-layer-group"></i> Estimasi: {{ $maxPorsi }} Porsi
                                            </span>
                                        </div>
                                    @endif

                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-primary w-100" onclick="tambahResep({{ $v->id }}, '{{ addslashes($produk->nama . ' - ' . $v->nama_varian) }}')">
                                            <i class="fa-solid fa-plus"></i> Tambah Bahan
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $resepBahan = $v->resep->filter(fn($r) => optional($r->bahanBaku)->kategori !== 'packaging');
                                        $resepPackaging = $v->resep->filter(fn($r) => optional($r->bahanBaku)->kategori === 'packaging');
                                        
                                        $hppBahan = $resepBahan->sum(fn($r) => $r->qty_dipakai * optional($r->bahanBaku)->harga_per_unit);
                                        $hppPackaging = $resepPackaging->sum(fn($r) => $r->qty_dipakai * optional($r->bahanBaku)->harga_per_unit);
                                    @endphp

                                    @if($resepBahan->count() > 0)
                                        <div class="fw-bold text-primary small mb-1"><i class="fa-solid fa-leaf me-1"></i> Bahan Baku</div>
                                        <ul class="list-unstyled mb-2 small">
                                            @foreach($resepBahan as $r)
                                            <li class="mb-1 d-flex justify-content-between align-items-center">
                                                <span>{{ $r->qty_dipakai }} {{ $r->bahanBaku->satuan ?? '?' }} {{ $r->bahanBaku->nama_bahan ?? 'Bahan Dihapus' }}</span>
                                                <form action="{{ route('dashboard.hpp.resep.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan ini dari resep?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm text-danger p-0 border-0"><i class="fa-solid fa-times"></i></button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($resepPackaging->count() > 0)
                                        <div class="fw-bold text-warning small mb-1 mt-2"><i class="fa-solid fa-box-open me-1"></i> Packaging</div>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($resepPackaging as $r)
                                            <li class="mb-1 d-flex justify-content-between align-items-center">
                                                <span>{{ $r->qty_dipakai }} {{ $r->bahanBaku->satuan ?? '?' }} {{ $r->bahanBaku->nama_bahan ?? 'Bahan Dihapus' }}</span>
                                                <form action="{{ route('dashboard.hpp.resep.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan ini dari resep?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm text-danger p-0 border-0"><i class="fa-solid fa-times"></i></button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($v->resep->count() == 0)
                                        <span class="text-muted small fst-italic">Belum ada resep.</span>
                                    @endif
                                </td>
                                <td>
                                    <button class="btn btn-sm btn-light border w-100 mb-2 text-start" onclick="aturKonfigurasi({{ $v->id }}, {{ $v->overhead_cost }}, {{ $v->harga_kompetitor ?? 0 }}, {{ $v->target_margin }}, {{ $v->resep_yield ?? 1 }})">
                                        <i class="fa-solid fa-cog text-muted me-1"></i> Edit Konfigurasi
                                    </button>
                                    <div class="small text-muted">
                                        <div><i class="fa-solid fa-gas-pump fa-fw"></i> Overhead: Rp{{ number_format($v->overhead_cost,0,',','.') }}</div>
                                        <div><i class="fa-solid fa-store fa-fw"></i> Pesaing: Rp{{ number_format($v->harga_kompetitor,0,',','.') }}</div>
                                        @if($v->resep_yield > 1)
                                            <div><i class="fa-solid fa-boxes-stacked fa-fw text-primary"></i> Yield: {{ $v->resep_yield }} Porsi</div>
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="text-muted small">HPP Bahan Baku:</span>
                                        <strong class="float-end">Rp{{ number_format($hppBahan, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="mb-1">
                                        <span class="text-muted small">HPP Packaging:</span>
                                        <strong class="float-end">Rp{{ number_format($hppPackaging, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="mb-1">
                                        <span class="text-muted small">+ Overhead:</span>
                                        <strong class="float-end">Rp{{ number_format($v->overhead_cost, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="border-top my-1"></div>
                                    <div class="mb-1">
                                        <span class="text-muted small">Total Modal {{ $v->resep_yield > 1 ? '(per Pcs)' : '' }}:</span>
                                        <strong class="float-end text-danger">Rp{{ number_format($v->hpp + $v->overhead_cost, 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="mb-1">
                                        <span class="text-muted small">Margin ({{ $v->target_margin }}%):</span>
                                        <strong class="float-end text-success">+ Rp{{ number_format(($v->hpp + $v->overhead_cost) * ($v->target_margin / 100), 0, ',', '.') }}</strong>
                                    </div>
                                    <div class="border-top border-2 my-1"></div>
                                    <div class="mb-1">
                                        <span class="text-muted small fw-bold">Harga Rekomendasi:</span>
                                        <strong class="float-end text-primary fs-6">Rp{{ number_format($v->harga_rekomendasi, 0, ',', '.') }}</strong>
                                    </div>
                                </td>
                                <td class="text-center bg-light">
                                    <div class="mb-2 text-start">
                                        <span class="text-muted d-block small">Harga Jual Saat Ini:</span>
                                        <span class="fw-bold fs-5 text-dark">Rp{{ number_format($v->harga ?? $produk->harga, 0, ',', '.') }}</span>
                                    </div>

                                    @php
                                        $hargaJual = $v->harga ?? $produk->harga;
                                        $marginAktual = 0;
                                        $totalModal = $v->hpp + $v->overhead_cost;
                                        if($totalModal > 0) {
                                            $marginAktual = (($hargaJual - $totalModal) / $totalModal) * 100;
                                        }
                                    @endphp
                                    
                                    <div class="mb-3 text-start">
                                        <span class="badge {{ $marginAktual >= $v->target_margin ? 'bg-success' : 'bg-warning text-dark' }}">
                                            Margin Aktual: {{ round($marginAktual, 1) }}%
                                        </span>
                                    </div>

                                    @if($hargaJual != $v->harga_rekomendasi && $v->harga_rekomendasi > 0)
                                    <form action="{{ route('dashboard.hpp.rekomendasi', $v->id) }}" method="POST" onsubmit="return confirm('Ubah harga jual menjadi Rp{{ number_format($v->harga_rekomendasi, 0, ',', '.') }}? Katalog AI juga akan terupdate.');">
                                        @csrf
                                        @method('PUT')
                                        <button class="btn btn-warning fw-bold w-100 shadow-sm" style="font-size:0.9rem;">
                                            <i class="fa-solid fa-wand-magic-sparkles"></i> Pakai Harga Rekomendasi
                                        </button>
                                    </form>
                                    @else
                                        <span class="text-success small fw-bold"><i class="fa-solid fa-check-circle"></i> Harga Optimal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach

                            @foreach($produk->addons as $a)
                            <tr class="bg-light">
                                <td>
                                    <strong><span class="badge bg-secondary me-1">Add-on</span> {{ $a->nama_addon }}</strong>
                                    <div class="mt-2">
                                        <button class="btn btn-sm btn-outline-info w-100" onclick="tambahResepAddon({{ $a->id }}, '{{ addslashes($produk->nama . ' - Addon: ' . $a->nama_addon) }}')">
                                            <i class="fa-solid fa-plus"></i> Tambah Bahan Add-on
                                        </button>
                                    </div>
                                </td>
                                <td>
                                    @php
                                        $resepBahan = $a->reseps->filter(fn($r) => optional($r->bahanBaku)->kategori !== 'packaging');
                                        $resepPackaging = $a->reseps->filter(fn($r) => optional($r->bahanBaku)->kategori === 'packaging');
                                    @endphp

                                    @if($resepBahan->count() > 0)
                                        <div class="fw-bold text-info small mb-1"><i class="fa-solid fa-leaf me-1"></i> Bahan Baku Add-on</div>
                                        <ul class="list-unstyled mb-2 small">
                                            @foreach($resepBahan as $r)
                                            <li class="mb-1 d-flex justify-content-between align-items-center">
                                                <span>{{ $r->qty_dipakai }} {{ $r->bahanBaku->satuan ?? '?' }} {{ $r->bahanBaku->nama_bahan ?? 'Bahan Dihapus' }}</span>
                                                <form action="{{ route('dashboard.hpp.resep_addon.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan ini dari resep Add-on?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm text-danger p-0 border-0"><i class="fa-solid fa-times"></i></button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($resepPackaging->count() > 0)
                                        <div class="fw-bold text-warning small mb-1 mt-2"><i class="fa-solid fa-box-open me-1"></i> Packaging</div>
                                        <ul class="list-unstyled mb-0 small">
                                            @foreach($resepPackaging as $r)
                                            <li class="mb-1 d-flex justify-content-between align-items-center">
                                                <span>{{ $r->qty_dipakai }} {{ $r->bahanBaku->satuan ?? '?' }} {{ $r->bahanBaku->nama_bahan ?? 'Bahan Dihapus' }}</span>
                                                <form action="{{ route('dashboard.hpp.resep_addon.destroy', $r->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus bahan ini dari resep Add-on?');">
                                                    @csrf
                                                    @method('DELETE')
                                                    <button type="submit" class="btn btn-sm text-danger p-0 border-0"><i class="fa-solid fa-times"></i></button>
                                                </form>
                                            </li>
                                            @endforeach
                                        </ul>
                                    @endif

                                    @if($a->reseps->count() == 0)
                                        <span class="text-muted small fst-italic">Belum ada resep Add-on.</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="text-muted small fst-italic">Konfigurasi Overhead / Yield tidak berlaku untuk Add-on.</span>
                                </td>
                                <td>
                                    <div class="mb-1">
                                        <span class="text-muted small">Total Modal (HPP):</span>
                                        <strong class="float-end text-danger">Rp{{ number_format($a->hpp, 0, ',', '.') }}</strong>
                                    </div>
                                </td>
                                <td class="text-center bg-white">
                                    <div class="mb-2 text-start">
                                        <span class="text-muted d-block small">Harga Jual Add-on:</span>
                                        <span class="fw-bold fs-5 text-dark">Rp{{ number_format($a->harga, 0, ',', '.') }}</span>
                                    </div>

                                    @php
                                        $marginAktual = 0;
                                        if($a->hpp > 0) {
                                            $marginAktual = (($a->harga - $a->hpp) / $a->hpp) * 100;
                                        }
                                    @endphp
                                    
                                    <div class="mb-3 text-start">
                                        <span class="badge {{ $marginAktual >= 30 ? 'bg-success' : 'bg-warning text-dark' }}">
                                            Margin Aktual: {{ round($marginAktual, 1) }}%
                                        </span>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @empty
        <div class="alert alert-info">Belum ada produk yang memiliki varian. Silakan tambahkan varian di menu Produk terlebih dahulu.</div>
    @endforelse
</div>

<!-- Modal Tambah Resep -->
<div class="modal fade" id="modalResep" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formResep" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-mortar-pestle text-primary me-2"></i> Tambah Bahan Resep</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <p class="text-muted small mb-3" id="resepSubtitle"></p>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Bahan Baku</label>
                        <select name="bahan_baku_id" class="form-select select2" required>
                            <option value="">-- Pilih Bahan --</option>
                            @foreach($semuaBahan as $b)
                                <option value="{{ $b->id }}">{{ $b->nama_bahan }} ({{ $b->satuan }}) - Rp{{ number_format($b->harga_per_unit, 2,',','.') }}/{{ $b->satuan }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kuantitas Dipakai</label>
                        <input type="number" step="0.01" name="qty_dipakai" class="form-control" placeholder="Berapa banyak bahan ini dipakai untuk 1 porsi?" required min="0.01">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Tambahkan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Konfigurasi -->
<div class="modal fade" id="modalKonfigurasi" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formKonfigurasi" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-cog text-secondary me-2"></i> Konfigurasi Harga & Margin</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Porsi Dihasilkan (Yield)</label>
                        <input type="number" name="resep_yield" id="conf_yield" class="form-control" placeholder="1 resep jadi berapa porsi/pcs?" min="1">
                        <small class="text-muted">Biarkan 1 jika produk *Made to Order* (seperti kopi).</small>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Biaya Overhead (Rp)</label>
                        <input type="number" name="overhead_cost" id="conf_overhead" class="form-control" placeholder="Biaya gas, listrik, kemasan dll per porsi" min="0">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Target Margin Keuntungan (%)</label>
                        <input type="number" step="0.01" name="target_margin" id="conf_margin" class="form-control" placeholder="Contoh: 50" min="0" max="1000">
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Harga Rata-Rata Kompetitor (Opsional)</label>
                        <input type="number" name="harga_kompetitor" id="conf_kompetitor" class="form-control" placeholder="Hanya sebagai pembanding visual" min="0">
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Simpan Konfigurasi</button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function tambahResep(varianId, namaLengkap) {
        document.getElementById('formResep').action = '/dashboard/hpp/kalkulator/' + varianId + '/resep';
        document.getElementById('resepSubtitle').innerText = 'Varian: ' + namaLengkap;
        new bootstrap.Modal(document.getElementById('modalResep')).show();
    }

    function tambahResepAddon(addonId, namaLengkap) {
        document.getElementById('formResep').action = '/dashboard/hpp/kalkulator/addon/' + addonId + '/resep';
        document.getElementById('resepSubtitle').innerText = 'Add-on: ' + namaLengkap;
        new bootstrap.Modal(document.getElementById('modalResep')).show();
    }

    function aturKonfigurasi(varianId, overhead, kompetitor, margin, yield) {
        document.getElementById('formKonfigurasi').action = '/dashboard/hpp/kalkulator/' + varianId + '/konfigurasi';
        document.getElementById('conf_overhead').value = overhead;
        document.getElementById('conf_kompetitor').value = kompetitor;
        document.getElementById('conf_margin').value = margin;
        document.getElementById('conf_yield').value = yield;
        new bootstrap.Modal(document.getElementById('modalKonfigurasi')).show();
    }
</script>
@endsection
