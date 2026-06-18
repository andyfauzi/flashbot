<?php

namespace App\Services;

use App\Models\ChatbotHistory;
use App\Models\ChatbotCart;
use App\Models\ChatbotCartItem;
use App\Models\Produk;
use App\Models\Pesanan;
use App\Models\PesananItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class GeminiAiService
{
    protected string $apiKey;
    protected string $apiUrl = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-3.1-flash-lite:generateContent';

    public function __construct()
    {
        $this->apiKey = env('GEMINI_API_KEY', '');
    }

    public function handleMessage(string $nomorWa, string $pesanUser, string $context = 'customer'): ?string
    {
        if (empty($this->apiKey)) {
            Log::error("Gemini API Key is missing!");
            return "Maaf, sistem AI belum dikonfigurasi dengan benar (API Key hilang).";
        }

        // 1. Simpan pesan user ke history
        ChatbotHistory::create([
            'nomor_wa' => $nomorWa,
            'role' => 'user',
            'content' => $pesanUser
        ]);

        // 2. Siapkan Context (System Instruction & History)
        $systemInstruction = $this->getSystemInstruction($context);
        $history = ChatbotHistory::where('nomor_wa', $nomorWa)
            ->orderBy('id', 'desc')
            ->take(15) // ambil 15 pesan terakhir
            ->get()
            ->reverse()
            ->values();

        $contents = [];
        foreach ($history as $h) {
            $contents[] = [
                'role' => $h->role, // user or model
                'parts' => [['text' => $h->content]]
            ];
        }

        // 3. Define Tools (Functions)
        $tools = [
            [
                'function_declarations' => [
                    [
                        'name' => 'get_katalog_produk',
                        'description' => 'Mendapatkan daftar semua produk yang tersedia beserta ID, stok, dan harganya. Gunakan ini jika pelanggan menanyakan menu atau ingin memesan.',
                    ],
                    [
                        'name' => 'tambah_ke_keranjang',
                        'description' => 'Memasukkan produk yang dipesan pelanggan ke dalam keranjang belanjanya. Anda harus sudah tahu ID produknya.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'produk_id' => ['type' => 'INTEGER', 'description' => 'ID dari produk'],
                                'produk_varian_id' => ['type' => 'INTEGER', 'description' => 'ID varian (jika ada, kosongkan jika tidak ada)'],
                                'jumlah' => ['type' => 'INTEGER', 'description' => 'Berapa banyak yang dipesan'],
                                'addons' => [
                                    'type' => 'ARRAY',
                                    'items' => [
                                        'type' => 'OBJECT',
                                        'properties' => [
                                            'id' => ['type' => 'INTEGER', 'description' => 'ID of the addon'],
                                            'teks' => ['type' => 'STRING', 'description' => 'Custom text for the addon if it requires text, otherwise leave empty']
                                        ],
                                        'required' => ['id']
                                    ],
                                    'description' => 'List of addons selected by the customer for this product'
                                ],
                                'catatan' => ['type' => 'STRING', 'description' => 'Catatan khusus dari pelanggan (contoh: ekstra pedas, kurangi gula). Kosongkan jika tidak ada catatan spesifik.']
                            ],
                            'required' => ['produk_id', 'jumlah']
                        ]
                    ],
                    [
                        'name' => 'checkout_pesanan',
                        'description' => 'Menyelesaikan pesanan jika pelanggan sudah memberikan nama, alamat, metode pembayaran (qris/transfer/cod), dan tanggal pengiriman/pengambilan.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nama_penerima' => ['type' => 'STRING', 'description' => 'Nama pelanggan'],
                                'alamat_penerima' => ['type' => 'STRING', 'description' => 'Alamat lengkap pengiriman'],
                                'metode_pembayaran' => ['type' => 'STRING', 'description' => 'qris, transfer, atau cod'],
                                'tanggal_diambil' => ['type' => 'STRING', 'description' => 'Tanggal pengiriman/pengambilan WAJIB format YYYY-MM-DD HH:MM:00 (misal: 2026-06-05 16:00:00). Ubah kata seperti "hari ini sore" menjadi format tersebut.'],
                                'biaya_pengantaran' => ['type' => 'INTEGER', 'description' => 'Biaya ongkos kirim sesuai kesepakatan wilayah']
                            ],
                            'required' => ['nama_penerima', 'alamat_penerima', 'metode_pembayaran', 'tanggal_diambil']
                        ]
                    ],
                    [
                        'name' => 'batalkan_keranjang',
                        'description' => 'Membatalkan seluruh pesanan yang ada di keranjang pelanggan saat ini (sebelum checkout). Gunakan ini jika pelanggan berkata "batal order" dan pesanan belum dicheckout.',
                    ],
                    [
                        'name' => 'batalkan_pesanan',
                        'description' => 'Membatalkan pesanan pelanggan yang SUDAH di-checkout dan sudah masuk database. Gunakan jika pelanggan meminta membatalkan pesanannya yang sudah berhasil dipesan.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nomor_order' => ['type' => 'STRING', 'description' => 'Nomor order pesanan (opsional, jika pelanggan memberitahukannya)']
                            ]
                        ]
                    ],
                    [
                        'name' => 'ubah_pesanan',
                        'description' => 'Mengubah detail pesanan yang sudah dibuat/checkout (misalnya mengubah tipe pengiriman, alamat, tanggal pengambilan, atau metode pembayaran). Gunakan ini jika pelanggan ingin mengedit data pesanan aktif.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nama_penerima' => ['type' => 'STRING', 'description' => 'Nama pelanggan baru (opsional)'],
                                'alamat_penerima' => ['type' => 'STRING', 'description' => 'Alamat lengkap baru untuk pengiriman. Isi "Ambil di Toko" jika pelanggan ingin mengambil sendiri ke toko.'],
                                'metode_pembayaran' => ['type' => 'STRING', 'description' => 'qris, transfer, atau cod (opsional)'],
                                'tanggal_diambil' => ['type' => 'STRING', 'description' => 'Tanggal pengiriman/pengambilan baru WAJIB format YYYY-MM-DD HH:MM:00 (opsional)']
                            ]
                        ]
                    ],
                    [
                        'name' => 'buat_reservasi',
                        'description' => 'Membuat reservasi meja untuk pelanggan yang ingin makan di tempat (dine-in). Pastikan data lengkap sebelum memanggil fungsi ini.',
                        'parameters' => [
                            'type' => 'OBJECT',
                            'properties' => [
                                'nama_pelanggan' => ['type' => 'STRING', 'description' => 'Nama pelanggan'],
                                'tanggal_waktu' => ['type' => 'STRING', 'description' => 'Tanggal dan jam reservasi WAJIB format YYYY-MM-DD HH:MM:00'],
                                'jumlah_orang' => ['type' => 'INTEGER', 'description' => 'Jumlah orang yang akan makan'],
                                'catatan' => ['type' => 'STRING', 'description' => 'Catatan khusus dari pelanggan (opsional)']
                            ],
                            'required' => ['nama_pelanggan', 'tanggal_waktu', 'jumlah_orang']
                        ]
                    ]
                ]
            ]
        ];

        // 4. Hit API Pertama Kali
        $payload = [
            'system_instruction' => [
                'parts' => [['text' => $systemInstruction]]
            ],
            'contents' => $contents,
            'tools' => $tools
        ];

        return $this->callGeminiWithToolHandling($nomorWa, $payload);
    }

    protected function callGeminiWithToolHandling($nomorWa, $payload)
    {
        $response = Http::withHeaders([
            'Content-Type' => 'application/json'
        ])->post($this->apiUrl . '?key=' . $this->apiKey, $payload);

        if (!$response->successful()) {
            Log::error("Gemini API Error: " . $response->body());
            return "Mohon maaf, sistem AI sedang mengalami gangguan.";
        }

        $data = $response->json();
        
        if (!isset($data['candidates'][0]['content']['parts'][0])) {
            return "Maaf, saya tidak mengerti.";
        }

        $part = $data['candidates'][0]['content']['parts'][0];

        // 5. Cek apakah AI memanggil fungsi (Tool Call)
        if (isset($part['functionCall'])) {
            $functionName = $part['functionCall']['name'];
            $args = $part['functionCall']['args'] ?? [];
            
            // Mencegah PHP mengubah JSON object kosong {} menjadi JSON array [] yang ditolak oleh API Gemini
            if (empty($args) || (is_array($args) && count($args) === 0)) {
                $data['candidates'][0]['content']['parts'][0]['functionCall']['args'] = new \stdClass();
            }

            Log::info("Gemini memanggil fungsi: {$functionName}", $args);

            $functionResult = [];

            if ($functionName === 'get_katalog_produk') {
                $functionResult = $this->getKatalogProduk();
            } elseif ($functionName === 'tambah_ke_keranjang') {
                $functionResult = $this->tambahKeKeranjang($nomorWa, $args);
            } elseif ($functionName === 'checkout_pesanan') {
                $functionResult = $this->checkoutPesanan($nomorWa, $args);
            } elseif ($functionName === 'batalkan_keranjang') {
                $functionResult = $this->batalkanKeranjang($nomorWa);
            } elseif ($functionName === 'batalkan_pesanan') {
                $functionResult = $this->batalkanPesanan($nomorWa, $args);
            } elseif ($functionName === 'ubah_pesanan') {
                $functionResult = $this->ubahPesanan($nomorWa, $args);
            } elseif ($functionName === 'buat_reservasi') {
                $functionResult = $this->buatReservasi($nomorWa, $args);
            } else {
                $functionResult = ['error' => 'Fungsi tidak ditemukan'];
            }

            // 6. Kirim kembali hasil fungsi ke AI agar AI bisa membuat jawaban akhir
            // Append the model's function call to history
            $payload['contents'][] = $data['candidates'][0]['content'];
            // Append the function response
            $payload['contents'][] = [
                'role' => 'function',
                'parts' => [
                    [
                        'functionResponse' => [
                            'name' => $functionName,
                            'response' => empty($functionResult) ? new \stdClass() : $functionResult
                        ]
                    ]
                ]
            ];

            return $this->callGeminiWithToolHandling($nomorWa, $payload);
        }

        // 7. Jika berupa teks balasan biasa
        if (isset($part['text'])) {
            $responseText = $part['text'];

            // Simpan ke history
            ChatbotHistory::create([
                'nomor_wa' => $nomorWa,
                'role' => 'model',
                'content' => $responseText
            ]);

            return $responseText;
        }

        return "Terjadi kesalahan yang tidak terduga.";
    }

    // =============================================
    // BATALKAN PESANAN (SUDAH CHECKOUT)
    // =============================================
    protected function batalkanPesanan($nomorWa, $args)
    {
        $nomorOrder = $args['nomor_order'] ?? null;
        
        $query = Pesanan::where('nomor_wa', $nomorWa)
                       ->whereIn('status', ['pending_approval', 'pending_payment', 'pending_ongkir', 'approved']);
        
        if (!empty($nomorOrder)) {
            $query->where('nomor_order', $nomorOrder);
        }
        
        $pesanans = $query->get();
        
        if ($pesanans->count() === 0) {
            return ['status' => 'error', 'pesan' => 'Maaf, tidak ada pesanan aktif (pending/proses) yang bisa dibatalkan saat ini.'];
        }
        
        if ($pesanans->count() > 1 && empty($nomorOrder)) {
            // Return a list of orders so the AI can ask the user which one
            $list = [];
            foreach ($pesanans as $p) {
                $list[] = "Nomor Order: {$p->nomor_order} (Atas Nama: {$p->nama_penerima})";
            }
            return ['status' => 'error', 'pesan' => 'Anda memiliki lebih dari 1 pesanan aktif. Tolong tanyakan kepada pelanggan nomor order mana yang ingin dibatalkan: ' . implode(' | ', $list)];
        }
        
        $pesanan = $pesanans->first();
        
        DB::transaction(function () use ($pesanan) {
            $pesanan->lockForUpdate();
            
            // Kembalikan stok hibrida (smart deduction rollback)
            foreach ($pesanan->items as $item) {
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
                                    'keterangan' => 'Batal Otomatis via AI Chatbot (Made-to-Order) Struk #' . $pesanan->nomor_order
                                ]);
                            }
                        }
                    }
                } else {
                    // JIKA MADE-TO-STOCK: Kembalikan stok produk jadi
                    if ($item->produk_varian_id) {
                        $lockedVarian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                        if ($lockedVarian) {
                            $lockedVarian->increment('stok', $item->jumlah);
                        }
                    } else {
                        $lockedProduk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                        if ($lockedProduk) {
                            $lockedProduk->increment('stok', $item->jumlah);
                        }
                    }
                }
            }
            
            $pesanan->status = 'cancelled';
            $pesanan->save();
        });
        
        // Clear history context to start a fresh session
        ChatbotHistory::where('nomor_wa', $nomorWa)->delete();
        
        return ['status' => 'success', 'pesan' => "Pesanan dengan nomor order {$pesanan->nomor_order} berhasil dibatalkan."];
    }

    // =============================================
    // UBAH PESANAN (SUDAH CHECKOUT)
    // =============================================
    protected function ubahPesanan($nomorWa, $args)
    {
        $pesanan = Pesanan::where('nomor_wa', $nomorWa)
            ->whereIn('status', ['pending_approval', 'pending_payment', 'pending_ongkir'])
            ->orderBy('id', 'desc')
            ->first();

        if (!$pesanan) {
            return [
                'status' => 'error',
                'pesan' => 'Maaf, tidak ada pesanan aktif (menunggu persetujuan/pembayaran/ongkir) yang dapat diubah saat ini.'
            ];
        }

        $namaPenerima = $args['nama_penerima'] ?? null;
        $alamat = $args['alamat_penerima'] ?? null;
        $metode = $args['metode_pembayaran'] ?? null;
        $tanggal = $args['tanggal_diambil'] ?? null;

        $perubahan = [];

        DB::transaction(function () use ($pesanan, $namaPenerima, $alamat, $metode, $tanggal, &$perubahan) {
            $pesanan->lockForUpdate();

            if (!empty($namaPenerima)) {
                $pesanan->nama_penerima = $namaPenerima;
                $perubahan[] = "Nama Penerima diubah menjadi '{$namaPenerima}'";
            }

            if (!empty($alamat)) {
                $pesanan->alamat_penerima = $alamat;
                
                $tipePengiriman = (
                    stripos($alamat, 'ambil') !== false || 
                    stripos($alamat, 'toko') !== false || 
                    stripos($alamat, 'pickup') !== false || 
                    stripos($alamat, 'pick up') !== false ||
                    stripos($alamat, 'tempat') !== false
                ) ? 'ambil_sendiri' : 'kurir_toko';

                if ($tipePengiriman === 'ambil_sendiri') {
                    $pesanan->tipe_pengiriman = 'ambil_sendiri';
                    $pesanan->alamat_penerima = 'Ambil Sendiri';
                    $pesanan->biaya_pengantaran = 0;
                    $pesanan->total_biaya = $pesanan->biaya_barang;
                    $perubahan[] = "Metode Pengiriman diubah menjadi 'Ambil Sendiri' (Ongkir di-reset ke Rp 0)";
                } else {
                    $pesanan->tipe_pengiriman = 'kurir_toko';
                    $pesanan->biaya_pengantaran = 0;
                    $pesanan->total_biaya = $pesanan->biaya_barang;
                    $pesanan->status = 'pending_ongkir';
                    $perubahan[] = "Alamat Pengiriman diubah menjadi '{$alamat}' dan Tipe Pengiriman diubah menjadi 'Kurir Toko' (Ongkir di-reset ke Rp 0, menunggu verifikasi ongkir admin)";
                }
            }

            if (!empty($metode)) {
                $metodeClean = strtolower($metode);
                $pesanan->metode_pembayaran = $metodeClean;
                
                if ($pesanan->tipe_pengiriman === 'kurir_toko' && $pesanan->biaya_pengantaran == 0) {
                    $pesanan->status = 'pending_ongkir';
                } else {
                    if ($metodeClean === 'qris' || $metodeClean === 'transfer') {
                        $pesanan->status = 'pending_payment';
                    } else {
                        $pesanan->status = 'pending_approval';
                    }
                }
                $perubahan[] = "Metode Pembayaran diubah menjadi '" . strtoupper($metodeClean) . "'";
            }

            if (!empty($tanggal)) {
                $pesanan->tanggal_diambil = $tanggal;
                $perubahan[] = "Tanggal Pengambilan/Kirim diubah menjadi '{$tanggal}'";
            }

            if (count($perubahan) > 0) {
                $pesanan->save();
            }
        });

        if (count($perubahan) === 0) {
            return [
                'status' => 'success',
                'pesan' => 'Tidak ada detail data yang diubah.'
            ];
        }

        try {
            $orderService = app(\App\Services\OrderService::class);
            $daftarProduk = "";
            foreach ($pesanan->items as $item) {
                $namaProduk = $item->produk ? $item->produk->nama : 'Produk';
                $varian = $item->produkVarian ? " ({$item->produkVarian->nama_varian})" : "";
                $daftarProduk .= "\n  - *{$namaProduk}{$varian}* x{$item->jumlah}";
            }
            $tipeTeks = $pesanan->tipe_pengiriman === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Kurir Toko';
            $pesanGrup = "🔄 *PERUBAHAN PESANAN DARI AI*\n" .
                         "━━━━━━━━━━━━━━━━\n" .
                         "Nomor Order: *{$pesanan->nomor_order}*\n" .
                         "Nama Penerima: {$pesanan->nama_penerima}\n" .
                         "Alamat Baru: {$pesanan->alamat_penerima}\n" .
                         "Tipe Pengiriman Baru: *{$tipeTeks}*\n" .
                         "Produk: {$daftarProduk}\n" .
                         "Total Biaya: Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "\n" .
                         "━━━━━━━━━━━━━━━━\n" .
                         "Detail Perubahan:\n" . implode("\n", array_map(fn($p) => "• " . $p, $perubahan)) . "\n\n";

            if ($pesanan->tipe_pengiriman === 'kurir_toko' && $pesanan->biaya_pengantaran == 0) {
                $pesanGrup .= "💡 Silakan tentukan ongkir via dashboard atau ketik perintah:\n`!set-ongkir {$pesanan->nomor_order} [nominal]`";
            } else {
                $pesanGrup .= "💡 Silakan setujui pesanan ini dengan perintah:\n`!setuju-order {$pesanan->nomor_order}`";
            }

            $orderService->notifikasiGrupSeller($pesanGrup);
        } catch (\Exception $e) {
            Log::error("Gagal kirim notifikasi update ke grup seller: " . $e->getMessage());
        }

        $pesanTeksResponse = "Pesanan dengan nomor order {$pesanan->nomor_order} berhasil diperbarui.\nDetail Perubahan:\n" . implode("\n", $perubahan);
        return [
            'status' => 'success',
            'pesan_ke_ai' => $pesanTeksResponse . "\nBeri tahu pelanggan rincian data baru tersebut dan konfirmasikan sisa langkah selanjutnya."
        ];
    }

    // ==========================================
    // INTERNAL FUNCTIONS (TOOLS)
    // ==========================================

    protected function getKatalogProduk()
    {
        $produks = Produk::with(['varians', 'kategori', 'addons'])->where('aktif', true)->get();
        $result = [];
        foreach ($produks as $p) {
            $var = [];
            foreach ($p->varians as $v) {
                $var[] = [
                    'varian_id' => $v->id, 
                    'nama' => $v->nama_varian, 
                    'stok' => $v->stok,
                    'stok_proses_dapur' => $v->stok_proses_dapur
                ];
            }
            $ads = [];
            foreach ($p->addons as $a) {
                $ads[] = [
                    'addon_id' => $a->id,
                    'nama' => $a->nama_addon,
                    'harga' => $a->harga,
                    'butuh_teks' => (bool)$a->butuh_teks
                ];
            }
            $result[] = [
                'produk_id' => $p->id,
                'kategori' => $p->kategori->nama ?? 'Umum',
                'nama' => $p->nama,
                'harga' => $p->harga,
                'stok' => $p->stok,
                'stok_proses_dapur' => $p->stok_proses_dapur,
                'varians' => $var,
                'addons' => $ads
            ];
        }
        return ['katalog' => $result];
    }

    protected function tambahKeKeranjang($nomorWa, $args)
    {
        $produkId = $args['produk_id'] ?? null;
        $varianId = $args['produk_varian_id'] ?? null;
        if (empty($varianId)) {
            $varianId = null;
        }
        $jumlah = $args['jumlah'] ?? 1;
        $addonsInput = $args['addons'] ?? [];
        $catatan = $args['catatan'] ?? null;

        if (!$produkId) return ['error' => 'produk_id wajib diisi'];

        $produk = Produk::find($produkId);
        if (!$produk) return ['error' => 'Produk tidak ditemukan'];

        // Resolve addons information from DB to ensure correct price & name are saved
        $formattedAddons = [];
        foreach ($addonsInput as $ai) {
            $addonModel = \App\Models\ProdukAddon::find($ai['id']);
            if ($addonModel) {
                $formattedAddons[] = [
                    'id' => $addonModel->id,
                    'nama_addon' => $addonModel->nama_addon,
                    'harga' => $addonModel->harga,
                    'butuh_teks' => (bool)$addonModel->butuh_teks,
                    'teks' => $ai['teks'] ?? null
                ];
            }
        }

        $cart = ChatbotCart::firstOrCreate(['nomor_wa' => $nomorWa]);

        ChatbotCartItem::create([
            'cart_id' => $cart->id,
            'produk_id' => $produkId,
            'produk_varian_id' => $varianId,
            'jumlah' => $jumlah,
            'addons' => empty($formattedAddons) ? null : $formattedAddons,
            'catatan' => $catatan
        ]);

        return [
            'status' => 'success',
            'pesan' => "Berhasil ditambahkan ke keranjang.",
            'item_saat_ini' => $cart->items()->with('produk')->get()->toArray()
        ];
    }

    protected function checkoutPesanan($nomorWa, $args)
    {
        $cart = ChatbotCart::where('nomor_wa', $nomorWa)->first();
        if (!$cart || $cart->items->count() === 0) {
            return ['error' => 'Keranjang kosong! Pelanggan belum memesan apa-apa.'];
        }

        $namaPenerima = $args['nama_penerima'] ?? 'Pelanggan';
        $alamat = $args['alamat_penerima'] ?? 'Ambil Sendiri';
        $metode = strtolower($args['metode_pembayaran'] ?? 'cod');
        $tanggal = $args['tanggal_diambil'] ?? date('Y-m-d');
        $biayaPengantaran = (float)($args['biaya_pengantaran'] ?? 0);

        $totalBiaya = 0;
        foreach ($cart->items as $item) {
            $varian = null;
            if ($item->produk_varian_id) {
                $varian = \App\Models\ProdukVarian::find($item->produk_varian_id);
            }
            $hargaSatuan = ($varian && $varian->harga > 0) ? $varian->harga : $item->produk->harga;
            
            $addonsPrice = 0;
            if (!empty($item->addons)) {
                foreach ($item->addons as $addon) {
                    $addonsPrice += (int)$addon['harga'];
                }
            }
            $totalBiaya += (($hargaSatuan + $addonsPrice) * $item->jumlah);
        }

        $tipePengiriman = (
            stripos($alamat, 'ambil') !== false || 
            stripos($alamat, 'toko') !== false || 
            stripos($alamat, 'pickup') !== false || 
            stripos($alamat, 'pick up') !== false ||
            stripos($alamat, 'tempat') !== false
        ) ? 'ambil_sendiri' : 'kurir_toko';

        if ($tipePengiriman === 'ambil_sendiri') {
            $alamat = 'Ambil Sendiri';
            $biayaPengantaran = 0;
        }

        // Tambah ongkir
        $totalBiaya += $biayaPengantaran;

        $status = 'pending_approval'; // default cod
        if ($metode === 'qris' || $metode === 'transfer') {
            $status = 'pending_payment';
        }

        if ($tipePengiriman === 'kurir_toko' && $biayaPengantaran == 0) {
            $status = 'pending_ongkir';
        }

        $pesanan = null;
        DB::transaction(function () use ($nomorWa, $namaPenerima, $alamat, $tipePengiriman, $metode, $tanggal, $totalBiaya, $biayaPengantaran, $status, $cart, &$pesanan) {
            $pesanan = Pesanan::create([
                'nomor_order' => 'ORD-' . strtoupper(Str::random(6)),
                'nomor_wa' => $nomorWa,
                'nama_penerima' => $namaPenerima,
                'alamat_penerima' => $alamat,
                'tipe_pengiriman' => $tipePengiriman,
                'metode_pembayaran' => $metode,
                'tanggal_diambil' => $tanggal,
                'biaya_barang' => $totalBiaya - $biayaPengantaran,
                'biaya_pengantaran' => $biayaPengantaran,
                'total_biaya' => $totalBiaya,
                'uang_muka' => 0,
                'status' => $status,
                'source' => 'chatbot_ai'
            ]);

            foreach ($cart->items as $item) {
                $varian = null;
                if ($item->produk_varian_id) {
                    $varian = \App\Models\ProdukVarian::find($item->produk_varian_id);
                }
                $hargaSatuan = ($varian && $varian->harga > 0) ? $varian->harga : $item->produk->harga;

                $addonsPrice = 0;
                if (!empty($item->addons)) {
                    foreach ($item->addons as $addon) {
                        $addonsPrice += (int)$addon['harga'];
                    }
                }
                $subtotal = ($hargaSatuan + $addonsPrice) * $item->jumlah;

                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $item->produk_id,
                    'produk_varian_id' => $item->produk_varian_id,
                    'jumlah' => $item->jumlah,
                    'harga_satuan' => $hargaSatuan + $addonsPrice,
                    'subtotal' => $subtotal,
                    'addons' => $item->addons,
                    'catatan' => $item->catatan
                ]);

                // Implementasi Smart Deduction (Hibrida)
                $isMadeToOrder = false;
                if ($item->produk) {
                    $isMadeToOrder = $item->produk->is_made_to_order;
                }

                if ($isMadeToOrder) {
                    // JIKA MADE-TO-ORDER: HANYA potong bahan baku
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
                                    'keterangan' => 'Terjual via AI Chatbot (Made-to-Order) Struk #' . $pesanan->nomor_order
                                ]);
                            }
                        }
                    }
                } else {
                    // JIKA MADE-TO-STOCK: HANYA potong stok produk jadi
                    if ($item->produk_varian_id) {
                        $lockedVarian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                        if ($lockedVarian) $lockedVarian->decrement('stok', $item->jumlah);
                    } else {
                        $lockedProduk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                        if ($lockedProduk) $lockedProduk->decrement('stok', $item->jumlah);
                    }
                }
            }

            // Hapus keranjang
            $cart->items()->delete();
            $cart->delete();
        });

        // Notif Admin
        try {
            $orderService = app(\App\Services\OrderService::class);
            $daftarProduk = "";
            foreach ($pesanan->items as $item) {
                $namaProduk = $item->produk ? $item->produk->nama : 'Produk';
                $varian = $item->produkVarian ? " ({$item->produkVarian->nama_varian})" : "";
                
                $addonsLabel = "";
                if (!empty($item->addons)) {
                    $addonDetails = [];
                    foreach ($item->addons as $addon) {
                        $addonStr = $addon['nama_addon'];
                        if (!empty($addon['teks'])) {
                            $addonStr .= ' ("' . $addon['teks'] . '")';
                        }
                        $addonDetails[] = $addonStr;
                    }
                    $addonsLabel = " + Tambahan: " . implode(', ', $addonDetails);
                }
                
                if (!empty($item->catatan)) {
                    $addonsLabel .= " | Catatan: " . $item->catatan;
                }
                
                $daftarProduk .= "\n  - *{$namaProduk}{$varian}* x{$item->jumlah}{$addonsLabel}";
            }
            $tipeTeks = $pesanan->tipe_pengiriman === 'ambil_sendiri' ? 'Ambil Sendiri' : 'Kurir Toko';
            $pesanGrup = "🔔 *PESANAN BARU DARI AI*\n" .
                         "━━━━━━━━━━━━━━━━\n" .
                         "Nomor Order: *{$pesanan->nomor_order}*\n" .
                         "Pelanggan: @{$nomorWa}\n" .
                         "Nama Penerima: {$namaPenerima}\n" .
                         "Alamat: {$alamat}\n" .
                         "Tipe Pengiriman: *{$tipeTeks}*\n" .
                         "Produk: {$daftarProduk}\n" .
                         "Ongkir: Rp " . number_format($biayaPengantaran, 0, ',', '.') . "\n" .
                         "Total: Rp " . number_format($totalBiaya, 0, ',', '.') . "\n" .
                         "Metode Pembayaran: " . strtoupper($metode) . "\n" .
                         "━━━━━━━━━━━━━━━━\n";
            if ($pesanan->tipe_pengiriman === 'kurir_toko' && $biayaPengantaran == 0) {
                $pesanGrup .= "💡 Silakan tentukan ongkir via dashboard atau ketik perintah:\n`!set-ongkir {$pesanan->nomor_order} [nominal]`";
            } else {
                $pesanGrup .= "💡 Silakan setujui pesanan ini dengan perintah:\n`!setuju-order {$pesanan->nomor_order}`";
            }
            $orderService->notifikasiGrupSeller($pesanGrup);
        } catch (\Exception $e) {
            Log::error("Gagal kirim notifikasi checkout ke grup seller: " . $e->getMessage());
        }

        // Clear history context to start a fresh session on the next purchase
        ChatbotHistory::where('nomor_wa', $nomorWa)->delete();

        return [
            'status' => 'success',
            'pesan_ke_ai' => "Pesanan berhasil dibuat. Beritahu pelanggan nomor order: {$pesanan->nomor_order}, ongkos kirim Rp {$biayaPengantaran}, total biaya Rp {$totalBiaya}, dan instruksikan pembayaran sesuai metode {$metode}."
        ];
    }

    protected function batalkanKeranjang($nomorWa)
    {
        $cart = ChatbotCart::where('nomor_wa', $nomorWa)->first();
        if (!$cart) {
            return ['status' => 'success', 'pesan' => 'Keranjang memang sudah kosong.'];
        }

        $cart->items()->delete();
        $cart->delete();
 
        // Clear history context to start a fresh session
        ChatbotHistory::where('nomor_wa', $nomorWa)->delete();

        return ['status' => 'success', 'pesan' => 'Keranjang berhasil dikosongkan.'];
    }

    protected function buatReservasi($nomorWa, $args)
    {
        $namaPelanggan = $args['nama_pelanggan'] ?? null;
        $tanggalWaktu = $args['tanggal_waktu'] ?? null;
        $jumlahOrang = $args['jumlah_orang'] ?? null;
        $catatan = $args['catatan'] ?? '';

        if (!$namaPelanggan || !$tanggalWaktu || !$jumlahOrang) {
            return ['status' => 'error', 'pesan' => 'Data reservasi belum lengkap (Nama, Tanggal/Waktu, Jumlah Orang dibutuhkan).'];
        }

        $identitas = \App\Models\IdentitasToko::first();
        $isDpRequired = $identitas && $identitas->wajib_dp_reservasi ? true : false;
        // Nominal DP bisa diatur default, misalnya 50000 jika wajib DP
        $nominalDp = $isDpRequired ? 50000 : 0;

        try {
            $reservasi = \App\Models\Reservasi::create([
                'nama_pelanggan' => $namaPelanggan,
                'nomor_telepon' => $nomorWa,
                'tanggal_waktu' => $tanggalWaktu,
                'jumlah_orang' => $jumlahOrang,
                'catatan' => $catatan,
                'is_dp_required' => $isDpRequired,
                'nominal_dp' => $nominalDp,
                'status_pembayaran_dp' => 'belum_bayar',
                'status' => 'menunggu'
            ]);

            $pesan = "Reservasi berhasil diajukan dengan ID #{$reservasi->id}. Mohon tunggu konfirmasi dari Admin (atau untuk alokasi meja).";
            if ($isDpRequired) {
                $pesan .= " Sistem kami mewajibkan pembayaran Uang Muka (DP) sebesar Rp " . number_format($nominalDp, 0, ',', '.') . " untuk mengamankan meja Anda. Silakan sampaikan informasi rekening pembayaran DP kepada pelanggan.";
            }

            return [
                'status' => 'success',
                'pesan' => $pesan,
                'reservasi_id' => $reservasi->id
            ];
        } catch (\Exception $e) {
            Log::error("Error buat_reservasi: " . $e->getMessage());
            return ['status' => 'error', 'pesan' => 'Gagal membuat reservasi karena kesalahan sistem.'];
        }
    }

    protected function getSystemInstruction($context = 'customer')
    {
        $now = date('Y-m-d H:i:s');
        $identitas = \App\Models\IdentitasToko::first();
        $namaToko = $identitas && $identitas->nama_toko ? $identitas->nama_toko : 'Toko Kami';
        $rekening = $identitas && $identitas->nomor_rekening 
            ? $identitas->nomor_rekening 
            : "Hubungi admin untuk informasi pembayaran";
        
        $namaBot = $identitas && $identitas->nama_bot ? $identitas->nama_bot : 'Teta Assistant';
        $karakterBot = $identitas && $identitas->karakter_bot ? $identitas->karakter_bot : 'Customer Service Virtual (AI) ramah';
        $tagline = $identitas && $identitas->pesan_footer ? $identitas->pesan_footer : "";
        $taglineText = $tagline ? " Jika memungkinkan, sertakan motto/tagline toko kami secara natural di akhir percakapan: \"{$tagline}\"." : "";
        
        $jenisLayanan = $identitas && $identitas->jenis_layanan ? $identitas->jenis_layanan : 'keduanya';
        $isDineInSupported = in_array($jenisLayanan, ['dine_in', 'keduanya']);
        $wajibDp = $identitas && $identitas->wajib_dp_reservasi ? true : false;
        
        $reservasiText = "";
        if ($isDineInSupported) {
            $reservasiText = "\n12. FITUR RESERVASI MEJA: Karena toko ini melayani Dine-in (Makan di tempat), pelanggan bisa melakukan reservasi meja. Jika pelanggan ingin reservasi, minta: Nama, Tanggal & Jam (Pastikan jam operasional masuk akal), dan Jumlah Orang (Pax). Jika informasi tersebut sudah lengkap, panggil fungsi `buat_reservasi`.";
            if ($wajibDp) {
                $reservasiText .= " Setelah fungsi berhasil dipanggil, WAJIB informasikan kepada pelanggan bahwa mereka harus membayar Uang Muka (DP) sebesar Rp 50.000 ke rekening berikut: \"{$rekening}\" agar meja bisa dikonfirmasi.";
            } else {
                $reservasiText .= " Setelah fungsi berhasil dipanggil, informasikan bahwa reservasi sedang diproses dan menunggu konfirmasi admin (ketersediaan meja).";
            }
        }

        if ($context === 'admin') {
            return "Anda adalah '{$namaBot}', Asisten Admin Virtual cerdas untuk staf internal {$namaToko}. Waktu saat ini adalah {$now}.
Tugas Anda:
1. Menjawab pertanyaan dari Admin atau Staf Toko (misalnya mengecek stok, harga produk, atau informasi sistem).
2. Anda BUKAN Customer Service untuk pelanggan di mode ini, melainkan Asisten Staf. Jangan tawarkan menu seolah-olah mereka pembeli.
3. Anda bisa menggunakan fungsi get_katalog_produk untuk mengecek stok jika staf menanyakan persediaan barang.
4. Gunakan bahasa yang profesional namun santai ala rekan kerja.
5. Dilarang mengarang data pesanan atau produk. Selalu berdasarkan data asli.";
        }

        return "Anda adalah '{$namaBot}', {$karakterBot} untuk {$namaToko}. Waktu saat ini adalah {$now}.{$taglineText}
Tugas Anda:
1. Menyapa dengan hangat dan memanggil pelanggan 'Kak'.
2. Membantu memberikan informasi menu (gunakan get_katalog_produk). HANYA tawarkan produk yang ada di katalog. Dilarang mengarang menu atau harga sendiri! Jika stok aktif siap kirim (`stok`) kosong tetapi ada stok yang sedang diproses di dapur (`stok_proses_dapur` > 0), beritahukan kepada Kakak/pelanggan bahwa produk tersebut sedang dalam proses pembuatan di dapur dan dapat dipesan untuk dikirim nanti setelah siap dimasak/diproduksi.
3. Jika pelanggan ingin memesan, masukkan ke keranjang (gunakan tambah_ke_keranjang). Pastikan Anda bertanya detail (varian, jumlah) jika pesanan belum jelas. 
4. PENTING: Before Anda menanyakan data identitas/alamat untuk pengiriman, Anda WAJIB menyebutkan daftar pesanan yang sudah ada di keranjang mereka dan bertanya \"Apakah pesanannya sudah benar, atau ada tambahan menu lain kak?\". Tunggu jawaban mereka sebelum melanjutkan!
5. JIKA pelanggan sudah mengkonfirmasi pesanannya selesai, barulah Anda WAJIB BERTANYA SECARA EKSPLISIT 4 HAL INI SEBELUM CHECKOUT: Nama Penerima, Alamat Lengkap (atau ambil di toko / dine in), Tanggal Pengiriman/Pengambilan, dan Metode Pembayaran (QRIS/Transfer/COD).
6. TENTANG ONGKOS KIRIM: Beritahu pelanggan bahwa jika barang dikirim, nominal ongkos kirim akan dicek dan diinformasikan oleh Admin *setelah* pesanan masuk (melalui chat terpisah). Saat checkout, kosongkan biaya_pengantaran.
7. Lakukan proses checkout (gunakan checkout_pesanan) JIKA DAN HANYA JIKA data pesanan sudah lengkap.
8. Jika pelanggan ingin membatalkan keranjang belanjanya (sebelum dicheckout), gunakan fungsi batalkan_keranjang.
9. Jika pelanggan ingin MEMBATALKAN pesanan yang SUDAH DIBUAT/CHECKOUT, gunakan fungsi batalkan_pesanan. Jika pelanggan ingin MENGUBAH detail pesanan yang SUDAH DIBUAT/CHECKOUT (seperti mengubah alamat, tipe pengiriman dari ambil di toko menjadi diantar, tanggal pengambilan, nama penerima, atau metode pembayaran), gunakan fungsi ubah_pesanan. JANGAN gunakan checkout_pesanan lagi.
10. Setelah checkout sukses, berikan ringkasan pesanan, nomor order. Jika tipe pengiriman adalah Kurir Toko, beritahu pelanggan untuk menunggu konfirmasi ongkir dari Admin. Jika pesanan berupa Ambil Sendiri dan pembayarannya QRIS atau Transfer, berikan nomor rekening/bank berikut agar pelanggan dapat mentransfer pembayaran: \"{$rekening}\".
11. INFORMASI REKENING & PEMBAYARAN: Jika pelanggan menanyakan nomor rekening, informasi transfer bank, atau pembayaran menggunakan QRIS kapan saja (baik sebelum memesan, saat memesan, atau setelah selesai memesan), Anda wajib memberikan rincian nomor rekening berikut: \"{$rekening}\". Sampaikan juga bahwa jika mereka ingin membayar menggunakan QRIS, gambar kode QRIS pembayaran akan otomatis terkirim bersamaan dengan pesan tersebut.{$reservasiText}";
    }
}
