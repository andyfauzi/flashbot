@extends('layouts.app')

@section('title', 'Admin WhatsApp')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h2 class="fw-bold mb-1 text-dark">Admin WhatsApp</h2>
            <p class="text-secondary mb-0 small">Kelola akses sistem chatbot dan pembagian akses per device</p>
        </div>
        <button class="btn btn-premium btn-primary px-4 rounded-pill shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fa-solid fa-plus me-1"></i> Tambah Akun
        </button>
    </div>

    <div class="card border-0 shadow-sm rounded-4">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table align-middle mb-0">
                    <thead class="bg-light">
                        <tr>
                            <th class="ps-4">Nama</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Akses Device</th>
                            <th class="text-end pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($systemUsers as $u)
                        <tr>
                            <td class="ps-4 fw-semibold">{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="badge bg-primary rounded-pill">Admin Utama</span>
                                @elseif($u->role === 'kasir')
                                    <span class="badge" style="background-color: #E8B4A0; color: #3A3A3A;">Kasir (POS)</span>
                                @else
                                    <span class="badge bg-secondary rounded-pill">User Biasa</span>
                                @endif
                            </td>
                            <td>
                                @if($u->role === 'admin')
                                    <span class="text-muted fst-italic">Semua Device</span>
                                @elseif($u->device)
                                    <span class="fw-semibold text-success"><i class="fa-solid fa-mobile-screen me-1"></i> {{ $u->device->nama_device }}</span>
                                @else
                                    <span class="text-danger">Belum diset</span>
                                @endif
                            </td>
                            <td class="text-end pe-4">
                                <button class="btn btn-sm btn-outline-primary rounded-pill" data-bs-toggle="modal" data-bs-target="#modalEdit{{ $u->id }}">
                                    <i class="fa-solid fa-pen-to-square"></i> Edit
                                </button>
                                @if($u->id !== auth()->id())
                                <form action="{{ route('chatbot.system_users.destroy', $u->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus akun ini?')">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-outline-danger rounded-pill">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </form>
                                @endif
                            </td>
                        </tr>

                        <!-- Modal Edit -->
                        <div class="modal fade" id="modalEdit{{ $u->id }}" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content rounded-4 border-0 shadow">
                                    <form action="{{ route('chatbot.system_users.update', $u->id) }}" method="POST">
                                        @csrf @method('PUT')
                                        <div class="modal-header border-0 pb-0">
                                            <h5 class="modal-title fw-bold">Edit Akun</h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body">
                                            <div class="mb-3">
                                                <label class="form-label">Nama</label>
                                                <input type="text" name="name" class="form-control" value="{{ $u->name }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Email</label>
                                                <input type="email" name="email" class="form-control" value="{{ $u->email }}" required>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Password Baru <small class="text-muted">(kosongkan jika tidak diubah)</small></label>
                                                <input type="password" name="password" class="form-control">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Role</label>
                                                <select name="role" class="form-select" required onchange="toggleDeviceSelect(this, 'editDevice{{ $u->id }}')">
                                                    <option value="admin" {{ $u->role === 'admin' ? 'selected' : '' }}>Admin Utama (Akses Semua)</option>
                                                    <option value="user" {{ $u->role === 'user' ? 'selected' : '' }}>Karyawan (Akses Sesuai Centang)</option>
                                                    <option value="kasir" {{ $u->role === 'kasir' ? 'selected' : '' }}>Kasir (Akses Sesuai Centang)</option>
                                                    <option value="gudang" {{ $u->role === 'gudang' ? 'selected' : '' }}>Gudang (Akses Sesuai Centang)</option>
                                                </select>
                                            </div>
                                            <div class="mb-3" id="editDevice{{ $u->id }}" style="{{ $u->role === 'admin' ? 'display: none;' : '' }}">
                                                <label class="form-label">Pilih Device</label>
                                                <select name="device_id" class="form-select">
                                                    <option value="">-- Pilih Device --</option>
                                                    @foreach($devices as $d)
                                                        <option value="{{ $d->id }}" {{ $u->device_id == $d->id ? 'selected' : '' }}>{{ $d->nama_device }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label d-block fw-semibold border-bottom pb-2">Hak Akses Menu</label>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="pos" id="perm_pos_{{ $u->id }}" {{ $u->hasPermission('pos') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_pos_{{ $u->id }}">POS Kasir & Transaksi Offline</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="jadwal" id="perm_jadwal_{{ $u->id }}" {{ $u->hasPermission('jadwal') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_jadwal_{{ $u->id }}">Jadwal Pesanan (Pesanan Online)</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="produk" id="perm_produk_{{ $u->id }}" {{ $u->hasPermission('produk') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_produk_{{ $u->id }}">Katalog Produk & Kategori</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="stok" id="perm_stok_{{ $u->id }}" {{ $u->hasPermission('stok') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_stok_{{ $u->id }}">Pengelolaan Stok Barang</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input" type="checkbox" name="permissions[]" value="keuangan" id="perm_keuangan_{{ $u->id }}" {{ $u->hasPermission('keuangan') ? 'checked' : '' }}>
                                                    <label class="form-check-label" for="perm_keuangan_{{ $u->id }}">Keuangan & Kalkulator HPP</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-0 pt-0">
                                            <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                                            <button type="submit" class="btn btn-primary rounded-pill px-4">Simpan Perubahan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah -->
<div class="modal fade" id="modalTambahUser" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content rounded-4 border-0 shadow">
            <form action="{{ route('chatbot.system_users.store') }}" method="POST">
                @csrf
                <div class="modal-header border-0 pb-0">
                    <h5 class="modal-title fw-bold">Tambah Akun Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="name" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Email</label>
                        <input type="email" name="email" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Password</label>
                        <input type="password" name="password" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Role</label>
                        <select name="role" class="form-select" required onchange="toggleDeviceSelect(this, 'addDevice')">
                            <option value="admin">Admin Utama (Akses Semua)</option>
                            <option value="user" selected>Karyawan (Akses Sesuai Centang)</option>
                            <option value="kasir">Kasir (Akses Sesuai Centang)</option>
                            <option value="gudang">Gudang (Akses Sesuai Centang)</option>
                        </select>
                    </div>
                    <div class="mb-3" id="addDevice">
                        <label class="form-label">Pilih Device</label>
                        <select name="device_id" class="form-select">
                            <option value="">-- Pilih Device --</option>
                            @foreach($devices as $d)
                                <option value="{{ $d->id }}">{{ $d->nama_device }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label d-block fw-semibold border-bottom pb-2">Hak Akses Menu</label>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="pos" id="perm_pos_add" checked>
                            <label class="form-check-label" for="perm_pos_add">POS Kasir & Transaksi Offline</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="jadwal" id="perm_jadwal_add" checked>
                            <label class="form-check-label" for="perm_jadwal_add">Jadwal Pesanan (Pesanan Online)</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="produk" id="perm_produk_add">
                            <label class="form-check-label" for="perm_produk_add">Katalog Produk & Kategori</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="stok" id="perm_stok_add">
                            <label class="form-check-label" for="perm_stok_add">Pengelolaan Stok Barang</label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" name="permissions[]" value="keuangan" id="perm_keuangan_add">
                            <label class="form-check-label" for="perm_keuangan_add">Keuangan & Kalkulator HPP</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-0 pt-0">
                    <button type="button" class="btn btn-light rounded-pill px-4" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary rounded-pill px-4">Buat Akun</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function toggleDeviceSelect(select, targetId) {
    var target = document.getElementById(targetId);
    if (select.value === 'user') {
        target.style.display = 'block';
    } else {
        target.style.display = 'none';
    }
}
</script>
@endsection
