@extends('layouts.app')

@section('title', 'Riwayat Pesan Chatbot')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Log Pesan</h2>
            <p class="text-secondary mb-0 small">Seluruh riwayat pesan WhatsApp masuk dan keluar yang tercatat</p>
        </div>
        <a href="{{ route('chatbot.dashboard') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- Form Filter --}}
    <div class="card-premium p-4 mb-4">
        <form method="GET" action="{{ route('chatbot.pesan') }}">
            <div class="row g-3 align-items-center">
                <!-- Filter Nomor -->
                <div class="col-md-4">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-light" style="border-radius: 12px 0 0 12px;">
                            <i class="fa-solid fa-filter text-secondary"></i>
                        </span>
                        <input type="text" name="nomor" class="form-control form-control-premium border-start-0 ps-1"
                            placeholder="Filter nomor WA..." value="{{ request('nomor') }}" style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>
                
                <!-- Filter Arah -->
                <div class="col-md-3">
                    <select name="arah" class="form-select form-control-premium">
                        <option value="">Semua Arah</option>
                        <option value="masuk"  {{ request('arah') == 'masuk'  ? 'selected' : '' }}>📥 Pesan Masuk</option>
                        <option value="keluar" {{ request('arah') == 'keluar' ? 'selected' : '' }}>📤 Pesan Keluar</option>
                    </select>
                </div>
                
                <!-- Aksi -->
                <div class="col-auto">
                    <button type="submit" class="btn btn-premium btn-premium-brand px-4">Terapkan Filter</button>
                    <a href="{{ route('chatbot.pesan') }}" class="btn btn-premium btn-light border px-4">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel Pesan --}}
    <div class="card-premium p-0 overflow-hidden mb-4">
        <div class="px-4 py-3 bg-white border-bottom border-light d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                <i class="fa-solid fa-list text-secondary me-2"></i>Log Aktivitas Pesan
            </h5>
            <div class="d-flex align-items-center gap-2">
                @if(request('nomor'))
                    <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill small">Nomor: {{ request('nomor') }}</span>
                @endif
                <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">Total: <strong>{{ $pesan->total() }}</strong></span>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table table-premium align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Nomor WA</th>
                        <th>Arah</th>
                        <th>Isi Pesan</th>
                        <th>Waktu Kirim</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pesan as $i => $p)
                    <tr>
                        <td><span class="text-secondary small fw-bold">{{ $pesan->firstItem() + $i }}</span></td>
                        <td>
                            <a href="{{ route('chatbot.pesan', ['nomor' => $p->nomor]) }}" class="text-decoration-none text-dark fw-bold hover-primary">
                                {{ $p->nomor }}
                            </a>
                        </td>
                        <td>
                            @if($p->arah === 'masuk')
                                <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-1.5 rounded-pill">Masuk</span>
                            @else
                                <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1.5 rounded-pill">Keluar</span>
                            @endif
                        </td>
                        <td>
                            <div class="text-break" style="max-width: 500px;">
                                @if($p->media_url)
                                    <div class="mb-2">
                                        <a href="{{ $p->media_url }}" target="_blank" class="d-inline-flex align-items-center gap-2 badge bg-info-subtle text-info border border-info-subtle px-3 py-1.5 rounded-pill text-decoration-none">
                                            <i class="fa-regular fa-image fs-6"></i>
                                            <span>Lihat Media</span>
                                        </a>
                                    </div>
                                @endif
                                <span style="white-space: pre-wrap;" class="text-dark small">{{ $p->isi }}</span>
                            </div>
                        </td>
                        <td>
                            <small class="text-secondary fw-semibold">{{ \Carbon\Carbon::parse($p->waktu)->format('d/m/Y H:i:s') }}</small>
                            <small class="text-muted d-block" style="font-size: 0.75rem;">{{ \Carbon\Carbon::parse($p->waktu)->diffForHumans() }}</small>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="text-center text-secondary py-5">
                            <i class="fa-solid fa-folder-open fs-3 mb-2"></i>
                            <div>Belum ada data pesan tercatat</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <div class="px-4 py-3 bg-white border-top border-light">
            {{ $pesan->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection