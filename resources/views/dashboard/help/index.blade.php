@extends('layouts.app')

@section('title', 'Pusat Bantuan')

@section('content')
<div class="container-fluid px-4 py-4">
    <div class="mb-4">
        <h2 class="h3 mb-0 text-gray-800 fw-bold">Pusat Bantuan</h2>
        <p class="text-muted mb-0">Panduan penggunaan aplikasi dan bantuan teknis.</p>
    </div>

    <div class="row">
        <!-- FAQ / Panduan Dasar -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm rounded-4 h-100">
                <div class="card-header bg-white border-0 pt-4 pb-0">
                    <h5 class="fw-bold text-primary mb-0"><i data-lucide="book-open" class="me-2"></i>Panduan Dasar Penggunaan</h5>
                </div>
                <div class="card-body p-4">
                    <div class="mb-4">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="fa-solid fa-search text-muted"></i></span>
                            <input type="text" id="searchHelp" class="form-control bg-light border-start-0 shadow-none" placeholder="Cari topik panduan atau fitur...">
                        </div>
                    </div>
                    
                    <div class="alert alert-warning d-none" id="noResultAlert">
                        <i class="fa-solid fa-magnifying-glass me-2"></i> Pencarian tidak menemukan hasil.
                    </div>

                    <div class="accordion" id="accordionHelp">
                        @php
                            $guides = \App\Models\LandlordHelpGuide::orderBy('urutan')->get();
                        @endphp

                        @foreach($guides as $index => $guide)
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="heading{{ $guide->id }}">
                                <button class="accordion-button fw-bold text-dark {{ $index == 0 ? '' : 'collapsed' }}" type="button" data-bs-toggle="collapse" data-bs-target="#collapse{{ $guide->id }}" aria-expanded="{{ $index == 0 ? 'true' : 'false' }}" aria-controls="collapse{{ $guide->id }}">
                                    {{ $guide->pertanyaan }}
                                </button>
                            </h2>
                            <div id="collapse{{ $guide->id }}" class="accordion-collapse collapse {{ $index == 0 ? 'show' : '' }}" aria-labelledby="heading{{ $guide->id }}" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    {!! nl2br(html_entity_decode($guide->jawaban)) !!}
                                </div>
                            </div>
                        </div>
                        @endforeach

                        @if($guides->isEmpty())
                        <div class="alert alert-info border-0 rounded-4 mb-4">
                            <i class="fa-solid fa-circle-info me-2"></i> Belum ada panduan dasar yang ditambahkan oleh Administrator Pusat.
                        </div>
                        @endif

                        <div class="accordion-item mb-3 border rounded border-success">
                            <h2 class="accordion-header" id="headingNewFeatures">
                                <button class="accordion-button fw-bold text-success collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseNewFeatures" aria-expanded="false" aria-controls="collapseNewFeatures">
                                    <i class="fa-solid fa-wand-magic-sparkles me-2"></i> Update Fitur Baru: Produksi, HPP, & Kasir
                                </button>
                            </h2>
                            <div id="collapseNewFeatures" class="accordion-collapse collapse" aria-labelledby="headingNewFeatures" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    <p class="mb-3">Berikut adalah daftar fitur-fitur baru yang telah kami kembangkan untuk meningkatkan produktivitas Anda:</p>
                                    <ul class="mb-0">
                                        <li class="mb-2"><strong>Master Bahan Baku & Satuan Konversi:</strong> Anda dapat mencatat bahan baku dengan satuan pembelian (contoh: Kg) dan sistem otomatis mengkonversinya ke satuan terkecil (contoh: Gram) untuk perhitungan HPP yang akurat.</li>
                                        <li class="mb-2"><strong>Resep HPP & Manufaktur:</strong> Fitur untuk meracik resep produk jadi. Saat Anda melakukan <strong>Eksekusi Produksi Harian</strong>, stok bahan baku akan otomatis terpotong dari gudang sesuai takaran resep.</li>
                                        <li class="mb-2"><strong>Validasi Dapur:</strong> Produk yang diproduksi akan masuk ke status "Sedang Diproses" terlebih dahulu. Setelah selesai, dapur melakukan validasi (Selesai/Waste) untuk memindahkan produk menjadi stok Siap Jual.</li>
                                        <li class="mb-2"><strong>Tambah Uang Kasir (Cash In):</strong> Kasir kini memiliki tombol <strong>Tambah Kas</strong> untuk mencatat uang tambahan di laci (seperti uang receh/kembalian dari owner), terpisah dari laporan omset penjualan harian.</li>
                                        <li class="mb-2"><strong>Dukungan Multi-Kasir:</strong> Sistem kini 100% mendukung penggunaan banyak kasir secara serentak di perangkat berbeda. Cukup buatkan masing-masing kasir akun pengguna tersendiri.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <div class="accordion-item mb-3 border rounded border-info">
                            <h2 class="accordion-header" id="headingKalkulator">
                                <button class="accordion-button fw-bold text-info collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseKalkulator" aria-expanded="false" aria-controls="collapseKalkulator">
                                    <i class="fa-solid fa-calculator me-2"></i> Panduan Fitur: Kalkulator Finansial & Paket Bundling
                                </button>
                            </h2>
                            <div id="collapseKalkulator" class="accordion-collapse collapse" aria-labelledby="headingKalkulator" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    <p class="mb-3">Tingkatkan performa bisnis Anda dengan fitur-fitur pintar berikut:</p>
                                    <ul class="mb-0">
                                        <li class="mb-2"><strong>Kalkulator Bisnis & BEP:</strong> Terletak di menu <em>Keuangan & Laporan</em>. Masukkan target profit bulanan dan biaya tetap (fixed cost) Anda. Sistem akan memvisualisasikannya dalam grafik Break-Even Point (BEP) dan menghitung persis berapa porsi yang harus dijual untuk mencapai target tersebut.</li>
                                        <li class="mb-2"><strong>Breakdown Target Harian:</strong> Di halaman yang sama, terdapat tabel otomatis yang membagi target penjualan bulanan secara spesifik per-produk dan per-hari, lengkap dengan estimasi potensi omset.</li>
                                        <li class="mb-2"><strong>Paket Bundling Produk:</strong> Saat membuat produk baru, Anda bisa mengaktifkan mode "Paket Bundling". Terdapat kalkulator mini yang otomatis menghitung apakah harga paket Anda lebih murah (memberikan diskon ke customer) atau malah lebih mahal dari harga satuan. Sistem akan menampilkan persentase margin keuntungan Anda secara <em>real-time</em>!</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border rounded border-primary">
                            <h2 class="accordion-header" id="headingFour">
                                <button class="accordion-button fw-bold text-primary collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseFour" aria-expanded="false" aria-controls="collapseFour">
                                    <i class="fa-solid fa-plug me-2"></i> Panduan Integrasi API (Midtrans, Xendit, Gemini, Meta)
                                </button>
                            </h2>
                            <div id="collapseFour" class="accordion-collapse collapse" aria-labelledby="headingFour" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    <p class="mb-3">Pelajari cara mendapatkan kunci API (API Keys) dari layanan pihak ketiga seperti Payment Gateway dan Meta WhatsApp untuk dipasangkan ke dalam sistem ini.</p>
                                    <a href="{{ route('dashboard.help.api') }}" class="btn btn-outline-primary btn-sm rounded-pill">
                                        <i class="fa-solid fa-arrow-right me-1"></i> Baca Panduan API Selengkapnya
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kontak Support / Landlord -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm rounded-4 text-center">
                <div class="card-body p-5">
                    <div class="mb-4">
                        <div class="rounded-circle bg-primary bg-opacity-10 d-inline-flex align-items-center justify-content-center" style="width: 80px; height: 80px;">
                            <i data-lucide="headphones" class="text-primary" style="width: 40px; height: 40px;"></i>
                        </div>
                    </div>
                    <h5 class="fw-bold mb-3">Butuh Bantuan Lebih Lanjut?</h5>
                    <p class="text-muted small mb-4">
                        Jika Anda mengalami kendala teknis atau memiliki pertanyaan terkait tagihan berlangganan, tim support kami siap membantu Anda.
                    </p>
                    
                    @php
                        // Ambil kontak admin dari landlord settings (contoh: +628123456789)
                        $adminWa = '';
                        try {
                            $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
                            $adminWa = $settings['contact_whatsapp'] ?? '628123456789';
                            // Pastikan format WA benar
                            $adminWa = preg_replace('/[^0-9]/', '', $adminWa);
                            if (substr($adminWa, 0, 1) == '0') {
                                $adminWa = '62' . substr($adminWa, 1);
                            }
                        } catch (\Exception $e) {
                            $adminWa = '628123456789';
                        }
                    @endphp

                    <a href="https://wa.me/{{ collect(\App\Models\LandlordSetting::where('key', 'contact_whatsapp')->first())->get('value', '6285223363659') }}?text=Halo%20Admin%20Tenanta%2C%20saya%20butuh%20bantuan%20terkait%20aplikasi." target="_blank" class="btn btn-success w-100 rounded-pill fw-bold d-flex align-items-center justify-content-center py-2">
                        <i data-lucide="message-circle" class="me-2"></i> Hubungi Support WA
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        const searchInput = document.getElementById('searchHelp');
        const accordionItems = document.querySelectorAll('#accordionHelp .accordion-item');
        const noResultAlert = document.getElementById('noResultAlert');

        if (searchInput) {
            searchInput.addEventListener('input', function(e) {
                const term = e.target.value.toLowerCase();
                let hasVisible = false;

                accordionItems.forEach(item => {
                    // Hanya cari di judul dan isi content
                    const title = item.querySelector('.accordion-button').textContent.toLowerCase();
                    const body = item.querySelector('.accordion-body').textContent.toLowerCase();
                    
                    if (title.includes(term) || body.includes(term)) {
                        item.style.display = 'block';
                        hasVisible = true;
                        
                        // Jika sedang mencari (term tidak kosong), otomatis buka accordion yang cocok
                        const collapseEl = item.querySelector('.accordion-collapse');
                        const btnEl = item.querySelector('.accordion-button');
                        if (term.trim() !== '') {
                            collapseEl.classList.add('show');
                            btnEl.classList.remove('collapsed');
                            btnEl.setAttribute('aria-expanded', 'true');
                        } else {
                            collapseEl.classList.remove('show');
                            btnEl.classList.add('collapsed');
                            btnEl.setAttribute('aria-expanded', 'false');
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                if (!hasVisible && term.trim() !== '') {
                    noResultAlert.classList.remove('d-none');
                } else {
                    noResultAlert.classList.add('d-none');
                }
            });
        }
    });
</script>
@endsection
