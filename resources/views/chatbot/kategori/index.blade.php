@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Kategori Produk</h2>
        <a href="{{ route('chatbot.kategori.create') }}" class="btn btn-primary">Tambah Kategori</a>
    </div>

    @if(session('success'))
        <div class="alert alert-success">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger">{{ session('error') }}</div>
    @endif

    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>Foto</th>
                            <th>Nama Kategori</th>
                            <th>Deskripsi</th>
                            <th>Jumlah Produk</th>
                            <th>Status</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($kategoris as $kat)
                        <tr>
                            <td>
                                @if($kat->foto)
                                    <img src="{{ asset('storage/' . $kat->foto) }}" alt="Foto" style="width: 40px; height: 40px; object-fit: cover; border-radius: 6px; border: 1px solid #eee;">
                                @else
                                    <div style="width: 40px; height: 40px; background: #f8f9fa; border-radius: 6px; display: flex; align-items: center; justify-content: center; color: #adb5bd; border: 1px solid #eee;">
                                        <i class="fa-solid fa-folder"></i>
                                    </div>
                                @endif
                            </td>
                            <td>{{ $kat->nama }}</td>
                            <td>{{ $kat->deskripsi ?? '-' }}</td>
                            <td>{{ $kat->produks_count }} Produk</td>
                            <td>
                                @if($kat->aktif)
                                    <span class="badge bg-success">Aktif</span>
                                @else
                                    <span class="badge bg-secondary">Non-Aktif</span>
                                @endif
                            </td>
                            <td>
                                <a href="{{ route('chatbot.kategori.edit', $kat->id) }}" class="btn btn-sm btn-warning">Edit</a>
                                @if($kat->nama !== 'Umum')
                                <form action="{{ route('chatbot.kategori.destroy', $kat->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus kategori ini? Semua produk di dalamnya akan dipindah ke kategori Umum.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger">Hapus</button>
                                </form>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center">Belum ada kategori.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>
@endsection
