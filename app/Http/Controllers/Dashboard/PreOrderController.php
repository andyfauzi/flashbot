<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Pesanan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PreOrderController extends Controller
{
    public function index(Request $request)
    {
        $tanggal = $request->input('tanggal', date('Y-m-d'));
        $search = $request->input('search');

        $status = $request->input('status', 'aktif'); // Default to aktif for Kitchen

        // Ambil pesanan yang belum selesai/dibatalkan pada tanggal tersebut
        $query = Pesanan::with(['items.produk', 'kurir', 'meja'])
            ->whereNotNull('tanggal_diambil')
            ->whereDate('tanggal_diambil', $tanggal);
            
        if ($status === 'aktif') {
            // Di kitchen, lunas/paid belum tentu selesai dimasak, jadi masih masuk aktif.
            $query->whereNotIn('status', ['completed', 'selesai', 'dikirim', 'cancelled', 'batal', 'dibatalkan']);
        } elseif ($status === 'selesai') {
            // Selesai hanya jika benar-benar completed/selesai (sudah menekan tombol selesai)
            $query->whereIn('status', ['completed', 'selesai', 'dikirim']);
        } elseif ($status === 'batal') {
            $query->whereIn('status', ['cancelled', 'batal', 'dibatalkan']);
        } else {
            // 'semua'
            $query->whereNotIn('status', ['cancelled', 'batal', 'dibatalkan']); // usually hide cancelled from all
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_order', 'like', '%' . $search . '%')
                  ->orWhere('nama_penerima', 'like', '%' . $search . '%')
                  ->orWhere('nomor_wa', 'like', '%' . $search . '%');
            });
        }

        $pesanans = $query->orderBy('id', 'desc')->get();

        $kurirs = \App\Models\Kurir::orderBy('nama', 'asc')->get();
        $identitas = \App\Models\IdentitasToko::first();

        // Ambil semua produk aktif beserta varian dan addon untuk form PO Manual
        $semuaProduk = \App\Models\Produk::with(['varians', 'addons'])
            ->where('aktif', true)
            ->get();

        return view('dashboard.preorder.index', compact('pesanans', 'tanggal', 'kurirs', 'search', 'identitas', 'status', 'semuaProduk'));
    }

    public function setDp(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'uang_muka' => 'required|numeric|min:0'
        ]);

        DB::transaction(function () use ($request, $pesanan) {
            $pesanan->update([
                'uang_muka' => $request->uang_muka,
                'status' => 'paid_sebagian'
            ]);

            // --- INTEGRASI ARUS KAS & SHIFT ---
            if ($request->uang_muka > 0) {
                $shift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
                if (config('flashbot.features.finance')) {
                    \App\Models\CashFlow::create([
                        'user_id' => auth()->id(),
                        'shift_id' => $shift ? $shift->id : null,
                        'tanggal' => now()->toDateString(),
                        'tipe' => 'in',
                        'kategori' => 'Pelunasan DP PO',
                        'nominal' => $request->uang_muka,
                        'keterangan' => 'Pembayaran DP untuk PO #' . $pesanan->nomor_order,
                    ]);
                }

                if ($shift && $pesanan->metode_pembayaran === 'tunai') {
                    $shift->increment('total_penjualan_tunai', $request->uang_muka);
                }
            }
            // ----------------------------------
        });

        $this->kirimStrukWa($pesanan, 'DP');

        // Kirim notifikasi ke kurir setelah DP dicatat
        if ($pesanan->tipe_pengiriman === 'kurir_toko' && $pesanan->kurir) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->kirimNotifikasiKurir($pesanan);
        }

        return back()->with('sukses', 'Uang muka (DP) berhasil dicatat.');
    }

    public function setOngkir(Request $request, Pesanan $pesanan)
    {
        $request->validate([
            'tipe_pengiriman' => 'required|in:ambil_sendiri,kurir_toko',
            'alamat_penerima' => 'nullable|string',
            'biaya_pengantaran' => 'nullable|numeric|min:0',
            'kurir_id' => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.kurirs,id',
            'nomor_hp' => 'nullable|string|max:20'
        ]);

        $tipe = $request->tipe_pengiriman;
        $ongkir = $tipe === 'ambil_sendiri' ? 0 : (float)($request->biaya_pengantaran ?? 0);
        $alamat = $tipe === 'ambil_sendiri' ? 'Ambil Sendiri' : ($request->alamat_penerima ?? $pesanan->alamat_penerima ?? 'Diantar');

        // Hitung total baru
        $totalBaru = $pesanan->biaya_barang + $ongkir;

        DB::transaction(function () use ($tipe, $alamat, $ongkir, $pesanan, $totalBaru, $request) {
            $updateData = [
                'tipe_pengiriman' => $tipe,
                'alamat_penerima' => $alamat,
                'biaya_pengantaran' => $ongkir,
                'total_biaya' => $totalBaru,
                'kurir_id' => $tipe === 'ambil_sendiri' ? null : ($request->kurir_id ?: null),
                'nomor_hp' => $tipe === 'ambil_sendiri' ? null : ($request->nomor_hp ?: null)
            ];

            // Jika sebelumnya pending_ongkir dan sekarang diset kurir dengan ongkir > 0, pindahkan ke pending_payment
            if ($pesanan->status === 'pending_ongkir' && $tipe === 'kurir_toko' && $ongkir > 0) {
                $updateData['status'] = 'pending_payment';
            }
            
            // Jika diubah menjadi ambil_sendiri dan metode bayar QRIS/Transfer, status disesuaikan ke pending_payment. Jika COD, ke pending_approval
            if ($tipe === 'ambil_sendiri') {
                if (in_array($pesanan->metode_pembayaran, ['qris', 'transfer'])) {
                    $updateData['status'] = 'pending_payment';
                } else {
                    $updateData['status'] = 'pending_approval';
                }
            }

            $pesanan->update($updateData);
        });

        $pesanan->load('kurir', 'items.produk', 'items.produkVarian');

        if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
            try {
                $waService = app(\App\Services\WhatsAppService::class);
                
                $namaPenerima = $pesanan->nama_penerima ?: 'Kak';
                $nomorOrder = $pesanan->nomor_order;
                $biayaBarangFmt = number_format($pesanan->biaya_barang, 0, ',', '.');
                $ongkirFmt = number_format($ongkir, 0, ',', '.');
                $totalBaruFmt = number_format($totalBaru, 0, ',', '.');
                
                if ($tipe === 'ambil_sendiri') {
                    $pesan = "Halo *{$namaPenerima}*,\n\n" .
                             "Admin telah menyesuaikan pesanan Anda (Order: *{$nomorOrder}*).\n\n" .
                             "Pesanan diatur untuk *Ambil Sendiri di Toko* (Tanpa Ongkir).\n" .
                             "- 🛍️ Barang: Rp {$biayaBarangFmt}\n" .
                             "- 💰 Total Tagihan: *Rp {$totalBaruFmt}*\n\n";
                } else {
                    $pesan = "Halo *{$namaPenerima}*,\n\n" .
                             "Admin telah memverifikasi pesanan Anda (Order: *{$nomorOrder}*).\n\n" .
                             "Rincian Pesanan:\n" .
                             "- 🛍️ Barang: Rp {$biayaBarangFmt}\n" .
                             "- 🚚 Ongkir: Rp {$ongkirFmt}\n" .
                             "- 💰 Total Tagihan: *Rp {$totalBaruFmt}*\n\n";
                             
                    if ($pesanan->kurir) {
                        $pesan .= "Pesanan Anda akan diantar oleh kurir kami: *{$pesanan->kurir->nama}* (Nomor HP/WA: +{$pesanan->kurir->nomor_hp}).\n\n";
                    }
                }
                
                // Kirim tugas ke Kurir jika ditugaskan DAN pembayaran sudah terkonfirmasi
                if ($tipe === 'kurir_toko' && $pesanan->kurir && in_array($pesanan->status, ['paid', 'paid_sebagian', 'approved'])) {
                    $orderService = app(\App\Services\OrderService::class);
                    $orderService->kirimNotifikasiKurir($pesanan);
                }
                
                // Cek status chatbot user
                $chatbotUser = \App\Models\ChatbotUser::where('nomor', $pesanan->nomor_wa)->first();
                $isIdle = !$chatbotUser || in_array($chatbotUser->langkah, ['menu', 'halo', '0', null]) || str_starts_with($chatbotUser->langkah, 'order_menunggu_bukti_') || str_starts_with($chatbotUser->langkah, 'order_bayar_ongkir_');
                
                if (empty($pesanan->metode_pembayaran)) {
                    if ($isIdle && $chatbotUser) {
                        $chatbotUser->update(['langkah' => 'order_bayar_ongkir_' . $pesanan->id]);
                    }
                    
                    $pesan .= "Silakan pilih metode pembayaran untuk pesanan Anda:\n" .
                              "[1] QRIS (E-Wallet)\n" .
                              "[2] Transfer Bank (Manual)\n\n" .
                              "Balas dengan angka pilihan Anda (1/2):";
                              
                    $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                } else {
                    $metode = $pesanan->metode_pembayaran;
                    if ($isIdle && $chatbotUser) {
                        $chatbotUser->update(['langkah' => 'order_menunggu_bukti_' . $pesanan->id]);
                    }
                    $waService = new \App\Services\WhatsAppService();
                    $identitas = \App\Models\IdentitasToko::first();
                    $total = number_format($pesanan->total_biaya, 0, ',', '.');
                    
                    // Cek Xendit
                    if ($identitas && $identitas->is_payment_gateway_active && $identitas->xendit_api_key && in_array($metode, ['qris', 'transfer'])) {
                        $xenditService = new \App\Services\XenditService($identitas->xendit_api_key);
                        // Prefix tenant ID agar webhook bisa mengidentifikasi tenant
                        $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : null;
                        $externalId = $tenantId ? "{$tenantId}-{$pesanan->nomor_order}" : $pesanan->nomor_order;
                        $invoiceUrl = $xenditService->createInvoice(
                            $externalId,
                            $pesanan->total_biaya,
                            "Pembayaran Tagihan {$pesanan->nomor_order}",
                            ['name' => $pesanan->nama_penerima, 'phone' => $pesanan->nomor_wa]
                        );
                        if ($invoiceUrl) {
                            $pesan .= "💳 *Pembayaran Digital Otomatis*\n\nSilakan klik link berikut untuk membayar:\n👉 {$invoiceUrl}\n\nSistem akan otomatis memverifikasi pembayaran Anda.";
                            $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                            return back()->with('sukses', 'Pengaturan pengiriman berhasil diperbarui dan WhatsApp konfirmasi telah dikirim ke pelanggan.');
                        }
                    }

                    if ($metode === 'qris') {
                        $qrisImage = config('chatbot.qris_image_url', '');
                        $identitas = \App\Models\IdentitasToko::first();
                        $qrisUrl = null;

                        if (!empty($qrisImage)) {
                            $qrisUrl = $qrisImage;
                        } elseif ($identitas && $identitas->qris_path && file_exists(public_path('storage/' . $identitas->qris_path))) {
                            $qrisUrl = config('chatbot.ngrok_public_url') 
                                ? rtrim(config('chatbot.ngrok_public_url'), '/') . '/storage/' . $identitas->qris_path
                                : url('storage/' . $identitas->qris_path);
                        } elseif (file_exists(public_path('uploads/qris.png'))) {
                            $qrisUrl = config('chatbot.ngrok_public_url') 
                                ? rtrim(config('chatbot.ngrok_public_url'), '/') . '/uploads/qris.png'
                                : url('/uploads/qris.png');
                        }
                        
                        // Kirim pesan teks utama terlebih dahulu agar terjamin sampai ke pelanggan
                        $pesanLengkap = $pesan . "Silakan scan kode QRIS pembayaran Anda.\n\nSetelah pembayaran berhasil, *kirim foto bukti pembayaran/transfer di sini* untuk verifikasi. Terima kasih! 🌸";
                        $waService->kirimPesan($pesanan->nomor_wa, $pesanLengkap);
                        
                        // Kirim media QRIS secara terpisah agar tidak memblokir pesan utama jika ada kendala download media
                        if ($qrisUrl) {
                            $namaToko = $identitas->nama_toko ?? 'Toko Anda';
                            $waService->kirimPesan($pesanan->nomor_wa, "QRIS Pembayaran {$namaToko}:", $qrisUrl, 'image');
                        } else {
                            $bankInfo = ($identitas && $identitas->nomor_rekening) 
                                ? $identitas->nomor_rekening 
                                : config('chatbot.bank_transfer_info', "Bank BCA\nNo Rekening: 123456789\na/n Toko Tenanta.id");
                            $pesanRek = "⚠️ QRIS Belum terkonfigurasi. Silakan transfer ke rekening bank berikut:\n\n{$bankInfo}";
                            $waService->kirimPesan($pesanan->nomor_wa, $pesanRek);
                        }
                    } elseif ($metode === 'transfer') {
                        $identitas = \App\Models\IdentitasToko::first();
                        $bankInfo = ($identitas && $identitas->nomor_rekening) 
                            ? $identitas->nomor_rekening 
                            : config('chatbot.bank_transfer_info', "Bank BCA\nNo Rekening: 123456789\na/n Toko Tenanta.id");
                        $pesan .= "🏦 *Silakan transfer ke rekening bank berikut*:\n\n{$bankInfo}\n\nSetelah transfer berhasil, *kirim foto bukti transfer di sini* untuk verifikasi. Terima kasih! 🌸";
                        $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                    } else {
                        // COD/lainnya
                        $pesan .= "Metode Pembayaran: *COD*.\n\nTerima kasih! 🌸";
                        $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                    }
                }
            } catch (\Exception $e) {
                // Abaikan jika gagal
                \Illuminate\Support\Facades\Log::error("Gagal kirim WhatsApp setOngkir: " . $e->getMessage());
            }
        }

        return back()->with('sukses', 'Pengaturan pengiriman berhasil diperbarui dan WhatsApp konfirmasi telah dikirim ke pelanggan.');
    }

    public function lunas(Pesanan $pesanan)
    {
        $sisaBayar = $pesanan->total_biaya - $pesanan->uang_muka;

        DB::transaction(function () use ($pesanan, $sisaBayar) {
            $pesanan->update([
                'uang_muka' => $pesanan->total_biaya,
                'status' => 'paid'
            ]);

            // --- INTEGRASI ARUS KAS & SHIFT ---
            if ($sisaBayar > 0) {
                $shift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
                if (config('flashbot.features.finance')) {
                    \App\Models\CashFlow::create([
                        'user_id' => auth()->id(),
                        'shift_id' => $shift ? $shift->id : null,
                        'tanggal' => now()->toDateString(),
                        'tipe' => 'in',
                        'kategori' => 'Pelunasan PO',
                        'nominal' => $sisaBayar,
                        'keterangan' => 'Pelunasan PO #' . $pesanan->nomor_order,
                    ]);
                }

                if ($shift && $pesanan->metode_pembayaran === 'tunai') {
                    $shift->increment('total_penjualan_tunai', $sisaBayar);
                }
            }
            // ----------------------------------
        });

        $this->kirimStrukWa($pesanan, 'LUNAS');

        // Kirim notifikasi ke kurir setelah dilunasi
        if ($pesanan->tipe_pengiriman === 'kurir_toko' && $pesanan->kurir) {
            $orderService = app(\App\Services\OrderService::class);
            $orderService->kirimNotifikasiKurir($pesanan);
        }

        return back()->with('sukses', 'Pesanan berhasil dilunasi.');
    }

    public function batal(Pesanan $pesanan)
    {
        DB::transaction(function () use ($pesanan) {
            // 1. Ubah status pesanan menjadi cancelled
            $pesanan->update([
                'status' => 'cancelled'
            ]);

            // 2. Kembalikan (restock) jumlah barang
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
                                    'user_id' => auth()->id() ?? null,
                                    'tipe' => 'koreksi',
                                    'qty' => $qtyDibutuhkan,
                                    'keterangan' => 'Batal PO (Made-to-Order) Struk #' . $pesanan->nomor_order
                                ]);
                            }
                        }
                    }
                } else {
                    if ($item->produk_varian_id) {
                        $varian = \App\Models\ProdukVarian::lockForUpdate()->find($item->produk_varian_id);
                        if ($varian) {
                            $varian->increment('stok', $item->jumlah);
                        }
                    } else {
                        $produk = \App\Models\Produk::lockForUpdate()->find($item->produk_id);
                        if ($produk) {
                            $produk->increment('stok', $item->jumlah);
                        }
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
                                                'user_id' => auth()->id() ?? null,
                                                'tipe' => 'koreksi',
                                                'qty' => $qtyDibutuhkan,
                                                'keterangan' => 'Batal PO (Add-on) Struk #' . $pesanan->nomor_order
                                            ]);
                                        }
                                    }
                                }
                            }
                        }
                    }
                }
            }
        });

        // 3. Notifikasi ke WhatsApp Grup Seller
        try {
            $waService = app(\App\Services\WhatsAppService::class);
            $orderService = app(\App\Services\OrderService::class);
            $pesanGrup = "❌ *PESANAN DIBATALKAN*\n" .
                         "Nomor Order: *{$pesanan->nomor_order}*\n" .
                         "Telah dibatalkan oleh Admin melalui Dashboard. Stok barang otomatis telah dikembalikan.";
            $orderService->notifikasiGrupSeller($pesanGrup);
            
            // 4. Notifikasi ke Pelanggan (jika ada nomor WA)
            if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
                $pesanPelanggan = "Mohon maaf, pesanan Anda dengan Nomor Order *{$pesanan->nomor_order}* telah *DIBATALKAN* oleh Admin.\n\nJika ini adalah kesalahan, silakan hubungi kami kembali.";
                // WhatsAppService sendText method doesn't exist, it uses kirimPesanSekarang or kirimPesan directly
                $waService->kirimPesan($pesanan->nomor_wa, $pesanPelanggan);
            }
        } catch (\Exception $e) {
            // Abaikan jika WA gagal
        }

        return back()->with('sukses', 'Pesanan berhasil dibatalkan dan stok dikembalikan.');
    }

    public function selesai(Pesanan $pesanan)
    {
        DB::transaction(function () use ($pesanan) {
            $pesanan->update([
                'status' => 'selesai'
            ]);
        });

        if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
            try {
                $waService = app(\App\Services\WhatsAppService::class);
                $tenant = app('current_tenant');
                $namaToko = $tenant->identitas_toko->nama_toko ?? $tenant->name ?? 'Toko Kami';
                $pesan = "Halo *" . ($pesanan->nama_penerima ?: 'Kak') . "*! 👋\n\nTerima kasih sudah berbelanja di *{$namaToko}*. Pesanan Anda telah selesai/diambil.\n\nBagaimana rasa produk kami? Mohon balas pesan ini dengan angka *1 sampai 5* (1=Sangat Kurang, 5=Sangat Enak) beserta ulasan singkatnya ya Kak. Masukan Kakak sangat berarti bagi kami! 🍰💖";
                $waService->kirimPesan($pesanan->nomor_wa, $pesan);
            } catch (\Exception $e) {
                // Abaikan jika gagal
            }
        }

        return back()->with('sukses', 'Pesanan diselesaikan. Permintaan ulasan otomatis terkirim ke WhatsApp pelanggan.');
    }

    private function kirimStrukWa(Pesanan $pesanan, $jenis)
    {
        if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
            try {
                $waService = app(\App\Services\WhatsAppService::class);
                $sisa = max(0, $pesanan->total_biaya - $pesanan->uang_muka);
                $statusPesan = $jenis === 'LUNAS' ? "LUNAS" : "Uang Muka (DP)";
                
                $tenant = app('current_tenant');
                $namaToko = $tenant->identitas_toko->nama_toko ?? $tenant->name ?? 'Toko Kami';
                
                $antrianTeks = $pesanan->nomor_antrian ? "Nomor Antrian: *{$pesanan->nomor_antrian}*\n" : "";

                $pesan = "Halo *" . ($pesanan->nama_penerima ?: 'Pelanggan') . "*,\n\nTerima kasih, pembayaran Anda untuk pesanan di *{$namaToko}* telah kami terima.\n\nBerikut rincian pembayaran Anda:\n{$antrianTeks}Nomor Order: *{$pesanan->nomor_order}*\nTotal Pesanan: *Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "*\nStatus: *$statusPesan*\nNominal Dibayar: *Rp " . number_format($pesanan->uang_muka, 0, ',', '.') . "*\nSisa Tagihan: *Rp " . number_format($sisa, 0, ',', '.') . "*\n\nIni adalah struk digital bukti sah pembayaran Anda. Pesanan akan disiapkan sesuai jadwal yang ditentukan. Terima kasih! 🌸";
                
                $waService->kirimPesan($pesanan->nomor_wa, $pesan);
            } catch (\Exception $e) {
                // Abaikan jika gagal
            }
        }
    }

    public function kirimNotifikasiSiap(Pesanan $pesanan)
    {
        if ($pesanan->is_ready_notified) {
            return back()->with('error', 'Notifikasi siap sudah pernah dikirimkan sebelumnya.');
        }

        $pesanan->update([
            'is_ready_notified' => true
        ]);

        if ($pesanan->nomor_wa && $pesanan->nomor_wa !== '-') {
            try {
                $waService = app(\App\Services\WhatsAppService::class);
                $orderService = app(\App\Services\OrderService::class);
                
                $tipe = (stripos($pesanan->tipe_pengiriman, 'ambil') !== false) ? 'siap diambil' : 'sedang menunggu pengiriman kurir';
                
                $pesan = "Halo *" . ($pesanan->nama_penerima ?: 'Pelanggan') . "*! 👋\n\n";
                $pesan .= "Pesanan Anda dengan Nomor Order *{$pesanan->nomor_order}* telah selesai diproses dan *{$tipe}*.\n\n";
                
                if ($pesanan->sisa_pembayaran > 0) {
                    $pesan .= "Pesanan Anda belum lunas (Sisa tagihan: *Rp " . number_format($pesanan->sisa_pembayaran, 0, ',', '.') . "*). Anda dapat melakukan pelunasan saat mengambil pesanan.\n\n";
                }
                
                $pesan .= "Kami akan mengirimkan rincian pesanan dan struk PDF Anda sesaat lagi. Terima kasih! 🌸";
                
                // Kirim notifikasi teks
                $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                
                // Kirim struk PDF menggunakan method yang baru dibuat di OrderService via Job
                \App\Jobs\GenerateStrukPdfJob::dispatch($pesanan->nomor_wa, $pesanan->nomor_order);
                
            } catch (\Exception $e) {
                // Abaikan jika gagal
                \Illuminate\Support\Facades\Log::error("Gagal kirim notifikasi siap: " . $e->getMessage());
            }
        }

        return back()->with('sukses', 'Notifikasi pesanan siap beserta Struk PDF telah dikirim ke WhatsApp pelanggan.');
    }

    public function storeManual(Request $request)
    {
        $request->validate([
            'nama_penerima' => 'required|string|max:150',
            'nomor_wa' => 'nullable|string|max:25',
            'tanggal_diambil' => 'required|date',
            'jam_diambil' => 'required|string',
            'tipe_pengiriman' => 'required|in:ambil_sendiri,kurir_toko',
            'alamat_penerima' => 'nullable|string',
            'uang_muka' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
        ]);

        try {
            DB::beginTransaction();

            $nomorOrder = 'PO-' . strtoupper(substr(uniqid(), -6));
            $tanggalDiambil = $request->tanggal_diambil . ' ' . $request->jam_diambil . ':00';
            
            $uangMuka = (float)($request->uang_muka ?? 0);

            $pesanan = Pesanan::create([
                'nomor_order' => $nomorOrder,
                'nama_penerima' => $request->nama_penerima,
                'nomor_wa' => $request->nomor_wa ?? '-',
                'tanggal_diambil' => $tanggalDiambil,
                'tipe_pengiriman' => $request->tipe_pengiriman,
                'alamat_penerima' => $request->tipe_pengiriman === 'ambil_sendiri' ? 'Ambil Sendiri' : $request->alamat_penerima,
                'biaya_pengantaran' => 0, // bisa di set nanti via setOngkir
                'uang_muka' => $uangMuka,
                'metode_pembayaran' => 'tunai', // default tunai untuk manual input
                'status' => $uangMuka > 0 ? 'paid_sebagian' : 'pending_payment',
                'source' => 'manual',
            ]);

            $totalBiayaBarang = 0;

            foreach ($request->items as $itemData) {
                $itemData = json_decode($itemData, true); // Since it comes from a hidden input array of JSON strings
                if (!$itemData) continue;
                
                $produkId = $itemData['produk_id'];
                $varianId = $itemData['varian_id'] ?? null;
                $qty = (int)$itemData['qty'];
                $catatan = $itemData['catatan'] ?? null;
                
                $produk = \App\Models\Produk::find($produkId);
                if (!$produk) continue;

                $harga = (float)($produk->harga ?? 0);
                $namaVarian = null;

                if ($varianId) {
                    $varian = \App\Models\ProdukVarian::find($varianId);
                    if ($varian) {
                        $harga = ($varian->harga > 0) ? (float)$varian->harga : $harga;
                        $namaVarian = $varian->nama_varian;
                    }
                }

                $subtotal = $harga * $qty;
                $totalBiayaBarang += $subtotal;

                // Hitung addon
                $addonList = [];
                if (!empty($itemData['addons'])) {
                    foreach ($itemData['addons'] as $addonId) {
                        $addon = \App\Models\ProdukAddon::find($addonId);
                        if ($addon) {
                            $addonList[] = [
                                'id' => $addon->id,
                                'nama' => $addon->nama_addon,
                                'harga' => $addon->harga_tambahan
                            ];
                            $subtotal += ($addon->harga_tambahan * $qty);
                            $totalBiayaBarang += ($addon->harga_tambahan * $qty);
                        }
                    }
                }

                \App\Models\PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $produk->id,
                    'produk_varian_id' => $varianId,
                    'jumlah' => $qty,
                    'harga_satuan' => $harga,
                    'subtotal' => $subtotal,
                    'catatan' => $catatan,
                    'addon_details' => empty($addonList) ? null : json_encode($addonList)
                ]);
            }

            $pesanan->update([
                'biaya_barang' => $totalBiayaBarang,
                'total_biaya' => $totalBiayaBarang
            ]);

            // Jika ada DP, catat ke CashFlow
            if ($uangMuka > 0 && config('flashbot.features.finance')) {
                $shift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
                \App\Models\CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift ? $shift->id : null,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'in',
                    'kategori' => 'DP Pre-Order',
                    'nominal' => $uangMuka,
                    'keterangan' => 'DP PO Manual #' . $pesanan->nomor_order,
                ]);
                if ($shift) {
                    $shift->increment('total_penjualan_tunai', $uangMuka);
                }
            }

            DB::commit();
            return redirect()->back()->with('sukses', 'Pesanan PO Manual berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return redirect()->back()->with('error', 'Gagal menambahkan pesanan: ' . $e->getMessage());
        }
    }

    public function syncXendit(Pesanan $pesanan)
    {
        if (!in_array($pesanan->status, ['pending_payment', 'pending_approval'])) {
            return back()->with('error', 'Status pesanan tidak memerlukan pengecekan pembayaran manual.');
        }

        $identitas = \App\Models\IdentitasToko::first();
        if (!$identitas || !$identitas->xendit_api_key) {
            return back()->with('error', 'Integrasi Xendit belum dikonfigurasi.');
        }

        try {
            $xenditService = new \App\Services\XenditService($identitas->xendit_api_key);
            $invoices = $xenditService->getInvoices(['external_id' => $pesanan->nomor_order]);

            if (empty($invoices)) {
                return back()->with('error', 'Invoice Xendit tidak ditemukan untuk nomor order ini.');
            }

            $invoice = collect($invoices)->first();
            $status = strtoupper($invoice['status']);

            if ($status === 'PAID' || $status === 'SETTLED') {
                return $this->lunas($pesanan);
            }

            return back()->with('sukses', "Status pembayaran di Xendit masih: {$status}.");
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Manual Sync Xendit Gagal: " . $e->getMessage());
            return back()->with('error', 'Gagal menghubungi server Xendit: ' . $e->getMessage());
        }
    }
}
