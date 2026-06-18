<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\ChatbotMenu;
use App\Models\ChatbotPesan;
use App\Models\ChatbotUser;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    protected WhatsAppService $wa;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    // =============================================
    // DASHBOARD UTAMA
    // =============================================
    public function index()
    {
        $statistik = [
            'total_user'       => ChatbotUser::count(),
            'user_hari_ini'    => ChatbotUser::whereDate('terakhir_chat', today())->count(),
            'total_pesan'      => ChatbotPesan::count(),
            'pesan_hari_ini'   => ChatbotPesan::whereDate('waktu', today())->count(),
            'pesan_masuk'      => ChatbotPesan::where('arah', 'masuk')->count(),
            'pesan_keluar'     => ChatbotPesan::where('arah', 'keluar')->count(),
        ];

        // Pesan terbaru
        $pesanTerbaru = ChatbotPesan::orderBy('waktu', 'desc')->limit(10)->get();

        // Grafik 7 hari terakhir (Pesan)
        $grafik = ChatbotPesan::selectRaw('DATE(waktu) as tanggal, COUNT(*) as total')
            ->where('waktu', '>=', now()->subDays(7))
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        // Peringatan Stok Menipis (<= 5)
        $stokMenipisProduk = \App\Models\Produk::where('stok', '<=', 5)->get();
        $stokMenipisVarian = \App\Models\ProdukVarian::with('produk')->where('stok', '<=', 5)->get();

        $gatewayStatus = $this->wa->statusGateway();

        return view('chatbot.dashboard', compact('statistik', 'pesanTerbaru', 'grafik', 'stokMenipisProduk', 'stokMenipisVarian', 'gatewayStatus'));
    }

    // =============================================
    // DAFTAR USER
    // =============================================
    public function users(Request $request)
    {
        $users = ChatbotUser::when($request->cari, function ($q) use ($request) {
                $q->where('nomor', 'like', "%{$request->cari}%")
                  ->orWhere('nama', 'like', "%{$request->cari}%");
            })
            ->orderBy('terakhir_chat', 'desc')
            ->paginate(20);

        return view('chatbot.users', compact('users'));
    }

    // =============================================
    // RIWAYAT PESAN
    // =============================================
    public function pesan(Request $request)
    {
        $pesan = ChatbotPesan::when($request->nomor, fn($q) => $q->where('nomor', $request->nomor))
            ->when($request->arah,  fn($q) => $q->where('arah', $request->arah))
            ->orderBy('waktu', 'desc')
            ->paginate(30);

        return view('chatbot.pesan', compact('pesan'));
    }

    // =============================================
    // MANAJEMEN MENU
    // =============================================
    public function menu(Request $request)
    {
        $devices = \App\Models\ChatbotDevice::orderBy('nama_device')->get();
        $selectedDeviceId = $request->query('device_id');
        
        // Default ke device pertama jika tidak ada yang dipilih
        if (!$selectedDeviceId && $devices->count() > 0) {
            $selectedDeviceId = $devices->first()->id;
        }
        
        $baseQuery = ChatbotMenu::query();
        if ($selectedDeviceId) {
            $baseQuery->where('device_id', $selectedDeviceId);
        } else {
            $baseQuery->where('id', -1);
        }

        // Menu Utama: tidak punya parent_kode
        $menusUtama = (clone $baseQuery)
            ->whereNull('parent_kode')
            ->orderBy('urutan')
            ->get();

        // Sub Menu: punya parent_kode, dikelompokkan per parent
        $subMenusRaw = (clone $baseQuery)
            ->whereNotNull('parent_kode')
            ->orderBy('parent_kode')
            ->orderBy('urutan')
            ->get();

        // Kelompokkan sub-menu berdasarkan parent_kode
        $subMenus = $subMenusRaw->groupBy('parent_kode');

        // Untuk backward compat di form
        $menus = (clone $baseQuery)->orderBy('urutan')->get();

        $selectedDevice = $selectedDeviceId ? $devices->find($selectedDeviceId) : null;

        return view('chatbot.menu', compact('menus', 'menusUtama', 'subMenus', 'devices', 'selectedDeviceId', 'selectedDevice'));
    }

    public function menuSimpan(Request $request)
    {
        $request->validate([
            'kode'           => 'required|max:255',
            'parent_kode'    => 'nullable|string|max:100',
            'judul'          => 'required|max:100',
            'isi'            => 'required',
            'urutan'         => 'required|integer',
            'media_url'      => 'nullable|url',
            'tipe_pesan'     => 'required|in:text,button,list',
            'pilihan_id.*'   => 'nullable|string',
            'pilihan_text.*' => 'nullable|string',
            'pilihan_desc.*' => 'nullable|string',
            'device_id'      => 'nullable|exists:chatbot_devices,id'
        ]);

        $pilihanInteraktif = null;
        if (in_array($request->tipe_pesan, ['button', 'list']) && $request->pilihan_text) {
            $pilihanInteraktif = [];
            foreach ($request->pilihan_text as $idx => $text) {
                if (!empty($text)) {
                    $pilihanInteraktif[] = [
                        'id' => $request->pilihan_id[$idx] ?? strval($idx + 1),
                        'text' => $text,
                        'desc' => $request->pilihan_desc[$idx] ?? ''
                    ];
                }
            }
        }

        $warning = null;
        if ($request->parent_kode) {
            $parentExists = ChatbotMenu::whereRaw("FIND_IN_SET(?, REPLACE(kode, ' ', ''))", [str_replace(' ', '', $request->parent_kode)])->exists();
            if (!$parentExists) {
                // Alternatif pencarian exact jika find_in_set gagal (karena kode pakai koma)
                // Kita sederhanakan dengan like saja
                $parentExists = ChatbotMenu::where('kode', 'like', "%{$request->parent_kode}%")->exists();
            }
            if (!$parentExists) {
                $warning = "⚠️ Peringatan: Menu induk (Parent Kode: {$request->parent_kode}) tidak ditemukan di database. Menu ini mungkin tidak bisa diakses pelanggan.";
            }
        }

        ChatbotMenu::updateOrCreate(
            ['kode' => $request->kode, 'device_id' => $request->device_id, 'parent_kode' => $request->parent_kode ?: null],
            [
                'parent_kode'        => $request->parent_kode ?: null,
                'judul'              => $request->judul,
                'isi'                => $request->isi,
                'aktif'              => $request->has('aktif'),
                'urutan'             => $request->urutan,
                'media_url'          => $request->media_url,
                'media_type'         => $request->media_url ? 'image' : null,
                'tipe_pesan'         => $request->tipe_pesan,
                'pilihan_interaktif' => $pilihanInteraktif
            ]
        );

        $redirect = redirect()->route('chatbot.menu', ['device_id' => $request->device_id]);
        if ($warning) {
            return $redirect->with('sukses', 'Menu berhasil disimpan!')->with('warning', $warning);
        }
        return $redirect->with('sukses', 'Menu berhasil disimpan!');
    }

    public function menuUpdate(Request $request, ChatbotMenu $menu)
    {
        $request->validate([
            'kode'           => 'required|max:255',
            'parent_kode'    => 'nullable|string|max:100',
            'judul'          => 'required|max:100',
            'isi'            => 'required',
            'urutan'         => 'required|integer',
            'media_url'      => 'nullable|url',
            'tipe_pesan'     => 'required|in:text,button,list',
            'pilihan_id.*'   => 'nullable|string',
            'pilihan_text.*' => 'nullable|string',
            'pilihan_desc.*' => 'nullable|string',
        ]);

        $pilihanInteraktif = null;
        if (in_array($request->tipe_pesan, ['button', 'list']) && $request->pilihan_text) {
            $pilihanInteraktif = [];
            foreach ($request->pilihan_text as $idx => $text) {
                if (!empty($text)) {
                    $pilihanInteraktif[] = [
                        'id'   => $request->pilihan_id[$idx] ?? strval($idx + 1),
                        'text' => $text,
                        'desc' => $request->pilihan_desc[$idx] ?? ''
                    ];
                }
            }
        }

        $warning = null;
        if ($request->parent_kode) {
            $parentExists = ChatbotMenu::where('kode', 'like', "%{$request->parent_kode}%")->exists();
            if (!$parentExists) {
                $warning = "⚠️ Peringatan: Menu induk (Parent Kode: {$request->parent_kode}) tidak ditemukan di database. Menu ini mungkin tidak bisa diakses pelanggan.";
            }
        }

        $menu->update([
            'kode'               => $request->kode,
            'parent_kode'        => $request->parent_kode ?: null,
            'judul'              => $request->judul,
            'isi'                => $request->isi,
            'aktif'              => $request->has('aktif'),
            'urutan'             => $request->urutan,
            'media_url'          => $request->media_url,
            'media_type'         => $request->media_url ? 'image' : null,
            'tipe_pesan'         => $request->tipe_pesan,
            'pilihan_interaktif' => $pilihanInteraktif,
        ]);

        $redirect = redirect()->route('chatbot.menu', ['device_id' => $menu->device_id]);
        if ($warning) {
            return $redirect->with('sukses', '✏️ Menu berhasil diperbarui!')->with('warning', $warning);
        }
        return $redirect->with('sukses', '✏️ Menu berhasil diperbarui!');
    }

    public function menuHapus(ChatbotMenu $menu)
    {
        $menu->delete();
        return redirect()->route('chatbot.menu')->with('sukses', 'Menu berhasil dihapus!');
    }

    // =============================================
    // KIRIM PESAN MANUAL
    // =============================================
    public function kirim(Request $request)
    {
        $request->validate([
            'nomor'     => 'required',
            'pesan'     => 'required',
            'media_url' => 'nullable|url',
        ]);

        $sukses = $this->wa->kirimPesan(
            $request->nomor, 
            $request->pesan, 
            $request->media_url, 
            $request->media_url ? 'image' : null
        );

        return back()->with(
            $sukses ? 'sukses' : 'error',
            $sukses ? 'Pesan berhasil dikirim!' : 'Gagal mengirim pesan.'
        );
    }

    // =============================================
    // LOGOUT WHATSAPP GATEWAY
    // =============================================
    public function gatewayLogout()
    {
        try {
            $baileysUrl = config('chatbot.baileys_api_url', 'http://127.0.0.1:3000');
            $response = \Illuminate\Support\Facades\Http::post("{$baileysUrl}/logout");
            if ($response->successful()) {
                return back()->with('sukses', 'Perangkat WhatsApp berhasil diputuskan!');
            }
        } catch (\Exception $e) {
            // Abaikan error
        }
        return back()->with('error', 'Gagal memutuskan perangkat WhatsApp.');
    }
}
