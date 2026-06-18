{{-- resources/views/chatbot/grup_detail.blade.php --}}
@extends('layouts.app')

@section('title', 'Detail Grup')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">💬 {{ $grupNama ?? 'Detail Grup' }}</h4>
            <small class="text-muted">{{ $grupId }}</small>
            @if(env('WHATSAPP_GROUP_ID_SELLER') === $grupId)
                <span class="badge bg-success ms-2"><i class="fa-solid fa-check-circle me-1"></i>Grup Admin Aktif</span>
            @endif
        </div>
        <div>
            @if(env('WHATSAPP_GROUP_ID_SELLER') === $grupId)
                <form action="{{ route('chatbot.grup.unsetadmin', ['grupId' => urlencode($grupId)]) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-warning btn-sm me-2 text-dark" onclick="return confirm('Batalkan grup ini sebagai Admin Utama? Notifikasi pesanan baru dari website tidak akan dikirim ke sini lagi.');">
                        <i class="fa-solid fa-xmark me-1"></i> Batalkan Admin Utama
                    </button>
                </form>
            @else
                <form action="{{ route('chatbot.grup.setadmin', ['grupId' => urlencode($grupId)]) }}" method="POST" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-primary btn-sm me-2" onclick="return confirm('Jadikan grup ini sebagai penerima Notifikasi Pesanan Baru dan Pengingat?');">
                        <i class="fa-solid fa-crown me-1"></i> Jadikan Grup Admin Toko
                    </button>
                </form>
            @endif

            <form action="{{ route('chatbot.grup.whitelist', ['grupId' => urlencode($grupId)]) }}" method="POST" class="d-inline">
                @csrf
                @php $isWhitelisted = $settings['is_whitelisted'] ?? '0'; @endphp
                @if($isWhitelisted === '1')
                    <button type="submit" class="btn btn-danger btn-sm me-2" onclick="return confirm('Cabut izin bot dari grup ini? Bot akan mengabaikan semua pesan dari grup ini.');">
                        <i class="fa-solid fa-ban me-1"></i> Cabut Izin Bot
                    </button>
                @else
                    <button type="submit" class="btn btn-success btn-sm me-2" onclick="return confirm('Izinkan bot beroperasi di grup ini?');">
                        <i class="fa-solid fa-check me-1"></i> Izinkan Bot Beroperasi
                    </button>
                @endif
            </form>

            <a href="{{ route('chatbot.grup') }}" class="btn btn-outline-secondary btn-sm">
                🔙 Kembali
            </a>
        </div>
    </div>

    {{-- Navigation Tabs --}}
    <div class="mb-4 border-bottom">
        <ul class="nav nav-tabs" id="grupTabs" role="tablist">
            <li class="nav-item" role="presentation">
                <button class="nav-link active fw-bold" id="detail-tab" data-bs-toggle="tab" data-bs-target="#detail" type="button" role="tab" style="color: var(--bs-primary);">
                    <i class="fa-solid fa-message me-1"></i> Riwayat & Kirim Pesan
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="autoreply-tab" data-bs-toggle="tab" data-bs-target="#autoreply" type="button" role="tab" style="color: var(--bs-teal);">
                    <i class="fa-solid fa-robot me-1"></i> Kata Kunci & Auto-Reply
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="catatan-tab" data-bs-toggle="tab" data-bs-target="#catatan" type="button" role="tab" style="color: var(--bs-warning);">
                    <i class="fa-solid fa-note-sticky me-1"></i> Catatan & Pengingat
                </button>
            </li>
            <li class="nav-item" role="presentation">
                <button class="nav-link fw-bold" id="admin-tab" data-bs-toggle="tab" data-bs-target="#admin" type="button" role="tab" style="color: var(--bs-info);">
                    <i class="fa-solid fa-user-shield me-1"></i> Kelola Admin
                </button>
            </li>
        </ul>
    </div>

    {{-- Tab Content --}}
    <div class="tab-content" id="grupTabContent">

        {{-- Tab 1: Riwayat & Kirim Pesan --}}
        <div class="tab-pane fade show active" id="detail" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3">
                            💬 Riwayat Pesan Grup
                            <span class="badge bg-primary float-end rounded-pill">{{ $pesan->total() }}</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pengirim</th>
                                            <th>Pesan</th>
                                            <th style="width: 100px;">Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pesan as $p)
                                        <tr>
                                            <td>
                                                @if($p->pengirim === 'Sistem/Admin' || $p->pengirim === 'Broadcast Admin')
                                                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle rounded-pill px-2 py-1"><i class="fa-solid fa-robot me-1"></i>{{ $p->pengirim }}</span>
                                                @else
                                                    <small class="fw-bold">{{ $p->nama_pengirim ?? $p->pengirim }}</small>
                                                @endif
                                            </td>
                                            <td>{{ $p->pesan }}</td>
                                            <td><small class="text-muted">{{ \Carbon\Carbon::parse($p->waktu)->format('d/m H:i') }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <i class="fa-regular fa-comments fs-2 mb-2 d-block"></i> Belum ada pesan
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white pt-3 pb-2 border-top">
                            {{ $pesan->links() }}
                        </div>
                    </div>
                </div>

                <div class="col-md-5">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white fw-bold text-primary py-3">
                            <i class="fa-solid fa-paper-plane me-2"></i>Kirim Pesan ke Grup
                        </div>
                        <div class="card-body">
                            <form action="{{ route('chatbot.grup.kirim', ['grupId' => urlencode($grupId)]) }}" method="POST">
                                @csrf
                                <div class="mb-3">
                                    <label class="form-label small fw-semibold text-secondary">Pesan</label>
                                    <textarea name="pesan" class="form-control" rows="4" placeholder="Ketik pesan yang ingin dikirimkan bot ke grup ini..." required></textarea>
                                </div>
                                <button class="btn btn-primary w-100 rounded-pill" type="submit">
                                    Kirim Sekarang <i class="fa-solid fa-paper-plane ms-1"></i>
                                </button>
                                <small class="text-muted mt-3 d-block text-center">
                                    Pesan akan dikirim oleh bot ke grup <strong>{{ $grupNama }}</strong>.
                                </small>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 2: Kata Kunci & Auto-Reply --}}
        <div class="tab-pane fade" id="autoreply" role="tabpanel">
            <div class="row g-4">
                {{-- Daftar Auto-Reply Kustom --}}
                <div class="col-md-7">
                    <div class="card border-0 shadow-sm mb-4">
                        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                            <span class="fw-bold text-teal"><i class="fa-solid fa-robot me-2"></i>Daftar Balasan Otomatis Kustom</span>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Kata Kunci (Pemicu)</th>
                                            <th>Balasan Bot</th>
                                            <th style="width: 70px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($autoReplies as $ar)
                                        <tr>
                                            <td>
                                                <span class="badge bg-teal-subtle text-teal border border-teal-subtle px-2 py-1 rounded-pill">{{ $ar->keyword }}</span>
                                                @if($ar->is_exact_match)
                                                    <span class="badge bg-secondary ms-1" style="font-size: 10px;" title="Harus sama persis">Exact</span>
                                                @else
                                                    <span class="badge bg-info ms-1" style="font-size: 10px;" title="Mengandung kata ini">Partial</span>
                                                @endif
                                            </td>
                                            <td><small>{{ $ar->balasan }}</small></td>
                                            <td class="d-flex gap-1">
                                                <button type="button" class="btn btn-sm btn-outline-primary px-2 rounded-pill" data-bs-toggle="modal" data-bs-target="#editAutoReplyModal{{ $ar->id }}">
                                                    <i class="fa-solid fa-pen"></i>
                                                </button>
                                                <form action="{{ route('chatbot.grup.autoreply.hapus', ['grupId' => urlencode($grupId), 'id' => $ar->id]) }}" method="POST" onsubmit="return confirm('Hapus auto-reply ini?');">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 rounded-pill"><i class="fa-solid fa-trash-can"></i></button>
                                                </form>

                                                {{-- Modal Edit Auto Reply --}}
                                                <div class="modal fade" id="editAutoReplyModal{{ $ar->id }}" tabindex="-1" aria-labelledby="editAutoReplyModalLabel{{ $ar->id }}" aria-hidden="true">
                                                    <div class="modal-dialog">
                                                        <div class="modal-content text-start">
                                                            <div class="modal-header">
                                                                <h5 class="modal-title fs-6" id="editAutoReplyModalLabel{{ $ar->id }}">Edit Auto-Reply</h5>
                                                                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                            </div>
                                                            <form action="{{ route('chatbot.grup.autoreply.update', ['grupId' => urlencode($grupId), 'id' => $ar->id]) }}" method="POST">
                                                                @csrf @method('PUT')
                                                                <div class="modal-body">
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Kata Kunci</label>
                                                                        <input type="text" name="keyword" class="form-control" value="{{ $ar->keyword }}" required>
                                                                    </div>
                                                                    <div class="mb-3">
                                                                        <label class="form-label small fw-bold">Balasan</label>
                                                                        <textarea name="balasan" class="form-control" rows="3" required>{{ $ar->balasan }}</textarea>
                                                                    </div>
                                                                    <div class="form-check form-switch">
                                                                        <input class="form-check-input" type="checkbox" role="switch" name="is_exact_match" id="editExactMatch{{ $ar->id }}" {{ $ar->is_exact_match ? 'checked' : '' }}>
                                                                        <label class="form-check-label small" for="editExactMatch{{ $ar->id }}">Exact Match (Pesan harus sama persis dengan kata kunci)</label>
                                                                    </div>
                                                                </div>
                                                                <div class="modal-footer">
                                                                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Batal</button>
                                                                    <button type="submit" class="btn btn-sm btn-primary">Simpan Perubahan</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </div>
                                                </div>
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <i class="fa-solid fa-ghost fs-2 mb-2 d-block text-secondary"></i> Belum ada auto-reply kustom untuk grup ini.
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- Form Tambah Auto Reply --}}
                    <div class="card border-0 shadow-sm bg-light">
                        <div class="card-body">
                            <h6 class="fw-bold text-teal mb-3"><i class="fa-solid fa-plus-circle me-1"></i> Tambah Auto-Reply Kustom</h6>
                            <form action="{{ route('chatbot.grup.autoreply.simpan', ['grupId' => urlencode($grupId)]) }}" method="POST">
                                @csrf
                                <div class="row g-2 mb-2">
                                    <div class="col-md-5">
                                        <input type="text" name="keyword" class="form-control form-control-sm" placeholder="Kata Kunci (Misal: halo bot)" required>
                                    </div>
                                    <div class="col-md-7 d-flex align-items-center">
                                        <div class="form-check form-switch ms-2">
                                            <input class="form-check-input" type="checkbox" role="switch" name="is_exact_match" id="exactMatch" checked>
                                            <label class="form-check-label small" for="exactMatch" title="Jika aktif, pesan harus SAMA PERSIS dengan kata kunci. Jika nonaktif, pesan hanya perlu MENGANDUNG kata kunci.">Exact Match</label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-2">
                                    <textarea name="balasan" class="form-control form-control-sm" rows="2" placeholder="Teks balasan dari bot..." required></textarea>
                                </div>
                                <button type="submit" class="btn btn-sm btn-teal text-white w-100 rounded-pill" style="background-color: var(--bs-teal);">Simpan Auto-Reply</button>
                            </form>
                        </div>
                    </div>
                </div>

                {{-- Pengaturan Alias Perintah Bawaan --}}
                <div class="col-md-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3 text-secondary">
                            <i class="fa-solid fa-sliders me-2"></i>Pengaturan Alias Perintah Bot
                        </div>
                        <div class="card-body">
                            <div class="alert alert-light border small text-muted mb-4">
                                Anda dapat mengubah awalan perintah bawaan bot. Pisahkan dengan koma (,) jika memiliki lebih dari 1 alias. Jangan hapus tanda seru <strong>(!)</strong> jika ingin menggunakan format tersebut.
                            </div>
                            <form action="{{ route('chatbot.grup.pengaturan.simpan', ['grupId' => urlencode($grupId)]) }}" method="POST">
                                @csrf
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Menu Bantuan</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_bantuan" class="form-control form-control-sm" value="{{ $settings['cmd_bantuan'] ?? '!bantuan,!help' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Simpan Catatan</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_simpan" class="form-control form-control-sm" value="{{ $settings['cmd_simpan'] ?? '!simpan' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Lihat Catatan</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_catatan" class="form-control form-control-sm" value="{{ $settings['cmd_catatan'] ?? '!catatan,!ringkasan' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Hapus Catatan</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_hapus" class="form-control form-control-sm" value="{{ $settings['cmd_hapus'] ?? '!hapus' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Buat Pengingat</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_ingatkan" class="form-control form-control-sm" value="{{ $settings['cmd_ingatkan'] ?? '!ingatkan,!reminder' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Edit Pengingat</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_edit_pengingat" class="form-control form-control-sm" value="{{ $settings['cmd_edit_pengingat'] ?? '!edit-pengingat' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Daftar Pengingat</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_pengingat" class="form-control form-control-sm" value="{{ $settings['cmd_pengingat'] ?? '!pengingat' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Hapus Pengingat</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_hapus_pengingat" class="form-control form-control-sm" value="{{ $settings['cmd_hapus_pengingat'] ?? '!hapus-pengingat,!delete-reminder' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Cari Pesan</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_cari" class="form-control form-control-sm" value="{{ $settings['cmd_cari'] ?? '!cari' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Lihat Admin</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_admin" class="form-control form-control-sm" value="{{ $settings['cmd_admin'] ?? '!admin' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Set Admin</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_set_admin" class="form-control form-control-sm" value="{{ $settings['cmd_set_admin'] ?? '!set-admin,!tambah-admin' }}">
                                    </div>
                                </div>
                                <div class="mb-2 row align-items-center">
                                    <label class="col-sm-5 col-form-label fw-semibold small text-secondary">Hapus Admin</label>
                                    <div class="col-sm-7">
                                        <input type="text" name="cmd_hapus_admin" class="form-control form-control-sm" value="{{ $settings['cmd_hapus_admin'] ?? '!hapus-admin,!remove-admin' }}">
                                    </div>
                                </div>
                                <hr class="my-3 text-light">
                                <button type="submit" class="btn btn-primary w-100 rounded-pill">Simpan Pengaturan</button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Tab 3: Catatan & Pengingat --}}
        <div class="tab-pane fade" id="catatan" role="tabpanel">
            <div class="row g-4">
                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3 text-warning">
                            <i class="fa-solid fa-note-sticky me-2"></i> Catatan Grup
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Catatan</th>
                                            <th>Disimpan Oleh</th>
                                            <th>Waktu</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($catatan as $c)
                                        <tr>
                                            <td>{{ $c->isi }}</td>
                                            <td><small class="fw-bold">{{ $c->disimpan_oleh }}</small></td>
                                            <td><small class="text-muted">{{ \Carbon\Carbon::parse($c->waktu)->format('d/m H:i') }}</small></td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <i class="fa-regular fa-folder-open fs-2 mb-2 d-block"></i> Belum ada catatan
                                            </td>
                                        </tr>
                                        @endforelse
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white fw-bold py-3 text-danger">
                            <i class="fa-solid fa-clock me-2"></i> Pengingat Grup
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-hover align-middle mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th>Pesan</th>
                                            <th>Jadwal</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @forelse($pengingat as $p)
                                        <tr>
                                            <td>
                                                <div style="max-width: 200px; white-space: pre-wrap;" class="small">
                                                    @php
                                                        $data = json_decode($p->pesan, true);
                                                    @endphp
                                                    @if($data && isset($data['kegiatan']))
                                                        <strong>{{ $data['kegiatan'] }}</strong>
                                                        <div class="text-secondary" style="font-size: 0.75rem;">
                                                            {{ $data['tempat'] ?? '-' }} | {{ $data['pemilik'] ?? '-' }}
                                                        </div>
                                                        @if(isset($data['link_zoom']) || isset($data['id_zoom']))
                                                            <div class="mt-1" style="font-size: 0.7rem;">
                                                                @if(isset($data['link_zoom'])) <span class="text-primary"><i class="fa-solid fa-video"></i> {{ Str::limit($data['link_zoom'], 20) }}</span><br> @endif
                                                                @if(isset($data['id_zoom'])) <span class="text-muted">ID: {{ $data['id_zoom'] }}</span> @endif
                                                            </div>
                                                        @endif
                                                    @else
                                                        {{ Str::limit($p->pesan, 50) }}
                                                    @endif
                                                </div>
                                            </td>
                                            <td><small class="fw-semibold text-danger">{{ \Carbon\Carbon::parse($p->waktu_ingatkan)->format('d M H:i') }}</small></td>
                                            <td>
                                                @if($p->sudah_dikirim)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle px-2 py-1 rounded-pill">Terkirim</span>
                                                @else
                                                    <span class="badge bg-warning-subtle text-warning border border-warning-subtle px-2 py-1 rounded-pill">Menunggu</span>
                                                @endif
                                            </td>
                                        </tr>
                                        @empty
                                        <tr>
                                            <td colspan="3" class="text-center text-muted py-5">
                                                <i class="fa-regular fa-bell-slash fs-2 mb-2 d-block"></i> Belum ada pengingat
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
        </div>

        {{-- Tab 4: Kelola Admin --}}
        <div class="tab-pane fade" id="admin" role="tabpanel">
            <div class="card border-0 shadow-sm text-center py-5">
                <div class="card-body">
                    <i class="fa-solid fa-user-shield fs-1 text-info mb-3"></i>
                    <h5 class="fw-bold text-dark">Manajemen Admin Grup</h5>
                    <p class="text-secondary mb-4">Atur siapa saja yang berhak menggunakan perintah khusus admin di grup ini.</p>
                    <a href="{{ route('chatbot.grup.admin', ['grupId' => urlencode($grupId)]) }}" class="btn btn-info text-white rounded-pill px-4">
                        <i class="fa-solid fa-external-link-alt me-2"></i> Buka Halaman Pengaturan Admin
                    </a>
                </div>
            </div>
        </div>

    </div>
</div>
@endsection
