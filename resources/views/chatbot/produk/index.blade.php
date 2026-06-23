@extends('layouts.app')

@section('title', 'Manajemen Produk')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Manajemen Produk & Varian</h2>
            <p class="text-secondary mb-0 small">Kelola produk toko, harga, dan stok tiap varian (ukuran/tambahan).</p>
        </div>
        <a href="{{ route('chatbot.produk.create') }}" class="btn btn-premium btn-premium-brand px-4 rounded-pill">
            <i class="fa-solid fa-plus me-1"></i> Tambah Produk Baru
        </a>
    </div>

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="px-4 py-3">Kode</th>
                            <th>Nama Produk</th>
                            <th>Kategori</th>
                            <th>Harga</th>
                            <th>Varian</th>
                            <th>Add-ons</th>
                            <th>Status</th>
                            <th class="text-end px-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($produks as $produk)
                        <tr>
                            <td class="px-4"><span class="badge bg-secondary">{{ $produk->kode }}</span></td>
                            <td class="fw-bold">
                                {{ $produk->nama }}
                                @if($produk->is_favorite)
                                    <i class="fa-solid fa-star text-warning ms-1" title="Menu Favorit"></i>
                                @endif
                            </td>
                            <td><span class="badge bg-light text-dark border">{{ $produk->kategori->nama ?? '-' }}</span></td>
                            <td>Rp {{ number_format($produk->harga, 0, ',', '.') }}</td>
                            <td>
                                @if($produk->varians->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                    @foreach($produk->varians as $v)
                                        <span class="badge bg-info text-dark border">{{ $v->nama_varian }}</span>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">Tanpa Varian</span>
                                @endif
                            </td>
                            <td>
                                @if($produk->addons->count() > 0)
                                    <div class="d-flex flex-wrap gap-1">
                                    @foreach($produk->addons as $a)
                                        <span class="badge bg-warning text-dark border">{{ $a->nama_addon }}</span>
                                    @endforeach
                                    </div>
                                @else
                                    <span class="text-muted fst-italic">-</span>
                                @endif
                            </td>
                            <td>
                                @if($produk->aktif)
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Aktif</span>
                                @else
                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-end px-4">
                                <form action="{{ route('chatbot.produk.duplicate', $produk->id) }}" method="POST" class="d-inline-block">
                                    @csrf
                                    <button type="submit" class="btn btn-sm btn-outline-success rounded-pill px-3 me-1" title="Duplikasi Produk">
                                        <i class="fa-solid fa-copy"></i>
                                    </button>
                                </form>
                                <a href="{{ route('chatbot.produk.edit', $produk->id) }}" class="btn btn-sm btn-outline-primary rounded-pill px-3">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </a>
                                <form action="{{ route('chatbot.produk.destroy', $produk->id) }}" method="POST" class="d-inline-block" onsubmit="return confirm('Hapus produk ini?')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger rounded-pill px-3">
                                        <i class="fa-solid fa-trash-can"></i> Hapus
                                    </button>
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-5 text-muted">
                                <i class="fa-solid fa-box-open fs-1 mb-3"></i>
                                <p>Belum ada data produk.</p>
                                <a href="{{ route('chatbot.produk.create') }}" class="btn btn-primary btn-sm rounded-pill px-3 mt-2">Tambah Produk Pertama</a>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
