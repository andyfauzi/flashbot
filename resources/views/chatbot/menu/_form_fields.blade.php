{{-- 
    Partial form fields untuk menu chatbot.
    Digunakan di form Tambah (prefix='') dan form Edit (prefix='edit_')
--}}
@php $p = $prefix ?? ''; @endphp

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Kode Pilihan (Keyword)</label>
    <input type="text" name="kode" id="{{ $p }}kode" class="form-control form-control-premium"
        placeholder="Contoh: halo, hi, menu" value="{{ $menu->kode ?? '' }}" required>
    <small class="text-secondary mt-1 d-block">Bisa gunakan koma untuk banyak keyword sekaligus. Gunakan <code>default</code> untuk balasan otomatis jika pesan tidak dikenali.</small>
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">
        Sub-menu dari (Parent Kode)
        <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle ms-1" style="font-size:10px;">Opsional</span>
    </label>
    <input type="text" name="parent_kode" id="{{ $p }}parent_kode" class="form-control form-control-premium"
        placeholder="Kosongkan jika ini menu utama. Isi kode parent jika ini sub-menu. Contoh: 1"
        value="{{ $menu->parent_kode ?? '' }}">
    <small class="text-secondary mt-1 d-block">
        <i class="fa-solid fa-sitemap me-1"></i>Jika diisi, menu ini hanya muncul saat user sedang berada di menu dengan kode tersebut.
        <strong>Contoh:</strong> Isi <code>1</code> untuk membuat sub-menu dari menu berkode <code>1</code>.
    </small>
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Judul Menu</label>
    <input type="text" name="judul" id="{{ $p }}judul" class="form-control form-control-premium"
        placeholder="Contoh: Menu Utama" value="{{ $menu->judul ?? '' }}" required>
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Isi Balasan</label>
    <textarea name="isi" id="{{ $p }}isi" class="form-control form-control-premium" rows="4"
        placeholder="Tulis balasan otomatis bot di sini..." required>{{ $menu->isi ?? '' }}</textarea>
    <small class="text-secondary mt-1 d-block">Gunakan *teks* untuk tulisan tebal (bold)</small>
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Tipe Pesan Balasan</label>
    <select name="tipe_pesan" id="{{ $p }}tipe_pesan" class="form-select form-select-premium"
        onchange="{{ $p === 'edit_' ? 'toggleInteraktifEdit()' : 'toggleInteraktif()' }}">
        <option value="text" {{ ($menu->tipe_pesan ?? 'text') === 'text' ? 'selected' : '' }}>Teks Biasa</option>
        <option value="button" {{ ($menu->tipe_pesan ?? '') === 'button' ? 'selected' : '' }}>Button (Tombol Interaktif)</option>
        <option value="list" {{ ($menu->tipe_pesan ?? '') === 'list' ? 'selected' : '' }}>List (Daftar Pilihan)</option>
    </select>
    <small class="text-secondary mt-1 d-block">Tipe Button dan List akan tampil sebagai teks dengan nomor pilihan.</small>
</div>

<div id="{{ $p }}wadah_interaktif" class="mb-3 p-3 bg-light border rounded-3" style="display: none;">
    <label class="form-label small fw-bold text-secondary">Daftar Pilihan (Button / List)</label>
    <p class="small text-muted mb-2">Teks di bawah ini akan dikirim user sebagai pesan saat memilih. Pastikan Anda membuat <b>Perintah</b> baru dengan <b>Keyword</b> yang sama dengan ID agar saling terhubung!</p>
    <div id="{{ $p }}list_pilihan">
        <div class="input-group mb-2 {{ $p === 'edit_' ? 'pilihan-item-edit' : 'pilihan-item' }}">
            <input type="text" name="pilihan_id[]" class="form-control" placeholder="ID (opsi)" style="max-width: 80px;" disabled>
            <input type="text" name="pilihan_text[]" class="form-control" placeholder="Teks Tombol/List" disabled>
            <button type="button" class="btn btn-outline-danger"
                onclick="{{ $p === 'edit_' ? 'hapusPilihanEdit(this)' : 'hapusPilihan(this)' }}">
                <i class="fa-solid fa-times"></i>
            </button>
        </div>
    </div>
    <button type="button" class="btn btn-sm btn-outline-success mt-1"
        onclick="{{ $p === 'edit_' ? 'tambahPilihanEditBtn()' : 'tambahPilihan()' }}">
        <i class="fa-solid fa-plus me-1"></i> Tambah Pilihan
    </button>
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">URL Media (Opsional)</label>
    <input type="url" name="media_url" id="{{ $p }}media_url" class="form-control form-control-premium"
        placeholder="https://example.com/gambar.jpg" value="{{ $menu->media_url ?? '' }}">
</div>

<div class="mb-3">
    <label class="form-label small fw-bold text-secondary">Urutan Tampil</label>
    <input type="number" name="urutan" id="{{ $p }}urutan" class="form-control form-control-premium"
        value="{{ $menu->urutan ?? ($menus->count() + 1) }}" required>
</div>

<div class="mb-4 form-check form-switch p-0 ps-5">
    <input type="checkbox" name="aktif" class="form-check-input ms-n5"
        id="{{ $p }}aktif" {{ ($menu->aktif ?? true) ? 'checked' : '' }} style="width: 2.5em; height: 1.25em;">
    <label class="form-check-label fw-semibold text-secondary small ms-2" for="{{ $p }}aktif">Aktifkan Menu Ini</label>
</div>
