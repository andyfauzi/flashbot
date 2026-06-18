@extends('layouts.app')

@section('title', 'Manajemen Multi-Device')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Manajemen Multi-Device</h2>
            <p class="text-secondary mb-0 small">Kelola nomor WhatsApp dan sesi Baileys Anda</p>
        </div>
        <a href="{{ route('chatbot.dashboard') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    @php
        $identitas = \App\Models\IdentitasToko::first();
        $isMeta = $identitas && $identitas->whatsapp_gateway === 'meta_mandiri';
    @endphp

    @if($isMeta)
    <div class="alert alert-info border-0 rounded-4 d-flex gap-3 px-4 py-3 mb-4 shadow-sm">
        <i class="fa-solid fa-circle-info fs-4 text-info mt-1"></i>
        <div>
            <span class="fw-bold text-dark">Info Koneksi Meta API Aktif</span>
            <p class="mb-0 small text-secondary">
                Anda saat ini menggunakan <strong>Meta WhatsApp Cloud API</strong> yang sudah terhubung langsung ke cloud.
                Pengaturan Multi-Device / Scan QR di bawah ini <strong>hanya diperlukan jika Anda ingin mengaktifkan fungsionalitas Grup WhatsApp</strong> (karena Meta belum mendukung Grup).
            </p>
        </div>
    </div>
    @endif

    <div class="row g-4">
        {{-- Form Tambah Device --}}
        <div class="col-md-4">
            <div class="card-premium p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box-premium brand">
                        <i class="fa-solid fa-mobile-screen"></i>
                    </div>
                    <div>
                        <h5 class="fw-bold mb-0" style="font-family: var(--font-heading);">Tambah Device</h5>
                        <p class="text-secondary mb-0 small">Tambahkan nomor WA baru</p>
                    </div>
                </div>

                <form action="{{ route('chatbot.device.store') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label small fw-bold text-secondary">Nama Device</label>
                        <input type="text" name="nama_device" class="form-control form-control-premium"
                            placeholder="Contoh: CS Pusat" required>
                    </div>
                    <button type="submit" class="btn btn-premium btn-premium-brand w-100 py-3">
                        <i class="fa-solid fa-plus"></i> Tambah Device
                    </button>
                </form>
            </div>
        </div>

        {{-- Daftar Device --}}
        <div class="col-md-8">
            <div class="card-premium p-0 overflow-hidden">
                <div class="table-responsive">
                    <table class="table table-premium align-middle mb-0">
                        <thead>
                            <tr>
                                <th>Nama Device</th>
                                <th>Status</th>
                                <th>Sesi/Default</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($devices as $device)
                            <tr>
                                <td>
                                    <span class="fw-semibold text-dark">{{ $device->nama_device }}</span>
                                    <small class="text-muted d-block">{{ $device->nomor ?? 'Belum terhubung' }}</small>
                                </td>
                                <td>
                                    <span id="status-badge-{{ $device->session_id }}">
                                        @if($device->status == 'connected')
                                            <span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check-circle me-1"></i> Terhubung</span>
                                        @elseif($device->status == 'qr')
                                            <span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="fa-solid fa-qrcode me-1"></i> Menunggu Scan QR</span>
                                        @else
                                            <span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-times-circle me-1"></i> Terputus</span>
                                        @endif
                                    </span>
                                </td>
                                <td>
                                    <small class="text-muted d-block">ID: {{ $device->session_id }}</small>
                                    @if($device->is_default)
                                        <span class="badge bg-primary px-2 py-1 rounded mt-1">Utama</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="d-flex gap-2 flex-wrap">
                                        {{-- Lihat QR: selalu tampil --}}
                                        <button id="btn-qr-{{ $device->session_id }}"
                                            class="btn btn-sm btn-outline-info rounded-pill qr-btn"
                                            onclick="showQr('{{ $device->session_id }}', '{{ $device->id }}')">
                                            <i class="fa-solid fa-qrcode me-1"></i> Lihat QR
                                        </button>

                                        {{-- Menu Chat: ke halaman menu ter-filter device ini --}}
                                        <a href="{{ route('chatbot.menu', ['device_id' => $device->id]) }}"
                                            class="btn btn-sm btn-outline-success rounded-pill"
                                            title="Atur menu chatbot untuk device ini">
                                            <i class="fa-solid fa-comments me-1"></i> Menu Chat
                                        </a>

                                        {{-- Reconnect: tampil saat disconnected atau connecting gagal --}}
                                        <form action="{{ route('chatbot.device.reconnect', $device) }}" method="POST" style="display:inline-block;" id="form-reconnect-{{ $device->id }}">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-warning rounded-pill"
                                                onclick="return confirm('Reconnect device {{ $device->nama_device }}?')"
                                                title="Putuskan sesi lama lalu hubungkan ulang">
                                                <i class="fa-solid fa-rotate-right me-1"></i> Reconnect
                                            </button>
                                        </form>

                                        {{-- Disconnect: hanya tampil saat connected atau qr --}}
                                        @if(in_array($device->status, ['connected', 'qr', 'connecting']))
                                        <form action="{{ route('chatbot.device.disconnect', $device) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill"
                                                onclick="return confirm('Putuskan koneksi device {{ $device->nama_device }}?')"
                                                title="Logout dari WhatsApp">
                                                <i class="fa-solid fa-plug-circle-xmark me-1"></i> Disconnect
                                            </button>
                                        </form>
                                        @endif

                                        {{-- Sapaan --}}
                                        <button class="btn btn-sm btn-outline-secondary rounded-pill"
                                            onclick="showSapaan({{ $device->id }})">
                                            <i class="fa-solid fa-comment me-1"></i> Sapaan
                                        </button>

                                        {{-- Set Utama --}}
                                        @if(!$device->is_default)
                                        <form action="{{ route('chatbot.device.default', $device) }}" method="POST" style="display:inline-block;">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-outline-primary rounded-pill">
                                                <i class="fa-solid fa-star me-1"></i> Set Utama
                                            </button>
                                        </form>
                                        @endif

                                        {{-- Hapus --}}
                                        <form action="{{ route('chatbot.device.hapus', $device) }}" method="POST" onsubmit="return confirm('Hapus device {{ $device->nama_device }}? Sesi WhatsApp-nya juga akan dihapus.');" style="display:inline-block;">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger rounded-pill">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-mobile-screen fs-3 mb-2 d-block"></i>
                                    Belum ada device. Tambahkan device pertama Anda.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ====================================================== --}}
{{-- SEMUA MODAL DI LUAR TABEL - agar tidak terpotong       --}}
{{-- ====================================================== --}}

{{-- Modal QR Code (satu modal, konten diganti lewat JS) --}}
<div class="modal fade" id="qrModalGlobal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content text-center p-4">
            <div class="modal-header border-0 pb-0">
                <h5 class="modal-title fw-bold w-100" id="qrModalLabel" style="font-family: var(--font-heading);">
                    <i class="fa-solid fa-qrcode text-primary me-2"></i> Scan QR Code
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <div class="d-flex justify-content-center mb-3">
                    <div class="p-2 border rounded-3 bg-white shadow-sm" style="display:inline-block;">
                        <img id="qrModalImage" src="" alt="QR Code" style="width: 260px; height: 260px; object-fit: contain;">
                    </div>
                </div>
                <div class="alert alert-warning border-0 rounded-3 text-start small mb-2">
                    <i class="fa-solid fa-circle-info me-1"></i>
                    Buka <strong>WhatsApp</strong> → <strong>Perangkat Tertaut</strong> → <strong>Tautkan Perangkat</strong> → Arahkan kamera ke QR Code.
                </div>
                <span class="badge bg-warning text-dark px-3 py-2 small">
                    <i class="fa-solid fa-sync fa-spin me-1"></i> QR Code diperbarui otomatis
                </span>
            </div>
        </div>
    </div>
</div>

{{-- Modal Sapaan per Device --}}
@foreach($devices as $device)
<div class="modal fade" id="sapaanModal-{{ $device->id }}" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form action="{{ route('chatbot.device.sapaan', $device) }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" style="font-family: var(--font-heading);">
                        <i class="fa-solid fa-comment-dots me-2 text-primary"></i> Pesan Sapaan: {{ $device->nama_device }}
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <label class="form-label small fw-bold text-secondary">Pesan Pembuka Khusus</label>
                    <textarea name="pesan_sapaan" class="form-control form-control-premium" rows="6"
                        placeholder="Contoh:&#10;🤖 *Selamat datang di Chatbot Kami!*&#10;&#10;Silakan pilih menu:">{{ $device->pesan_sapaan }}</textarea>
                    <small class="text-muted d-block mt-2">
                        <i class="fa-solid fa-lightbulb me-1 text-warning"></i>
                        Gunakan <code>*teks*</code> untuk bold. Biarkan kosong untuk memakai teks bawaan sistem.
                    </small>
                </div>
                <div class="modal-footer border-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-premium btn-premium-brand px-4">
                        <i class="fa-solid fa-save me-1"></i> Simpan Sapaan
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endforeach

@section('scripts')
<script>
    // Data device dari server
    const deviceSessionIds = @json($devices->pluck('session_id'));
    const baseStatusUrl = "{{ url('chatbot/device/status') }}";
    const baseQrUrl = "{{ asset('images/whatsapp-qr-') }}";

    let activeQrSession = null;
    let qrModalInstance = null;

    // Tampilkan QR modal - otomatis start sesi jika belum ada
    function showQr(sessionId, deviceId) {
        activeQrSession = sessionId;

        // Tampilkan modal dengan loading spinner dulu
        const img = document.getElementById('qrModalImage');
        const label = document.getElementById('qrModalLabel');
        img.style.opacity = '0.3';
        img.src = ''; // kosongkan dulu
        img.style.background = '#f8f9fa';

        // Ganti konten modal jadi loading
        const modalBody = img.parentElement.parentElement;
        const loadingEl = document.getElementById('qrLoadingText');
        if (loadingEl) loadingEl.remove();
        const loading = document.createElement('p');
        loading.id = 'qrLoadingText';
        loading.className = 'text-secondary small mt-2';
        loading.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> Memuat QR Code, harap tunggu...';
        modalBody.appendChild(loading);

        if (!qrModalInstance) {
            qrModalInstance = new bootstrap.Modal(document.getElementById('qrModalGlobal'));
        }
        qrModalInstance.show();

        // Panggil status API - jika not_found/disconnected, DeviceController akan otomatis start sesinya
        fetch(`${baseStatusUrl}/${sessionId}`)
            .then(res => res.json())
            .then(data => {
                if (data.status === 'qr') {
                    // QR sudah siap, langsung tampilkan
                    img.src = baseQrUrl + sessionId + '.png?t=' + Date.now();
                    img.style.opacity = '1';
                    if (loadingEl) loadingEl.textContent = '';
                } else {
                    // Sesi sedang dimulai, tunggu polling yang akan memperbarui gambar
                    img.src = '';
                }
            })
            .catch(() => {});

        // Mulai polling agresif setiap 2 detik saat modal terbuka
        clearInterval(window.qrPollInterval);
        window.qrPollInterval = setInterval(() => {
            if (!activeQrSession) { clearInterval(window.qrPollInterval); return; }
            fetch(`${baseStatusUrl}/${activeQrSession}`)
                .then(res => res.json())
                .then(data => {
                    const img = document.getElementById('qrModalImage');
                    const loadingEl = document.getElementById('qrLoadingText');
                    if (data.status === 'qr') {
                        img.src = baseQrUrl + activeQrSession + '.png?t=' + Date.now();
                        img.style.opacity = '1';
                        img.style.background = '';
                        if (loadingEl) loadingEl.innerHTML = '<i class="fa-solid fa-sync fa-spin me-1 text-warning"></i> QR diperbarui otomatis';
                    }
                })
                .catch(() => {});
        }, 2000);
    }

    // Hentikan polling agresif saat modal ditutup
    document.getElementById('qrModalGlobal').addEventListener('hidden.bs.modal', function () {
        activeQrSession = null;
        clearInterval(window.qrPollInterval);
    });

    // Tampilkan Sapaan modal
    function showSapaan(deviceId) {
        const modal = new bootstrap.Modal(document.getElementById('sapaanModal-' + deviceId));
        modal.show();
    }

    // Perbarui gambar QR saat modal dibuka
    document.getElementById('qrModalGlobal').addEventListener('shown.bs.modal', function () {
        if (activeQrSession) {
            const img = document.getElementById('qrModalImage');
            if (img && img.src && img.src.includes('whatsapp-qr')) {
                img.src = baseQrUrl + activeQrSession + '.png?t=' + Date.now();
            }
        }
    });

    // Polling status setiap 3 detik
    const checkStatus = () => {
        deviceSessionIds.forEach(sessionId => {
            fetch(`${baseStatusUrl}/${sessionId}`)
                .then(res => res.json())
                .then(data => {
                    const btnQr = document.getElementById('btn-qr-' + sessionId);
                    const badgeSpan = document.getElementById('status-badge-' + sessionId);

                    // Perbarui gambar di modal jika sedang terbuka untuk sesi ini
                    if (btnQr && data.status === 'qr' && activeQrSession === sessionId) {
                        const img = document.getElementById('qrModalImage');
                        if (img) img.src = baseQrUrl + sessionId + '.png?t=' + Date.now();
                    }

                    // Auto-reload saat device baru saja tersambung
                    if (btnQr && data.status === 'connected' && btnQr.dataset.wasQr === 'true') {
                        window.location.reload();
                    }
                    if (data.status === 'qr' && btnQr) {
                        btnQr.dataset.wasQr = 'true';
                    }

                    // Update badge status secara live
                    if (badgeSpan) {
                        if (data.status === 'connected') {
                            badgeSpan.innerHTML = '<span class="badge bg-success-subtle text-success px-3 py-2 rounded-pill"><i class="fa-solid fa-check-circle me-1"></i> Terhubung</span>';
                        } else if (data.status === 'qr') {
                            badgeSpan.innerHTML = '<span class="badge bg-warning-subtle text-warning px-3 py-2 rounded-pill"><i class="fa-solid fa-qrcode me-1"></i> Menunggu Scan QR</span>';
                        } else {
                            badgeSpan.innerHTML = '<span class="badge bg-danger-subtle text-danger px-3 py-2 rounded-pill"><i class="fa-solid fa-times-circle me-1"></i> Terputus</span>';
                        }
                    }
                })
                .catch(err => console.error('Status check error:', err));
        });
    };

    document.addEventListener('DOMContentLoaded', function () {
        setTimeout(checkStatus, 800);
        setInterval(checkStatus, 3000);
    });
</script>
@endsection
@endsection
