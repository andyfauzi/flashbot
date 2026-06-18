@extends('layouts.app')

@section('title', 'Manajemen Pengguna & Hak Akses')

@section('content')
<div class="container-fluid">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="fw-bold text-dark mb-0" style="font-family: var(--font-heading);">
            <i class="fa-solid fa-users-gear me-2"></i> Pengguna & Hak Akses
        </h2>
        <button class="btn btn-primary fw-bold shadow-sm" data-bs-toggle="modal" data-bs-target="#modalTambahUser">
            <i class="fa-solid fa-plus me-2"></i>Tambah Karyawan
        </button>
    </div>

    @if(session('sukses'))
        <div class="alert alert-success alert-dismissible fade show">
            {{ session('sukses') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show">
            {{ session('error') }}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    @endif

    <div class="card shadow-sm border-0">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Nama</th>
                            <th>Email</th>
                            <th>Jabatan (Role)</th>
                            <th>Hak Akses (Menu)</th>
                            <th class="text-center pe-4">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($users as $user)
                        <tr>
                            <td class="ps-4 fw-bold">{{ $user->name }}</td>
                            <td>{{ $user->email }}</td>
                            <td>
                                @if($user->role == 'owner')
                                    <span class="badge bg-danger">Owner</span>
                                @elseif($user->role == 'admin')
                                    <span class="badge bg-primary">Admin</span>
                                @else
                                    <span class="badge bg-secondary text-capitalize">{{ $user->role }}</span>
                                @endif
                            </td>
                            <td>
                                @if($user->role == 'owner')
                                    <span class="badge bg-success-subtle text-success border border-success-subtle"><i class="fa-solid fa-star"></i> Akses Penuh</span>
                                @else
                                    @forelse($user->permissions as $perm)
                                        <span class="badge bg-light text-dark border me-1 mb-1">{{ str_replace('akses_', '', $perm->name) }}</span>
                                    @empty
                                        <span class="text-muted small fst-italic">Tidak ada akses</span>
                                    @endforelse
                                @endif
                            </td>
                            <td class="text-center pe-4">
                                <button class="btn btn-sm btn-outline-primary" title="Edit Akses" onclick="editUser({{ $user->id }}, '{{ $user->name }}', '{{ $user->email }}', '{{ $user->role }}', {{ json_encode($user->permissions->pluck('name')) }})">
                                    <i class="fa-solid fa-edit"></i> Edit Akses
                                </button>
                                @if($user->role != 'owner' && $user->id != auth()->id())
                                    <form action="{{ route('dashboard.users.destroy', $user->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Yakin ingin menghapus pengguna ini?');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus User">
                                            <i class="fa-solid fa-trash"></i>
                                        </button>
                                    </form>
                                @endif
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal Tambah/Edit User -->
<div class="modal fade" id="modalUser" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow">
            <form id="formUser" action="{{ route('dashboard.users.store') }}" method="POST">
                @csrf
                <input type="hidden" name="_method" id="formMethod" value="POST">
                <div class="modal-header bg-primary text-white border-bottom-0">
                    <h5 class="modal-title fw-bold" id="modalTitle"><i class="fa-solid fa-user-plus me-2"></i> Tambah Pengguna</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Nama Lengkap</label>
                            <input type="text" name="name" id="name" class="form-control" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Email</label>
                            <input type="email" name="email" id="email" class="form-control" required>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Password</label>
                            <input type="password" name="password" id="password" class="form-control">
                            <small class="text-muted" id="passwordHelper">Minimal 8 karakter.</small>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold">Jabatan Dasar</label>
                            <select name="role" id="role" class="form-select" required>
                                <option value="kasir">Kasir</option>
                                <option value="admin">Admin</option>
                                <option value="user">User Biasa</option>
                                <option value="owner">Owner</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-4">
                        <h6 class="fw-bold border-bottom pb-2 mb-3"><i class="fa-solid fa-toggle-on text-primary me-2"></i>Hak Akses Menu (Permission)</h6>
                        <div class="row">
                            @foreach($permissions as $perm)
                                <div class="col-md-4 mb-2">
                                    <div class="form-check form-switch">
                                        <input class="form-check-input perm-checkbox" type="checkbox" name="permissions[]" value="{{ $perm->name }}" id="perm_{{ $perm->name }}">
                                        <label class="form-check-label text-capitalize" for="perm_{{ $perm->name }}">{{ str_replace('_', ' ', $perm->name) }}</label>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-top-0">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary fw-bold px-4">Simpan Pengguna</button>
                </div>
            </form>
        </div>
    </div>
</div>

@endsection

@section('scripts')
<script>
    function resetForm() {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-plus me-2"></i> Tambah Pengguna';
        document.getElementById('formUser').action = '{{ route("dashboard.users.store") }}';
        document.getElementById('formMethod').value = 'POST';
        document.getElementById('password').required = true;
        document.getElementById('passwordHelper').innerText = 'Minimal 8 karakter.';
        document.getElementById('name').value = '';
        document.getElementById('email').value = '';
        document.getElementById('role').value = 'kasir';
        
        // Uncheck all permissions
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
    }

    document.querySelector('[data-bs-target="#modalTambahUser"]').addEventListener('click', function() {
        resetForm();
        new bootstrap.Modal(document.getElementById('modalUser')).show();
    });

    function editUser(id, name, email, role, perms) {
        document.getElementById('modalTitle').innerHTML = '<i class="fa-solid fa-user-pen me-2"></i> Edit Pengguna & Hak Akses';
        document.getElementById('formUser').action = '/dashboard/users/' + id;
        document.getElementById('formMethod').value = 'PUT';
        
        document.getElementById('password').required = false;
        document.getElementById('passwordHelper').innerText = 'Kosongkan jika tidak ingin mengubah password.';
        
        document.getElementById('name').value = name;
        document.getElementById('email').value = email;
        document.getElementById('role').value = role;

        // Uncheck all first
        document.querySelectorAll('.perm-checkbox').forEach(cb => cb.checked = false);
        
        // Check owned permissions
        perms.forEach(p => {
            let cb = document.getElementById('perm_' + p);
            if(cb) cb.checked = true;
        });

        new bootstrap.Modal(document.getElementById('modalUser')).show();
    }
</script>
@endsection
