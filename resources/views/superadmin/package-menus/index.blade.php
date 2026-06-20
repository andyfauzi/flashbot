@extends('layouts.app')
@section('title', 'Manajemen Paket Tenant (Fitur Matrix)')
@section('content')

<div class="mb-4">
    <h2 class="h4 mb-0 fw-bold">Manajemen Paket Tenant</h2>
    <p class="text-muted">Atur menu apa saja yang dapat diakses oleh masing-masing paket langganan.</p>
</div>

<div class="card border-0 shadow-sm rounded-4">
    <div class="card-body p-4">
        <form action="{{ route('superadmin.package_menus.update') }}" method="POST">
            @csrf
            
            <div class="table-responsive">
                <table class="table table-hover align-middle">
                    <thead class="table-light">
                        <tr>
                            <th style="width: 40%">Nama Menu</th>
                            <th class="text-center" style="width: 15%">Gratis</th>
                            <th class="text-center" style="width: 15%">Starter</th>
                            <th class="text-center" style="width: 15%">Pro</th>
                            <th class="text-center" style="width: 15%">Business</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($menus as $category => $items)
                            <tr class="table-active">
                                <td colspan="5" class="fw-bold">
                                    <i data-lucide="folder" class="me-2" style="width: 18px; height: 18px;"></i>
                                    {{ $category ?: 'Lainnya' }}
                                </td>
                            </tr>
                            
                            <!-- Category Select All Row -->
                            <tr>
                                <td class="text-end fst-italic text-muted" style="font-size: 0.85rem">Pilih Semua Kategori Ini:</td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input category-toggle" type="checkbox" data-category="{{ Str::slug($category) }}" data-plan="gratis">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input category-toggle" type="checkbox" data-category="{{ Str::slug($category) }}" data-plan="starter">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input category-toggle" type="checkbox" data-category="{{ Str::slug($category) }}" data-plan="pro">
                                    </div>
                                </td>
                                <td class="text-center">
                                    <div class="form-check form-switch d-flex justify-content-center">
                                        <input class="form-check-input category-toggle" type="checkbox" data-category="{{ Str::slug($category) }}" data-plan="business">
                                    </div>
                                </td>
                            </tr>

                            @foreach($items as $menu)
                                <tr>
                                    <td class="ps-4">{{ $menu->menu_label }}</td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input checkbox-{{ Str::slug($category) }}-gratis" type="checkbox" name="gratis[{{ $menu->menu_key }}]" value="1" {{ $menu->gratis_enabled ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input checkbox-{{ Str::slug($category) }}-starter" type="checkbox" name="starter[{{ $menu->menu_key }}]" value="1" {{ $menu->starter_enabled ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input checkbox-{{ Str::slug($category) }}-pro" type="checkbox" name="pro[{{ $menu->menu_key }}]" value="1" {{ $menu->pro_enabled ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                    <td class="text-center">
                                        <div class="form-check form-switch d-flex justify-content-center">
                                            <input class="form-check-input checkbox-{{ Str::slug($category) }}-business" type="checkbox" name="business[{{ $menu->menu_key }}]" value="1" {{ $menu->business_enabled ? 'checked' : '' }}>
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tambahan: Batas Maksimal Karyawan -->
            <div class="mt-5 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2"><i class="fa-solid fa-users-gear me-2"></i> Pengaturan Batas Maksimal Karyawan</h5>
                <p class="text-muted small">Tentukan berapa banyak akun karyawan (kasir/manajer) yang bisa dibuat oleh tenant berdasarkan paket langganannya.</p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Gratis</label>
                        <input type="number" class="form-control" name="limit_karyawan_gratis" value="{{ \App\Models\LandlordSetting::get('limit_karyawan_gratis', 1) }}" min="1">
                        <small class="text-muted">Misal: 1 (Hanya Owner)</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Starter</label>
                        <input type="number" class="form-control" name="limit_karyawan_starter" value="{{ \App\Models\LandlordSetting::get('limit_karyawan_starter', 2) }}" min="1">
                        <small class="text-muted">Misal: 2</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Pro</label>
                        <input type="number" class="form-control" name="limit_karyawan_pro" value="{{ \App\Models\LandlordSetting::get('limit_karyawan_pro', 10) }}" min="1">
                        <small class="text-muted">Misal: 10</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Business</label>
                        <input type="number" class="form-control" name="limit_karyawan_business" value="{{ \App\Models\LandlordSetting::get('limit_karyawan_business', 999) }}" min="1">
                        <small class="text-muted">Isi angka besar (999) untuk Unlimited</small>
                    </div>
                </div>
            </div>

            <!-- Tambahan: Rate Limiting / Kuota Bot WA -->
            <div class="mt-5 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2"><i class="fa-solid fa-robot me-2"></i> Pengaturan Kuota Balasan Bot WA (Per Bulan)</h5>
                <p class="text-muted small">Tentukan berapa maksimal pesan bot yang dapat dikirim oleh tenant setiap bulan.</p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Gratis</label>
                        <input type="number" class="form-control" name="limit_wa_gratis" value="{{ \App\Models\LandlordSetting::get('limit_wa_gratis', 100) }}" min="0">
                        <small class="text-muted">Misal: 100 Pesan</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Starter</label>
                        <input type="number" class="form-control" name="limit_wa_starter" value="{{ \App\Models\LandlordSetting::get('limit_wa_starter', 1000) }}" min="0">
                        <small class="text-muted">Misal: 1000 Pesan</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Pro</label>
                        <input type="number" class="form-control" name="limit_wa_pro" value="{{ \App\Models\LandlordSetting::get('limit_wa_pro', 5000) }}" min="0">
                        <small class="text-muted">Misal: 5000 Pesan</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Business</label>
                        <input type="number" class="form-control" name="limit_wa_business" value="{{ \App\Models\LandlordSetting::get('limit_wa_business', 999999) }}" min="0">
                        <small class="text-muted">Isi angka besar (999999) untuk Unlimited</small>
                    </div>
                </div>
            </div>

            <!-- Tambahan: Batas Device / Koneksi WA -->
            <div class="mt-5 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2"><i class="fa-solid fa-mobile-screen me-2"></i> Pengaturan Batas Maksimal Device WA</h5>
                <p class="text-muted small">Tentukan berapa banyak nomor/koneksi WhatsApp (Device) yang bisa ditambahkan oleh tenant.</p>
                <div class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Gratis</label>
                        <input type="number" class="form-control" name="limit_device_gratis" value="{{ \App\Models\LandlordSetting::get('limit_device_gratis', 1) }}" min="1">
                        <small class="text-muted">Misal: 1</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Starter</label>
                        <input type="number" class="form-control" name="limit_device_starter" value="{{ \App\Models\LandlordSetting::get('limit_device_starter', 1) }}" min="1">
                        <small class="text-muted">Misal: 1</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Pro</label>
                        <input type="number" class="form-control" name="limit_device_pro" value="{{ \App\Models\LandlordSetting::get('limit_device_pro', 3) }}" min="1">
                        <small class="text-muted">Misal: 3</small>
                    </div>
                    <div class="col-md-3">
                        <label class="form-label fw-bold">Paket Business</label>
                        <input type="number" class="form-control" name="limit_device_business" value="{{ \App\Models\LandlordSetting::get('limit_device_business', 10) }}" min="1">
                        <small class="text-muted">Misal: 10</small>
                    </div>
                </div>
            </div>

            <!-- Tambahan: Harga Paket Tahunan (Berdasarkan Diskon) -->
            <div class="mt-5 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2"><i class="fa-solid fa-tags me-2"></i> Pengaturan Harga Paket Langganan Tahunan (1 Tahun)</h5>
                <p class="text-muted small">Tentukan persentase diskon untuk pelanggan yang memilih langganan tahunan. Harga tahunan akan otomatis dihitung: <code>(Harga Bulanan * 12) - Diskon</code>.</p>
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Diskon Paket Tahunan (%)</label>
                        <div class="input-group">
                            <input type="number" class="form-control" name="discount_yearly_percent" value="{{ \App\Models\LandlordSetting::get('discount_yearly_percent', '20') }}" min="0" max="100">
                            <span class="input-group-text">%</span>
                        </div>
                        <small class="text-muted">Misal: 20 (berarti diskon 20%)</small>
                    </div>
                </div>
            </div>

            <!-- Tambahan: Fallback Pembayaran Manual (Midtrans Error) -->
            <div class="mt-5 mb-4">
                <h5 class="fw-bold text-primary border-bottom pb-2"><i class="fa-solid fa-building-columns me-2"></i> Fallback Rekening Pembayaran Manual</h5>
                <p class="text-muted small">Instruksi ini akan otomatis muncul pada saat tenant akan membayar langganan namun server Midtrans sedang gagal memuat *Snap Token*.</p>
                <div class="row g-3">
                    <div class="col-12">
                        <label class="form-label fw-bold">Instruksi / Info Rekening Bank Landlord</label>
                        <textarea class="form-control" name="payment_instructions_fallback" rows="4">{{ \App\Models\LandlordSetting::get('payment_instructions_fallback', "BCA: 1234567890\nMandiri: 0987654321\nA.n PT Tenanta Inovasi") }}</textarea>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Nomor WhatsApp Konfirmasi</label>
                        <input type="text" class="form-control" name="whatsapp_confirmation_number" value="{{ \App\Models\LandlordSetting::get('whatsapp_confirmation_number', '6281234567890') }}">
                        <small class="text-muted">Gunakan awalan 62. Contoh: 6281234567890</small>
                    </div>
                </div>
            </div>

            <div class="d-flex justify-content-end mt-4">
                <button type="submit" class="btn btn-primary d-flex align-items-center">
                    <i data-lucide="save" class="me-2"></i> Simpan Konfigurasi
                </button>
            </div>
        </form>
    </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Category Select All Logic
    const categoryToggles = document.querySelectorAll('.category-toggle');
    categoryToggles.forEach(toggle => {
        toggle.addEventListener('change', function() {
            const category = this.dataset.category;
            const plan = this.dataset.plan;
            const isChecked = this.checked;
            
            const checkboxes = document.querySelectorAll(`.checkbox-${category}-${plan}`);
            checkboxes.forEach(cb => {
                cb.checked = isChecked;
            });
        });
    });
    
    // Initial check for 'Select All' toggles
    // You could also add logic here to automatically check the 'category-toggle' 
    // if all child checkboxes are manually checked.
});
</script>
@endsection
