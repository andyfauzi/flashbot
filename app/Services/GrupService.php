<?php

namespace App\Services;

use App\Models\ChatbotMenu;
use App\Models\GrupAdmin;
use App\Models\GrupCatatan;
use App\Models\GrupPengingat;
use App\Models\GrupPesan;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class GrupService
{
    protected WhatsAppService $wa;
    protected bool $currentSenderIsAdmin = false;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    // Helper untuk mengecek admin grup (baik level database maupun level WhatsApp)
    private function checkIsAdmin(string $grupId, string $pengirim): bool
    {
        return $this->currentSenderIsAdmin || GrupAdmin::isAdmin($grupId, $pengirim);
    }

    // =============================================
    // PROSES PESAN MASUK DI GRUP
    // =============================================
    public function prosesPesanGrup(string $grupId, string $pengirim, string $pesan, ?string $grupNama = null, bool $isSenderAdmin = false): void
    {
        $this->currentSenderIsAdmin = $isSenderAdmin;
        GrupPesan::simpan($grupId, $pengirim, $pesan, $grupNama);
        Log::info("📨 Pesan grup [{$grupId}] dari {$pengirim}: {$pesan}");

        $pesan = trim($pesan);
        $teks = strtolower($pesan);

        // =============================================
        // CEK PENDAFTARAN GRUP BARU & WHITELIST GATE
        // =============================================
        $isWhitelisted = \App\Models\GrupSetting::ambil($grupId, 'is_whitelisted', '0') === '1';
        $isSellerGroup = (env('WHATSAPP_GROUP_ID_SELLER') === $grupId);

        if ($teks === '!aktifkan-bot' || $teks === '!daftarkan-grup') {
            if ($isWhitelisted || $isSellerGroup) {
                $this->wa->kirimPesan($grupId, "✅ Tenanta.id sudah aktif di grup ini.");
            } else {
                $this->wa->kirimPesan($grupId, "✅ *Permintaan pendaftaran grup diterima!*\n\nNama grup ini sekarang sudah tercatat di *Dashboard Admin*.\n\nSilakan minta Admin Utama untuk mengubah status izin (Whitelist) di Dashboard agar bot dapat berfungsi penuh.");
            }
            return;
        }

        // Hentikan proses jika grup belum di-whitelist (Privasi Ekstra)
        if (!$isWhitelisted && !$isSellerGroup) {
            return;
        }

        // =============================================
        // 1. CEK PANGGILAN AI (MENTION)
        // =============================================
        if (env('USE_GEMINI_AI') === true || env('USE_GEMINI_AI') === 'true') {
            if (preg_match('/\b(bot|teta|@bot|@teta)\b/i', $pesan)) {
                Log::info("🤖 Panggilan AI terdeteksi di grup [{$grupId}] dari {$pengirim}");
                try {
                    $gemini = app(\App\Services\GeminiAiService::class);
                    // Melempar pesan ke AI. Kita sertakan nomor pengirim agar AI tahu siapa yang bicara
                    $context = (env('WHATSAPP_GROUP_ID_SELLER') === $grupId) ? 'admin' : 'customer';
                    $reply = $gemini->handleMessage($grupId, "Pesan dari {$pengirim}: " . $pesan, $context);
                    $this->wa->kirimPesan($grupId, $reply);
                } catch (\Exception $e) {
                    Log::error("Gemini AI Group Error: " . $e->getMessage());
                    $this->wa->kirimPesan($grupId, "Sistem AI sedang offline. Pesan error: " . $e->getMessage());
                }
                return; // Stop eksekusi jika AI sudah menangani
            }
        }

        // =============================================
        // 2. CEK BALASAN OTOMATIS KUSTOM (AUTO-REPLY GRUP)
        // =============================================
        $balasanOtomatis = \App\Models\GrupAutoReply::cariBalasan($grupId, $teks);
        if ($balasanOtomatis) {
            $this->wa->kirimPesan($grupId, $balasanOtomatis);
            return;
        }

        // =============================================
        // 2. PERINTAH SISTEM (Bisa diubah aliasnya)
        // =============================================
        $ambilCmd = function($kunci, $default) use ($grupId) {
            return array_map('trim', array_map('strtolower', explode(',', \App\Models\GrupSetting::ambil($grupId, $kunci, $default))));
        };

        $cmdBantuan        = $ambilCmd('cmd_bantuan', '!bantuan,!help');
        $cmdAdmin          = $ambilCmd('cmd_admin', '!admin');
        $cmdSetAdmin       = $ambilCmd('cmd_set_admin', '!set-admin,!tambah-admin');
        $cmdHapusAdmin     = $ambilCmd('cmd_hapus_admin', '!hapus-admin,!remove-admin');

        $barisPesan    = preg_split('/\r\n|\r|\n/', trim($pesan), 2);
        $barisUtama    = trim($barisPesan[0]);
        $bagian        = explode(' ', $barisUtama, 2);
        $perintah      = strtolower($bagian[0]);
        $argumenBaris1 = isset($bagian[1]) ? trim($bagian[1]) : '';
        $argumenBaris2 = isset($barisPesan[1]) ? trim($barisPesan[1]) : '';
        $argumen       = !empty($argumenBaris1) ? $argumenBaris1 . "\n" . $argumenBaris2 : $argumenBaris2;

        if (in_array($perintah, $cmdBantuan)) {
            $this->kirimBantuan($grupId); return;
        } elseif (in_array($perintah, $cmdAdmin)) {
            $this->tampilkanAdmin($grupId); return;
        } elseif (in_array($perintah, $cmdSetAdmin)) {
            $this->tambahAdmin($grupId, $pengirim, $argumen); return;
        } elseif (in_array($perintah, $cmdHapusAdmin)) {
            $this->hapusAdmin($grupId, $pengirim, $argumen); return;
        } elseif (in_array($perintah, ['!stok', '!produk'])) {
            $this->manageStokGrup($grupId, $pengirim, 'list', $argumen); return;
        } elseif ($perintah === '!tambah-produk') {
            $this->manageStokGrup($grupId, $pengirim, 'tambah-produk', $argumen); return;
        } elseif ($perintah === '!stok-set') {
            $this->manageStokGrup($grupId, $pengirim, 'set', $argumen); return;
        } elseif ($perintah === '!stok-tambah') {
            $this->manageStokGrup($grupId, $pengirim, 'tambah', $argumen); return;
        } elseif ($perintah === '!stok-kurang') {
            $this->manageStokGrup($grupId, $pengirim, 'kurang', $argumen); return;
        } elseif ($perintah === '!hapus-produk') {
            $this->manageStokGrup($grupId, $pengirim, 'hapus-produk', $argumen); return;
        } elseif ($perintah === '!orderan-pending') {
            $this->manageOrderGrup($grupId, $pengirim, 'pending', $argumen); return;
        } elseif ($perintah === '!orderan-proses') {
            $this->manageOrderGrup($grupId, $pengirim, 'proses', $argumen); return;
        } elseif ($perintah === '!orderan-detail') {
            $this->manageOrderGrup($grupId, $pengirim, 'detail', $argumen); return;
        } elseif ($perintah === '!set-ongkir') {
            $this->manageOrderGrup($grupId, $pengirim, 'set-ongkir', $argumen); return;
        } elseif ($perintah === '!set-dp') {
            $this->manageOrderGrup($grupId, $pengirim, 'set-dp', $argumen); return;
        } elseif ($perintah === '!konfirmasi-bayar') {
            $this->manageOrderGrup($grupId, $pengirim, 'konfirmasi-bayar', $argumen); return;
        } elseif ($perintah === '!setuju-order') {
            $this->manageOrderGrup($grupId, $pengirim, 'setuju-order', $argumen); return;
        } elseif ($perintah === '!orderan-batal') {
            $this->manageOrderGrup($grupId, $pengirim, 'batal', $argumen); return;
        }

        // Kalau perintah dimulai dengan ! tapi tidak dikenali, berikan warning
        if (str_starts_with($pesan, '!')) {
            $cmdBantuanDisplay = $cmdBantuan[0] ?? '!bantuan';
            $this->wa->kirimPesan($grupId,
                "❓ Perintah tidak dikenali.\n\nKetik *{$cmdBantuanDisplay}* untuk melihat daftar perintah."
            );
            return;
        }

        // =============================================
        // RESPON INTERAKTIF (seperti personal chat)
        // =============================================

        // Keyword untuk tampilkan menu
        $sapaan = ['halo', 'hi', 'hello', 'hei', 'hai', 'mulai', 'start', 'menu', '0'];
        if (in_array($teks, $sapaan)) {
            $this->wa->kirimPesan($grupId, ChatbotMenu::buildMenuUtama());
            return;
        }

        // Cek pilihan menu
        $menu = ChatbotMenu::where('kode', $teks)->where('aktif', true)->first();
        if ($menu) {
            $this->wa->kirimPesan($grupId, $menu->isi, $menu->media_url, $menu->media_type);
            return;
        }

        // Cek nomor order
        if (str_starts_with($teks, 'order-')) {
            $nomorOrder = strtoupper($pesan);
            $this->wa->kirimPesan($grupId,
                "🔍 Mencari pesanan *{$nomorOrder}*...\n\n" .
                "Status: *Sedang diproses* 📦\n" .
                "Estimasi tiba: 2-3 hari kerja\n\n" .
                "Ketik *0* untuk kembali ke menu."
            );
            return;
        }

        // Default - tidak dikenali (Obrolan biasa antar anggota grup)
        // Bot diam saja dan tidak merespon agar tidak mengganggu (spam).
        return;
    }

    // =============================================
    // TAMPILKAN BANTUAN
    // =============================================
    private function kirimBantuan(string $grupId): void
    {
        $identitas = \App\Models\IdentitasToko::first();
        $namaToko = $identitas->nama_toko ?? 'Toko Anda';
        $teks  = "🤖 *Teta Assistant " . strtoupper($namaToko) . " - Daftar Perintah*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";
        $teks .= "🛒 *Manajemen Order (Admin):*\n";
        $teks .= "• `!orderan-pending` — Lihat pesanan baru\n";
        $teks .= "• `!orderan-proses` — Lihat pesanan diproses\n";
        $teks .= "• `!set-ongkir [NoOrder] [Nominal]` — Tetapkan ongkir\n";
        $teks .= "• `!setuju-order [NoOrder]` — Setujui pesanan\n";
        $teks .= "• `!orderan-batal [NoOrder]` — Batalkan pesanan\n\n";
        $teks .= "📦 *Manajemen Stok (Admin):*\n";
        $teks .= "• `!stok` / `!produk` — Lihat semua stok\n";
        $teks .= "• `!stok-set [Kode] [Jumlah]` — Ubah stok\n";
        $teks .= "• `!stok-tambah [Kode] [Jumlah]` — Tambah stok\n\n";
        $teks .= "👨‍💼 *Manajemen Admin:*\n";
        $teks .= "• `!admin` — Lihat daftar admin\n";
        $teks .= "• `!set-admin [Nomor]` — Tambah admin\n";
        $teks .= "• `!hapus-admin [Nomor]` — Hapus admin\n\n";
        $teks .= "━━━━━━━━━━━━━━━━\n";
        $identitas = \App\Models\IdentitasToko::first();
        $namaToko = $identitas->nama_toko ?? 'Toko Anda';
        $teks .= "💡 _Gunakan perintah di atas untuk mengelola {$namaToko} langsung dari grup!_\n\n";
        $teks .= "🤖 _Untuk mengobrol dengan AI Customer Service, sebutkan kata *bot* atau *@teta* di dalam pesan Anda!_";

        $this->wa->kirimPesan($grupId, $teks);
    }

    // =============================================
    // SIMPAN CATATAN
    // =============================================
    private function simpanCatatan(string $grupId, string $pengirim, string $isi): void
    {
        if (empty(trim($isi))) {
            $this->wa->kirimPesan($grupId,
                "❌ Isi catatan tidak boleh kosong!\n\nContoh: `!simpan Rapat besok jam 10`"
            );
            return;
        }

        $catatan = GrupCatatan::simpan($grupId, $pengirim, $isi);

        $this->wa->kirimPesan($grupId,
            "✅ *Catatan tersimpan!*\n\n" .
            "📌 ID: *#{$catatan->id}*\n" .
            "📝 Isi: {$isi}\n" .
            "👤 Disimpan oleh: {$pengirim}\n\n" .
            "Ketik `!catatan` untuk melihat semua catatan."
        );
    }

    // =============================================
    // TAMPILKAN SEMUA CATATAN
    // =============================================
    private function tampilkanCatatan(string $grupId): void
    {
        $catatan = GrupCatatan::ambilSemua($grupId);

        if ($catatan->isEmpty()) {
            $this->wa->kirimPesan($grupId,
                "📋 Belum ada catatan tersimpan.\n\nKetik `!simpan [teks]` untuk menyimpan catatan."
            );
            return;
        }

        $teks  = "📋 *Daftar Catatan Grup*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";

        foreach ($catatan as $c) {
            $waktu = Carbon::parse($c->waktu)->format('d/m/Y H:i');
            $teks .= "📌 *#{$c->id}* — {$waktu}\n";
            $teks .= "{$c->isi}\n";
            $teks .= "👤 _{$c->disimpan_oleh}_\n\n";
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Ketik `!hapus [id]` untuk menghapus catatan.";

        $this->wa->kirimPesan($grupId, $teks);
    }

    // =============================================
    // HAPUS CATATAN (hanya admin)
    // =============================================
    private function hapusCatatan(string $grupId, string $pengirim, string $idStr): void
    {
        if (!$this->checkIsAdmin($grupId, $pengirim)) {
            $this->wa->kirimPesan($grupId,
                "❌ *Hanya admin grup yang dapat menghapus catatan!*"
            );
            return;
        }

        $id = (int) trim($idStr);

        if ($id <= 0) {
            $this->wa->kirimPesan($grupId, "❌ ID tidak valid!\n\nContoh: `!hapus 5`");
            return;
        }

        $berhasil = GrupCatatan::hapusById($grupId, $id);

        if ($berhasil) {
            $this->wa->kirimPesan($grupId, "✅ Catatan *#{$id}* berhasil dihapus!");
        } else {
            $this->wa->kirimPesan($grupId, "❌ Catatan *#{$id}* tidak ditemukan.");
        }
    }

    // =============================================
    // HAPUS PENGINGAT
    // =============================================
    private function hapusPengingat(string $grupId, string $pengirim, string $idStr): void
    {
        $id = (int) trim($idStr);

        if ($id <= 0) {
            $this->wa->kirimPesan($grupId, "❌ ID tidak valid!\n\nContoh: `!hapus-pengingat 3`");
            return;
        }

        $pengingat = GrupPengingat::where('grup_id', $grupId)->where('id', $id)->first();

        if (!$pengingat) {
            $this->wa->kirimPesan($grupId, "❌ Pengingat *#{$id}* tidak ditemukan.");
            return;
        }

        $pengingat->delete();
        $this->wa->kirimPesan($grupId, "✅ Pengingat *#{$id}* berhasil dihapus!");
    }

    // =============================================
    // CARI PESAN LAMA
    // =============================================
    private function cariPesan(string $grupId, string $keyword): void
    {
        if (empty(trim($keyword))) {
            $this->wa->kirimPesan($grupId,
                "❌ Masukkan kata yang ingin dicari!\n\nContoh: `!cari meeting`"
            );
            return;
        }

        $hasil = GrupPesan::cari($grupId, $keyword);

        if ($hasil->isEmpty()) {
            $this->wa->kirimPesan($grupId,
                "🔍 Tidak ada pesan yang mengandung kata *\"{$keyword}\"*."
            );
            return;
        }

        $teks  = "🔍 *Hasil Pencarian: \"{$keyword}\"*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";

        foreach ($hasil as $p) {
            $waktu = Carbon::parse($p->waktu)->format('d/m/Y H:i');
            $teks .= "👤 *{$p->pengirim}* — {$waktu}\n";
            $teks .= "{$p->pesan}\n\n";
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Menampilkan *{$hasil->count()}* pesan terbaru.";

        $this->wa->kirimPesan($grupId, $teks);
    }

    // =============================================
    // BUAT PENGINGAT
    // =============================================
    private function buatPengingat(string $grupId, string $pengirim, string $argumen): void
    {
        if (empty(trim($argumen))) {
            $this->wa->kirimPesan($grupId,
                "⏰ *Buat Pengingat Kegiatan*\n\n" .
                "Kirim pesan dengan format:\n\n" .
                "!ingatkan\n" .
                "Kegiatan: Rapat Koordinasi\n" .
                "Pemilik: Pak Budi\n" .
                "Tanggal: 25/12/2026 09:00\n" .
                "Tempat: Ruang Rapat Lt.2\n\n" .
                "⚠️ Semua field wajib diisi."
            );
            return;
        }

        $baris = preg_split('/\r\n|\r|\n/', trim($argumen));
        $data  = [];

        foreach ($baris as $b) {
            $b = trim($b);
            if (stripos($b, 'Kegiatan:') === 0) {
                $data['kegiatan'] = trim(substr($b, 9));
            } elseif (stripos($b, 'Pemilik:') === 0) {
                $data['pemilik'] = trim(substr($b, 8));
            } elseif (stripos($b, 'Tanggal:') === 0) {
                $data['tanggal'] = trim(substr($b, 8));
            } elseif (stripos($b, 'Tempat:') === 0) {
                $data['tempat'] = trim(substr($b, 7));
            } elseif (stripos($b, 'Link Zoom:') === 0) {
                $data['link_zoom'] = trim(substr($b, 10));
            } elseif (stripos($b, 'ID Zoom:') === 0) {
                $data['id_zoom'] = trim(substr($b, 8));
            }
        }

        $missing = [];
        if (empty($data['kegiatan'])) $missing[] = 'Kegiatan';
        if (empty($data['pemilik']))  $missing[] = 'Pemilik';
        if (empty($data['tanggal']))  $missing[] = 'Tanggal';
        if (empty($data['tempat']))   $missing[] = 'Tempat';

        if (!empty($missing)) {
            $this->wa->kirimPesan($grupId,
                "❌ Field wajib belum diisi: " . implode(', ', $missing) . "\n\n" .
                "Ketik `!ingatkan` untuk melihat format lengkap."
            );
            return;
        }

        try {
            $waktuStr = $data['tanggal'];
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/', $waktuStr)) {
                $waktu = Carbon::createFromFormat('d/m/Y H:i', $waktuStr);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $waktuStr)) {
                $waktu = Carbon::today()->setTimeFromTimeString($waktuStr);
                if ($waktu->isPast()) $waktu->addDay();
            } else {
                throw new \Exception('Format waktu tidak dikenali');
            }
        } catch (\Exception $e) {
            $this->wa->kirimPesan($grupId,
                "❌ Format tanggal tidak valid!\n\n" .
                "Gunakan: `25/12/2026 09:00` atau `09:00`"
            );
            return;
        }

        $pesanArr = [
            'kegiatan' => $data['kegiatan'],
            'pemilik'  => $data['pemilik'],
            'tempat'   => $data['tempat'],
        ];
        if (!empty($data['link_zoom'])) $pesanArr['link_zoom'] = $data['link_zoom'];
        if (!empty($data['id_zoom'])) $pesanArr['id_zoom'] = $data['id_zoom'];

        $pesanData = json_encode($pesanArr);

        $pengingat = GrupPengingat::buat($grupId, $pengirim, $pesanData, $waktu);

        $teksSukses = "✅ *Pengingat Kegiatan Tersimpan!*\n" .
                      "━━━━━━━━━━━━━━━━\n\n" .
                      "📌 ID: *#{$pengingat->id}*\n" .
                      "📋 Kegiatan: *{$pesanArr['kegiatan']}*\n" .
                      "👤 Pemilik: {$pesanArr['pemilik']}\n" .
                      "📅 Tanggal: *{$waktu->format('d/m/Y H:i')}*\n" .
                      "📍 Tempat: {$pesanArr['tempat']}\n";
        
        if (isset($pesanArr['link_zoom'])) $teksSukses .= "🔗 Link Zoom: {$pesanArr['link_zoom']}\n";
        if (isset($pesanArr['id_zoom']))   $teksSukses .= "🆔 ID Zoom: {$pesanArr['id_zoom']}\n";

        $teksSukses .= "\n🔔 _Saya akan mengirimkan notifikasi 10 menit sebelum kegiatan dimulai._";

        $this->wa->kirimPesan($grupId, $teksSukses);
    }

    // =============================================
    // EDIT PENGINGAT
    // =============================================
    private function editPengingat(string $grupId, string $pengirim, string $argumen): void
    {
        $baris = preg_split('/\r\n|\r|\n/', trim($argumen));
        
        if (empty($baris) || empty(trim($baris[0]))) {
            $cmd = explode(',', \App\Models\GrupSetting::ambil($grupId, 'cmd_edit_pengingat', '!edit-pengingat'))[0];
            $this->wa->kirimPesan($grupId,
                "❌ *ID Pengingat diperlukan!*\n\n" .
                "Gunakan format:\n" .
                "{$cmd} [ID]\n" .
                "Kegiatan: Rapat Baru\n" .
                "Pemilik: Pak Budi Baru\n" .
                "Tanggal: 26/12/2026 10:00\n" .
                "Tempat: Ruang Meeting Baru"
            );
            return;
        }

        $idStr = array_shift($baris); // Ambil baris pertama sebagai ID
        $id = (int) trim($idStr);

        $pengingat = GrupPengingat::where('grup_id', $grupId)->where('id', $id)->first();
        
        if (!$pengingat) {
            $this->wa->kirimPesan($grupId, "❌ Pengingat dengan ID *#{$id}* tidak ditemukan.");
            return;
        }

        if ($pengingat->sudah_dikirim) {
            $this->wa->kirimPesan($grupId, "❌ Pengingat dengan ID *#{$id}* sudah terkirim dan tidak dapat diedit lagi.");
            return;
        }

        // Ambil data yang ada saat ini
        $dataLama = json_decode($pengingat->pesan, true);
        $dataBaru = [
            'kegiatan' => $dataLama['kegiatan'] ?? '',
            'pemilik'  => $dataLama['pemilik'] ?? '',
            'tanggal'  => \Carbon\Carbon::parse($pengingat->waktu_ingatkan)->format('d/m/Y H:i'),
            'tempat'   => $dataLama['tempat'] ?? ''
        ];
        if (isset($dataLama['link_zoom'])) $dataBaru['link_zoom'] = $dataLama['link_zoom'];
        if (isset($dataLama['id_zoom'])) $dataBaru['id_zoom'] = $dataLama['id_zoom'];

        // Timpa dengan data baru yang dimasukkan
        foreach ($baris as $b) {
            $b = trim($b);
            if (stripos($b, 'Kegiatan:') === 0) {
                $dataBaru['kegiatan'] = trim(substr($b, 9));
            } elseif (stripos($b, 'Pemilik:') === 0) {
                $dataBaru['pemilik'] = trim(substr($b, 8));
            } elseif (stripos($b, 'Tanggal:') === 0) {
                $dataBaru['tanggal'] = trim(substr($b, 8));
            } elseif (stripos($b, 'Tempat:') === 0) {
                $dataBaru['tempat'] = trim(substr($b, 7));
            } elseif (stripos($b, 'Link Zoom:') === 0) {
                $val = trim(substr($b, 10));
                if (empty($val) || strtolower($val) == 'hapus') unset($dataBaru['link_zoom']);
                else $dataBaru['link_zoom'] = $val;
            } elseif (stripos($b, 'ID Zoom:') === 0) {
                $val = trim(substr($b, 8));
                if (empty($val) || strtolower($val) == 'hapus') unset($dataBaru['id_zoom']);
                else $dataBaru['id_zoom'] = $val;
            }
        }

        try {
            $waktuStr = $dataBaru['tanggal'];
            if (preg_match('/^\d{2}\/\d{2}\/\d{4}\s+\d{2}:\d{2}$/', $waktuStr)) {
                $waktu = Carbon::createFromFormat('d/m/Y H:i', $waktuStr);
            } elseif (preg_match('/^\d{2}:\d{2}$/', $waktuStr)) {
                $waktu = Carbon::today()->setTimeFromTimeString($waktuStr);
                if ($waktu->isPast()) $waktu->addDay();
            } else {
                throw new \Exception('Format waktu tidak dikenali');
            }
        } catch (\Exception $e) {
            $this->wa->kirimPesan($grupId, "❌ Format tanggal baru tidak valid!\n\nGunakan: `25/12/2026 09:00` atau `09:00`");
            return;
        }

        $pesanData = json_encode($dataBaru);

        $pengingat->update([
            'pesan' => $pesanData,
            'waktu_ingatkan' => $waktu
        ]);

        $teksSukses = "✅ *Pengingat Kegiatan Berhasil Diedit!*\n" .
                      "━━━━━━━━━━━━━━━━\n\n" .
                      "📌 ID: *#{$pengingat->id}*\n" .
                      "📋 Kegiatan: *{$dataBaru['kegiatan']}*\n" .
                      "👤 Pemilik: {$dataBaru['pemilik']}\n" .
                      "📅 Tanggal: *{$waktu->format('d/m/Y H:i')}*\n" .
                      "📍 Tempat: {$dataBaru['tempat']}\n";
        
        if (isset($dataBaru['link_zoom'])) $teksSukses .= "🔗 Link Zoom: {$dataBaru['link_zoom']}\n";
        if (isset($dataBaru['id_zoom']))   $teksSukses .= "🆔 ID Zoom: {$dataBaru['id_zoom']}\n";

        $teksSukses .= "\n🔔 _Saya akan mengirimkan notifikasi 10 menit sebelum kegiatan dimulai._";

        $this->wa->kirimPesan($grupId, $teksSukses);
    }

    // =============================================
    // TAMPILKAN SEMUA PENGINGAT
    // =============================================
    private function tampilkanPengingat(string $grupId): void
    {
        $pengingat = GrupPengingat::where('grup_id', $grupId)
            ->where('sudah_dikirim', false)
            ->orderBy('waktu_ingatkan')
            ->get();

        if ($pengingat->isEmpty()) {
            $this->wa->kirimPesan($grupId,
                "⏰ Belum ada pengingat aktif.\n\nKetik `!ingatkan` untuk membuat pengingat."
            );
            return;
        }

        $teks  = "⏰ *Daftar Pengingat Aktif*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";

        foreach ($pengingat as $p) {
            $waktu = Carbon::parse($p->waktu_ingatkan)->format('d/m/Y H:i');
            $data  = json_decode($p->pesan, true);

            if ($data && isset($data['kegiatan'])) {
                $teks .= "📌 *#{$p->id}*\n";
                $teks .= "📋 *{$data['kegiatan']}*\n";
                $teks .= "👤 Pemilik: {$data['pemilik']}\n";
                $teks .= "📅 Tanggal: {$waktu}\n";
                $teks .= "📍 Tempat: {$data['tempat']}\n";
                if (isset($data['link_zoom'])) $teks .= "🔗 Link Zoom: {$data['link_zoom']}\n";
                if (isset($data['id_zoom']))   $teks .= "🆔 ID Zoom: {$data['id_zoom']}\n";
                $teks .= "\n";
            } else {
                $teks .= "📌 *#{$p->id}* — {$waktu}\n";
                $teks .= "{$p->pesan}\n\n";
            }
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Total: *{$pengingat->count()}* pengingat aktif.\n";
        $teks .= "Ketik `!hapus-pengingat [id]` untuk menghapus.";

        $this->wa->kirimPesan($grupId, $teks);
    }

    // =============================================
    // KIRIM PENGINGAT YANG SUDAH WAKTUNYA
    // =============================================
    public function kirimPengingatYangJatuhTempo(): void
    {
        $pengingat = GrupPengingat::ambilYangHarusDikirim();

        foreach ($pengingat as $p) {
            $data = json_decode($p->pesan, true);

            if ($data && isset($data['kegiatan'])) {
                $teks  = "🔔 *PENGINGAT KEGIATAN!*\n";
                $teks .= "━━━━━━━━━━━━━━━━\n\n";
                $teks .= "📋 Kegiatan: *{$data['kegiatan']}*\n";
                $teks .= "👤 Pemilik: {$data['pemilik']}\n";
                $teks .= "📅 Waktu: *" . Carbon::parse($p->waktu_ingatkan)->format('d/m/Y H:i') . "*\n";
                $teks .= "📍 Tempat: {$data['tempat']}\n";
                if (isset($data['link_zoom'])) $teks .= "🔗 Link Zoom: {$data['link_zoom']}\n";
                if (isset($data['id_zoom']))   $teks .= "🆔 ID Zoom: {$data['id_zoom']}\n";
                $teks .= "\n━━━━━━━━━━━━━━━━\n";
                $teks .= "⚠️ Kegiatan akan dimulai dalam 10 menit!";
            } else {
                $teks  = "🔔 *PENGINGAT!*\n";
                $teks .= "━━━━━━━━━━━━━━━━\n\n";
                $teks .= "📝 {$p->pesan}\n\n";
                $teks .= "👤 Dibuat oleh: {$p->dibuat_oleh}";
            }

            $this->wa->kirimPesan($p->grup_id, $teks);
            
            // Hapus pengingat yang sudah lewat/dikirim sesuai permintaan user
            $p->delete();

            Log::info("⏰ Pengingat #{$p->id} terkirim dan dihapus dari grup {$p->grup_id}");
        }
    }

    // =============================================
    // TAMPILKAN SEMUA ADMIN GRUP
    // =============================================
    private function tampilkanAdmin(string $grupId): void
    {
        $admins = GrupAdmin::ambilSemuaAdmin($grupId);

        if ($admins->isEmpty()) {
            $this->wa->kirimPesan($grupId,
                "👤 Belum ada admin di grup ini.\n\nGunakan `!set-admin [nomor]` untuk menambah admin."
            );
            return;
        }

        $teks  = "👨‍💼 *Daftar Admin Grup*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";

        foreach ($admins as $admin) {
            $nama  = $admin->nama_admin ?? 'Tidak dikenal';
            $waktu = Carbon::parse($admin->created_at)->format('d/m/Y H:i');
            $teks .= "📌 *{$admin->nomor_admin}*\n";
            $teks .= "👤 {$nama}\n";
            $teks .= "📅 Sejak: {$waktu}\n\n";
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Total: *{$admins->count()}* admin aktif";

        $this->wa->kirimPesan($grupId, $teks);
    }

    // =============================================
    // TAMBAH ADMIN GRUP
    // =============================================
    private function tambahAdmin(string $grupId, string $pengirim, string $argumen): void
    {
        if (!$this->checkIsAdmin($grupId, $pengirim)) {
            $this->wa->kirimPesan($grupId,
                "❌ *Hanya admin grup yang dapat menambah admin!*"
            );
            return;
        }

        $nomorBaru = trim($argumen);

        if (empty($nomorBaru)) {
            $this->wa->kirimPesan($grupId,
                "❌ Masukkan nomor!\n\nContoh: `!set-admin 6282123456789`"
            );
            return;
        }

        if (GrupAdmin::isAdmin($grupId, $nomorBaru)) {
            $this->wa->kirimPesan($grupId, "ℹ️ Nomor *{$nomorBaru}* sudah menjadi admin.");
            return;
        }

        GrupAdmin::tambahAdmin($grupId, $nomorBaru, null, $pengirim);

        $this->wa->kirimPesan($grupId,
            "✅ *Admin Ditambahkan!*\n\n" .
            "📌 Nomor: *{$nomorBaru}*"
        );

        Log::info("👨‍💼 Admin {$nomorBaru} ditambahkan ke grup {$grupId} oleh {$pengirim}");
    }

    // =============================================
    // HAPUS ADMIN GRUP
    // =============================================
    private function hapusAdmin(string $grupId, string $pengirim, string $argumen): void
    {
        if (!$this->checkIsAdmin($grupId, $pengirim)) {
            $this->wa->kirimPesan($grupId,
                "❌ *Hanya admin grup yang dapat menghapus admin!*"
            );
            return;
        }

        $nomorHapus = trim($argumen);

        if (empty($nomorHapus)) {
            $this->wa->kirimPesan($grupId,
                "❌ Masukkan nomor!\n\nContoh: `!hapus-admin 6282123456789`"
            );
            return;
        }

        if (!GrupAdmin::isAdmin($grupId, $nomorHapus)) {
            $this->wa->kirimPesan($grupId, "❌ Nomor *{$nomorHapus}* bukan admin grup ini.");
            return;
        }

        if ($nomorHapus === $pengirim) {
            $this->wa->kirimPesan($grupId, "⚠️ *Tidak bisa menghapus diri sendiri sebagai admin!*");
            return;
        }

        GrupAdmin::hapusAdmin($grupId, $nomorHapus);

        $this->wa->kirimPesan($grupId,
            "✅ *Admin Dihapus!*\n\n" .
            "📌 Nomor: *{$nomorHapus}*"
        );

        Log::info("👨‍💼 Admin {$nomorHapus} dihapus dari grup {$grupId} oleh {$pengirim}");
    }

    // =============================================
    // MANAJEMEN STOK DI GRUP
    // =============================================
    private function manageStokGrup(string $grupId, string $pengirim, string $aksi, string $argumen): void
    {
        $isAdmin = $this->checkIsAdmin($grupId, $pengirim);

        if ($aksi !== 'list' && !$isAdmin) {
            $this->wa->kirimPesan($grupId, "❌ *Hanya admin grup yang dapat memodifikasi stok/produk!*");
            return;
        }

        switch ($aksi) {
            case 'list':
                $produks = \App\Models\Produk::orderBy('kode')->get();
                if ($produks->isEmpty()) {
                    $this->wa->kirimPesan($grupId, "📦 Belum ada produk terdaftar di database.");
                    return;
                }
                $teks = "📦 *Daftar Stok Produk:* \n";
                $teks .= "━━━━━━━━━━━━━━━━\n\n";
                foreach ($produks as $p) {
                    $harga = number_format($p->harga, 0, ',', '.');
                    $status = $p->aktif ? 'Aktif' : 'Non-aktif';
                    $teks .= "🔹 *[{$p->kode}]* {$p->nama}\n";
                    $teks .= "    ↳ Harga: Rp {$harga}\n";
                    $teks .= "    ↳ Stok: *{$p->stok} pcs*\n";
                    $teks .= "    ↳ Status: {$status}\n\n";
                }
                $teks .= "━━━━━━━━━━━━━━━━\n";
                $teks .= "Gunakan perintah admin berikut:\n";
                $teks .= "- `!stok-tambah [kode] [jumlah]`\n";
                $teks .= "- `!stok-set [kode] [jumlah]`\n";
                $teks .= "- `!tambah-produk [kode] [nama] [harga] [stok]`";
                $this->wa->kirimPesan($grupId, $teks);
                break;

            case 'tambah-produk':
                $parts = explode(' ', trim($argumen));
                if (count($parts) < 4) {
                    $this->wa->kirimPesan($grupId, "❌ Format salah!\n\nGunakan: `!tambah-produk [kode] [nama] [harga] [stok]`\nContoh: `!tambah-produk BRG01 Kaos Polos 50000 20`");
                    return;
                }
                $kode = strtoupper($parts[0]);
                $stok = (int) array_pop($parts);
                $harga = (float) array_pop($parts);
                array_shift($parts); // Hapus kode
                $nama = implode(' ', $parts);

                // Cek duplikasi kode
                if (\App\Models\Produk::where('kode', $kode)->exists()) {
                    $this->wa->kirimPesan($grupId, "❌ Produk dengan kode *{$kode}* sudah ada.");
                    return;
                }

                $produk = \App\Models\Produk::create([
                    'kode'  => $kode,
                    'nama'  => $nama,
                    'harga' => $harga,
                    'stok'  => $stok,
                    'aktif' => true
                ]);

                $this->wa->kirimPesan($grupId, "✅ *Produk berhasil ditambahkan!*\n\nKode: *{$kode}*\nNama: {$nama}\nHarga: Rp " . number_format($harga, 0, ',', '.') . "\nStok: {$stok} pcs");
                break;

            case 'set':
            case 'tambah':
            case 'kurang':
                $parts = explode(' ', trim($argumen));
                if (count($parts) < 2) {
                    $this->wa->kirimPesan($grupId, "❌ Format salah!\n\nGunakan: `!stok-{$aksi} [kode] [jumlah]`\nContoh: `!stok-{$aksi} BRG01 10`");
                    return;
                }
                $kode = strtoupper($parts[0]);
                $jumlah = (int)$parts[1];

                $success = true;
                $pesanError = '';
                $stokBaru = 0;
                $namaProduk = '';
                
                DB::transaction(function () use ($kode, $jumlah, $aksi, &$success, &$pesanError, &$stokBaru, &$namaProduk) {
                    $produk = \App\Models\Produk::where('kode', $kode)->lockForUpdate()->first();
                    if (!$produk) {
                        $success = false;
                        $pesanError = "❌ Produk dengan kode *{$kode}* tidak ditemukan.";
                        return;
                    }
                    $namaProduk = $produk->nama;

                    if ($aksi === 'set') {
                        $produk->update(['stok' => $jumlah]);
                    } elseif ($aksi === 'tambah') {
                        $produk->increment('stok', $jumlah);
                    } elseif ($aksi === 'kurang') {
                        $produk->decrement('stok', $jumlah);
                    }
                    
                    // Reload to get fresh stock
                    $stokBaru = $produk->fresh()->stok;
                });

                if (!$success) {
                    $this->wa->kirimPesan($grupId, $pesanError);
                    return;
                }

                $this->wa->kirimPesan($grupId, "✅ Stok produk *{$namaProduk} ({$kode})* berhasil diubah. Stok saat ini: *{$stokBaru} pcs*.");
                break;

            case 'hapus-produk':
                $kode = strtoupper(trim($argumen));
                $produk = \App\Models\Produk::where('kode', $kode)->first();
                if (!$produk) {
                    $this->wa->kirimPesan($grupId, "❌ Produk dengan kode *{$kode}* tidak ditemukan.");
                    return;
                }
                $nama = $produk->nama;
                $produk->delete();
                $this->wa->kirimPesan($grupId, "✅ Produk *{$nama} ({$kode})* berhasil dihapus dari database.");
                break;
        }
    }

    // =============================================
    // MANAJEMEN ORDER DI GRUP
    // =============================================
    private function manageOrderGrup(string $grupId, string $pengirim, string $aksi, string $argumen): void
    {
        if (!$this->checkIsAdmin($grupId, $pengirim)) {
            $this->wa->kirimPesan($grupId, "❌ *Hanya admin grup yang dapat mengelola orderan!*");
            return;
        }

        // Pembersihan otomatis tanda kurung siku [ ] pada argumen nomor order
        $argumen = trim(str_replace(['[', ']'], '', $argumen));

        switch ($aksi) {
            case 'pending':
                $orders = \App\Models\Pesanan::whereIn('status', ['pending_ongkir', 'pending_payment', 'pending', 'pending_approval'])
                    ->orderBy('created_at', 'desc')->get();
                if ($orders->isEmpty()) {
                    $this->wa->kirimPesan($grupId, "📋 Tidak ada orderan pending saat ini.");
                    return;
                }
                $teks = "📋 *Daftar Orderan Pending:* \n";
                $teks .= "━━━━━━━━━━━━━━━━\n\n";
                foreach ($orders as $o) {
                    $total = number_format($o->total_biaya, 0, ',', '.');
                    $teks .= "🔸 *[{$o->nomor_order}]* - @{$o->nomor_wa}\n";
                    $teks .= "    ↳ Status: *{$o->status}*\n";
                    $teks .= "    ↳ Metode: " . strtoupper($o->metode_pembayaran ?? 'Belum dipilih') . "\n";
                    $teks .= "    ↳ Total: Rp {$total}\n\n";
                }
                $teks .= "━━━━━━━━━━━━━━━━\n";
                $teks .= "Ketik `!orderan-detail [nomor_order]` untuk rincian.";
                $this->wa->kirimPesan($grupId, $teks);
                break;

            case 'proses':
                $orders = \App\Models\Pesanan::whereIn('status', ['paid', 'approved', 'processed'])
                    ->orderBy('created_at', 'desc')->get();
                if ($orders->isEmpty()) {
                    $this->wa->kirimPesan($grupId, "📦 Tidak ada orderan aktif yang sedang diproses.");
                    return;
                }
                $teks = "📦 *Daftar Orderan Sedang Diproses:* \n";
                $teks .= "━━━━━━━━━━━━━━━━\n\n";
                foreach ($orders as $o) {
                    $total = number_format($o->total_biaya, 0, ',', '.');
                    $teks .= "🔹 *[{$o->nomor_order}]* - Penerima: {$o->nama_penerima}\n";
                    $teks .= "    ↳ Status: *{$o->status}*\n";
                    $teks .= "    ↳ Total: Rp {$total}\n\n";
                }
                $this->wa->kirimPesan($grupId, $teks);
                break;

            case 'detail':
                $nomorOrder = strtoupper(trim($argumen));
                $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->first();
                if (!$order) {
                    $this->wa->kirimPesan($grupId, "❌ Order *{$nomorOrder}* tidak ditemukan.");
                    return;
                }
                
                $daftarProduk = "";
                foreach ($order->items as $item) {
                    $namaProduk = $item->produk ? $item->produk->nama : 'Produk Terhapus';
                    $varian = $item->produkVarian ? " ({$item->produkVarian->nama_varian})" : "";
                    $daftarProduk .= "\n  - *{$namaProduk}{$varian}* x{$item->jumlah}";
                }
                
                $teks = "🔍 *Detail Order {$nomorOrder}:*\n";
                $teks .= "━━━━━━━━━━━━━━━━\n";
                $teks .= "👤 Pelanggan: @{$order->nomor_wa}\n";
                $teks .= "👤 Penerima: {$order->nama_penerima}\n";
                $teks .= "📍 Alamat: {$order->alamat_penerima}\n";
                $teks .= "🚚 Pengiriman: " . ($order->tipe_pengiriman === 'kurir_toko' ? 'Kurir Toko' : ($order->tipe_pengiriman === 'kurir_customer' ? 'Kurir Customer (Ojol)' : 'Ambil Sendiri')) . "\n";
                $teks .= "🛍️ Produk: {$daftarProduk}\n";
                $teks .= "💵 Biaya Barang: Rp " . number_format($order->biaya_barang, 0, ',', '.') . "\n";
                $teks .= "🚚 Ongkir: Rp " . number_format($order->biaya_pengantaran, 0, ',', '.') . "\n";
                $teks .= "💰 Total Bayar: Rp " . number_format($order->total_biaya, 0, ',', '.') . "\n";
                $teks .= "💳 Uang Muka (DP): Rp " . number_format($order->uang_muka, 0, ',', '.') . "\n";
                $teks .= "💵 Sisa Bayar: Rp " . number_format($order->sisa_pembayaran, 0, ',', '.') . "\n";
                $teks .= "📅 Jadwal: " . ($order->tanggal_diambil ? date('d/m/Y', strtotime($order->tanggal_diambil)) : '-') . "\n";
                $teks .= "💳 Metode Bayar: " . strtoupper($order->metode_pembayaran ?? '-') . "\n";
                $teks .= "📌 Status: *{$order->status}*\n";
                $teks .= "━━━━━━━━━━━━━━━━\n";
                if ($order->bukti_pembayaran) {
                    $urlBukti = env('NGROK_PUBLIC_URL') 
                        ? rtrim(env('NGROK_PUBLIC_URL'), '/') . $order->bukti_pembayaran
                        : url($order->bukti_pembayaran);
                    $teks .= "📸 Bukti Transfer: {$urlBukti}\n\n";
                }
                $teks .= "Aksi:\n";
                $teks .= "- Konfirmasi QRIS/Transfer: `!konfirmasi-bayar {$nomorOrder}`\n";
                $teks .= "- Setujui COD/Pickup: `!setuju-order {$nomorOrder}`\n";
                $teks .= "- Batalkan Orderan: `!orderan-batal {$nomorOrder} [alasan]`";
                $this->wa->kirimPesan($grupId, $teks);
                break;

            case 'set-ongkir':
                $parts = explode(' ', trim($argumen));
                if (count($parts) < 2) {
                    $this->wa->kirimPesan($grupId, "❌ Format salah! Gunakan: `!set-ongkir [nomor_order] [nominal]`");
                    return;
                }
                $nomorOrder = strtoupper($parts[0]);
                $ongkir = (float)$parts[1];

                $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->first();
                if (!$order) {
                    $this->wa->kirimPesan($grupId, "❌ Order *{$nomorOrder}* tidak ditemukan.");
                    return;
                }

                if ($order->status !== 'pending_ongkir') {
                    $this->wa->kirimPesan($grupId, "❌ Order *{$nomorOrder}* tidak dalam status menunggu ongkir (Status saat ini: {$order->status}).");
                    return;
                }

                $order->update([
                    'biaya_pengantaran' => $ongkir,
                    'total_biaya'       => $order->biaya_barang + $ongkir,
                    'status'            => 'pending_payment' // Berubah status menunggu pembayaran
                ]);

                // Hubungi pelanggan agar melakukan pembayaran
                $item = $order->items()->first();
                $namaBarang = $item && $item->produk ? $item->produk->nama : 'Barang';
                $jumlah = $item ? $item->jumlah : 0;
                $totalFmt = number_format($order->total_biaya, 0, ',', '.');
                $ongkirFmt = number_format($ongkir, 0, ',', '.');
                $subtotalFmt = number_format($order->biaya_barang, 0, ',', '.');

                // Cek status chatbot user
                $chatbotUser = \App\Models\ChatbotUser::where('nomor', $order->nomor_wa)->first();
                $isIdle = !$chatbotUser || in_array($chatbotUser->langkah, ['menu', 'halo', '0', null]) || str_starts_with($chatbotUser->langkah, 'order_menunggu_bukti_') || str_starts_with($chatbotUser->langkah, 'order_bayar_ongkir_');

                if ($isIdle && $chatbotUser) {
                    $chatbotUser->update(['langkah' => 'order_bayar_ongkir_' . $order->id]);
                    $pesanCustomer = "🚚 *Ongkos kirim Anda telah ditentukan!*\n\n" .
                                     "Rincian Pembayaran untuk order *{$nomorOrder}*:\n" .
                                     "- 🛍️ Barang: Rp {$subtotalFmt} ({$namaBarang} - {$jumlah} pcs)\n" .
                                     "- 🚚 Ongkir: Rp {$ongkirFmt}\n" .
                                     "- 💰 Total Tagihan: *Rp {$totalFmt}*\n\n" .
                                     "Silakan pilih metode pembayaran untuk pesanan Anda:\n" .
                                     "[1] QRIS (E-Wallet)\n" .
                                     "[2] Transfer Bank (Manual)\n\n" .
                                     "Balas dengan angka pilihan Anda (1/2):";
                } else {
                    $pesanCustomer = "🚚 *Ongkos kirim Anda telah ditentukan!*\n\n" .
                                     "Rincian Pembayaran untuk order *{$nomorOrder}*:\n" .
                                     "- 🛍️ Barang: Rp {$subtotalFmt} ({$namaBarang} - {$jumlah} pcs)\n" .
                                     "- 🚚 Ongkir: Rp {$ongkirFmt}\n" .
                                     "- 💰 Total Tagihan: *Rp {$totalFmt}*\n\n" .
                                     "Ketik *bayar* untuk memilih metode pembayaran pesanan ini.";
                }

                $this->wa->kirimPesan($order->nomor_wa, $pesanCustomer);

                $this->wa->kirimPesan($grupId, "✅ Ongkir order *{$nomorOrder}* berhasil ditetapkan sebesar *Rp {$ongkirFmt}*. Notifikasi telah dikirim ke pelanggan.");
                break;

            case 'set-dp':
                $parts = explode(' ', trim($argumen));
                if (count($parts) < 2) {
                    $this->wa->kirimPesan($grupId, "❌ Format salah! Gunakan: `!set-dp [nomor_order] [nominal]`");
                    return;
                }
                $nomorOrder = strtoupper($parts[0]);
                $dp = (float)$parts[1];

                $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->first();
                if (!$order) {
                    $this->wa->kirimPesan($grupId, "❌ Order *{$nomorOrder}* tidak ditemukan.");
                    return;
                }

                $order->update([
                    'uang_muka' => $dp,
                    'status'    => 'paid_sebagian'
                ]);

                // Update stock logic sama dengan konfirmasi bayar
                $this->potongStokOtomatis($order);

                // Kirim pesan ke customer
                $dpFmt = number_format($dp, 0, ',', '.');
                $sisaFmt = number_format($order->sisa_pembayaran, 0, ',', '.');
                $pesanCustomer = "✅ *Pembayaran DP Diterima*\n\n" .
                                 "Pesanan Anda (Order: *{$nomorOrder}*) telah dikonfirmasi!\n" .
                                 "Uang Muka: Rp {$dpFmt}\n" .
                                 "Sisa Tagihan: Rp {$sisaFmt}\n\n" .
                                 "Pesanan sedang kami proses. Terima kasih!";
                
                $this->wa->kirimPesan($order->nomor_wa, $pesanCustomer);
                $this->wa->kirimPesan($grupId, "✅ DP order *{$nomorOrder}* berhasil dicatat sebesar *Rp {$dpFmt}*. Sisa tagihan: Rp {$sisaFmt}.");
                break;

            case 'konfirmasi-bayar':
                $nomorOrder = strtoupper(trim($argumen));
                
                $success = true;
                $pesanError = '';
                
                DB::transaction(function () use ($nomorOrder, &$success, &$pesanError, &$order) {
                    $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->lockForUpdate()->first();
                    if (!$order) {
                        $success = false;
                        $pesanError = "❌ Order *{$nomorOrder}* tidak ditemukan.";
                        return;
                    }

                    if ($order->status === 'paid' || $order->status === 'processed') {
                        $success = false;
                        $pesanError = "ℹ️ Order *{$nomorOrder}* sudah dikonfirmasi berbayar sebelumnya.";
                        return;
                    }

                    // Implementasi Smart Deduction (Hibrida)
                    foreach ($order->items as $item) {
                        $isMadeToOrder = false;
                        if ($item->produk) {
                            $isMadeToOrder = $item->produk->is_made_to_order;
                        }

                        if ($isMadeToOrder) {
                            // JIKA MADE-TO-ORDER: HANYA potong bahan baku (stok produk jadi diabaikan/tak terbatas)
                            if ($item->produk_varian_id) {
                                $resep = \App\Models\ResepVarian::where('produk_varian_id', $item->produk_varian_id)->get();
                                foreach ($resep as $r) {
                                    $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                    $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                    if ($lockedBahan) {
                                        $lockedBahan->decrement('stok', $qtyDibutuhkan);
                                        \App\Models\StokBahanHistory::create([
                                            'bahan_baku_id' => $lockedBahan->id,
                                            'user_id' => null,
                                            'tipe' => 'produksi',
                                            'qty' => $qtyDibutuhkan,
                                            'keterangan' => 'Terjual via Chatbot (Made-to-Order) Struk #' . $order->nomor_order
                                        ]);
                                    }
                                }
                            }
                        } else {
                            // JIKA MADE-TO-STOCK: HANYA potong stok produk jadi (bahan baku sudah dipotong saat manufaktur dapur)
                            if ($item->produkVarian) {
                                $lockedVarian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                                if ($lockedVarian) $lockedVarian->decrement('stok', $item->jumlah);
                            } elseif ($item->produk) {
                                $lockedProduk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                                if ($lockedProduk) $lockedProduk->decrement('stok', $item->jumlah);
                            }
                        }

                        // Potong Stok Add-ons
                        if (!empty($item->addon_details)) {
                            $addonsList = is_string($item->addon_details) ? json_decode($item->addon_details, true) : $item->addon_details;
                            if (is_array($addonsList)) {
                                foreach ($addonsList as $addonInfo) {
                                    $addonId = $addonInfo['id'] ?? null;
                                    if ($addonId) {
                                        $addon = \App\Models\ProdukAddon::find($addonId);
                                        if ($addon && $addon->reseps) {
                                            foreach ($addon->reseps as $r) {
                                                $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                                $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                                if ($lockedBahan) {
                                                    $lockedBahan->decrement('stok', $qtyDibutuhkan);
                                                    \App\Models\StokBahanHistory::create([
                                                        'bahan_baku_id' => $lockedBahan->id,
                                                        'user_id' => null,
                                                        'tipe' => 'produksi',
                                                        'qty' => $qtyDibutuhkan,
                                                        'keterangan' => 'Terjual via Chatbot (Add-on) Struk #' . $order->nomor_order
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $order->update(['status' => 'paid']);
                });

                if (!$success) {
                    $this->wa->kirimPesan($grupId, $pesanError);
                    return;
                }

                // Notifikasi ke customer
                $pesanCustomer = "✅ *Pembayaran Terkonfirmasi!*\n\n" .
                                 "Pembayaran Anda untuk order *{$nomorOrder}* telah berhasil diverifikasi oleh admin.\n" .
                                 "Pesanan Anda sedang kami persiapkan untuk proses pengiriman. Terima kasih!";
                $this->wa->kirimPesan($order->nomor_wa, $pesanCustomer);

                // Kirim notifikasi ke kurir setelah pembayaran dikonfirmasi
                if ($order->tipe_pengiriman === 'kurir_toko' && $order->kurir) {
                    $orderService = app(\App\Services\OrderService::class);
                    $orderService->kirimNotifikasiKurir($order);
                }

                $this->wa->kirimPesan($grupId, "✅ Pembayaran order *{$nomorOrder}* berhasil dikonfirmasi. Stok barang telah dikurangi dan pembeli telah dinotifikasi.");
                break;

            case 'setuju-order':
                $nomorOrder = strtoupper(trim($argumen));
                
                $success = true;
                $pesanError = '';
                
                DB::transaction(function () use ($nomorOrder, &$success, &$pesanError, &$order) {
                    $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->lockForUpdate()->first();
                    if (!$order) {
                        $success = false;
                        $pesanError = "❌ Order *{$nomorOrder}* tidak ditemukan.";
                        return;
                    }

                    if ($order->status === 'approved' || $order->status === 'processed') {
                        $success = false;
                        $pesanError = "ℹ️ Order *{$nomorOrder}* sudah disetujui sebelumnya.";
                        return;
                    }

                    // Implementasi Smart Deduction (Hibrida)
                    foreach ($order->items as $item) {
                        $isMadeToOrder = false;
                        if ($item->produk) {
                            $isMadeToOrder = $item->produk->is_made_to_order;
                        }

                        if ($isMadeToOrder) {
                            // JIKA MADE-TO-ORDER: HANYA potong bahan baku (stok produk jadi diabaikan/tak terbatas)
                            if ($item->produk_varian_id) {
                                $resep = \App\Models\ResepVarian::where('produk_varian_id', $item->produk_varian_id)->get();
                                foreach ($resep as $r) {
                                    $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                    $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                    if ($lockedBahan) {
                                        $lockedBahan->decrement('stok', $qtyDibutuhkan);
                                        \App\Models\StokBahanHistory::create([
                                            'bahan_baku_id' => $lockedBahan->id,
                                            'user_id' => null,
                                            'tipe' => 'produksi',
                                            'qty' => $qtyDibutuhkan,
                                            'keterangan' => 'Terjual via Chatbot (Made-to-Order) Struk #' . $order->nomor_order
                                        ]);
                                    }
                                }
                            }
                        } else {
                            // JIKA MADE-TO-STOCK: HANYA potong stok produk jadi (bahan baku sudah dipotong saat manufaktur dapur)
                            if ($item->produkVarian) {
                                $lockedVarian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                                if ($lockedVarian) $lockedVarian->decrement('stok', $item->jumlah);
                            } elseif ($item->produk) {
                                $lockedProduk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                                if ($lockedProduk) $lockedProduk->decrement('stok', $item->jumlah);
                            }
                        }

                        // Potong Stok Add-ons
                        if (!empty($item->addon_details)) {
                            $addonsList = is_string($item->addon_details) ? json_decode($item->addon_details, true) : $item->addon_details;
                            if (is_array($addonsList)) {
                                foreach ($addonsList as $addonInfo) {
                                    $addonId = $addonInfo['id'] ?? null;
                                    if ($addonId) {
                                        $addon = \App\Models\ProdukAddon::find($addonId);
                                        if ($addon && $addon->reseps) {
                                            foreach ($addon->reseps as $r) {
                                                $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                                $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                                if ($lockedBahan) {
                                                    $lockedBahan->decrement('stok', $qtyDibutuhkan);
                                                    \App\Models\StokBahanHistory::create([
                                                        'bahan_baku_id' => $lockedBahan->id,
                                                        'user_id' => null,
                                                        'tipe' => 'produksi',
                                                        'qty' => $qtyDibutuhkan,
                                                        'keterangan' => 'Terjual via Chatbot (Add-on) Struk #' . $order->nomor_order
                                                    ]);
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $order->update(['status' => 'approved']);
                });

                if (!$success) {
                    $this->wa->kirimPesan($grupId, $pesanError);
                    return;
                }

                // Notifikasi ke customer
                $pesanCustomer = "✅ *Pesanan Disetujui!*\n\n" .
                                 "Pesanan Anda *{$nomorOrder}* (Ambil di tempat / COD) telah disetujui oleh admin.\n" .
                                 "Silakan datang ke toko untuk mengambil pesanan Anda. Terima kasih!";
                $this->wa->kirimPesan($order->nomor_wa, $pesanCustomer);

                // Kirim notifikasi ke kurir setelah order disetujui (jika ditugaskan ke kurir toko)
                if ($order->tipe_pengiriman === 'kurir_toko' && $order->kurir) {
                    $orderService = app(\App\Services\OrderService::class);
                    $orderService->kirimNotifikasiKurir($order);
                }

                $this->wa->kirimPesan($grupId, "✅ Pesanan COD *{$nomorOrder}* berhasil disetujui. Stok barang telah dikurangi.");
                break;

            case 'batal':
                $parts = explode(' ', trim($argumen), 2);
                $nomorOrder = strtoupper($parts[0]);
                $alasan = $parts[1] ?? 'Dibatalkan oleh admin';

                $success = true;
                $pesanError = '';
                
                DB::transaction(function () use ($nomorOrder, &$success, &$pesanError, &$order) {
                    $order = \App\Models\Pesanan::where('nomor_order', $nomorOrder)->lockForUpdate()->first();
                    if (!$order) {
                        $success = false;
                        $pesanError = "❌ Order *{$nomorOrder}* tidak ditemukan.";
                        return;
                    }

                    // Kembalikan Stok jika statusnya sebelumnya sudah memotong stok
                    if (in_array($order->status, ['paid', 'approved', 'processed', 'completed'])) {
                        foreach ($order->items as $item) {
                            $isMadeToOrder = $item->produk ? $item->produk->is_made_to_order : false;

                            if ($isMadeToOrder) {
                                // JIKA MADE-TO-ORDER: Kembalikan bahan baku
                                if ($item->produk_varian_id) {
                                    $resep = \App\Models\ResepVarian::where('produk_varian_id', $item->produk_varian_id)->get();
                                    foreach ($resep as $r) {
                                        $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                        $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                        if ($lockedBahan) {
                                            $lockedBahan->increment('stok', $qtyDibutuhkan);
                                            \App\Models\StokBahanHistory::create([
                                                'bahan_baku_id' => $lockedBahan->id,
                                                'user_id' => null,
                                                'tipe' => 'koreksi',
                                                'qty' => $qtyDibutuhkan,
                                                'keterangan' => 'Batal Otomatis via AI Chatbot (Made-to-Order) Struk #' . $order->nomor_order
                                            ]);
                                        }
                                    }
                                }
                            } else {
                                if ($item->produkVarian) {
                                    $lockedVarian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                                    if ($lockedVarian) $lockedVarian->increment('stok', $item->jumlah);
                                } elseif ($item->produk) {
                                    $lockedProduk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                                    if ($lockedProduk) $lockedProduk->increment('stok', $item->jumlah);
                                }
                            }

                            // Kembalikan Stok Add-ons
                            if (!empty($item->addon_details)) {
                                $addonsList = is_string($item->addon_details) ? json_decode($item->addon_details, true) : $item->addon_details;
                                if (is_array($addonsList)) {
                                    foreach ($addonsList as $addonInfo) {
                                        $addonId = $addonInfo['id'] ?? null;
                                        if ($addonId) {
                                            $addon = \App\Models\ProdukAddon::find($addonId);
                                            if ($addon && $addon->reseps) {
                                                foreach ($addon->reseps as $r) {
                                                    $qtyDibutuhkan = $r->qty_dipakai * $item->jumlah;
                                                    $lockedBahan = \App\Models\BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                                    if ($lockedBahan) {
                                                        $lockedBahan->increment('stok', $qtyDibutuhkan);
                                                        \App\Models\StokBahanHistory::create([
                                                            'bahan_baku_id' => $lockedBahan->id,
                                                            'user_id' => null,
                                                            'tipe' => 'koreksi',
                                                            'qty' => $qtyDibutuhkan,
                                                            'keterangan' => 'Batal Otomatis via AI Chatbot (Add-on) Struk #' . $order->nomor_order
                                                        ]);
                                                    }
                                                }
                                            }
                                        }
                                    }
                                }
                            }
                        }
                    }

                    $order->update(['status' => 'cancelled']);
                });

                if (!$success) {
                    $this->wa->kirimPesan($grupId, $pesanError);
                    return;
                }

                // Notifikasi ke customer
                $pesanCustomer = "❌ *Pesanan Dibatalkan*\n\n" .
                                 "Pesanan Anda *{$nomorOrder}* telah dibatalkan oleh admin.\n" .
                                 "Alasan: *{$alasan}*\n\n" .
                                 "Silakan hubungi admin jika ada pertanyaan.";
                $this->wa->kirimPesan($order->nomor_wa, $pesanCustomer);

                $this->wa->kirimPesan($grupId, "❌ Pesanan *{$nomorOrder}* berhasil dibatalkan. Stok dikembalikan (jika sebelumnya sudah dikurangi) dan pembeli telah dinotifikasi.");
                break;
        }
    }
}