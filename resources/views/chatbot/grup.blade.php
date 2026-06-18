@extends('layouts.app')

@section('title', 'Dashboard Grup')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Grup Chatbot</h2>
            <p class="text-secondary mb-0 small">Manajemen aktivitas chatbot pada grup WhatsApp terhubung</p>
        </div>
        <div class="d-flex gap-2">
            <button type="button" class="btn btn-premium btn-primary rounded-pill px-4" data-bs-toggle="modal" data-bs-target="#broadcastModal">
                <i class="fa-solid fa-bullhorn me-2"></i> Broadcast Pesan
            </button>
            <a href="{{ route('chatbot.dashboard') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
                <i class="fa-solid fa-arrow-left me-1"></i> Kembali
            </a>
        </div>
    </div>

    {{-- Statistik Grup --}}
    <div class="row g-4 mb-4">
        <div class="col-md-3">
            <div class="card-premium stat-card brand p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Total Grup</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $totalGrup }}</div>
                        <span class="badge bg-light text-primary border rounded-pill">Grup WhatsApp</span>
                    </div>
                    <div class="icon-box-premium brand">
                        <i class="fa-solid fa-comments"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium stat-card success p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Total Pesan Grup</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $totalPesan }}</div>
                        <span class="badge bg-light text-success border rounded-pill">Tercatat</span>
                    </div>
                    <div class="icon-box-premium success">
                        <i class="fa-solid fa-message"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium stat-card warning p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Total Catatan</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $totalCatatan }}</div>
                        <span class="badge bg-light text-warning border rounded-pill">Mini Knowledge Base</span>
                    </div>
                    <div class="icon-box-premium warning">
                        <i class="fa-solid fa-notebook"></i>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card-premium stat-card danger p-4">
                <div class="d-flex justify-content-between align-items-start">
                    <div>
                        <div class="text-secondary small fw-semibold mb-1">Pengingat Aktif</div>
                        <div class="fs-1 fw-extrabold text-dark tracking-tight mb-2">{{ $totalPengingat }}</div>
                        <span class="badge bg-light text-danger border rounded-pill">Menunggu Waktu</span>
                    </div>
                    <div class="icon-box-premium danger">
                        <i class="fa-solid fa-bell"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="row g-4">
        {{-- Daftar Grup --}}
        <div class="col-md-5">
            <div class="card-premium p-0 overflow-hidden">
                <div class="px-4 py-3 bg-white border-bottom border-light">
                    <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                        <i class="fa-solid fa-server text-secondary me-2"></i>Daftar Grup Aktif
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium align-middle">
                        <thead>
                            <tr>
                                <th>Nama Grup</th>
                                <th>Pesan</th>
                                <th>Notes</th>
                                <th style="width: 140px;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($daftarGrup as $grup)
                            <tr>
                                <td>
                                    <div class="fw-bold text-dark">{{ $grup->grup_nama ?? 'Grup Tanpa Nama' }}</div>
                                    <small class="text-secondary d-block font-monospace" style="font-size: 0.75rem;">{{ Str::limit($grup->grup_id, 25) }}</small>
                                </td>
                                <td>
                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2.5 py-1 rounded-pill">{{ $grup->total_pesan }}</span>
                                </td>
                                <td>
                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2.5 py-1 rounded-pill">{{ $grup->total_catatan }}</span>
                                </td>
                                <td>
                                    <div class="d-flex gap-1.5">
                                        <a href="{{ route('chatbot.grup.detail', ['grupId' => urlencode($grup->grup_id)]) }}"
                                           class="btn btn-sm btn-outline-primary px-2.5 rounded-pill"
                                           title="Detail">
                                            <i class="fa-solid fa-list-ul"></i> Detail
                                        </a>
                                        <a href="{{ route('chatbot.grup.admin', ['grupId' => urlencode($grup->grup_id)]) }}"
                                           class="btn btn-sm btn-outline-info px-2.5 rounded-pill"
                                           title="Admin">
                                            <i class="fa-solid fa-user-shield"></i> Admin
                                        </a>
                                        <form action="{{ route('chatbot.grup.abaikan', ['grupId' => urlencode($grup->grup_id)]) }}" method="POST" style="display:inline-block;" class="form-abaikan">
                                            @csrf
                                            <button type="button" class="btn btn-sm btn-outline-danger px-2.5 rounded-pill btn-abaikan" title="Abaikan Grup">
                                                <i class="fa-solid fa-eye-slash"></i> Abaikan
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-layer-group fs-3 mb-2"></i>
                                    <div>Belum ada grup yang aktif tercatat</div>
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Pengingat Aktif --}}
        <div class="col-md-7">
            <div class="card-premium p-0 overflow-hidden">
                <div class="px-4 py-3 bg-white border-bottom border-light">
                    <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                        <i class="fa-solid fa-bell-concierge text-secondary me-2"></i>Pengingat Aktif Grup
                    </h5>
                </div>
                <div class="table-responsive">
                    <table class="table table-premium align-middle">
                        <thead>
                            <tr>
                                <th>Grup WA</th>
                                <th>Isi Pengingat / Kegiatan</th>
                                <th>Waktu Pengingat</th>
                                <th>Dibuat Oleh</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($pengingat as $p)
                            <tr>
                                <td>
                                    <small class="fw-semibold text-dark font-monospace" style="font-size: 0.75rem;">{{ Str::limit($p->grup_id, 15) }}</small>
                                </td>
                                <td>
                                    <div class="text-dark small" style="max-width: 250px; white-space: pre-wrap;">
                                        @php
                                            $data = json_decode($p->pesan, true);
                                        @endphp
                                        @if($data && isset($data['kegiatan']))
                                            <strong>{{ $data['kegiatan'] }}</strong>
                                            <div class="text-secondary" style="font-size: 0.75rem;">
                                                <i class="fa-solid fa-location-dot me-1"></i>{{ $data['tempat'] ?? '-' }} | <i class="fa-solid fa-user me-1"></i>{{ $data['pemilik'] ?? '-' }}
                                            </div>
                                        @else
                                            {{ $p->pesan }}
                                        @endif
                                    </div>
                                </td>
                                <td>
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle px-3 py-1.5 rounded-pill fw-semibold">
                                        <i class="fa-regular fa-clock me-1"></i>{{ \Carbon\Carbon::parse($p->waktu_ingatkan)->format('d M H:i') }}
                                    </span>
                                    <small class="text-muted d-block mt-1" style="font-size: 0.72rem;">{{ \Carbon\Carbon::parse($p->waktu_ingatkan)->diffForHumans() }}</small>
                                </td>
                                <td>
                                    <span class="text-secondary small">{{ $p->dibuat_oleh }}</span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center text-secondary py-5">
                                    <i class="fa-solid fa-bell-slash fs-3 mb-2"></i>
                                    <div>Tidak ada jadwal pengingat grup yang aktif</div>
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

{{-- Modal Broadcast --}}
<div class="modal fade" id="broadcastModal" tabindex="-1" aria-labelledby="broadcastModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content border-0 shadow">
            <form action="{{ route('chatbot.grup.broadcast') }}" method="POST">
                @csrf
                <div class="modal-header bg-light border-bottom-0">
                    <h5 class="modal-title fw-bold" id="broadcastModalLabel"><i class="fa-solid fa-bullhorn text-primary me-2"></i>Broadcast Pesan ke Grup</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="mb-3">
                        <label class="form-label fw-bold">Pilih Grup Tujuan</label>
                        <div class="border rounded p-3" style="max-height: 200px; overflow-y: auto;">
                            <div class="form-check mb-2 pb-2 border-bottom">
                                <input class="form-check-input" type="checkbox" id="checkAllGroups" onclick="toggleAllGroups(this)">
                                <label class="form-check-label fw-bold text-primary" for="checkAllGroups">
                                    Pilih Semua Grup
                                </label>
                            </div>
                            @forelse($daftarGrup as $grup)
                            <div class="form-check mb-1">
                                <input class="form-check-input group-checkbox" type="checkbox" name="grup_ids[]" value="{{ $grup->grup_id }}" id="grup_{{ $loop->index }}">
                                <label class="form-check-label" for="grup_{{ $loop->index }}">
                                    <strong>{{ $grup->grup_nama ?? 'Grup Tanpa Nama' }}</strong> 
                                    <small class="text-muted">({{ $grup->total_pesan }} pesan)</small>
                                </label>
                            </div>
                            @empty
                            <div class="text-muted small">Belum ada grup yang tersedia.</div>
                            @endforelse
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label fw-bold">Isi Pesan Broadcast</label>
                        <textarea class="form-control bg-light border-light" name="pesan" rows="6" placeholder="Ketik pesan broadcast di sini... Anda bisa menggunakan format *tebal*, _miring_, atau ~coret~" required></textarea>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">
                        <i class="fa-solid fa-paper-plane me-2"></i> Kirim Broadcast
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function toggleAllGroups(source) {
        checkboxes = document.querySelectorAll('.group-checkbox');
        for(var i=0, n=checkboxes.length;i<n;i++) {
            checkboxes[i].checked = source.checked;
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        const abaikanBtns = document.querySelectorAll('.btn-abaikan');
        abaikanBtns.forEach(btn => {
            btn.addEventListener('click', function(e) {
                const form = this.closest('form');
                Swal.fire({
                    title: 'Abaikan Grup?',
                    text: 'Grup ini akan disembunyikan dari dashboard. Anda yakin?',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Ya, Abaikan!',
                    cancelButtonText: 'Batal'
                }).then((result) => {
                    if (result.isConfirmed) {
                        form.submit();
                    }
                });
            });
        });
    });
</script>@endsection
