@extends('layouts.app')

@section('title', 'Super Admin')

@section('styles')
<style>
    .header-panel {
        background: linear-gradient(135deg, #0f172a 0%, #1e293b 100%);
        position: relative;
        overflow: hidden;
        border-radius: 16px;
        padding: 20px 24px;
        color: #fff;
        box-shadow: 0 10px 30px rgba(15, 23, 42, 0.1);
        margin-bottom: 24px;
    }
    .header-panel::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 300px;
        height: 300px;
        background: radial-gradient(circle, rgba(79,70,229,0.4) 0%, rgba(0,0,0,0) 70%);
        border-radius: 50%;
        opacity: 0.8;
        pointer-events: none;
    }
    .stats-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        padding: 24px;
        transition: all 0.3s ease;
        position: relative;
        overflow: hidden;
    }
    .stats-card::after {
        content: '';
        position: absolute;
        top: 0; left: 0; width: 100%; height: 4px;
        background: linear-gradient(135deg, #4f46e5 0%, #6366f1 100%);
        transform: scaleX(0);
        transform-origin: left;
        transition: transform 0.3s ease;
    }
    .stats-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 15px 30px -10px rgba(0,0,0,0.1);
    }
    .stats-card:hover::after {
        transform: scaleX(1);
    }
    .custom-card {
        background: #fff;
        border: 1px solid rgba(226, 232, 240, 0.6);
        border-radius: 16px;
        box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
        padding: 24px;
        margin-bottom: 24px;
    }
    .table-custom {
        border-collapse: separate;
        border-spacing: 0 8px;
    }
    .table-custom tr {
        background: #fff;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.02);
        border-radius: 8px;
    }
    .table-custom td {
        padding: 16px;
        border: none;
        vertical-align: middle;
    }
    .table-custom td:first-child {
        border-top-left-radius: 8px;
        border-bottom-left-radius: 8px;
    }
    .table-custom td:last-child {
        border-top-right-radius: 8px;
        border-bottom-right-radius: 8px;
    }
    .badge-premium {
        padding: 6px 12px;
        border-radius: 30px;
        font-size: 0.75rem;
        font-weight: 700;
        text-transform: uppercase;
    }
    .badge-starter { background-color: #d1fae5; color: #065f46; }
    .badge-pro { background-color: #eedffc; color: #5b21b6; }
    .badge-business { background-color: #ffedd5; color: #9a3412; }
    .subdomain-badge {
        background-color: #f1f5f9;
        color: #475569;
        padding: 4px 8px;
        border-radius: 6px;
        font-family: monospace;
        font-size: 0.85rem;
    }
</style>
@endsection

@section('content')
<div class="header-panel">
    <div class="d-flex justify-content-between align-items-center">
        <div>
            <h2 class="fw-bold mb-1 fs-4">Dashboard Landlord</h2>
            <p class="text-white-50 mb-0 small">Kelola tenant, alokasi database, dan konfigurasi platform SaaS Tenanta.id</p>
        </div>
        <div>
            <div class="badge bg-light text-dark fw-bold rounded-pill px-3 py-2 border"><i class="fa-solid fa-crown text-warning me-1"></i> Super Admin</div>
        </div>
    </div>
</div>

<div class="row g-3 mb-4">
    <div class="col-md-4">
        <div class="stats-card d-flex align-items-center gap-3">
            <div class="bg-primary bg-opacity-10 text-primary p-3 rounded-circle" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-users fs-4"></i>
            </div>
            <div>
                <div class="text-muted small">Total Tenant</div>
                <h3 class="fw-bold mb-0">{{ $tenants->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card d-flex align-items-center gap-3">
            <div class="bg-success bg-opacity-10 text-success p-3 rounded-circle" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-circle-check fs-4"></i>
            </div>
            <div>
                <div class="text-muted small">Tenant Aktif</div>
                <h3 class="fw-bold mb-0">{{ $tenants->where('is_active', true)->count() }}</h3>
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="stats-card d-flex align-items-center gap-3">
            <div class="bg-danger bg-opacity-10 text-danger p-3 rounded-circle" style="width: 55px; height: 55px; display: flex; align-items: center; justify-content: center;">
                <i class="fa-solid fa-circle-xmark fs-4"></i>
            </div>
            <div>
                <div class="text-muted small">Ditangguhkan (Suspended)</div>
                <h3 class="fw-bold mb-0">{{ $tenants->where('is_active', false)->count() }}</h3>
            </div>
        </div>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-circle-check me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if($errors->has('error'))
    <div class="alert alert-danger border-0 shadow-sm rounded-3 mb-4" role="alert">
        <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ $errors->first('error') }}
    </div>
@endif

@if(session('tenant_temp_password'))
    <div class="alert alert-warning border-0 shadow-sm rounded-3 mb-4 p-4" role="alert" style="background-color: #fff3cd; color: #856404; border-left: 5px solid #ffc107 !important;">
        <div class="d-flex align-items-start">
            <i class="fa-solid fa-triangle-exclamation fs-3 me-3 mt-1 text-warning"></i>
            <div>
                <h5 class="fw-bold mb-2">Simpan password ini sekarang! Tidak akan ditampilkan lagi.</h5>
                <p class="mb-2">Gunakan informasi berikut untuk login pertama kali. Sistem akan memaksa owner mengganti kata sandi ini segera setelah login berhasil.</p>
                <div class="bg-white p-3 rounded border my-3 font-monospace">
                    Toko  : <strong>{{ session('tenant_temp_password')['toko'] }}</strong><br>
                    Email : <strong>{{ session('tenant_temp_password')['email'] }}</strong><br>
                    Sandi : <strong id="tempPasswordText">{{ session('tenant_temp_password')['password'] }}</strong>
                </div>
                <button type="button" class="btn btn-sm btn-dark" onclick="copyTempPassword()">
                    <i class="fa-regular fa-copy me-1"></i> Copy Password
                </button>
            </div>
        </div>
    </div>
    <script>
        function copyTempPassword() {
            const text = document.getElementById('tempPasswordText').innerText;
            navigator.clipboard.writeText(text).then(() => {
                alert('Password tersalin ke clipboard!');
            });
        }
    </script>
@endif

<div class="row">
    <!-- Form Tambah Tenant Card -->
    <div class="col-lg-4">
        <div class="custom-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-circle-plus text-primary me-2"></i>Daftarkan Tenant Baru</h5>
            <form action="{{ route('superadmin.store') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label class="form-label fw-semibold">Nama Toko / Bisnis</label>
                    <input type="text" name="name" class="form-control py-2 @error('name') is-invalid @enderror" placeholder="Contoh: Toko Kue Budi" required>
                    @error('name')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-3">
                    <label class="form-label fw-semibold">Subdomain</label>
                    <div class="input-group">
                        <input type="text" name="subdomain" class="form-control py-2 @error('subdomain') is-invalid @enderror" placeholder="tokobudi" required>
                        <span class="input-group-text text-muted">.localhost</span>
                    </div>
                    <small class="text-muted">Hanya boleh huruf, angka, dan dash (-). Contoh: <code>tokobudi</code></small>
                    @error('subdomain')
                        <div class="invalid-feedback d-block">{{ $message }}</div>
                    @enderror
                </div>

                <div class="mb-4">
                    <label class="form-label fw-semibold">Paket Langganan (Plan)</label>
                    <select name="plan" class="form-select py-2" required>
                        <option value="starter">Starter Plan</option>
                        <option value="pro">Pro Plan</option>
                        <option value="business">Business Plan</option>
                    </select>
                </div>

                <button type="submit" class="btn btn-primary w-100 fw-bold py-2"><i class="fa-solid fa-circle-nodes me-2"></i>Buat & Inisialisasi Database</button>
            </form>
        </div>
    </div>

    <!-- List Card -->
    <div class="col-lg-8">
        <div class="custom-card">
            <h5 class="fw-bold mb-4"><i class="fa-solid fa-database text-primary me-2"></i>Daftar Tenant Platform</h5>
            @if($tenants->isEmpty())
                <div class="text-center py-5">
                    <img src="https://illustrations.popsy.co/solid/server.svg" alt="no-data" style="width: 150px; opacity: 0.6;" class="mb-3">
                    <p class="text-muted">Belum ada tenant terdaftar. Silakan tambahkan tenant di panel sebelah kiri.</p>
                </div>
            @else
                <div class="table-responsive">
                    <table class="table table-custom align-middle text-nowrap">
                        <thead>
                            <tr style="background: none; box-shadow: none;">
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Tenant / Toko</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Gateway</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Paket</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Payment Gateway</th>
                                <th class="text-muted border-0 pb-3" style="font-weight: 600;">Status</th>
                                <th class="text-muted border-0 pb-3 text-end" style="font-weight: 600;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($tenants as $tenant)
                                <tr>
                                    <td>
                                        <div class="fw-bold">{{ $tenant->name }}</div>
                                        <small class="text-muted"><span class="subdomain-badge">{{ $tenant->subdomain }}</span></small>
                                    </td>
                                    <td>
                                        @if($tenant->whatsapp_gateway === 'meta_mandiri')
                                            <span class="badge bg-success bg-opacity-10 text-success border border-success"><i class="fa-brands fa-whatsapp me-1"></i> Meta API</span>
                                        @else
                                            <span class="badge bg-dark bg-opacity-10 text-dark border border-dark"><i class="fa-solid fa-robot me-1"></i> Baileys</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-premium badge-{{ $tenant->plan }}">
                                            {{ $tenant->plan }}
                                        </span>
                                    </td>
                                    <td>
                                        <form action="{{ route('superadmin.toggle_payment_gateway', $tenant->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            @if($tenant->is_payment_gateway_active)
                                                <button type="submit" class="badge bg-success text-white border-0" title="Nonaktifkan Payment Gateway">Aktif</button>
                                            @else
                                                <button type="submit" class="badge bg-secondary text-white border-0" title="Aktifkan Payment Gateway">Nonaktif</button>
                                            @endif
                                        </form>
                                    </td>
                                    <td>
                                        @if($tenant->is_active)
                                            <span class="badge bg-success bg-opacity-10 text-success py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">Aktif</span>
                                        @else
                                            <span class="badge bg-danger bg-opacity-10 text-danger py-1 px-2.5 rounded-pill" style="font-size: 0.75rem; font-weight: 600;">Suspended</span>
                                        @endif
                                    </td>
                                    <td class="text-end">
                                        <button type="button" class="btn btn-sm btn-outline-primary px-3 rounded-pill fw-semibold me-1" data-bs-toggle="modal" data-bs-target="#editPlanModal{{ $tenant->id }}">
                                            <i class="fa-solid fa-gear me-1"></i> Atur
                                        </button>
                                        
                                        <form action="{{ route('superadmin.toggle_broadcast', $tenant->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            @if($tenant->is_broadcast_approved)
                                                <button type="submit" class="btn btn-sm btn-success px-3 rounded-pill fw-semibold me-1" title="Nonaktifkan Broadcast">
                                                    <i class="fa-solid fa-bullhorn me-1"></i> Broadcast On
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-secondary px-3 rounded-pill fw-semibold me-1" title="Aktifkan Broadcast (Layanan Business)">
                                                    <i class="fa-solid fa-bullhorn me-1"></i> Broadcast Off
                                                </button>
                                            @endif
                                        </form>
                                        
                                        <form action="{{ route('superadmin.toggle', $tenant->id) }}" method="POST" class="d-inline">
                                            @csrf
                                            @method('POST')
                                            @if($tenant->is_active)
                                                <button type="submit" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-semibold">
                                                    <i class="fa-solid fa-ban me-1"></i> Suspend
                                                </button>
                                            @else
                                                <button type="submit" class="btn btn-sm btn-outline-success px-3 rounded-pill fw-semibold">
                                                    <i class="fa-solid fa-check me-1"></i> Aktifkan
                                                </button>
                                            @endif
                                        </form>

                                        <!-- Delete Button Form -->
                                        <form action="{{ route('superadmin.destroy', $tenant->id) }}" method="POST" class="d-inline" id="delete-form-{{ $tenant->id }}">
                                            @csrf
                                            @method('DELETE')
                                            <button type="button" class="btn btn-sm btn-outline-danger px-3 rounded-pill fw-semibold ms-1" onclick="confirmDelete('{{ $tenant->id }}', '{{ $tenant->name }}')">
                                                <i class="fa-solid fa-trash"></i>
                                            </button>
                                        </form>

                                        <!-- Modal Edit Paket & Fitur -->
                                        <div class="modal fade text-start" id="editPlanModal{{ $tenant->id }}" tabindex="-1" aria-labelledby="editPlanModalLabel{{ $tenant->id }}" aria-hidden="true">
                                            <div class="modal-dialog">
                                                <form action="{{ route('superadmin.update_plan', $tenant->id) }}" method="POST" class="modal-content">
                                                    @csrf
                                                    <div class="modal-header">
                                                        <h5 class="modal-title fw-bold text-dark" id="editPlanModalLabel{{ $tenant->id }}">
                                                            <i class="fa-solid fa-sliders text-primary me-2"></i>Atur Paket & Fitur: {{ $tenant->name }}
                                                        </h5>
                                                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                                    </div>
                                                    <div class="modal-body text-start text-dark">
                                                        <!-- Paket -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Paket Langganan (Plan)</label>
                                                            <select name="plan" class="form-select" required>
                                                                <option value="starter" {{ $tenant->plan === 'starter' ? 'selected' : '' }}>Starter Plan</option>
                                                                <option value="pro" {{ $tenant->plan === 'pro' ? 'selected' : '' }}>Pro Plan</option>
                                                                <option value="business" {{ $tenant->plan === 'business' ? 'selected' : '' }}>Business Plan</option>
                                                            </select>
                                                        </div>

                                                        <!-- Tanggal Kedaluwarsa -->
                                                        <div class="mb-3">
                                                            <label class="form-label fw-semibold">Masa Aktif Paket</label>
                                                            <input type="date" name="plan_expires_at" class="form-control" value="{{ $tenant->plan_expires_at ? $tenant->plan_expires_at->format('Y-m-d') : '' }}">
                                                            <small class="text-muted">Kosongkan jika ingin masa aktif tanpa batas (lifetime).</small>
                                                        </div>

                                                        <!-- Fitur Flags -->
                                                        <div class="mb-2 fw-semibold">Batasan Fitur Aktif</div>
                                                        @php
                                                            $flags = $tenant->feature_flags ?? [];
                                                        @endphp
                                                        <div class="card p-3 bg-light border-0">
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" name="features[pos]" value="1" id="featurePos{{ $tenant->id }}" {{ (!isset($flags['pos']) || $flags['pos']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="featurePos{{ $tenant->id }}">Point of Sale (POS) Kasir</label>
                                                            </div>
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" name="features[chatbot]" value="1" id="featureChatbot{{ $tenant->id }}" {{ (!isset($flags['chatbot']) || $flags['chatbot']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="featureChatbot{{ $tenant->id }}">WhatsApp Chatbot & Auto-Reply</label>
                                                            </div>
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" name="features[erp]" value="1" id="featureErp{{ $tenant->id }}" {{ (!isset($flags['erp']) || $flags['erp']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="featureErp{{ $tenant->id }}">Produksi Dapur (HPP & Resep)</label>
                                                            </div>
                                                            <div class="form-check form-switch mb-2">
                                                                <input class="form-check-input" type="checkbox" name="features[finance]" value="1" id="featureFinance{{ $tenant->id }}" {{ (!isset($flags['finance']) || $flags['finance']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="featureFinance{{ $tenant->id }}">Buku Kas & Keuangan Platform</label>
                                                            </div>
                                                            <div class="form-check form-switch">
                                                                <input class="form-check-input" type="checkbox" name="features[gemini_ai]" value="1" id="featureGemini{{ $tenant->id }}" {{ (!isset($flags['gemini_ai']) || $flags['gemini_ai']) ? 'checked' : '' }}>
                                                                <label class="form-check-label" for="featureGemini{{ $tenant->id }}">AI Agent (Integrasi Gemini AI)</label>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary rounded-3" data-bs-dismiss="modal">Batal</button>
                                                        <button type="submit" class="btn btn-primary px-4"><i class="fa-solid fa-circle-check me-2"></i>Simpan Perubahan</button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    function confirmDelete(id, name) {
        Swal.fire({
            title: 'Apakah Anda yakin?',
            text: "Toko '" + name + "' beserta seluruh databasenya akan dihapus permanen dan tidak dapat dikembalikan!",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#d33',
            cancelButtonColor: '#3085d6',
            confirmButtonText: 'Ya, Hapus Permanen!',
            cancelButtonText: 'Batal'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('delete-form-' + id).submit();
            }
        });
    }
</script>
@endsection
