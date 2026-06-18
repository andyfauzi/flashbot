@extends('layouts.app')

@section('title', 'Manajemen Menu Chatbot')

@section('styles')
<style>
    .menu-row {
        cursor: pointer;
        transition: background 0.15s;
    }
    .menu-row:hover {
        background: rgba(99, 102, 241, 0.06) !important;
    }
    .menu-row.row-editing {
        background: rgba(99, 102, 241, 0.12) !important;
        outline: 2px solid rgba(99, 102, 241, 0.4);
        outline-offset: -2px;
    }
    .edit-hint {
        font-size: 11px;
        color: #94a3b8;
        display: flex;
        align-items: center;
        gap: 4px;
    }
    #cardTambahMenu {
        transition: box-shadow 0.2s;
    }
    #cardTambahMenu.editing-mode {
        box-shadow: 0 0 0 3px rgba(99, 102, 241, 0.35), 0 4px 20px rgba(99,102,241,0.15) !important;
    }
    #btnBatal {
        display: none;
    }
    #formTitle { transition: all 0.2s; }
</style>
@endsection

@section('content')
<div class="container-fluid">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            @if($selectedDeviceId && ($selectedDevice = $devices->find($selectedDeviceId)))
                <div class="d-flex align-items-center gap-2 mb-1">
                    <span class="badge bg-success-subtle text-success border border-success-subtle px-3 py-1 rounded-pill">
                        <i class="fa-solid fa-mobile-screen me-1"></i> {{ $selectedDevice->nama_device }}
                    </span>
                </div>
                <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Menu Chat Device Ini</h2>
                <p class="text-secondary mb-0 small">Jawaban & menu khusus untuk <strong>{{ $selectedDevice->nama_device }}</strong></p>
            @else
                <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Menu Chatbot</h2>
                <p class="text-secondary mb-0 small">Tambahkan perangkat (device) terlebih dahulu untuk mengatur menu.</p>
            @endif
        </div>
        <div class="d-flex gap-2">
            @if($selectedDeviceId)
                <a href="{{ route('chatbot.device.index') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
                    <i class="fa-solid fa-mobile-screen me-1"></i> Kembali ke Device
                </a>
            @else
                <a href="{{ route('chatbot.dashboard') }}" class="btn btn-premium btn-light border px-4 rounded-pill">
                    <i class="fa-solid fa-arrow-left me-1"></i> Kembali
                </a>
            @endif
        </div>
    </div>

    <!-- Pilihan Device -->
    <div class="card-premium p-3 mb-4 d-flex align-items-center justify-content-between gap-3">
        <form action="{{ route('chatbot.menu') }}" method="GET" class="d-flex align-items-center gap-3 flex-grow-1">
            <label class="fw-bold mb-0 text-nowrap"><i class="fa-solid fa-mobile-screen me-2"></i>Pilih Device:</label>
            <select name="device_id" class="form-select form-select-sm w-auto d-inline-block" onchange="this.form.submit()">
                @if($devices->count() === 0)
                    <option value="">-- Belum ada device --</option>
                @endif
                @foreach($devices as $device)
                    <option value="{{ $device->id }}" {{ $selectedDeviceId == $device->id ? 'selected' : '' }}>
                        {{ $device->nama_device }}
                    </option>
                @endforeach
            </select>
            <span class="small text-muted">Pilih device untuk mengatur menu chatbot-nya.</span>
        </form>
    </div>

    <div class="row g-4">
        {{-- Kolom Kiri: Form Tambah / Edit Menu --}}
        <div class="col-md-4">
            <div id="cardTambahMenu" class="card-premium p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="icon-box-premium brand">
                        <i id="formIcon" class="fa-solid fa-wand-magic-sparkles"></i>
                    </div>
                    <div>
                        <h5 id="formTitle" class="fw-bold mb-0" style="font-family: var(--font-heading);">Flow Builder (Perintah)</h5>
                        <p id="formSubtitle" class="text-secondary mb-0 small">Atur keyword dan balasan bot</p>
                    </div>
                </div>

                {{-- FORM TAMBAH BARU --}}
                <form id="formTambah" action="{{ route('chatbot.menu.simpan') }}" method="POST">
                    @csrf
                    <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
                    @include('chatbot.menu._form_fields', ['menu' => null])
                    <div class="d-flex gap-2">
                        <button type="submit" id="btnSimpan" class="btn btn-premium btn-premium-brand flex-grow-1 py-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-save"></i> <span>Simpan Perintah</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-3 py-3 rounded-3" onclick="kosongkanForm('formTambah')" title="Kosongkan semua field">
                            <i class="fa-solid fa-eraser"></i>
                        </button>
                    </div>
                </form>

                {{-- FORM EDIT (hidden by default) --}}
                <form id="formEdit" action="" method="POST" style="display:none;">
                    @csrf
                    @method('PUT')
                    <input type="hidden" name="device_id" value="{{ $selectedDeviceId }}">
                    @include('chatbot.menu._form_fields', ['menu' => null, 'prefix' => 'edit_'])
                    <div class="d-flex gap-2">
                        <button type="submit" class="btn btn-premium btn-premium-brand flex-grow-1 py-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="fa-solid fa-floppy-disk"></i> <span>Simpan Perubahan</span>
                        </button>
                        <button type="button" class="btn btn-outline-secondary px-3 py-3 rounded-3" onclick="kosongkanForm('formEdit')" title="Kosongkan semua field">
                            <i class="fa-solid fa-eraser"></i>
                        </button>
                        <button type="button" id="btnBatal" class="btn btn-outline-danger px-3 py-3 rounded-3" onclick="resetForm()" title="Batal Edit">
                            <i class="fa-solid fa-xmark"></i>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Daftar Perintah Menu (TABS) --}}
        <div class="col-md-8">
            <div class="card-premium p-0 overflow-hidden">
                {{-- Header & Tabs --}}
                <div class="px-4 pt-3 border-bottom border-light bg-white">
                    <div class="d-flex align-items-center justify-content-between mb-3">
                        <h5 class="fw-bold mb-0 text-dark" style="font-family: var(--font-heading); font-size: 1rem;">
                            <i class="fa-solid fa-list-check text-secondary me-2"></i>Daftar Pilihan Menu Chatbot
                        </h5>
                        <span style="font-size: 11px; color: #64748b;">
                            <i class="fa-solid fa-hand-pointer me-1"></i>Klik baris untuk edit
                        </span>
                    </div>
                    
                    <ul class="nav nav-tabs border-bottom-0" id="menuTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active fw-bold" id="utama-tab" data-bs-toggle="tab" data-bs-target="#utama-pane" type="button" role="tab" style="color: #6366f1;">
                                <i class="fa-solid fa-house me-1"></i> Menu Utama
                                <span class="badge bg-primary-subtle text-primary ms-1 rounded-pill">{{ $menusUtama->count() }}</span>
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link fw-bold" id="sub-tab" data-bs-toggle="tab" data-bs-target="#sub-pane" type="button" role="tab" style="color: #0ea5e9;">
                                <i class="fa-solid fa-sitemap me-1"></i> Sub Menu
                                <span class="badge bg-info-subtle text-info ms-1 rounded-pill">{{ $subMenus->flatten()->count() }}</span>
                            </button>
                        </li>
                    </ul>
                </div>

                {{-- Tab Content --}}
                <div class="tab-content" id="menuTabContent">
                    
                    {{-- ===== TAB 1: MENU UTAMA ===== --}}
                    <div class="tab-pane fade show active" id="utama-pane" role="tabpanel" aria-labelledby="utama-tab" tabindex="0">
                        <div class="table-responsive">
                            <table class="table table-premium align-middle mb-0">
                                <thead>
                                    <tr>
                                        <th style="width: 100px;">Kode</th>
                                        <th>Nama Menu</th>
                                        <th>Preview Balasan</th>
                                        <th>Status</th>
                                        <th style="width: 70px;">Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($menusUtama as $menu)
                                    <tr class="menu-row"
                                        id="row-{{ $menu->id }}"
                                        onclick="editMenu({{ $menu->toJson() }})"
                                        title="Klik untuk edit">
                                        <td>
                                            <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-2 py-1 rounded-pill fw-bold" style="font-size:11px;">
                                                {{ Str::limit($menu->kode, 14) }}
                                            </span>
                                        </td>
                                        <td>
                                            <span class="fw-semibold text-dark">{{ $menu->judul }}</span>
                                            <small class="text-muted d-block">Urutan: {{ $menu->urutan }}</small>
                                        </td>
                                        <td>
                                            <div class="text-truncate" style="max-width: 260px;">
                                                @if($menu->media_url)
                                                    <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill me-1" style="font-size:10px;"><i class="fa-regular fa-image me-1"></i>Gambar</span>
                                                @endif
                                                <span class="text-secondary small">{{ $menu->isi }}</span>
                                            </div>
                                        </td>
                                        <td>
                                            @if($menu->aktif)
                                                <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2">Aktif</span>
                                            @else
                                                <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2">Nonaktif</span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('chatbot.menu.hapus', $menu) }}" method="POST"
                                                  onsubmit="return confirm('Hapus menu ini?')" onclick="event.stopPropagation()">
                                                @csrf @method('DELETE')
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-2 rounded-pill">
                                                    <i class="fa-solid fa-trash-can"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="5" class="text-center text-secondary py-5">
                                            <i class="fa-solid fa-folder-open fs-3 d-block mb-2"></i>
                                            Belum ada menu utama
                                        </td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>

                    {{-- ===== TAB 2: SUB MENU ===== --}}
                    <div class="tab-pane fade" id="sub-pane" role="tabpanel" aria-labelledby="sub-tab" tabindex="0">
                        @if($subMenus->isEmpty())
                        <div class="text-center text-secondary py-5">
                            <i class="fa-solid fa-diagram-project fs-3 d-block mb-2"></i>
                            <small>Belum ada sub-menu. Isi kolom <strong>Parent Kode</strong> saat menambah menu untuk membuat sub-menu.</small>
                        </div>
                        @else
                            @foreach($subMenus as $parentKode => $group)
                            {{-- Header per parent --}}
                            <div class="px-4 py-2 d-flex align-items-center gap-2"
                                 style="background: #f1f5f9; border-bottom: 1px solid #e2e8f0; border-top: {{ $loop->first ? 'none' : '1px solid #e2e8f0' }};">
                                <i class="fa-solid fa-folder-open text-info" style="font-size: 12px;"></i>
                                <span class="fw-bold text-secondary" style="font-size: 12px;">
                                    Sub-menu dari parent kode:
                                </span>
                                <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill px-2" style="font-size: 11px;">
                                    {{ $parentKode }}
                                </span>
                                <span class="text-muted ms-auto" style="font-size: 11px;">{{ $group->count() }} item</span>
                            </div>
                            <div class="table-responsive">
                                <table class="table table-premium align-middle mb-0">
                                    <thead style="background: #f8fafc;">
                                        <tr>
                                            <th style="width: 80px; font-size: 12px;">Kode</th>
                                            <th style="font-size: 12px;">Nama Sub-Menu</th>
                                            <th style="font-size: 12px;">Preview</th>
                                            <th style="font-size: 12px;">Status</th>
                                            <th style="width: 70px; font-size: 12px;">Aksi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($group as $menu)
                                        <tr class="menu-row"
                                            id="row-{{ $menu->id }}"
                                            onclick="editMenu({{ $menu->toJson() }})"
                                            title="Klik untuk edit">
                                            <td>
                                                <span class="badge bg-info-subtle text-info border border-info-subtle px-2 py-1 rounded-pill fw-bold" style="font-size:11px;">
                                                    {{ $menu->kode }}
                                                </span>
                                            </td>
                                            <td>
                                                <span class="fw-semibold text-dark" style="font-size: 13px;">{{ $menu->judul }}</span>
                                                <small class="text-muted d-block" style="font-size: 11px;">
                                                    Path: <code style="font-size: 10px;">{{ $menu->parent_kode }}.{{ $menu->kode }}</code>
                                                </small>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 220px;">
                                                    @if($menu->media_url)
                                                        <span class="badge bg-info-subtle text-info border border-info-subtle rounded-pill me-1" style="font-size:10px;"><i class="fa-regular fa-image"></i></span>
                                                    @endif
                                                    <span class="text-secondary small">{{ $menu->isi }}</span>
                                                </div>
                                            </td>
                                            <td>
                                                @if($menu->aktif)
                                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2" style="font-size:10px;">Aktif</span>
                                                @else
                                                    <span class="badge bg-danger-subtle text-danger border border-danger-subtle rounded-pill px-2" style="font-size:10px;">Nonaktif</span>
                                                @endif
                                            </td>
                                            <td>
                                                <form action="{{ route('chatbot.menu.hapus', $menu) }}" method="POST"
                                                      onsubmit="return confirm('Hapus sub-menu ini?')" onclick="event.stopPropagation()">
                                                    @csrf @method('DELETE')
                                                    <button type="submit" class="btn btn-sm btn-outline-danger px-2 rounded-pill">
                                                        <i class="fa-solid fa-trash-can"></i>
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            @endforeach
                        @endif
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>

<script>
let currentEditingRowId = null;

function kosongkanForm(formId) {
    const prefix = formId === 'formEdit' ? 'edit_' : '';
    
    // Kosongkan semua field teks / textarea
    ['kode', 'parent_kode', 'judul', 'isi', 'media_url'].forEach(function(field) {
        const el = document.getElementById(prefix + field);
        if (el) el.value = '';
    });

    // Reset urutan ke 1
    const urutan = document.getElementById(prefix + 'urutan');
    if (urutan) urutan.value = 1;

    // Reset tipe pesan ke teks
    const tipe = document.getElementById(prefix + 'tipe_pesan');
    if (tipe) {
        tipe.value = 'text';
        // Sembunyikan bagian interaktif
        const wadah = document.getElementById(prefix + 'wadah_interaktif');
        if (wadah) {
            wadah.style.display = 'none';
            wadah.querySelectorAll('input').forEach(i => i.disabled = true);
        }
    }

    // Reset checkbox aktif ke checked
    const aktif = document.getElementById(prefix + 'aktif');
    if (aktif) aktif.checked = true;

    // Fokus ke field kode
    const kodeEl = document.getElementById(prefix + 'kode');
    if (kodeEl) kodeEl.focus();
}

function editMenu(menu) {
    // Highlight row
    document.querySelectorAll('.menu-row').forEach(r => r.classList.remove('row-editing'));
    const row = document.getElementById('row-' + menu.id);
    if (row) row.classList.add('row-editing');
    currentEditingRowId = menu.id;

    // Update form action untuk PUT ke menu/{id}
    const baseUrl = "{{ url('chatbot/menu') }}";
    document.getElementById('formEdit').action = baseUrl + '/' + menu.id;

    // Isi semua field form edit
    document.getElementById('edit_kode').value        = menu.kode || '';
    document.getElementById('edit_parent_kode').value  = menu.parent_kode || '';
    document.getElementById('edit_judul').value        = menu.judul || '';
    document.getElementById('edit_isi').value        = menu.isi || '';
    document.getElementById('edit_urutan').value     = menu.urutan || 1;
    document.getElementById('edit_media_url').value  = menu.media_url || '';
    document.getElementById('edit_tipe_pesan').value = menu.tipe_pesan || 'text';
    document.getElementById('edit_aktif').checked    = menu.aktif == 1;

    // Handle pilihan interaktif (button/list)
    const listPilihan = document.getElementById('edit_list_pilihan');
    listPilihan.innerHTML = '';
    const pilihanData = menu.pilihan_interaktif
        ? (typeof menu.pilihan_interaktif === 'string' ? JSON.parse(menu.pilihan_interaktif) : menu.pilihan_interaktif)
        : [];

    if (pilihanData.length > 0) {
        pilihanData.forEach(opt => tambahPilihanEdit(opt.id, opt.text));
    } else {
        tambahPilihanEdit('', '');
    }

    toggleInteraktifEdit();

    // Switch tampilan form
    document.getElementById('formTambah').style.display = 'none';
    document.getElementById('formEdit').style.display   = 'block';
    document.getElementById('btnBatal').style.display   = 'inline-flex';
    document.getElementById('cardTambahMenu').classList.add('editing-mode');
    document.getElementById('formTitle').textContent    = '✏️ Edit Perintah';
    document.getElementById('formSubtitle').textContent = 'Mengubah: ' + menu.judul;
    document.getElementById('formIcon').className       = 'fa-solid fa-pen-to-square';

    // Scroll ke form
    document.getElementById('cardTambahMenu').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function resetForm() {
    // Reset highlighting
    document.querySelectorAll('.menu-row').forEach(r => r.classList.remove('row-editing'));
    currentEditingRowId = null;

    // Switch kembali ke form tambah
    document.getElementById('formTambah').style.display = 'block';
    document.getElementById('formEdit').style.display   = 'none';
    document.getElementById('btnBatal').style.display   = 'none';
    document.getElementById('cardTambahMenu').classList.remove('editing-mode');
    document.getElementById('formTitle').textContent    = 'Flow Builder (Perintah)';
    document.getElementById('formSubtitle').textContent = 'Atur keyword dan balasan bot';
    document.getElementById('formIcon').className       = 'fa-solid fa-wand-magic-sparkles';
}

// Toggle interaktif untuk form TAMBAH
function toggleInteraktif() {
    const tipe   = document.getElementById('tipe_pesan').value;
    const wadah  = document.getElementById('wadah_interaktif');
    const inputs = wadah.querySelectorAll('input');
    if (tipe === 'button' || tipe === 'list') {
        wadah.style.display = 'block';
        inputs.forEach(input => input.disabled = false);
    } else {
        wadah.style.display = 'none';
        inputs.forEach(input => input.disabled = true);
    }
}

// Toggle interaktif untuk form EDIT
function toggleInteraktifEdit() {
    const tipe   = document.getElementById('edit_tipe_pesan').value;
    const wadah  = document.getElementById('edit_wadah_interaktif');
    const inputs = wadah.querySelectorAll('input');
    if (tipe === 'button' || tipe === 'list') {
        wadah.style.display = 'block';
        inputs.forEach(input => input.disabled = false);
    } else {
        wadah.style.display = 'none';
        inputs.forEach(input => input.disabled = true);
    }
}

function tambahPilihan() {
    const tipe  = document.getElementById('tipe_pesan').value;
    const list  = document.getElementById('list_pilihan');
    const count = list.children.length;
    if (tipe === 'button' && count >= 10) { alert('Maksimal 10 pilihan!'); return; }
    if (tipe === 'list'   && count >= 10) { alert('Maksimal 10 item list!'); return; }
    list.insertAdjacentHTML('beforeend', buatHtmlPilihan('', ''));
}

function tambahPilihanEdit(id = '', text = '') {
    const list = document.getElementById('edit_list_pilihan');
    list.insertAdjacentHTML('beforeend', buatHtmlPilihanEdit(id, text));
}

function tambahPilihanEditBtn() {
    const tipe  = document.getElementById('edit_tipe_pesan').value;
    const list  = document.getElementById('edit_list_pilihan');
    const count = list.children.length;
    if (tipe === 'button' && count >= 10) { alert('Maksimal 10 pilihan!'); return; }
    if (tipe === 'list'   && count >= 10) { alert('Maksimal 10 item list!'); return; }
    tambahPilihanEdit();
}

function buatHtmlPilihan(id, text) {
    return `<div class="input-group mb-2 pilihan-item">
        <input type="text" name="pilihan_id[]" class="form-control" placeholder="ID (opsi)" style="max-width: 80px;" value="${id}">
        <input type="text" name="pilihan_text[]" class="form-control" placeholder="Teks Tombol/List" value="${text}">
        <button type="button" class="btn btn-outline-danger" onclick="hapusPilihan(this)"><i class="fa-solid fa-times"></i></button>
    </div>`;
}

function buatHtmlPilihanEdit(id, text) {
    return `<div class="input-group mb-2 pilihan-item-edit">
        <input type="text" name="pilihan_id[]" class="form-control" placeholder="ID (opsi)" style="max-width: 80px;" value="${id}">
        <input type="text" name="pilihan_text[]" class="form-control" placeholder="Teks Tombol/List" value="${text}">
        <button type="button" class="btn btn-outline-danger" onclick="hapusPilihanEdit(this)"><i class="fa-solid fa-times"></i></button>
    </div>`;
}

function hapusPilihan(btn) {
    if (document.querySelectorAll('.pilihan-item').length > 1) {
        btn.closest('.pilihan-item').remove();
    } else { alert('Minimal harus ada 1 pilihan!'); }
}

function hapusPilihanEdit(btn) {
    if (document.querySelectorAll('.pilihan-item-edit').length > 1) {
        btn.closest('.pilihan-item-edit').remove();
    } else { alert('Minimal harus ada 1 pilihan!'); }
}
</script>
@endsection
