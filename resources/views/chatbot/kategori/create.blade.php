@extends('layouts.app')

@section('content')
<div class="container py-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Tambah Kategori</h2>
        <a href="{{ route('chatbot.kategori.index') }}" class="btn btn-secondary">Kembali</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="card">
        <div class="card-body">
            <form action="{{ route('chatbot.kategori.store') }}" method="POST" enctype="multipart/form-data">
                @csrf
                <div class="mb-3">
                    <label for="nama" class="form-label">Nama Kategori</label>
                    <input type="text" name="nama" class="form-control" id="nama" value="{{ old('nama') }}" required placeholder="Contoh: Brownies">
                </div>
                <div class="mb-3">
                    <label for="deskripsi" class="form-label">Deskripsi</label>
                    <textarea name="deskripsi" class="form-control" id="deskripsi" rows="3" placeholder="Opsional">{{ old('deskripsi') }}</textarea>
                </div>
                <div class="mb-3">
                    <label for="foto" class="form-label">Foto Kategori (Opsional)</label>
                    <input type="file" name="foto" class="form-control" id="foto" accept="image/*">
                    <small class="text-muted d-block mt-1">
                        <i class="fa-solid fa-circle-info text-primary"></i> 
                        Disarankan: Rasio 1:1 (Bujursangkar) dengan ukuran 150x150 px. Foto akan terpotong rapi secara otomatis di layar kasir.
                    </small>
                </div>
                <div class="mb-3">
                    <label class="form-label">Pilih Icon (Opsional, jika tidak ada foto)</label>
                    <div class="d-flex flex-wrap gap-2">
                        @php
                            $icons = [
                                'fa-tags', 'fa-box', 'fa-mug-hot', 'fa-burger', 'fa-pizza-slice', 
                                'fa-ice-cream', 'fa-bowl-food', 'fa-bottle-water', 'fa-martini-glass-citrus', 
                                'fa-utensils', 'fa-fire', 'fa-leaf', 'fa-drumstick-bite', 'fa-fish', 
                                'fa-apple-whole', 'fa-carrot', 'fa-cheese', 'fa-bread-slice', 'fa-cookie', 
                                'fa-candy-cane', 'fa-wine-glass', 'fa-beer-mug-empty', 'fa-mug-saucer', 
                                'fa-cubes', 'fa-snowflake', 'fa-star', 'fa-heart', 'fa-pepper-hot', 
                                'fa-seedling', 'fa-lemon', 'fa-bacon'
                            ];
                        @endphp
                        <label class="btn btn-outline-secondary p-2 d-flex justify-content-center align-items-center icon-label" style="width: 45px; height: 45px; cursor: pointer;">
                            <input type="radio" name="icon" value="" class="d-none icon-radio" {{ !old('icon') ? 'checked' : '' }}>
                            <i class="fa-solid fa-ban fs-5"></i>
                        </label>
                        @foreach($icons as $icon)
                        <label class="btn btn-outline-secondary p-2 d-flex justify-content-center align-items-center icon-label" style="width: 45px; height: 45px; cursor: pointer;">
                            <input type="radio" name="icon" value="{{ $icon }}" class="d-none icon-radio" {{ old('icon') == $icon ? 'checked' : '' }}>
                            <i class="fa-solid {{ $icon }} fs-4"></i>
                        </label>
                        @endforeach
                    </div>
                </div>

                <style>
                    .icon-radio:checked + i {
                        color: var(--brand);
                    }
                    .icon-label:has(.icon-radio:checked) {
                        border-color: var(--brand) !important;
                        background-color: rgba(79, 70, 229, 0.1) !important;
                    }
                </style>

                <div class="mb-3 form-check">
                    <input type="checkbox" name="aktif" class="form-check-input" id="aktif" value="1" checked>
                    <label class="form-check-label" for="aktif">Aktif</label>
                </div>
                <button type="submit" class="btn btn-primary">Simpan Kategori</button>
            </form>
        </div>
    </div>
</div>
@endsection
