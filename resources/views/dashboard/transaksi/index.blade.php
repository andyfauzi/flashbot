@extends('layouts.app')

@section('title', 'Riwayat Transaksi')

@section('content')
<div class="d-flex flex-column flex-md-row justify-content-between align-items-md-center mb-4 gap-3">
    <div>
        <h1 class="h3 mb-0" style="color: var(--text-primary); font-weight: 700; letter-spacing: -0.02em;">Riwayat Transaksi</h1>
        <p class="text-muted mb-0" style="font-size: 14px;">Kelola dan pantau semua transaksi kasir.</p>
    </div>
    <div style="min-width: 300px;">
        <form action="{{ route('dashboard.transaksi.index') }}" method="GET">
            <div class="input-group shadow-sm bg-white" style="border-radius: 12px; overflow: hidden; border: 1px solid var(--border-light);">
                <input type="text" name="search" class="form-control border-0 bg-transparent py-2 px-3 shadow-none" placeholder="Cari No. Order / Pelanggan..." value="{{ request('search') }}" style="font-size: 13px;">
                <button class="btn px-4 border-0 text-white" style="background: var(--brand);" type="submit"><i class="fa-solid fa-search"></i></button>
            </div>
        </form>
    </div>
</div>

<!-- Statistik Utama -->
@if(isset($statistik))
<div class="row g-4 mb-4">
    <div class="col-md-6">
        <div class="card-premium stat-card p-4 h-100 position-relative overflow-hidden" style="background: linear-gradient(135deg, #4F46E5 0%, #3730A3 100%); border-radius: 16px;">
            <!-- Wavy background SVG -->
            <svg class="position-absolute bottom-0 end-0" style="width:100%; height:auto; opacity:0.15; pointer-events:none; transform: scale(1.1); transform-origin: bottom right;" viewBox="0 0 1440 320" xmlns="http://www.w3.org/2000/svg">
                <path fill="#ffffff" fill-opacity="1" d="M0,224L60,202.7C120,181,240,139,360,133.3C480,128,600,160,720,186.7C840,213,960,235,1080,213.3C1200,192,1320,128,1380,96L1440,64L1440,320L1380,320C1320,320,1200,320,1080,320C960,320,840,320,720,320C600,320,480,320,360,320C240,320,120,320,60,320L0,320Z"></path>
            </svg>
            <div class="position-relative z-1">
                <div class="d-flex align-items-center gap-3 mb-3">
                    <div class="rounded-circle d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2);">
                        <i class="fa-solid fa-chart-line text-white" style="font-size: 18px;"></i>
                    </div>
                    <div class="text-white opacity-75 small fw-medium">Omzet Hari Ini</div>
                    <div class="ms-auto text-white opacity-75 fw-medium">Rp</div>
                </div>
                <div class="text-white fw-bold mb-4" style="font-size: 2.2rem; letter-spacing: -1px;">
                    Rp {{ number_format($statistik['omzet_hari_ini'], 0, ',', '.') }}
                </div>
                <div class="d-inline-flex align-items-center gap-2 px-3 py-1 rounded-pill" style="background: rgba(0,0,0,0.2); border: 1px solid rgba(255,255,255,0.1);">
                    <div class="rounded-circle" style="width: 6px; height: 6px; background-color: #34D399;"></div>
                    <span class="text-white fw-medium" style="font-size: 11px;">{{ $statistik['pesanan_hari_ini'] }} Pesanan Hari Ini</span>
                </div>
            </div>
        </div>
    </div>
    <div class="col-md-6">
        <div class="card-premium p-4 h-100">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-bold mb-0 text-secondary" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-chart-line text-muted me-2"></i>Penjualan 
                </h6>
                <div class="dropdown">
                    <button class="btn btn-sm btn-light border-0 shadow-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="font-size: 0.85rem; font-weight: 600; color: #4b5563;">
                        {{ isset($range) && $range == '30' ? '1 Bulan Terakhir' : '7 Hari Terakhir' }}
                    </button>
                    <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0" style="font-size: 0.85rem;">
                        <li><a class="dropdown-item py-2 {{ (!isset($range) || $range != '30') ? 'active bg-primary text-white' : '' }}" href="{{ request()->fullUrlWithQuery(['range' => '7']) }}">7 Hari Terakhir</a></li>
                        <li><a class="dropdown-item py-2 {{ (isset($range) && $range == '30') ? 'active bg-primary text-white' : '' }}" href="{{ request()->fullUrlWithQuery(['range' => '30']) }}">1 Bulan Terakhir</a></li>
                    </ul>
                </div>
            </div>
            @if(isset($grafikPenjualan) && $grafikPenjualan->sum('total') > 0)
                <div style="height: 120px; position: relative;">
                    <canvas id="trafficChart"></canvas>
                </div>
            @else
                <div class="d-flex flex-column justify-content-center align-items-center h-100 text-center" style="min-height: 120px;">
                    <div class="mb-2 text-muted" style="opacity: 0.4;">
                        <i class="fa-solid fa-chart-line fs-2"></i>
                    </div>
                    <span class="text-secondary fw-semibold mb-1" style="font-size: 0.9rem;">Belum ada data penjualan</span>
                    <span class="text-muted" style="font-size: 0.75rem;">Penjualan akan tampil setelah transaksi lunas tercatat.</span>
                </div>
            @endif
        </div>
    </div>
</div>
@endif

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-4">No. Order</th>
                        <th>Waktu</th>
                        <th>Pelanggan</th>
                        <th>Daftar Pesanan</th>
                        <th>Total Bayar</th>
                        <th>Status</th>
                        <th class="text-end pe-4">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transaksis as $trx)
                    <tr>
                        <td class="ps-4">
                            <div class="fw-bold">#{{ $trx->nomor_order }}</div>
                            @if($trx->nomor_antrian)
                                <span class="badge bg-danger mt-1" style="font-size: 0.75rem;">Antrian: {{ $trx->nomor_antrian }}</span>
                            @endif
                            @if($trx->reservasi_id)
                                <a href="{{ route('dashboard.reservasi.index') }}" class="badge bg-info mt-1 text-decoration-none" style="font-size: 0.75rem;"><i class="fa-solid fa-calendar-check me-1"></i>Reservasi</a>
                            @endif
                        </td>
                        <td>
                            <div class="small">{{ $trx->created_at->format('d M Y') }}</div>
                            <div class="text-muted" style="font-size: 11px;">{{ $trx->created_at->format('H:i') }}</div>
                        </td>
                        <td>{{ $trx->nama_penerima }}</td>
                        <td>
                            <ul class="list-unstyled mb-0 small text-muted">
                                @foreach($trx->items as $item)
                                    <li>{{ $item->jumlah }}x {{ $item->produk->nama ?? 'Produk Terhapus' }} 
                                        @if($item->produkVarian)
                                            <span class="fst-italic">({{ $item->produkVarian->nama_varian }})</span>
                                        @endif
                                    </li>
                                @endforeach
                            </ul>
                        </td>
                        <td class="fw-bold text-success">Rp {{ number_format($trx->total_biaya, 0, ',', '.') }}</td>
                        <td>
                            @if($trx->status === 'completed' || $trx->status === 'paid')
                                <span class="badge rounded-pill px-3 py-1.5" style="color: #059669; background: #ECFDF5; border: 1px solid #10B981;"><i class="fa-solid fa-circle-check me-1"></i>Selesai</span>
                            @elseif($trx->status === 'batal')
                                <span class="badge rounded-pill px-3 py-1.5" style="color: #DC2626; background: #FEE2E2; border: 1px solid #EF4444;"><i class="fa-solid fa-circle-xmark me-1"></i>Batal (Void)</span>
                                <div class="text-danger mt-1" style="font-size: 10px; max-width: 150px; white-space: normal;">
                                    {!! nl2br(e($trx->catatan)) !!}
                                </div>
                            @elseif($trx->status === 'pending_payment')
                                <span class="badge rounded-pill px-3 py-1.5" style="color: #D97706; background: #FFFBEB; border: 1px solid #F59E0B;"><i class="fa-solid fa-clock me-1"></i>Pending_payment</span>
                            @else
                                <span class="badge rounded-pill px-3 py-1.5" style="color: #D97706; background: #FFFBEB; border: 1px solid #F59E0B;"><i class="fa-solid fa-clock me-1"></i>{{ ucfirst($trx->status) }}</span>
                            @endif
                        </td>
                        <td class="text-end pe-4">
                            @if($trx->status !== 'batal')
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center justify-content-center me-1 rounded-circle" style="width: 32px; height: 32px; border: 1px solid #C7D2FE; color: var(--brand); background: transparent; transition: all 0.2s;" onmouseover="this.style.background='#EEF2FF'" onmouseout="this.style.background='transparent'" onclick="cetakStruk({{ $trx->id }})" title="Cetak Ulang">
                                    <i class="fa-solid fa-print" style="font-size: 13px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center justify-content-center me-1 rounded-circle" style="width: 32px; height: 32px; border: 1px solid #FDE68A; color: #D97706; background: transparent; transition: all 0.2s;" onmouseover="this.style.background='#FEF3C7'" onmouseout="this.style.background='transparent'" onclick="editTransaksi({{ $trx->id }}, '{{ $trx->nomor_order }}')" title="Edit / Batal Sebagian">
                                    <i class="fa-solid fa-pen-to-square" style="font-size: 13px;"></i>
                                </button>
                                <button type="button" class="btn btn-sm d-inline-flex align-items-center justify-content-center rounded-circle" style="width: 32px; height: 32px; border: 1px solid #FECACA; color: #DC2626; background: transparent; transition: all 0.2s;" onmouseover="this.style.background='#FEE2E2'" onmouseout="this.style.background='transparent'" onclick="batalTransaksi({{ $trx->id }}, '{{ $trx->nomor_order }}')" title="Batal Total (Void)">
                                    <i class="fa-solid fa-ban" style="font-size: 13px;"></i>
                                </button>
                            @else
                                <span class="text-muted small"><i class="fa-solid fa-lock"></i> Terkunci</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center py-5 text-muted">Belum ada riwayat transaksi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white border-0 py-3">
        {{ $transaksis->links('pagination::bootstrap-4') }}
    </div>
</div>

<!-- Modal Batal / Edit Transaksi -->
<div class="modal fade" id="batalModal" tabindex="-1">
    <div class="modal-dialog">
        <form id="formBatal" method="POST" class="modal-content border-0 shadow">
            @csrf
            <div class="modal-header bg-danger text-white border-0">
                <h5 class="modal-title" id="batalModalTitle"><i class="fa-solid fa-triangle-exclamation me-2"></i>Batalkan Transaksi</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-4">
                <div class="alert alert-warning border-0" id="batalAlertMsg">
                    Anda akan membatalkan pesanan <strong id="batalOrderNo"></strong>. 
                    <br>Uang di laci kasir otomatis dikurangi sejumlah total tagihan.
                </div>
                
                <input type="hidden" name="is_edit" id="isEditFlag" value="0">

                <div class="mb-4">
                    <label class="form-label fw-bold">Alasan Batal <span class="text-danger">*</span></label>
                    <input type="text" name="alasan_batal" class="form-control" placeholder="Contoh: Salah input kasir / Pelanggan kabur" required>
                </div>

                <div class="mb-3">
                    <label class="form-label fw-bold">Tindakan Stok Barang <span class="text-danger">*</span></label>
                    <div class="form-check custom-radio border rounded p-3 mb-2 shadow-sm" style="cursor: pointer;" onclick="document.getElementById('stokRestock').checked = true;">
                        <input class="form-check-input ms-0 mt-1" type="radio" name="tindakan_stok" id="stokRestock" value="restock" required>
                        <label class="form-check-label ms-2 d-block" for="stokRestock" style="cursor: pointer;">
                            <span class="fw-bold text-success"><i class="fa-solid fa-box-open me-1"></i> Kembalikan ke Gudang (Restock)</span><br>
                            <small class="text-muted">Barang utuh dan bisa dijual lagi. Stok akan dikembalikan (+).</small>
                        </label>
                    </div>
                    <div class="form-check custom-radio border rounded p-3 shadow-sm" style="cursor: pointer;" onclick="document.getElementById('stokWaste').checked = true;">
                        <input class="form-check-input ms-0 mt-1" type="radio" name="tindakan_stok" id="stokWaste" value="waste" required>
                        <label class="form-check-label ms-2 d-block" for="stokWaste" style="cursor: pointer;">
                            <span class="fw-bold text-danger"><i class="fa-solid fa-trash-can me-1"></i> Buang / Rusak (Waste)</span><br>
                            <small class="text-muted">Barang sudah hancur/diracik (misal kopi tumpah). Stok hangus (-).</small>
                        </label>
                    </div>
                </div>
            </div>
            <div class="modal-footer border-0 pb-4 px-4">
                <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal (Kembali)</button>
                <button type="submit" class="btn btn-danger px-4 fw-bold" id="btnSubmitBatal">Konfirmasi Pembatalan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Print (Pop Up) -->
<div class="modal fade" id="printModal" data-bs-backdrop="static" tabindex="-1">
    <div class="modal-dialog modal-sm modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg" style="border-radius: 16px; overflow: hidden;">
            <div class="modal-header border-0 pb-0 justify-content-center bg-primary text-white py-3">
                <h5 class="modal-title fw-bold"><i class="fa-solid fa-print me-2"></i>Cetak Ulang Struk</h5>
                <button type="button" class="btn-close btn-close-white position-absolute end-0 me-3" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body p-0 text-center bg-light">
                <iframe id="printIframe" style="width: 100%; height: 65vh; border: none; background: #fff;"></iframe>
            </div>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<style>
    .custom-radio:hover {
        background-color: #f8f9fa;
        border-color: #dee2e6 !important;
    }
    .custom-radio input[type="radio"]:checked + label .text-success {
        color: #198754 !important;
    }
</style>
<script>
    function cetakStruk(id) {
        document.getElementById('printIframe').src = "{{ url('pos/print') }}/" + id;
        var printModal = new bootstrap.Modal(document.getElementById('printModal'));
        printModal.show();
    }

    function batalTransaksi(id, orderNo) {
        document.getElementById('formBatal').action = "{{ url('dashboard/transaksi') }}/" + id + "/cancel";
        document.getElementById('batalOrderNo').innerText = "#" + orderNo;
        document.getElementById('batalModalTitle').innerHTML = '<i class="fa-solid fa-ban me-2"></i>Batalkan Transaksi (Total)';
        document.getElementById('isEditFlag').value = '0';
        document.getElementById('btnSubmitBatal').innerHTML = 'Batalkan & Void';
        
        var modal = new bootstrap.Modal(document.getElementById('batalModal'));
        modal.show();
    }

    function editTransaksi(id, orderNo) {
        document.getElementById('formBatal').action = "{{ url('dashboard/transaksi') }}/" + id + "/cancel";
        document.getElementById('batalOrderNo').innerText = "#" + orderNo;
        document.getElementById('batalModalTitle').innerHTML = '<i class="fa-solid fa-pen-to-square me-2"></i>Edit & Cetak Ulang';
        document.getElementById('batalAlertMsg').innerHTML = 'Order <strong>#' + orderNo + '</strong> akan di-VOID terlebih dahulu. Setelah dikonfirmasi, Anda akan diarahkan ke layar kasir dengan keranjang terisi ulang untuk diedit.';
        document.getElementById('isEditFlag').value = '1'; // Flag for redirect to POS
        document.getElementById('btnSubmitBatal').innerHTML = 'Void & Lanjutkan Edit';
        
        var modal = new bootstrap.Modal(document.getElementById('batalModal'));
        modal.show();
    }

    function closePrintModal() {
        var modal = bootstrap.Modal.getInstance(document.getElementById('printModal'));
        if (modal) {
            modal.hide();
        }
    }

    @if(isset($grafikPenjualan) && $grafikPenjualan->sum('total') > 0)
    document.addEventListener("DOMContentLoaded", function () {
        const ctx = document.getElementById('trafficChart').getContext('2d');
        
        const rawLabels = {!! json_encode($grafikPenjualan->pluck('tanggal')) !!};
        const rawData = {!! json_encode($grafikPenjualan->pluck('total')) !!};
        
        // Format label menjadi tanggal (Contoh: "21 Jun")
        const labels = rawLabels.map(dateStr => {
            const d = new Date(dateStr);
            return d.toLocaleDateString('id-ID', { day: 'numeric', month: 'short' });
        });
        
        const gradient = ctx.createLinearGradient(0, 0, 0, 120);
        gradient.addColorStop(0, 'rgba(37, 99, 235, 0.35)');
        gradient.addColorStop(1, 'rgba(37, 99, 235, 0.00)');
        
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'Pendapatan',
                    data: rawData,
                    borderColor: 'rgb(37, 99, 235)',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.4,
                    pointBackgroundColor: 'rgb(37, 99, 235)',
                    pointRadius: 3,
                    pointHoverRadius: 5
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        display: true,
                        grid: {
                            display: false,
                            drawBorder: false
                        },
                        ticks: {
                            font: { size: 10 },
                            color: '#9ca3af',
                            maxRotation: 0,
                            maxTicksLimit: 7
                        }
                    },
                    y: {
                        display: false,
                        beginAtZero: true
                    }
                },
                layout: {
                    padding: 0
                }
            }
        });
    });
    @endif
</script>
@endsection
