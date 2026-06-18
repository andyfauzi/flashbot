@extends('layouts.app')

@section('title', 'Buat Broadcast Promosi')

@section('content')
<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-lg-8">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h2 class="fw-bold mb-1 text-dark" style="font-family: var(--font-heading);">Buat Broadcast Promosi</h2>
                    <p class="text-secondary mb-0 small">Kirim pesan massal ke pengguna Anda.</p>
                </div>
                <a href="{{ route('chatbot.broadcast.index') }}" class="btn btn-light border px-4 rounded-pill fw-bold text-dark">
                    <i class="fa-solid fa-arrow-left me-2"></i> Kembali
                </a>
            </div>

            @if(session('error'))
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                <i class="fa-solid fa-triangle-exclamation me-2"></i> {{ session('error') }}
            </div>
            @endif

            @if($errors->any())
            <div class="alert alert-danger border-0 rounded-4 shadow-sm mb-4">
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
            @endif

            <form action="{{ route('chatbot.broadcast.store') }}" method="POST">
                @csrf
                <div class="card-premium p-4 shadow-sm mb-4">
                    
                    @if(!$isMeta)
                    <div class="alert alert-danger border-0 rounded-4 p-4 mb-4" style="background-color: #fee2e2;">
                        <h5 class="fw-bold text-danger mb-3"><i class="fa-solid fa-triangle-exclamation me-2"></i> PERINGATAN BANNED</h5>
                        <p class="text-danger mb-3">
                            Mengirim pesan massal (broadcast) menggunakan Gateway Sistem (Baileys) berpotensi sangat besar menyebabkan nomor WhatsApp Anda <strong>DIBLOKIR SECARA PERMANEN</strong> oleh Meta. 
                        </p>
                        <div class="form-check p-3 bg-white rounded border border-danger">
                            <input class="form-check-input ms-1 me-2" type="checkbox" name="persetujuan_risiko" id="persetujuanRisiko" value="1" required>
                            <label class="form-check-label fw-bold text-dark" for="persetujuanRisiko">
                                Saya mengerti, setuju, dan menanggung sepenuhnya segala risiko pemblokiran nomor WhatsApp yang mungkin terjadi.
                            </label>
                        </div>
                    </div>
                    @else
                    <div class="alert alert-success border-0 rounded-4 p-3 mb-4 d-flex align-items-center gap-3" style="background-color: #d1fae5;">
                        <i class="fa-solid fa-shield-check fs-2 text-success"></i>
                        <div>
                            <span class="fw-bold text-dark d-block">Meta API Aktif - Aman untuk Broadcast</span>
                            <small class="text-dark">Pesan massal Anda aman. Harap perhatikan limit tier (250-1000 pesan/hari) dan gunakan pesan Template (Message Template) jika di luar jendela 24 jam interaksi.</small>
                        </div>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Judul Broadcast <span class="text-danger">*</span></label>
                        <input type="text" name="judul" class="form-control form-control-premium" placeholder="Contoh: Promo Akhir Tahun 2026" required>
                        <small class="text-muted">Hanya untuk identifikasi riwayat internal Anda.</small>
                    </div>

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Target Penerima <span class="text-danger">*</span></label>
                        <select name="target_filter" class="form-select form-select-premium">
                            <option value="all">Semua Pengguna (Estimasi: {{ number_format($totalUsers) }} kontak)</option>
                            <option value="interaksi_rendah">Hanya Interaksi Rendah / Jarang Pesan (Limit 50)</option>
                        </select>
                        <small class="text-muted mt-1 d-block">Membatasi target dapat menghemat kuota limit Meta dan mengurangi risiko spam.</small>
                    </div>

                    @if($isMeta)
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Nama Template Meta (Opsional)</label>
                        <input type="text" name="meta_template_name" class="form-control form-control-premium" placeholder="Contoh: promo_akhir_tahun">
                        <small class="text-muted">Jika penerima belum berinteraksi dalam 24 jam terakhir, Anda WAJIB menggunakan Template Message yang telah di-approve oleh Meta.</small>
                    </div>
                    @endif

                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark">Isi Pesan Promosi <span class="text-danger">*</span></label>
                        <textarea name="isi_pesan" class="form-control form-control-premium" rows="6" placeholder="Halo kak, ada diskon spesial nih..." required></textarea>
                        @if($isMeta)
                            <small class="text-muted mt-1 d-block"><i class="fa-solid fa-circle-info me-1"></i> Jika menggunakan Template, isi pesan ini akan dikirim jika pengguna masih dalam jendela 24 jam (sebagai fallback), atau biarkan sebagai teks utama jika Template tidak diset.</small>
                        @endif
                    </div>

                    <div class="d-flex justify-content-end mt-4 pt-3 border-top">
                        <button type="submit" class="btn btn-premium btn-premium-brand px-5 py-3 fw-bold fs-6">
                            <i class="fa-solid fa-paper-plane me-2"></i> Eksekusi Broadcast Sekarang
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection
