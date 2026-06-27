<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\Kategori;
use App\Models\IdentitasToko;
use App\Services\WhatsAppService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

class PortalController extends Controller
{
    public function index()
    {
        $tenant = $this->requireTenant();

        $kategoris = Kategori::with(['produks' => function ($q) {
            $q->where('aktif', true)->with(['varians', 'addons']);
        }])->orderBy('nama')->get();

        $produkTanpaKategori = Produk::where('aktif', true)
            ->whereNull('kategori_id')
            ->with(['varians', 'addons'])
            ->get();

        $identitas = IdentitasToko::first();

        return view('chatbot.portal', compact('kategoris', 'produkTanpaKategori', 'identitas'));
    }

    public function dineIn(\App\Models\Meja $meja)
    {
        $tenant = $this->requireTenant();

        $kategoris = Kategori::with(['produks' => function ($q) {
            $q->where('aktif', true)->with(['varians', 'addons']);
        }])->orderBy('nama')->get();

        $produkTanpaKategori = Produk::where('aktif', true)
            ->whereNull('kategori_id')
            ->with(['varians', 'addons'])
            ->get();

        $identitas = IdentitasToko::first();

        return view('chatbot.portal', compact('kategoris', 'produkTanpaKategori', 'identitas', 'meja'));
    }

    public function store(Request $request)
    {
        $tenant = $this->requireTenant();

        $validated = $request->validate([
            'nama_penerima'     => 'required|string|max:100',
            'nomor_wa'          => 'nullable|string|max:20', // nomor wa optional untuk dine-in
            'tipe_pengiriman'   => 'required|in:kurir_toko,kurir_customer,ambil_sendiri,dine_in',
            'meja_id'           => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.mejas,id',
            'alamat_penerima'   => 'required_if:tipe_pengiriman,kurir_toko,kurir_customer|nullable|string',
            'tanggal_diambil'   => 'required_unless:tipe_pengiriman,dine_in|nullable|date',
            'metode_pembayaran' => 'required|in:cod,transfer,manual,midtrans', // cod = cash
            'cart'              => 'required|array|min:1',
            'cart.*.id'         => 'required|exists:'.\App\Services\TenantManager::getTenantConnection().'.produks,id',
            'cart.*.varian_id'  => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.produk_varians,id',
            'cart.*.qty'        => 'required|integer|min:1',
            'cart.*.addons'     => 'nullable|array',
        ]);

        try {
            $pesanan = DB::transaction(function () use ($validated) {
                $totalBiaya = 0;
                $itemsData = [];

                // Normalisasi nomor WA
                $nomorWa = $validated['nomor_wa'] ? preg_replace('/[^0-9]/', '', $validated['nomor_wa']) : '-';
                if ($nomorWa !== '-' && str_starts_with($nomorWa, '0')) {
                    $nomorWa = '62' . substr($nomorWa, 1);
                }

                foreach ($validated['cart'] as $item) {
                    $produk = Produk::findOrFail($item['id']);
                    $varian = null;

                    if (!empty($item['varian_id'])) {
                        $varian = ProdukVarian::findOrFail($item['varian_id']);
                        $hargaSatuan = $varian->harga > 0 ? $varian->harga : $produk->harga;
                    } else {
                        $hargaSatuan = $produk->harga;
                    }

                    // Hitung Addons jika ada
                    $hargaAddons = 0;
                    $formattedAddons = [];
                    if (!empty($item['addons'])) {
                        foreach ($item['addons'] as $addon) {
                            $hargaAddons += (float)($addon['harga'] ?? 0);
                            $formattedAddons[] = [
                                'id' => $addon['id'],
                                'nama_addon' => $addon['nama'] ?? ($addon['nama_addon'] ?? ''),
                                'harga' => (float)($addon['harga'] ?? 0),
                                'teks' => $addon['teks'] ?? null
                            ];
                        }
                    }

                    // Cek bundle promo
                    $totalBiayaProduk = 0;
                    if (!empty($produk->promo_min_qty) && !empty($produk->promo_harga) && $item['qty'] >= $produk->promo_min_qty) {
                        $jmlPaket = (int)floor($item['qty'] / $produk->promo_min_qty);
                        $sisaItem = $item['qty'] % $produk->promo_min_qty;
                        $totalBiayaProduk = ($jmlPaket * $produk->promo_harga) + ($sisaItem * $hargaSatuan);
                    } else {
                        $totalBiayaProduk = $hargaSatuan * $item['qty'];
                    }

                    $totalBiayaAddon = $hargaAddons * $item['qty'];
                    $subtotal = $totalBiayaProduk + $totalBiayaAddon;

                    $hargaSatuanDenganAddons = $hargaSatuan + $hargaAddons;
                    $totalBiaya += $subtotal;

                    $itemsData[] = [
                        'produk' => $produk,
                        'varian' => $varian,
                        'qty' => $item['qty'],
                        'harga_satuan' => $hargaSatuanDenganAddons,
                        'subtotal' => $subtotal,
                        'addons' => $formattedAddons
                    ];
                }

                // Tentukan status pesanan awal
                // Jika kirim via kurir toko/ojol, set pending_ongkir agar admin bisa mengisi tarif ongkir
                $statusPesanan = 'pending';
                if ($validated['tipe_pengiriman'] === 'kurir_toko' || $validated['tipe_pengiriman'] === 'kurir_customer') {
                    $statusPesanan = 'pending_ongkir';
                } elseif ($validated['tipe_pengiriman'] === 'dine_in' || $validated['tipe_pengiriman'] === 'ambil_sendiri') {
                    if ($validated['metode_pembayaran'] === 'transfer' || $validated['metode_pembayaran'] === 'manual' || $validated['metode_pembayaran'] === 'midtrans' || $validated['metode_pembayaran'] === 'qris') {
                        $statusPesanan = 'pending_payment'; // Xendit/Midtrans/QRIS/Manual harus nunggu dibayar dulu
                    } else {
                        $statusPesanan = 'menunggu_pembayaran'; // Kasir
                    }
                }

                $alamat = $validated['alamat_penerima'] ?? 'Ambil Sendiri di Toko';
                if ($validated['tipe_pengiriman'] === 'dine_in') {
                    $mejaInfo = \App\Models\Meja::find($validated['meja_id']);
                    $alamat = $mejaInfo ? 'Dine In - Meja ' . $mejaInfo->nomor_meja : 'Dine In';
                }

                // Simpan Pesanan
                $pesanan = Pesanan::create([
                    'nomor_order'       => 'PRT-' . strtoupper(Str::random(8)),
                    'nomor_wa'          => $nomorWa,
                    'nama_penerima'     => $validated['nama_penerima'],
                    'alamat_penerima'   => $alamat,
                    'tipe_pengiriman'   => $validated['tipe_pengiriman'],
                    'tanggal_diambil'   => $validated['tanggal_diambil'] ?? date('Y-m-d H:i:s'),
                    'biaya_barang'      => $totalBiaya,
                    'biaya_pengantaran' => 0,
                    'total_biaya'       => $totalBiaya,
                    'uang_muka'         => 0,
                    'metode_pembayaran' => $validated['metode_pembayaran'],
                    'status'            => $statusPesanan,
                    'source'            => 'portal_online',
                    'meja_id'           => $validated['meja_id'] ?? null,
                ]);

                // Simpan Pesanan Item
                foreach ($itemsData as $itemData) {
                    PesananItem::create([
                        'pesanan_id'       => $pesanan->id,
                        'produk_id'        => $itemData['produk']->id,
                        'produk_varian_id' => $itemData['varian'] ? $itemData['varian']->id : null,
                        'jumlah'           => $itemData['qty'],
                        'harga_satuan'     => $itemData['harga_satuan'],
                        'subtotal'         => $itemData['subtotal'],
                        'addons'           => $itemData['addons']
                    ]);
                }

                return $pesanan;
            });

            // Kirim notifikasi WhatsApp ke Grup Admin
            try {
                $waService = app(WhatsAppService::class);
                $sellerGroupId = config('chatbot.whatsapp_group_id_seller', '');

                if ($sellerGroupId) {
                    $itemSummary = "";
                    foreach ($pesanan->items as $index => $item) {
                        $varianText = $item->produkVarian ? " ({$item->produkVarian->nama_varian})" : "";
                        $addonsLabel = "";
                        if (!empty($item->addons)) {
                            $addonDetails = [];
                            foreach ($item->addons as $addon) {
                                $addonStr = $addon['nama_addon'] ?? ($addon['nama'] ?? 'Addon');
                                if (!empty($addon['teks'])) {
                                    $addonStr .= ' ("' . $addon['teks'] . '")';
                                }
                                $addonDetails[] = $addonStr;
                            }
                            $addonsLabel = " + Tambahan: " . implode(', ', $addonDetails);
                        }
                        $itemSummary .= "- *" . $item->produk->nama . $varianText . "* x" . $item->jumlah . $addonsLabel . "\n";
                    }

                    $alamatText = $pesanan->tipe_pengiriman === 'ambil_sendiri' ? 'Ambil di Toko' : $pesanan->alamat_penerima;
                    $isDineIn = $pesanan->tipe_pengiriman === 'dine_in';
                    
                    if ($isDineIn) {
                        $antrianTeks = $pesanan->nomor_antrian ? "🎫 Antrian: *{$pesanan->nomor_antrian}*\n" : "";
                        $notifText = "🔔 *PESANAN DINE-IN BARU*\n" .
                            "━━━━━━━━━━━━━━━━\n\n" .
                            "📦 No Order: *{$pesanan->nomor_order}*\n" .
                            "{$antrianTeks}👤 Pemesan: *{$pesanan->nama_penerima}*\n" .
                            "🍽️ " . strtoupper($alamatText) . "\n" .
                            "💵 Pembayaran: " . strtoupper($pesanan->metode_pembayaran) . " (" . ($pesanan->metode_pembayaran === 'cod' ? 'Bayar di Kasir' : 'Lunas/Transfer') . ")\n\n" .
                            "🛍️ Menu:\n{$itemSummary}\n" .
                            "💰 Total: *Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "*\n" .
                            "━━━━━━━━━━━━━━━━\n\n";
                    } else {
                        $antrianTeks = $pesanan->nomor_antrian ? "🎫 Antrian: *{$pesanan->nomor_antrian}*\n" : "";
                        $notifText = "🔔 *PESANAN BARU DARI PORTAL CUSTOMER*\n" .
                            "━━━━━━━━━━━━━━━━\n\n" .
                            "📦 No Order: *{$pesanan->nomor_order}*\n" .
                            "{$antrianTeks}👤 Penerima: *{$pesanan->nama_penerima}* (+{$pesanan->nomor_wa})\n" .
                            "🚚 Tipe Kirim: " . strtoupper(str_replace('_', ' ', $pesanan->tipe_pengiriman)) . "\n" .
                            "📍 Alamat: {$alamatText}\n" .
                            "📅 Jadwal Ambil: " . date('d/m/Y H:i', strtotime($pesanan->tanggal_diambil)) . "\n" .
                            "💵 Pembayaran: " . strtoupper($pesanan->metode_pembayaran) . "\n\n" .
                            "🛍️ Produk:\n{$itemSummary}\n" .
                            "💰 Subtotal Barang: *Rp " . number_format($pesanan->biaya_barang, 0, ',', '.') . "*\n" .
                            "━━━━━━━━━━━━━━━━\n\n";
                    }

                    if ($pesanan->status === 'pending_ongkir') {
                        $notifText .= "⚠️ *Menunggu Tarif Ongkir!*\nSilakan balas dengan perintah:\n`!set-ongkir {$pesanan->nomor_order} [nominal]`";
                    } elseif ($pesanan->status === 'menunggu_pembayaran') {
                        $notifText .= "Silakan arahkan customer ke Kasir untuk pembayaran.\nAtau jika kasir di aplikasi: Setujui dan Lunas.";
                    } elseif ($pesanan->status === 'diproses_dapur') {
                        $notifText .= "Pesanan otomatis masuk ke Dapur (Transfer/Non-Tunai).";
                    } else {
                        $notifText .= "Silakan setujui pesanan ini dengan perintah:\n`!setuju-order {$pesanan->nomor_order}`";
                    }

                    $waService->kirimPesan($sellerGroupId, $notifText);
                }
            } catch (\Exception $waEx) {
                Log::error("Gagal mengirim notifikasi WhatsApp grup untuk order portal: " . $waEx->getMessage());
            }

            $snapToken = null;
            if ($pesanan->metode_pembayaran === 'midtrans' && $pesanan->status === 'pending_payment') {
                $midtransService = new \App\Services\MidtransService();
                if ($midtransService->isActive()) {
                    $snapToken = $midtransService->getSnapToken($pesanan);
                    // Jika gagal dapat token, log saja, nanti customer bisa bayar manual via WA
                }
            }

            return response()->json([
                'status'      => 'success',
                'message'     => 'Pesanan berhasil dikirim!',
                'nomor_order' => $pesanan->nomor_order,
                'total_biaya' => $pesanan->total_biaya,
                'snap_token'  => $snapToken,
                'is_luar_jam_operasional' => $isLuarJamOperasional ?? false,
                'jam_buka'    => isset($identitas) && $identitas->jam_buka ? \Carbon\Carbon::parse($identitas->jam_buka)->format('H:i') : null
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 400);
        }
    }

    public function submitReservasi(Request $request, $nama_toko_slug)
    {
        $tenant = $this->requireTenant();
        
        $validated = $request->validate([
            'nama_pelanggan' => 'required|string|max:255',
            'nomor_telepon'  => 'required|string|max:50',
            'tanggal_waktu'  => 'required|date',
            'jumlah_orang'   => 'required|integer|min:1',
            'meja_id'        => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.mejas,id',
            'catatan'        => 'nullable|string'
        ]);

        $identitas = \App\Models\IdentitasToko::first();
        
        // Cek max pax
        if ($identitas && $identitas->max_pax_per_reservation > 0) {
            if ($validated['jumlah_orang'] > $identitas->max_pax_per_reservation) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Maksimal tamu per reservasi adalah {$identitas->max_pax_per_reservation} orang."
                ], 400);
            }
        }

        // Cek minimal jam reservasi
        $zonaWaktu = $identitas->zona_waktu ?? 'Asia/Jakarta';

        if ($identitas && $identitas->minimal_jam_reservasi > 0) {
            $minJam = $identitas->minimal_jam_reservasi;
            $waktuReservasi = \Carbon\Carbon::parse($validated['tanggal_waktu'], $zonaWaktu);
            $minWaktu = \Carbon\Carbon::now($zonaWaktu)->addHours($minJam);
            if ($waktuReservasi->lessThan($minWaktu)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Reservasi minimal dilakukan H-{$minJam} jam dari waktu saat ini."
                ], 400);
            }
        }

        // Cek jam operasional
        if ($identitas && $identitas->jam_buka && $identitas->jam_tutup) {
            $waktuReservasi = \Carbon\Carbon::parse($validated['tanggal_waktu'], $zonaWaktu);
            $jamBuka = \Carbon\Carbon::parse($identitas->jam_buka, $zonaWaktu);
            $jamTutup = \Carbon\Carbon::parse($identitas->jam_tutup, $zonaWaktu);
            
            $waktuTime = \Carbon\Carbon::createFromTime($waktuReservasi->hour, $waktuReservasi->minute, 0, $zonaWaktu);
            $bukaTime = \Carbon\Carbon::createFromTime($jamBuka->hour, $jamBuka->minute, 0, $zonaWaktu);
            $tutupTime = \Carbon\Carbon::createFromTime($jamTutup->hour, $jamTutup->minute, 0, $zonaWaktu);

            if ($waktuTime->lessThan($bukaTime) || $waktuTime->greaterThan($tutupTime)) {
                return response()->json([
                    'status' => 'error',
                    'message' => "Reservasi ditolak. Waktu reservasi harus dalam jam operasional toko (" . $bukaTime->format('H:i') . " - " . $tutupTime->format('H:i') . ")."
                ], 400);
            }
        }

        $wajibDp = $identitas->wajib_dp_reservasi ?? false;
        $nominalDp = $identitas->nominal_dp_reservasi ?? 50000;
        $holdDuration = $identitas->hold_duration_hours ?? 2;

        try {
            $reservasi = \Illuminate\Support\Facades\DB::transaction(function () use ($validated, $wajibDp, $nominalDp, $holdDuration) {
                // Lock meja untuk menghindari race condition
                if (!empty($validated['meja_id'])) {
                    $meja = \App\Models\Meja::where('id', $validated['meja_id'])->lockForUpdate()->first();
                    if (!$meja->isAvailableFor(\Carbon\Carbon::parse($validated['tanggal_waktu'])->format('Y-m-d'), \Carbon\Carbon::parse($validated['tanggal_waktu'])->format('H:i:s'), $validated['jumlah_orang'])) {
                        throw new \Exception("Maaf, meja ini baru saja direservasi oleh orang lain.");
                    }
                    $meja->update(['status' => 'direservasi']);
                }

                $reservasi = \App\Models\Reservasi::create([
                    'nama_pelanggan' => $validated['nama_pelanggan'],
                    'nomor_telepon'  => $validated['nomor_telepon'],
                    'tanggal_waktu'  => $validated['tanggal_waktu'],
                    'jumlah_orang'   => $validated['jumlah_orang'],
                    'meja_id'        => $validated['meja_id'] ?? null,
                    'catatan'        => $validated['catatan'] ?? null,
                    'is_dp_required' => $wajibDp,
                    'nominal_dp'     => $wajibDp ? $nominalDp : 0,
                    'status_pembayaran_dp' => $wajibDp ? 'belum_bayar' : null,
                    'status'         => 'on_hold',
                    'hold_expires_at' => now()->addHours($holdDuration)
                ]);

                return $reservasi;
            });

            // Dispatch job
            \App\Jobs\ExpireReservationJob::dispatch($reservasi->id)->delay(now()->addHours($holdDuration));

            $namaToko = $identitas ? $identitas->nama_toko : 'Toko';
            
            // Notifikasi WA (Bungkus di try-catch agar kegagalan kirim WA tidak membuat status order gagal)
            try {
                $waService = app(\App\Services\WhatsAppService::class);
                
                // Notif Customer
                $pesanCustomer = "Halo {$reservasi->nama_pelanggan}, reservasi Anda di {$namaToko} sedang menunggu konfirmasi dari toko. Kami akan menghubungi Anda segera. Terima kasih!";
                $waService->kirimPesan($reservasi->nomor_telepon, $pesanCustomer);

                // Notif Owner
                $sellerGroupId = config('chatbot.whatsapp_group_id_seller', '');
                if ($sellerGroupId) {
                    $mejaStr = $reservasi->meja ? $reservasi->meja->nomor_meja : 'Belum dipilih';
                    $pesanOwner = "🔔 *Reservasi Baru - {$namaToko}*\n\n" .
                        "Nama: {$reservasi->nama_pelanggan}\n" .
                        "WA: {$reservasi->nomor_telepon}\n" .
                        "Tanggal: " . \Carbon\Carbon::parse($reservasi->tanggal_waktu)->format('d/m/Y') . "\n" .
                        "Jam: " . \Carbon\Carbon::parse($reservasi->tanggal_waktu)->format('H:i') . "\n" .
                        "Pax: {$reservasi->jumlah_orang} orang\n" .
                        "Meja: {$mejaStr}\n" .
                        "Catatan: {$reservasi->catatan}\n\n" .
                        "Balas dengan perintah:\n" .
                        "✅ `!setuju-reservasi {$reservasi->id}` untuk konfirmasi\n" .
                        "❌ `!tolak-reservasi {$reservasi->id} [alasan]` untuk menolak";
                    $waService->kirimPesan($sellerGroupId, $pesanOwner);
                }
            } catch (\Exception $e) {
                \Illuminate\Support\Facades\Log::error('Gagal mengirim notif reservasi: ' . $e->getMessage());
            }

            return response()->json([
                'status'      => 'success',
                'message'     => 'Reservasi berhasil dikirim!',
                'reservasi_id'=> $reservasi->id,
                'wajib_dp'    => $wajibDp,
                'nominal_dp'  => $wajibDp ? $nominalDp : 0,
                'rekening'    => $identitas->rekening ?? ''
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function checkTableAvailability(Request $request, $nama_toko_slug)
    {
        try {
            $tenant = $this->requireTenant();
            
            $tanggalWaktu = $request->input('tanggal_waktu');
            $pax = (int) $request->input('pax', 0);
            
            if (!$tanggalWaktu) {
                return response()->json(['status' => 'error', 'message' => 'Tanggal dan waktu tidak valid.'], 400);
            }

            $waktuCari = \Carbon\Carbon::parse($tanggalWaktu);
            
            $tanggal = $waktuCari->format('Y-m-d');
            $jam = $waktuCari->format('H:i:s');

            $semuaMeja = \App\Models\Meja::orderBy('nomor_meja')->get();
            
            $totalKapasitasTersedia = 0;
            $totalKapasitasKeseluruhan = 0;

            $mejasList = [];
            foreach ($semuaMeja as $meja) {
                // Jika pax = 0, isAvailableFor tetap bisa ngecek konflik tanpa batasan kapasitas
                $isAvailable = $meja->isAvailableFor($tanggal, $jam, $pax);
                $mejasList[] = [
                    'id' => $meja->id,
                    'nomor_meja' => $meja->nama_meja ?? $meja->nomor_meja,
                    'kapasitas' => $meja->kapasitas,
                    'deskripsi' => $meja->deskripsi,
                    'is_available' => $isAvailable,
                    'is_recommended' => false
                ];
                
                $totalKapasitasKeseluruhan += $meja->kapasitas;
                if ($isAvailable) {
                    $totalKapasitasTersedia += $meja->kapasitas;
                }
            }

            // Logic Rekomendasi Meja jika Pax ditentukan
            $recommendedTableIds = [];
            $recommendationMessage = '';

            if ($pax > 0) {
                // Jika ada meja tunggal yang muat
                $singleTableMatch = collect($mejasList)
                    ->where('is_available', true)
                    ->where('kapasitas', '>=', $pax)
                    ->sortBy('kapasitas')
                    ->first();

                if ($singleTableMatch) {
                    $recommendedTableIds = [$singleTableMatch['id']];
                    $recommendationMessage = "Kami merekomendasikan Meja " . $singleTableMatch['nomor_meja'] . " untuk " . $pax . " orang.";
                } else {
                    // Cari gabungan meja berurutan
                    $availableTables = collect($mejasList)->where('is_available', true)->values()->all();
                    $foundCombination = false;
                    
                    for ($i = 0; $i < count($availableTables); $i++) {
                        $comboKapasitas = 0;
                        $comboIds = [];
                        $comboNames = [];
                        
                        for ($j = $i; $j < count($availableTables); $j++) {
                            // Pastikan meja berurutan (misal Meja 1 dan Meja 2) - simulasi berdasarkan index jika urut
                            if ($j > $i) {
                                // Cek selisih string nomor meja, walau sederhana
                                $prevNum = (int) preg_replace('/\D/', '', $availableTables[$j-1]['nomor_meja']);
                                $currNum = (int) preg_replace('/\D/', '', $availableTables[$j]['nomor_meja']);
                                
                                // Toleransi perbedaan nomor 1 (e.g 1->2)
                                if (abs($currNum - $prevNum) > 1) {
                                    break; // Tidak berurutan, hentikan kombinasi
                                }
                            }

                            $comboKapasitas += $availableTables[$j]['kapasitas'];
                            $comboIds[] = $availableTables[$j]['id'];
                            $comboNames[] = $availableTables[$j]['nomor_meja'];

                            if ($comboKapasitas >= $pax) {
                                $foundCombination = true;
                                $recommendedTableIds = $comboIds;
                                $recommendationMessage = "Kami merekomendasikan gabungan Meja " . implode(', ', $comboNames) . " agar rombongan Anda bisa duduk berdekatan.";
                                break 2;
                            }
                        }
                    }
                    
                    if (!$foundCombination && $totalKapasitasTersedia >= $pax) {
                        $recommendationMessage = "Kapasitas tersedia, namun kami tidak menemukan meja berdekatan yang pas. Silakan pilih meja terpisah dan tambahkan catatan.";
                    } elseif ($totalKapasitasTersedia < $pax) {
                        $recommendationMessage = "Maaf, sisa kapasitas (" . $totalKapasitasTersedia . " pax) tidak mencukupi untuk rombongan Anda.";
                    }
                }
            }

            // Apply recommendation status
            foreach ($mejasList as &$m) {
                if (in_array($m['id'], $recommendedTableIds)) {
                    $m['is_recommended'] = true;
                }
            }

            return response()->json([
                'status' => 'success',
                'mejas' => $mejasList,
                'ketersediaan' => [
                    'total_kapasitas' => $totalKapasitasKeseluruhan,
                    'tersedia' => $totalKapasitasTersedia,
                    'rekomendasi_msg' => $recommendationMessage,
                    'recommended_ids' => $recommendedTableIds
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => 'Terjadi kesalahan sistem: ' . $e->getMessage()
            ], 500);
        }
    }

}
