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
                    <div class="accordion" id="accordionHelp">
                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="headingOne">
                                <button class="accordion-button fw-bold text-dark" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOne" aria-expanded="true" aria-controls="collapseOne">
                                    Bagaimana cara mengatur bot WhatsApp?
                                </button>
                            </h2>
                            <div id="collapseOne" class="accordion-collapse collapse show" aria-labelledby="headingOne" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    Anda dapat membuka menu <strong>Dashboard Chatbot</strong>, lalu pastikan koneksi device berstatus "Connected" (jika menggunakan Baileys). Anda juga bisa mengatur nama bot dan karakter pelayanannya pada menu <strong>Pengaturan Toko</strong>.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="headingTwo">
                                <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseTwo" aria-expanded="false" aria-controls="collapseTwo">
                                    Bagaimana cara mengubah harga atau stok produk?
                                </button>
                            </h2>
                            <div id="collapseTwo" class="accordion-collapse collapse" aria-labelledby="headingTwo" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    Buka menu <strong>Manajemen Produk</strong> > <strong>Produk & Varian</strong>. Klik tombol "Edit" pada produk yang ingin diubah. Jika Anda menggunakan sistem HPP, masuk ke menu <strong>Kalkulator HPP</strong> untuk mengatur margin keuntungan dari resep.
                                </div>
                            </div>
                        </div>

                        <div class="accordion-item mb-3 border rounded">
                            <h2 class="accordion-header" id="headingThree">
                                <button class="accordion-button fw-bold text-dark collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#collapseThree" aria-expanded="false" aria-controls="collapseThree">
                                    Bagaimana cara menerima pesanan?
                                </button>
                            </h2>
                            <div id="collapseThree" class="accordion-collapse collapse" aria-labelledby="headingThree" data-bs-parent="#accordionHelp">
                                <div class="accordion-body text-muted">
                                    Setiap ada pesanan baru dari WhatsApp atau Portal, pesanan tersebut akan otomatis muncul di menu <strong>Kasir (POS)</strong> di tab "Pesanan Aktif" atau di menu <strong>Riwayat Transaksi</strong>. Anda dapat menerima pesanan dan memprosesnya hingga selesai.
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
