{{-- resources/views/chatbot/grup_admin.blade.php --}}
@extends('layouts.app')

@section('title', 'Manajemen Admin Grup')

@section('content')
<div class="container-fluid py-4">

    {{-- Header --}}
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h4 class="fw-bold mb-0">👨‍💼 Manajemen Admin Grup</h4>
            <small class="text-muted">{{ $grupNama }} ({{ $grupId }})</small>
        </div>
        <a href="{{ route('chatbot.grup.detail', ['grupId' => $grupId]) }}" class="btn btn-outline-secondary btn-sm">
            ← Kembali ke Detail Grup
        </a>
    </div>

    {{-- Alert --}}
    @if($errors->any())
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <strong>Terjadi Kesalahan!</strong>
            <ul class="mb-0 mt-2">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            ✅ {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-warning alert-dismissible fade show" role="alert">
            ⚠️ {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="row g-3">

        {{-- Form Tambah Admin --}}
        <div class="col-md-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-primary text-white fw-bold">
                    ➕ Tambah Admin Baru
                </div>
                <div class="card-body">
                    <form action="{{ route('chatbot.grup.admin.tambah', ['grupId' => $grupId]) }}" method="POST">
                        @csrf

                        <div class="mb-3">
                            <label for="nomor_admin" class="form-label fw-bold">Nomor WhatsApp</label>
                            <input 
                                type="text" 
                                class="form-control @error('nomor_admin') is-invalid @enderror"
                                id="nomor_admin" 
                                name="nomor_admin"
                                placeholder="62812345678..."
                                pattern="^62[0-9]{9,}$"
                                required
                            >
                            <small class="text-muted">Format: 62 + nomor (contoh: 6282123456789)</small>
                            @error('nomor_admin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="nama_admin" class="form-label fw-bold">Nama Admin (Optional)</label>
                            <input 
                                type="text" 
                                class="form-control @error('nama_admin') is-invalid @enderror"
                                id="nama_admin" 
                                name="nama_admin"
                                placeholder="Nama admin"
                            >
                            @error('nama_admin')
                                <div class="invalid-feedback d-block">{{ $message }}</div>
                            @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <i class="fas fa-plus"></i> Tambah Admin
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Daftar Admin --}}
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white fw-bold">
                    👥 Daftar Admin Grup
                    <span class="badge bg-info float-end">{{ $admins->count() }}</span>
                </div>
                <div class="card-body p-0">
                    @if($admins->isNotEmpty())
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th>#</th>
                                        <th>Nomor</th>
                                        <th>Nama</th>
                                        <th>Ditambahkan</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($admins as $admin)
                                    <tr>
                                        <td>
                                            <span class="badge bg-secondary">{{ $loop->iteration }}</span>
                                        </td>
                                        <td>
                                            <strong>{{ $admin->nomor_admin }}</strong>
                                        </td>
                                        <td>
                                            {{ $admin->nama_admin ?? '<span class="text-muted">-</span>' }}
                                        </td>
                                        <td>
                                            <small class="text-muted">
                                                {{ \Carbon\Carbon::parse($admin->created_at)->format('d/m/Y H:i') }}
                                            </small>
                                        </td>
                                        <td>
                                            <form 
                                                action="{{ route('chatbot.grup.admin.hapus', ['grupId' => $grupId, 'nomorAdmin' => $admin->nomor_admin]) }}" 
                                                method="POST" 
                                                style="display:inline;"
                                            >
                                                @csrf
                                                @method('DELETE')
                                                <button 
                                                    type="submit" 
                                                    class="btn btn-sm btn-danger"
                                                    onclick="return confirm('Hapus admin {{ $admin->nomor_admin }}?')"
                                                >
                                                    <i class="fas fa-trash"></i> Hapus
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="alert alert-info m-0 rounded-0">
                            <i class="fas fa-info-circle"></i>
                            Belum ada admin di grup ini. Tambahkan admin baru menggunakan form di samping.
                        </div>
                    @endif
                </div>
            </div>

            {{-- Info Bantuan --}}
            <div class="card border-0 shadow-sm mt-3">
                <div class="card-header bg-light fw-bold">
                    ℹ️ Informasi Perintah WhatsApp
                </div>
                <div class="card-body">
                    <p class="mb-2"><strong>Admin dapat menggunakan perintah:</strong></p>
                    <ul class="mb-0">
                        <li><code>!admin</code> — Lihat semua admin di grup</li>
                        <li><code>!set-admin [nomor]</code> — Tambah admin baru</li>
                        <li><code>!hapus-admin [nomor]</code> — Hapus admin</li>
                        <li><code>!hapus [id]</code> — Hapus catatan (hanya admin)</li>
                    </ul>
                </div>
            </div>
        </div>

    </div>

</div>

<style>
    .table-responsive {
        max-height: 500px;
        overflow-y: auto;
    }
    
    code {
        background-color: #f4f4f4;
        padding: 2px 6px;
        border-radius: 3px;
        font-size: 0.9em;
    }
</style>

@endsection
