@extends('layouts.app')

@section('title', 'Daftar User Chatbot')

@section('content')
<div class="container-fluid">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Database User</h2>
            <p class="text-secondary mb-0 small">Daftar pengguna yang berinteraksi dengan chatbot Anda</p>
        </div>
        <a href="{{ route('chatbot.dashboard') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
            <i class="fa-solid fa-arrow-left me-1"></i> Kembali
        </a>
    </div>

    {{-- Form Cari --}}
    <div class="card-premium p-4 mb-4">
        <form method="GET" action="{{ route('chatbot.users') }}">
            <div class="row g-3 align-items-center">
                <div class="col-md-5">
                    <div class="input-group">
                        <span class="input-group-text bg-light border-end-0 border-light" style="border-radius: 12px 0 0 12px;">
                            <i class="fa-solid fa-magnifying-glass text-secondary"></i>
                        </span>
                        <input type="text" name="cari" class="form-control form-control-premium border-start-0 ps-1"
                            placeholder="Cari nomor HP atau nama..." value="{{ request('cari') }}" style="border-radius: 0 12px 12px 0;">
                    </div>
                </div>
                <div class="col-auto">
                    <button type="submit" class="btn btn-premium btn-premium-brand px-4">Cari</button>
                    <a href="{{ route('chatbot.users') }}" class="btn btn-premium btn-light border px-4">Reset</a>
                </div>
            </div>
        </form>
    </div>

    {{-- Tabel User --}}
    <div class="card-premium p-0 overflow-hidden mb-4">
        <div class="px-4 py-3 bg-white border-bottom border-light d-flex justify-content-between align-items-center">
            <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                <i class="fa-solid fa-users-viewfinder text-secondary me-2"></i>Daftar Pengguna
            </h5>
            <span class="badge bg-light text-dark border px-3 py-2 rounded-pill small">Total: <strong>{{ $users->total() }}</strong></span>
        </div>
        
        <div class="table-responsive">
            <table class="table table-premium align-middle">
                <thead>
                    <tr>
                        <th style="width: 60px;">#</th>
                        <th>Nomor WA</th>
                        <th>Nama Kontak</th>
                        <th>Langkah/Menu</th>
                        <th>Pertama Chat</th>
                        <th>Terakhir Chat</th>
                        <th style="width: 120px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $i => $user)
                    <tr>
                        <td><span class="text-secondary small fw-bold">{{ $users->firstItem() + $i }}</span></td>
                        <td>
                            <div class="d-flex align-items-center gap-2">
                                <div class="bg-primary-subtle text-primary rounded-pill d-flex align-items-center justify-content-center" style="width: 32px; height: 32px;">
                                    <i class="fa-brands fa-whatsapp fs-5"></i>
                                </div>
                                <span class="fw-bold text-dark">{{ $user->nomor }}</span>
                            </div>
                        </td>
                        <td>
                            <span class="text-dark fw-semibold">{{ $user->nama ?? '-' }}</span>
                        </td>
                        <td>
                            <span class="badge bg-light text-secondary border border-light px-3 py-1.5 rounded-pill">
                                {{ strtoupper($user->langkah) }}
                            </span>
                        </td>
                        <td>
                            <small class="text-secondary">{{ \Carbon\Carbon::parse($user->pertama_chat)->format('d/m/Y H:i') }}</small>
                        </td>
                        <td>
                            <small class="text-secondary fw-semibold">{{ \Carbon\Carbon::parse($user->terakhir_chat)->diffForHumans() }}</small>
                        </td>
                        <td>
                            <a href="{{ route('chatbot.pesan', ['nomor' => $user->nomor]) }}"
                               class="btn btn-sm btn-outline-primary px-3 rounded-pill">
                                <i class="fa-solid fa-comments me-1"></i> Riwayat
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="text-center text-secondary py-5">
                            <i class="fa-solid fa-users-slash fs-3 mb-2"></i>
                            <div>Belum ada data user tercatat</div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <div class="px-4 py-3 bg-white border-top border-light">
            {{ $users->appends(request()->query())->links() }}
        </div>
    </div>

</div>
@endsection