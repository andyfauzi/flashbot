@extends('layouts.app')

@section('title', 'Pengelolaan Stok')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">📦 Pengelolaan Stok</h2>
            <p class="text-secondary mb-0 small">Perbarui jumlah stok semua produk dan varian dengan cepat.</p>
        </div>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-check-circle me-2"></i> {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif
    
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <form action="{{ route('chatbot.stok.update') }}" method="POST">
            @csrf
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="bg-light">
                            <tr>
                                <th class="px-4 py-3" style="width: 15%">Kode Produk</th>
                                <th style="width: 30%">Nama Produk</th>
                                <th style="width: 20%">Varian</th>
                                <th class="text-center" style="width: 15%">Sedang Diproses (Dapur)</th>
                                <th class="text-center px-4" style="width: 20%">Sisa Stok</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($produks as $produk)
                                @if($produk->varians->count() > 0)
                                    @foreach($produk->varians as $index => $varian)
                                        <tr>
                                            @if($index === 0)
                                                <td class="px-4" rowspan="{{ $produk->varians->count() }}">
                                                    <span class="badge bg-secondary">{{ $produk->kode }}</span>
                                                </td>
                                                <td class="fw-bold" rowspan="{{ $produk->varians->count() }}">
                                                    {{ $produk->nama }}
                                                    <div class="small text-muted fw-normal mt-1">{{ $produk->kategori->nama ?? '-' }}</div>
                                                </td>
                                            @endif
                                            <td>
                                                <span class="badge bg-light text-dark border">{{ $varian->nama_varian }}</span>
                                            </td>
                                            <td class="text-center">
                                                <div class="input-group input-group-sm mx-auto" style="max-width: 140px;">
                                                    <input type="number" name="stok_dapur[{{ $varian->id }}]" class="form-control text-center text-info fw-bold" value="{{ $varian->stok_proses_dapur }}" min="0" required>
                                                    <span class="input-group-text bg-light border-end-0">pcs</span>
                                                </div>
                                            </td>
                                            <td class="px-4 text-center">
                                                <div class="input-group input-group-sm mx-auto" style="max-width: 160px;">
                                                    <input type="number" name="stok[{{ $varian->id }}]" class="form-control text-center fw-bold" value="{{ $varian->stok }}" min="0" required>
                                                    <span class="input-group-text bg-white border-end-0">pcs</span>
                                                    <button type="submit" class="btn btn-outline-primary" title="Simpan Perubahan">
                                                        <i class="fa-solid fa-save"></i>
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td class="px-4"><span class="badge bg-secondary">{{ $produk->kode }}</span></td>
                                        <td class="fw-bold">{{ $produk->nama }}</td>
                                        <td colspan="3" class="text-center text-muted fst-italic">Produk ini belum memiliki varian. Silakan tambahkan varian di menu Manajemen Produk terlebih dahulu.</td>
                                    </tr>
                                @endif
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center py-5 text-muted">Belum ada produk.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            
            @if($produks->count() > 0)
            <div class="card-footer bg-white text-end py-3 px-4">
                <button type="submit" class="btn btn-primary btn-lg rounded-pill px-5 shadow-sm">
                    <i class="fa-solid fa-floppy-disk me-2"></i> Simpan Semua Perubahan Stok
                </button>
            </div>
            @endif
        </form>
    </div>
</div>
@endsection
