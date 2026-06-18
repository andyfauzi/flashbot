<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\GrupAdmin;
use App\Models\GrupCatatan;
use App\Models\GrupPengingat;
use App\Models\GrupPesan;
use Illuminate\Http\Request;

class GrupController extends Controller
{
    // =============================================
    // DASHBOARD GRUP
    // =============================================
    public function index()
    {
        // Statistik
        $totalGrup     = GrupPesan::distinct('grup_id')->count('grup_id');
        $totalPesan    = GrupPesan::count();
        $totalCatatan  = GrupCatatan::count();
        $totalPengingat= GrupPengingat::where('sudah_dikirim', false)->count();

        // Ambil grup yang di-hide
        $hiddenGroups = \App\Models\GrupSetting::where('kunci', 'is_hidden')->where('nilai', '1')->pluck('grup_id')->toArray();

        // Daftar grup dengan jumlah pesan dan catatan
        $daftarGrup = GrupPesan::selectRaw('grup_id, grup_nama, COUNT(*) as total_pesan')
            ->whereNotIn('grup_id', $hiddenGroups)
            ->groupBy('grup_id', 'grup_nama')
            ->orderBy('total_pesan', 'desc')
            ->get()
            ->map(function ($grup) {
                $grup->total_catatan = GrupCatatan::where('grup_id', $grup->grup_id)->count();
                return $grup;
            });

        // Pengingat aktif
        $pengingat = GrupPengingat::where('sudah_dikirim', false)
            ->orderBy('waktu_ingatkan')
            ->limit(10)
            ->get();

        return view('chatbot.grup', compact(
            'totalGrup', 'totalPesan', 'totalCatatan',
            'totalPengingat', 'daftarGrup', 'pengingat'
        ));
    }

    public function abaikan(string $grupId)
    {
        $grupId = urldecode($grupId);
        \App\Models\GrupSetting::simpan($grupId, 'is_hidden', '1');
        return back()->with('sukses', 'Grup berhasil disembunyikan dari daftar!');
    }

    // =============================================
    // DETAIL GRUP
    // =============================================
    public function detail(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);

        $grupInfo = GrupPesan::where('grup_id', $grupId)->first();
        if (!$grupInfo) {
            return redirect()->route('chatbot.grup')->with('error', 'Grup tidak ditemukan atau belum ada interaksi');
        }
        $grupNama = $grupInfo->grup_nama ?? 'Tanpa Nama';

        $catatan  = GrupCatatan::where('grup_id', $grupId)
            ->orderBy('waktu', 'desc')
            ->get();

        $pengingat = GrupPengingat::where('grup_id', $grupId)
            ->orderBy('waktu_ingatkan', 'desc')
            ->get();

        $pesan = GrupPesan::where('grup_id', $grupId)
            ->orderBy('waktu', 'desc')
            ->paginate(20);

        // Fetch auto replies and settings for this group
        $autoReplies = \App\Models\GrupAutoReply::where('grup_id', $grupId)->orderBy('created_at', 'desc')->get();
        $settings = \App\Models\GrupSetting::where('grup_id', $grupId)->pluck('nilai', 'kunci')->toArray();

        return view('chatbot.grup_detail', compact(
            'grupId', 'grupNama', 'catatan', 'pengingat', 'pesan', 'autoReplies', 'settings'
        ));
    }

    // =============================================
    // SET GRUP SEBAGAI ADMIN UTAMA
    // =============================================
    public function setAdmin(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);
        
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        // Update .env (membutuhkan restart untuk terbaca oleh env() di request selanjutnya)
        if (strpos($envContent, 'WHATSAPP_GROUP_ID_SELLER=') !== false) {
            $envContent = preg_replace('/^WHATSAPP_GROUP_ID_SELLER=.*$/m', 'WHATSAPP_GROUP_ID_SELLER="' . $grupId . '"', $envContent);
        } else {
            $envContent .= "\nWHATSAPP_GROUP_ID_SELLER=\"" . $grupId . "\"\n";
        }
        
        file_put_contents($envFile, $envContent);

        // Memaksa whitelisting via database agar langsung terbaca API tanpa harus restart server
        \App\Models\GrupSetting::simpan($grupId, 'is_whitelisted', '1');

        // Beri tahu Node.js untuk refresh
        try {
            $apiUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            \Illuminate\Support\Facades\Http::timeout(3)->post("{$apiUrl}/whitelist/refresh", [
                'api_key' => config('chatbot.webhook_secret')
            ]);
        } catch (\Exception $e) {}

        return back()->with('sukses', 'Grup ini berhasil ditetapkan sebagai Penerima Notifikasi. Harap restart server Queue (php artisan queue:work) agar efek berlaku sepenuhnya!');
    }

    // =============================================
    // BATALKAN GRUP SEBAGAI ADMIN UTAMA
    // =============================================
    public function unsetAdmin(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);
        
        $envFile = base_path('.env');
        $envContent = file_get_contents($envFile);
        
        if (strpos($envContent, 'WHATSAPP_GROUP_ID_SELLER=') !== false) {
            $envContent = preg_replace('/^WHATSAPP_GROUP_ID_SELLER=.*$/m', 'WHATSAPP_GROUP_ID_SELLER=""', $envContent);
        }
        
        file_put_contents($envFile, $envContent);

        // Beri tahu Node.js untuk refresh
        try {
            $apiUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            \Illuminate\Support\Facades\Http::timeout(3)->post("{$apiUrl}/whitelist/refresh", [
                'api_key' => config('chatbot.webhook_secret')
            ]);
        } catch (\Exception $e) {}

        return back()->with('sukses', 'Grup ini tidak lagi menjadi Penerima Notifikasi Pesanan Baru. Harap me-restart server Queue (php artisan queue:work) agar perubahan berlaku!');
    }

    // =============================================
    // KIRIM PESAN KE GRUP (DARI DASHBOARD)
    // =============================================
    public function kirim(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);

        $request->validate([
            'pesan' => 'required|string',
        ], [
            'pesan.required' => 'Pesan tidak boleh kosong',
        ]);

        $waService = app(\App\Services\WhatsAppService::class);
        $waService->kirimPesan($grupId, $request->pesan);

        // Simpan ke riwayat pesan sebagai pesan dari Admin/Sistem
        GrupPesan::simpan($grupId, 'Sistem/Admin', $request->pesan, 'Pesan Keluar');

        return back()->with('sukses', 'Pesan berhasil dikirim ke grup!');
    }

    // =============================================
    // PENGATURAN KATA KUNCI & AUTO REPLY GRUP
    // =============================================
    public function simpanPengaturan(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);
        $settings = $request->except(['_token']);

        foreach ($settings as $kunci => $nilai) {
            if (!empty($nilai)) {
                \App\Models\GrupSetting::simpan($grupId, $kunci, $nilai);
            }
        }

        return back()->with('sukses', 'Pengaturan kata kunci sistem berhasil disimpan!');
    }

    public function simpanAutoReply(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);
        $request->validate([
            'keyword' => 'required|string|max:255',
            'balasan' => 'required|string'
        ]);

        \App\Models\GrupAutoReply::create([
            'grup_id' => $grupId,
            'keyword' => strtolower(trim($request->keyword)),
            'balasan' => trim($request->balasan),
            'is_exact_match' => $request->has('is_exact_match') ? true : false,
            'aktif' => true,
        ]);

        return back()->with('sukses', 'Kata kunci auto-reply berhasil ditambahkan!');
    }

    public function updateAutoReply(Request $request, string $grupId, $id)
    {
        $grupId = urldecode($grupId);
        $request->validate([
            'keyword' => 'required|string|max:255',
            'balasan' => 'required|string'
        ]);

        $autoReply = \App\Models\GrupAutoReply::where('grup_id', $grupId)->findOrFail($id);
        $autoReply->update([
            'keyword' => strtolower(trim($request->keyword)),
            'balasan' => trim($request->balasan),
            'is_exact_match' => $request->has('is_exact_match') ? true : false,
        ]);

        return back()->with('sukses', 'Kata kunci auto-reply berhasil diperbarui!');
    }

    public function hapusAutoReply(string $grupId, $id)
    {
        $grupId = urldecode($grupId);
        $autoReply = \App\Models\GrupAutoReply::where('grup_id', $grupId)->findOrFail($id);
        $autoReply->delete();

        return back()->with('sukses', 'Kata kunci auto-reply berhasil dihapus!');
    }

    // =============================================
    // BROADCAST KE BANYAK GRUP
    // =============================================
    public function broadcast(Request $request)
    {
        $request->validate([
            'grup_ids' => 'required|array|min:1',
            'pesan'    => 'required|string',
        ], [
            'grup_ids.required' => 'Pilih minimal satu grup',
            'pesan.required'    => 'Pesan broadcast tidak boleh kosong',
        ]);

        $waService = app(\App\Services\WhatsAppService::class);
        $berhasil = 0;

        foreach ($request->grup_ids as $grupId) {
            try {
                $waService->kirimPesan($grupId, $request->pesan);
                GrupPesan::simpan($grupId, 'Broadcast Admin', $request->pesan, 'Pesan Keluar');
                $berhasil++;
            } catch (\Exception $e) {
                // Lanjut ke grup berikutnya
            }
        }

        return back()->with('sukses', "Broadcast berhasil dikirim ke {$berhasil} grup!");
    }

    // =============================================
    // MANAJEMEN ADMIN GRUP
    // =============================================
    public function admin(string $grupId)
    {
        $grupId   = urldecode($grupId);
        $grupInfo = GrupPesan::where('grup_id', $grupId)->first();
        $grupNama = $grupInfo->grup_nama ?? 'Tanpa Nama';
        
        $admins = GrupAdmin::ambilSemuaAdmin($grupId);

        return view('chatbot.grup_admin', compact(
            'grupId', 'grupNama', 'admins'
        ));
    }

    // =============================================
    // TAMBAH ADMIN
    // =============================================
    public function adminTambah(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);

        $request->validate([
            'nomor_admin' => 'required|regex:/^62[0-9]{9,}$/',
            'nama_admin'  => 'nullable|string|max:100',
        ], [
            'nomor_admin.required' => 'Nomor admin harus diisi',
            'nomor_admin.regex'    => 'Format nomor tidak valid (gunakan format 62XXXXXXXXX)',
        ]);

        // Cek apakah sudah admin
        if (GrupAdmin::isAdmin($grupId, $request->nomor_admin)) {
            return back()->with('error', 'Nomor ini sudah menjadi admin.');
        }

        GrupAdmin::tambahAdmin(
            $grupId,
            $request->nomor_admin,
            $request->nama_admin
        );

        return back()->with('sukses', 'Admin berhasil ditambahkan!');
    }

    // =============================================
    // HAPUS ADMIN
    // =============================================
    public function adminHapus(string $grupId, string $nomorAdmin)
    {
        $grupId     = urldecode($grupId);
        $nomorAdmin = urldecode($nomorAdmin);

        $berhasil = GrupAdmin::hapusAdmin($grupId, $nomorAdmin);

        if ($berhasil) {
            return back()->with('sukses', 'Admin berhasil dihapus!');
        } else {
            return back()->with('error', 'Admin tidak ditemukan.');
        }
    }

    // =============================================
    // TOGGLE WHITELIST GRUP
    // =============================================
    public function toggleWhitelist(Request $request, string $grupId)
    {
        $grupId = urldecode($grupId);
        $statusSekarang = \App\Models\GrupSetting::ambil($grupId, 'is_whitelisted', '0');
        $statusBaru = $statusSekarang === '1' ? '0' : '1';

        \App\Models\GrupSetting::simpan($grupId, 'is_whitelisted', $statusBaru);

        // Sync with Node.js Baileys via API
        try {
            $apiUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            \Illuminate\Support\Facades\Http::timeout(3)->post("{$apiUrl}/whitelist/refresh", [
                'api_key' => config('chatbot.webhook_secret')
            ]);
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal refresh whitelist di Node.js: " . $e->getMessage());
            // It's ok, Node.js might be down or not running
        }

        $pesan = $statusBaru === '1' ? 'Bot diizinkan beroperasi di grup ini!' : 'Izin bot dicabut dari grup ini!';
        return back()->with('sukses', $pesan);
    }
}
