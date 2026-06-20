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
