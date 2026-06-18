@extends('layouts.app')

@section('title', 'Dashboard Chatbot')

@section('content')
<div class="container-fluid">

    <!-- Header & Indikator Gateway -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Selamat Datang, {{ auth()->user()->name ?? explode('@', auth()->user()->email)[0] }} 🌸</h2>
            <p class="text-secondary mb-0 small">Berikut adalah ringkasan aktivitas dan pesan Anda hari ini.</p>
        </div>
        <div>
            <span class="badge bg-white text-dark border px-3 py-2 rounded-pill shadow-sm">
                <i class="fa-solid fa-clock text-primary me-1"></i>
                <span class="fw-semibold">Server Time:</span> {{ now()->format('d M Y H:i') }}
            </span>
        </div>
    </div>

    <!-- Status Gateway Meta WhatsApp Cloud API -->
    <div class="row g-4 mb-4">
        <div class="col-12">
            <div class="card-premium p-4">
                <div class="row align-items-center g-4">
                    <div class="col-md-8">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="icon-box-premium brand">
                                @if(isset($gatewayStatus['gateway']) && $gatewayStatus['gateway'] === 'meta')
                                    <i class="fa-brands fa-meta text-primary fs-3"></i>
                                @else
                                    <i class="fa-brands fa-whatsapp text-success fs-3"></i>
                                @endif
                            </div>
                            <div>
                                @if(isset($gatewayStatus['gateway']) && $gatewayStatus['gateway'] === 'meta')
                                    <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);">Status Meta WhatsApp Cloud API</h5>
                                    <p class="text-secondary mb-0 small">Gateway resmi Meta</p>
                                @else
                                    <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);">Status Gateway WhatsApp (Sistem)</h5>
                                    <p class="text-secondary mb-0 small">Gateway Sistem Baileys</p>
                                @endif
                            </div>
                        </div>

                        <div class="mb-3">
                            @if(isset($gatewayStatus['status']) && $gatewayStatus['status'] === 'connected')
                                <div class="alert alert-success border-0 rounded-4 d-flex align-items-center gap-2 px-4 py-3">
                                    <i class="fa-solid fa-circle-check fs-4"></i>
                                    <div>
                                        <span class="fw-bold">Terhubung!</span>
                                        @if(isset($gatewayStatus['verified_name']))
                                            Akun: <strong class="text-dark">{{ $gatewayStatus['verified_name'] }}</strong>
                                        @endif
                                        @if(isset($gatewayStatus['phone_number']))
                                            · Nomor: <strong class="text-dark">{{ $gatewayStatus['phone_number'] }}</strong>
                                        @endif
                                    </div>
                                </div>
                            @else
                                <div class="alert alert-warning border-0 rounded-4 d-flex align-items-center gap-2 px-4 py-3">
                                    <i class="fa-solid fa-triangle-exclamation fs-4"></i>
                                    <div>
                                        <span class="fw-bold">Belum dikonfigurasi!</span>
                                        @if(isset($gatewayStatus['message']))
                                            <div class="small mt-1 text-muted">{{ $gatewayStatus['message'] }}</div>
                                        @else
                                            <div class="small mt-1 text-muted">Silakan isi META_PHONE_NUMBER_ID dan META_ACCESS_TOKEN di halaman Settings.</div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>

                        <div class="d-flex gap-2 flex-wrap">
                            <a href="{{ route('dashboard.pengaturan.toko') }}" class="btn btn-premium btn-premium-brand px-4">
                                <i class="fa-solid fa-gear me-1"></i> Pengaturan Koneksi
                            </a>
                            <button onclick="location.reload()" class="btn btn-outline-secondary px-4 rounded-4">
                                <i class="fa-solid fa-arrows-rotate me-1"></i> Refresh
                            </button>
                        </div>
                    </div>

                    <!-- Ngrok / Webhook Info -->
                    <div class="col-md-4">
                        <div class="bg-light rounded-4 p-3 border">
                            <div class="small fw-bold text-secondary mb-2">
                                <i class="fa-solid fa-link me-1"></i> URL Webhook Aktif
                            </div>
                            @php
                                $isMeta = isset($gatewayStatus['gateway']) && $gatewayStatus['gateway'] === 'meta';
                                $webhookDisplay = $isMeta ? url('/api/webhook/meta') : url('/webhook/whatsapp');
                            @endphp
                            <div class="bg-white rounded-3 p-2 border mb-2" style="font-size: 0.72rem; word-break: break-all; font-family: monospace;">
                                {{ $webhookDisplay }}
                            </div>
                            <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2 py-1">
                                <i class="fa-solid fa-bolt me-1"></i> Jalur Aktif
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Statistik Utama -->
    <div class="row g-4 mb-4">
        <!-- Total Users Card -->
        <div class="col-md-4">
            <div class="card-premium stat-card brand p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Total Users</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $statistik['total_user'] }}</div>
                        <span class="badge bg-light text-primary border rounded-pill">{{ $statistik['user_hari_ini'] }} aktif hari ini</span>
                    </div>
                    <div class="icon-box-premium brand">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pesan Masuk Card -->
        <div class="col-md-4">
            <div class="card-premium stat-card warning p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Pesan Masuk</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $statistik['pesan_masuk'] }}</div>
                        <span class="badge bg-light text-warning border rounded-pill">Dari Users</span>
                    </div>
                    <div class="icon-box-premium warning">
                        <i class="fa-solid fa-arrow-down"></i>
                    </div>
                </div>
            </div>
        </div>
        <!-- Pesan Keluar Card -->
        <div class="col-md-4">
            <div class="card-premium stat-card danger p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Pesan Keluar</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $statistik['pesan_keluar'] }}</div>
                        <span class="badge bg-light text-danger border rounded-pill">Dari Bot</span>
                    </div>
                    <div class="icon-box-premium danger">
                        <i class="fa-solid fa-arrow-up"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Peringatan Stok Menipis -->
    @if($stokMenipisProduk->count() > 0 || $stokMenipisVarian->count() > 0)
    <div class="row mb-4">
        <div class="col-12">
            <div class="alert alert-danger border-0 shadow-sm rounded-4 d-flex align-items-start gap-3 p-4">
                <i class="fa-solid fa-triangle-exclamation fs-3 mt-1"></i>
                <div>
                    <h5 class="fw-bold mb-1">Peringatan: Stok Menipis (<= 5)</h5>
                    <ul class="mb-0 ps-3">
                        @foreach($stokMenipisProduk as $prod)
                            @if($prod->varians->count() === 0)
                                <li><strong>{{ $prod->nama }}</strong> - Sisa Stok: {{ $prod->stok }}</li>
                            @endif
                        @endforeach
                        @foreach($stokMenipisVarian as $var)
                            <li><strong>{{ $var->produk->nama ?? '' }}</strong> (Varian: {{ $var->nama_varian }}) - Sisa Stok: {{ $var->stok }}</li>
                        @endforeach
                    </ul>
                </div>
            </div>
        </div>
    </div>
    @endif

    <!-- Baris Grafik & Kirim Manual -->
    <div class="row g-4">
        <!-- Grafik Tren Penjualan & Riwayat -->
        <div class="col-md-8">
            <!-- Info Grafik Dihapus -->

            <!-- Tabel Riwayat -->
            <div class="card-premium p-0 overflow-hidden">
                <div class="px-4 py-3 bg-white border-bottom border-light d-flex justify-content-between align-items-center">
                    <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                        <i class="fa-solid fa-history text-secondary me-2"></i>Pesan Terbaru
                    </h5>
                    <a href="{{ route('chatbot.pesan') }}" class="btn btn-sm btn-light rounded-pill px-3">Lihat Semua</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium align-middle">
                        <thead>
                            <tr>
                                <th>Nomor WA</th>
                                <th>Arah</th>
                                <th>Isi Pesan</th>
                                <th>Waktu</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pesanTerbaru as $p)
                            <tr>
                                <td>
                                    <div class="d-flex align-items-center gap-2">
                                        <div class="bg-light rounded-pill d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                            <i class="fa-solid fa-user-ninja text-secondary small"></i>
                                        </div>
                                        <span class="fw-semibold text-dark">{{ $p->nomor }}</span>
                                    </div>
                                </td>
                                <td>
                                    @if($p->arah === 'masuk')
                                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">Masuk</span>
                                    @else
                                        <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">Keluar</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-truncate" style="max-width: 350px;">
                                        @if($p->media_url)
                                            <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill me-1"><i class="fa-regular fa-image me-1"></i>Media</span>
                                        @endif
                                        <span class="text-dark">{{ $p->isi }}</span>
                                    </div>
                                </td>
                                <td>
                                    <small class="text-secondary fw-semibold">{{ \Carbon\Carbon::parse($p->waktu)->diffForHumans() }}</small>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-folder-open fs-3 mb-2"></i>
                                    <div>Belum ada pesan tercatat</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Panel Form Kirim Manual -->
        <div class="col-md-4">
            <div class="card-premium p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box-premium success">
                        <i class="fa-solid fa-paper-plane"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);">Kirim Pesan Manual</h5>
                        <p class="text-secondary mb-0 small">Kirim chat & media secara langsung</p>
                    </div>
                </div>

                <form action="{{ route('chatbot.kirim') }}" method="POST">
                    @csrf
                    <!-- Nomor WA -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nomor Tujuan WA</label>
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0 border-light" style="border-radius: 12px 0 0 12px;">62</span>
                            <input type="text" name="nomor" class="form-control form-control-premium border-start-0 ps-1"
                                   placeholder="8123456789" style="border-radius: 0 12px 12px 0;" required>
                        </div>
                        <small class="text-secondary mt-1 d-block">Masukkan nomor tanpa kode negara `62` di depan (langsung `8xxxx` saja)</small>
                    </div>

                    <!-- Isi Pesan -->
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Isi Pesan Teks</label>
                        <textarea name="pesan" class="form-control form-control-premium" rows="4"
                                  placeholder="Tulis pesan Anda di sini..." required></textarea>
                    </div>

                    <!-- Media URL -->
                    <div class="mb-4">
                        <label class="form-label small fw-bold text-secondary">URL Media/Gambar (Opsional)</label>
                        <input type="url" name="media_url" class="form-control form-control-premium"
                               placeholder="https://example.com/image.jpg">
                        <small class="text-secondary mt-1 d-block">Mendukung format gambar .jpg, .png</small>
                    </div>

                    <!-- Tombol Kirim -->
                    <button type="submit" class="btn btn-premium btn-premium-success w-100 py-3 d-flex align-items-center justify-content-center gap-2">
                        <i class="fa-solid fa-circle-arrow-right fs-5"></i>
                        <span>Kirim Pesan</span>
                    </button>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <!-- Script grafik dihapus -->
@endsection
