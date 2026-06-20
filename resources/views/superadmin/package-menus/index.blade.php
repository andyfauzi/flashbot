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
