@extends('layouts.app')

@section('title', 'Daftar Pesanan')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-list-check me-2"></i> Daftar Pesanan (Kitchen)
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

    <div class="card shadow-sm border-0 mb-4">
        <div class="card-body">
            <form action="{{ route('dashboard.preorder.index') }}" method="GET" class="row g-3 align-items-center">
                <div class="col-auto">
                    <label class="form-label fw-bold mb-0">Tanggal Pengambilan:</label>
                </div>
                <div class="col-auto">
                    <input type="date" name="tanggal" class="form-control" value="{{ $tanggal }}">
                </div>
                <div class="col-md-5 col-12">
                    <div class="input-group">
                        <input type="text" name="search" class="form-control" placeholder="Cari No. Order / Pelanggan..." value="{{ request('search') }}">
                        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-search me-1"></i> Cari</button>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">No. Order</th>
                            <th>Nama Pemesan</th>
                            <th>No. WA</th>
                            <th>Produk</th>
                            <th class="text-end">Total Biaya</th>
                            <th class="text-end text-success">Uang Muka (DP)</th>
                            <th class="text-end text-danger">Sisa Bayar</th>
                            <th class="text-center">Status</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pesanans as $p)
                        <tr>
                            <td class="ps-4">
                                <div class="fw-bold text-primary">{{ $p->nomor_order }}</div>
                                @if($p->nomor_antrian)
                                    <span class="badge bg-danger mt-1" style="font-size: 0.75rem;">Antrian: {{ $p->nomor_antrian }}</span>
                                @endif
                            </td>
                            <td>
                                <div class="fw-bold">{{ $p->nama_penerima }}</div>
                                @if($p->tipe_pengiriman === 'dine_in' && isset($identitas) && in_array($identitas->jenis_layanan ?? 'keduanya', ['dine_in', 'keduanya']))
                                    <span class="badge bg-warning text-dark mt-1" style="font-size: 0.75rem;"><i class="fa-solid fa-chair me-1"></i> Meja {{ $p->meja->nomor_meja ?? '?' }}</span>
                                @elseif(stripos($p->tipe_pengiriman, 'ambil') !== false)
                                    <span class="badge bg-secondary mt-1" style="font-size: 0.75rem;"><i class="fa-solid fa-store me-1"></i> Ambil Sendiri</span>
                                @else
                                    <span class="badge bg-info text-white mt-1" style="font-size: 0.75rem;"><i class="fa-solid fa-motorcycle me-1"></i> Kurir Toko</span>
                                    @if($p->kurir)
                                        <div class="small text-muted mt-1"><i class="fa-solid fa-user-tag fa-xs me-1"></i> {{ $p->kurir->nama }}</div>
                                    @endif
                                @endif
                            </td>
                            <td>{{ $p->nomor_wa }}</td>
                            <td>
                                @php
                                    $itemCount = $p->items->sum('jumlah');
                                    $itemTypeCount = $p->items->count();
                                @endphp
                                @if($itemTypeCount > 0)
                                    <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#modalItems{{ $p->id }}">
                                        <i class="fa-solid fa-box-open me-1"></i> Lihat {{ $itemCount }} Item
                                    </button>
                                @else
                                    <span class="text-muted small">Kosong</span>
                                @endif
                            </td>
                            <td class="text-end fw-bold">Rp {{ number_format($p->total_biaya, 0, ',', '.') }}</td>
                            <td class="text-end text-success fw-bold">Rp {{ number_format($p->uang_muka, 0, ',', '.') }}</td>
                            <td class="text-end text-danger fw-bold">Rp {{ number_format($p->sisa_pembayaran, 0, ',', '.') }}</td>
                            <td class="text-center">
                                @if($p->sisa_pembayaran <= 0)
                                    <span class="badge bg-success px-3 py-2">LUNAS</span>
                                @else
                                    <span class="badge bg-warning text-dark px-3 py-2">BELUM LUNAS</span>
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <a href="{{ route('pos.print', $p->id) }}" target="_blank" class="btn btn-sm btn-outline-secondary me-1" title="Cetak Struk">
                                    <i class="fa-solid fa-print"></i>
                                </a>
                                @if($p->is_ready_notified)
                                    <button class="btn btn-sm btn-secondary me-1" title="Notifikasi Siap Terkirim" disabled>
                                        <i class="fa-solid fa-bell"></i>
                                    </button>
                                @else
                                    <form action="{{ route('dashboard.preorder.notif_siap', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Kirim pesan WA bahwa pesanan siap dan lampirkan PDF ke pelanggan?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm btn-outline-warning me-1 text-dark" title="Notifikasi Pelanggan (Pesanan Siap)">
                                            <i class="fa-solid fa-bell"></i>
                                        </button>
                                    </form>
                                @endif
                                @if($p->sisa_pembayaran > 0)
                                    @if(in_array($p->status, ['pending_payment', 'pending_approval']))
                                        <form action="{{ route('dashboard.preorder.sync_xendit', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Cek status pembayaran terbaru dari Xendit?');">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary me-1" title="Cek Status Xendit">
                                                <i class="fa-solid fa-arrows-rotate"></i>
                                            </button>
                                        </form>
                                    @endif
                                     <button class="btn btn-sm btn-outline-info me-1" onclick="setOngkir({{ $p->id }}, {{ $p->biaya_pengantaran }}, '{{ $p->tipe_pengiriman }}', '{{ addslashes($p->alamat_penerima) }}', {{ $p->kurir_id ?? 'null' }}, '{{ $p->nomor_hp }}')" title="Atur Pengiriman & Ongkir">
                                         <i class="fa-solid fa-motorcycle"></i>
                                     </button>
                                    <button class="btn btn-sm btn-outline-success me-1" onclick="setDp({{ $p->id }}, {{ $p->uang_muka }})" title="Set DP">
                                        <i class="fa-solid fa-money-bill"></i>
                                    </button>
                                    <form action="{{ route('dashboard.preorder.lunas', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin pesanan ini sudah LUNAS?');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-success me-1" title="Lunasi">
                                            <i class="fa-solid fa-check"></i>
                                        </button>
                                    </form>
                                @else
                                    <form action="{{ route('dashboard.preorder.selesai', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin pesanan ini telah selesai/diambil? Ini akan otomatis mengirimkan WA testimoni ke pelanggan.');">
                                        @csrf
                                        @method('PUT')
                                        <button type="submit" class="btn btn-sm btn-primary me-1" title="Selesaikan Pesanan">
                                            <i class="fa-solid fa-flag-checkered"></i> Selesai
                                        </button>
                                    </form>
                                @endif
                                <form action="{{ route('dashboard.preorder.batal', $p->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan pesanan ini? Stok akan otomatis dikembalikan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Batal Pesanan">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="9" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-clipboard-check fa-3x mb-3 text-light"></i>
                                <h5>Tidak ada pesanan aktif untuk tanggal ini</h5>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Set DP -->
<div class="modal fade" id="modalDp" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formDp" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-money-bill text-success me-2"></i> Input Uang Muka (DP)</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Nominal DP (Rp)</label>
                        <input type="number" name="uang_muka" id="inputUangMuka" class="form-control form-control-lg" required min="0">
                        <small class="text-muted mt-2 d-block">Masukkan nominal uang muka yang telah dibayarkan oleh pelanggan.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-success fw-bold px-4">Simpan DP</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Set Ongkir -->
<div class="modal fade" id="modalOngkir" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form id="formOngkir" method="POST">
                @csrf
                @method('PUT')
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold"><i class="fa-solid fa-motorcycle text-info me-2"></i> Atur Pengiriman & Ongkir</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Tipe Pengiriman</label>
                        <select name="tipe_pengiriman" id="selectTipePengiriman" class="form-select form-select-lg" onchange="toggleAlamatOngkirField()">
                            <option value="ambil_sendiri">Ambil Sendiri (Pickup)</option>
                            <option value="kurir_toko">Kurir Toko (Delivery)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="groupAlamat">
                        <label class="form-label fw-bold">Alamat Pengiriman</label>
                        <textarea name="alamat_penerima" id="inputAlamat" class="form-control" rows="2"></textarea>
                    </div>
                    <div class="mb-3" id="groupOngkir">
                        <label class="form-label fw-bold">Nominal Ongkos Kirim (Rp)</label>
                        <input type="number" name="biaya_pengantaran" id="inputOngkir" class="form-control form-control-lg" min="0">
                        <small class="text-muted mt-2 d-block">Sistem akan menginformasikan nilai ini ke pelanggan melalui WhatsApp dan menyesuaikan total tagihan.</small>
                    </div>
                    <div class="mb-3" id="groupKurir">
                        <label class="form-label fw-bold">Pilih Kurir</label>
                        <select name="kurir_id" id="selectKurir" class="form-select">
                            <option value="">-- Tanpa Kurir (Kirim Nanti) --</option>
                            @foreach($kurirs as $kurir)
                                <option value="{{ $kurir->id }}">{{ $kurir->nama }} (+{{ $kurir->nomor_hp }})</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3" id="groupNomorHp">
                        <label class="form-label fw-bold">Nomor HP Pelanggan (Opsional)</label>
                        <input type="text" name="nomor_hp" id="inputNomorHp" class="form-control" placeholder="Masukkan nomor HP aktif untuk kurir">
                        <small class="text-muted mt-2 d-block">Sangat berguna jika nomor WA pelanggan menggunakan ID LID privat sehingga kurir bisa menelepon.</small>
                    </div>
                </div>
                <div class="modal-footer border-top-0">
                    <button type="button" class="btn btn-light" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-info fw-bold px-4 text-white">Simpan Pengaturan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal List Item Pesanan -->
@foreach($pesanans as $p)
    @if($p->items->count() > 0)
    <div class="modal fade" id="modalItems{{ $p->id }}" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered modal-dialog-scrollable">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold">
                        <i class="fa-solid fa-box-open text-primary me-2"></i> Detail Pesanan #{{ $p->nomor_order }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-0">
                    <ul class="list-group list-group-flush">
                        @foreach($p->items as $item)
                        <li class="list-group-item d-flex justify-content-between align-items-start py-3">
                            <div class="ms-2 me-auto">
                                <div class="fw-bold">{{ $item->produk->nama ?? 'Produk Terhapus' }}</div>
                                @if($item->produkVarian)
                                    <small class="text-muted d-block">Varian: {{ $item->produkVarian->nama_varian }}</small>
                                @endif
                                @if(!empty($item->addons) && is_array($item->addons))
                                    <small class="text-muted d-block">Addons: 
                                        @foreach($item->addons as $addon)
                                            {{ $addon['nama'] ?? '' }}@if(!$loop->last), @endif
                                        @endforeach
                                    </small>
                                @endif
                                @if($item->catatan)
                                    <small class="text-danger d-block mt-1"><i class="fa-solid fa-note-sticky fa-xs"></i> "{{ $item->catatan }}"</small>
                                @endif
                            </div>
                            <span class="badge bg-primary rounded-pill" style="font-size: 14px;">{{ $item->jumlah }}x</span>
                        </li>
                        @endforeach
                    </ul>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                </div>
            </div>
        </div>
    </div>
    @endif
@endforeach

@endsection

@section('scripts')
<script>
    function toggleAlamatOngkirField() {
        const tipe = document.getElementById('selectTipePengiriman').value;
        const groupAlamat = document.getElementById('groupAlamat');
        const groupOngkir = document.getElementById('groupOngkir');
        const groupKurir = document.getElementById('groupKurir');
        const groupNomorHp = document.getElementById('groupNomorHp');
        const inputAlamat = document.getElementById('inputAlamat');
        const inputOngkir = document.getElementById('inputOngkir');
        const selectKurir = document.getElementById('selectKurir');
        const inputNomorHp = document.getElementById('inputNomorHp');
        
        if (tipe === 'ambil_sendiri') {
            groupAlamat.style.display = 'none';
            groupOngkir.style.display = 'none';
            groupKurir.style.display = 'none';
            groupNomorHp.style.display = 'none';
            inputAlamat.required = false;
            inputOngkir.required = false;
            inputOngkir.value = 0;
            selectKurir.value = "";
            inputNomorHp.value = "";
        } else {
            groupAlamat.style.display = 'block';
            groupOngkir.style.display = 'block';
            groupKurir.style.display = 'block';
            groupNomorHp.style.display = 'block';
            inputAlamat.required = true;
            inputOngkir.required = true;
        }
    }

    function setDp(pesananId, currentDp) {
        const modal = new bootstrap.Modal(document.getElementById('modalDp'));
        const form = document.getElementById('formDp');
        const input = document.getElementById('inputUangMuka');
        
        form.action = `/dashboard/preorder/${pesananId}/dp`;
        input.value = currentDp;
        
        modal.show();
    }

    function setOngkir(pesananId, currentOngkir, tipePengiriman, alamatPenerima, kurirId, nomorHp) {
        const modal = new bootstrap.Modal(document.getElementById('modalOngkir'));
        const form = document.getElementById('formOngkir');
        const inputOngkir = document.getElementById('inputOngkir');
        const selectTipe = document.getElementById('selectTipePengiriman');
        const inputAlamat = document.getElementById('inputAlamat');
        const selectKurir = document.getElementById('selectKurir');
        const inputNomorHp = document.getElementById('inputNomorHp');
        
        form.action = `/dashboard/preorder/${pesananId}/ongkir`;
        inputOngkir.value = currentOngkir;
        selectTipe.value = tipePengiriman || 'kurir_toko';
        inputAlamat.value = alamatPenerima || '';
        selectKurir.value = kurirId || '';
        inputNomorHp.value = nomorHp || '';
        
        toggleAlamatOngkirField();
        
        modal.show();
    }
</script>
@endsection
