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
            'meja_id'           => 'nullable|exists:mejas,id',
            'alamat_penerima'   => 'required_if:tipe_pengiriman,kurir_toko,kurir_customer|nullable|string',
            'tanggal_diambil'   => 'required_unless:tipe_pengiriman,dine_in|nullable|date',
            'metode_pembayaran' => 'required|in:cod,transfer', // cod = cash
            'cart'              => 'required|array|min:1',
            'cart.*.id'         => 'required|exists:produks,id',
            'cart.*.varian_id'  => 'nullable|exists:produk_varians,id',
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
                    if ($validated['metode_pembayaran'] === 'transfer' || $validated['metode_pembayaran'] === 'qris') {
                        $statusPesanan = 'pending_payment'; // Xendit/Midtrans/QRIS harus nunggu dibayar dulu
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
                        $notifText = "🔔 *PESANAN DINE-IN BARU*\n" .
                            "━━━━━━━━━━━━━━━━\n\n" .
                            "📦 No Order: *{$pesanan->nomor_order}*\n" .
                            "👤 Pemesan: *{$pesanan->nama_penerima}*\n" .
                            "🍽️ " . strtoupper($alamatText) . "\n" .
                            "💵 Pembayaran: " . strtoupper($pesanan->metode_pembayaran) . " (" . ($pesanan->metode_pembayaran === 'cod' ? 'Bayar di Kasir' : 'Lunas/Transfer') . ")\n\n" .
                            "🛍️ Menu:\n{$itemSummary}\n" .
                            "💰 Total: *Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "*\n" .
                            "━━━━━━━━━━━━━━━━\n\n";
                    } else {
                        $notifText = "🔔 *PESANAN BARU DARI PORTAL CUSTOMER*\n" .
                            "━━━━━━━━━━━━━━━━\n\n" .
                            "📦 No Order: *{$pesanan->nomor_order}*\n" .
                            "👤 Penerima: *{$pesanan->nama_penerima}* (+{$pesanan->nomor_wa})\n" .
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
            if ($pesanan->metode_pembayaran === 'transfer' && $pesanan->status === 'pending_payment') {
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
                'snap_token'  => $snapToken
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Gagal memproses pesanan: ' . $e->getMessage()
            ], 400);
        }
    }
}
