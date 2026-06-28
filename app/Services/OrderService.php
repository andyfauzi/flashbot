<?php

namespace App\Services;

use App\Models\ChatbotOrderSession;
use App\Models\ChatbotUser;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Produk;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class OrderService
{
    protected WhatsAppService $wa;

    public function __construct(WhatsAppService $wa)
    {
        $this->wa = $wa;
    }

    /**
     * Memulai alur belanja dari menu utama
     */
    public function mulaiBelanja(string $nomor, ChatbotUser $user): void
    {
        // Cek pesanan yang menggantung
        $pendingOrder = \App\Models\Pesanan::where('nomor_wa', $nomor)
            ->whereIn('status', ['pending_ongkir', 'pending_payment'])
            ->first();

        if ($pendingOrder) {
            $this->wa->kirimPesan($nomor, "⚠️ Maaf, Anda masih memiliki pesanan yang belum diselesaikan (Nomor: *{$pendingOrder->nomor_order}*).\n\nSilakan selesaikan pembayaran pesanan tersebut, atau ketik *batal* jika ingin membatalkannya sebelum membuat pesanan baru.");
            return;
        }

        $user->update(['langkah' => 'order_pilih_produk']);
        
        // Hapus sesi draf lama jika ada
        ChatbotOrderSession::where('nomor_wa', $nomor)->delete();
        ChatbotOrderSession::create([
            'nomor_wa' => $nomor,
            'langkah'  => 'pilih_produk'
        ]);

        $this->kirimDaftarProduk($nomor);
    }

    /**
     * Mengirimkan daftar produk ke pelanggan
     */
    protected function kirimDaftarProduk(string $nomor): void
    {
        $produks = Produk::with('varians')->where('aktif', true)->orderBy('id')->get();
        // Hanya tampilkan produk yang punya varian dengan stok > 0, atau punya stok > 0
        $produksTersedia = [];
        foreach ($produks as $p) {
            $stokTotal = $p->varians->sum('stok');
            if ($stokTotal > 0) {
                $p->stok = $stokTotal; // Update temporary property
                $produksTersedia[] = $p;
            }
        }

        if (empty($produksTersedia)) {
            $this->wa->kirimPesan($nomor, "⚠️ Maaf, saat ini belum ada produk yang tersedia atau stok sedang kosong. Silakan hubungi admin nanti.");
            // Reset langkah ke menu
            $user = ChatbotUser::where('nomor', $nomor)->first();
            if ($user) $user->update(['langkah' => 'menu']);
            return;
        }

        $teks = "🛍️ *Daftar Produk Toko Kami:*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";
        
        foreach ($produksTersedia as $i => $p) {
            $num = $i + 1;
            $harga = number_format($p->harga, 0, ',', '.');
            $teks .= "[{$num}] *{$p->nama}*\n";
            $teks .= "    ↳ Kode: {$p->kode}\n";
            $teks .= "    ↳ Harga: Rp {$harga}\n";
            $teks .= "    ↳ Stok Total: {$p->stok} pcs\n";
            
            if ($p->varians->count() > 1) {
                $teks .= "    ↳ Varian: ";
                $varList = [];
                foreach ($p->varians as $v) {
                    if ($v->stok > 0) $varList[] = $v->nama_varian;
                }
                $teks .= implode(', ', $varList) . "\n";
            }
            $teks .= "\n";
        }
        
        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Balas dengan *nomor pilihan Anda (angka)* untuk membeli, atau ketik *batal* untuk kembali ke menu utama.";

        $this->wa->kirimPesan($nomor, $teks);
    }

    /**
     * Mengirimkan daftar produk ke pelanggan (Hanya Info Stok)
     */
    public function kirimInfoStok(string $nomor): void
    {
        $produks = Produk::with('varians')->where('aktif', true)->orderBy('id')->get();
        $produksTersedia = [];
        foreach ($produks as $p) {
            $stokTotal = $p->varians->sum('stok');
            $stokDapurTotal = $p->varians->sum('stok_proses_dapur');
            if ($stokTotal > 0 || $stokDapurTotal > 0) {
                $p->stok = $stokTotal; 
                $p->stok_dapur = $stokDapurTotal;
                $produksTersedia[] = $p;
            }
        }

        if (empty($produksTersedia)) {
            $this->wa->kirimPesan($nomor, "⚠️ Maaf, saat ini belum ada produk yang tersedia atau stok sedang kosong. Silakan hubungi admin nanti.");
            return;
        }

        $teks = "📦 *Informasi Stok Produk (Ready & Proses Dapur):*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n\n";
        
        foreach ($produksTersedia as $i => $p) {
            $harga = number_format($p->harga, 0, ',', '.');
            $teks .= "🔹 *{$p->nama}*\n";
            $teks .= "    ↳ Kode: {$p->kode}\n";
            $teks .= "    ↳ Harga: Rp {$harga}\n";
            
            $infoStok = "    ↳ Stok Total: {$p->stok} pcs";
            if ($p->stok_dapur > 0) {
                $infoStok .= " _(sedang diproses di dapur: {$p->stok_dapur} pcs)_";
            }
            $teks .= $infoStok . "\n";
            
            if ($p->varians->count() > 1) {
                $teks .= "    ↳ Detail Varian:\n";
                foreach ($p->varians as $v) {
                    if ($v->stok > 0 || $v->stok_proses_dapur > 0) {
                        $teks .= "        - {$v->nama_varian} (Stok: {$v->stok} pcs";
                        if ($v->stok_proses_dapur > 0) {
                            $teks .= ", dapur: {$v->stok_proses_dapur} pcs";
                        }
                        $teks .= ")\n";
                    }
                }
            }
            $teks .= "\n";
        }
        
        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Ketik *order* jika Anda ingin melakukan pemesanan, atau ketik *menu* untuk kembali ke layanan utama.";

        $this->wa->kirimPesan($nomor, $teks);
    }

    /**
     * Memproses alur pemesanan berdasarkan state saat ini
     */
    public function prosesOrderFlow(string $nomor, string $teks, ?string $mediaUrl = null, ?string $mediaType = null): void
    {
        $user = ChatbotUser::where('nomor', $nomor)->first();
        if (!$user) return;

        $state = $user->langkah;
        $teksClean = trim($teks);
        $teksLower = strtolower($teksClean);

        // Keyword Batal
        if (in_array($teksLower, ['batal', 'cancel', 'reset'])) {
            ChatbotOrderSession::where('nomor_wa', $nomor)->delete();
            $user->update(['langkah' => 'menu']);

            // Cari dan batalkan pesanan yang masih menggantung
            $pendingOrders = \App\Models\Pesanan::where('nomor_wa', $nomor)
                ->whereIn('status', ['pending_ongkir', 'pending_payment', 'pending_approval'])
                ->get();

            if ($pendingOrders->count() > 0) {
                foreach ($pendingOrders as $pendingOrder) {
                    $pendingOrder->update(['status' => 'cancelled']);
                    $this->notifikasiGrupSeller("🚫 *Pesanan Dibatalkan Pelanggan*\nPesanan *{$pendingOrder->nomor_order}* telah dibatalkan oleh pelanggan.");
                }
                $this->wa->kirimPesan($nomor, "✅ Sesi belanja dan pesanan Anda yang belum lunas telah dibatalkan.\n\nKetik *menu* untuk melihat layanan lainnya.");
            } else {
                $this->wa->kirimPesan($nomor, "✅ Sesi belanja dibatalkan.\n\nKetik *menu* untuk melihat layanan lainnya.");
            }
            return;
        }

        // Keyword Bayar
        if (str_starts_with($teksLower, 'bayar')) {
            // Cari pesanan yang siap dibayar
            $pendingPayment = \App\Models\Pesanan::where('nomor_wa', $nomor)
                ->where('status', 'pending_payment')
                ->first();

            if ($pendingPayment) {
                if ($pendingPayment->metode_pembayaran) {
                    $user->update(['langkah' => 'order_menunggu_bukti_' . $pendingPayment->id]);
                    $this->kirimInstruksiPembayaran($nomor, $pendingPayment);
                } else {
                    $user->update(['langkah' => 'order_bayar_ongkir_' . $pendingPayment->id]);
                    $this->wa->kirimPesan($nomor,
                        "Silakan pilih metode pembayaran untuk pesanan *{$pendingPayment->nomor_order}*:\n" .
                        "[1] QRIS (E-Wallet)\n" .
                        "[2] Transfer Bank (Manual)\n\n" .
                        "Balas dengan angka pilihan Anda (1/2):"
                    );
                }
            } else {
                $this->wa->kirimPesan($nomor, "⚠️ Anda tidak memiliki pesanan yang siap dibayar saat ini.");
            }
            return;
        }

        // Cari sesi draf order
        $session = ChatbotOrderSession::where('nomor_wa', $nomor)->first();
        if (!$session && !str_starts_with($state, 'order_bayar_ongkir_') && !str_starts_with($state, 'order_menunggu_bukti_')) {
            // Jika sesi draf hilang tetapi user dalam state order, buat ulang sesi
            $session = ChatbotOrderSession::create([
                'nomor_wa' => $nomor,
                'langkah'  => 'pilih_produk'
            ]);
            $user->update(['langkah' => 'order_pilih_produk']);
            $state = 'order_pilih_produk';
        }

        // Mesin Status Alur Pemesanan
        switch ($state) {
            case 'order_pilih_produk':
                $this->handlePilihProduk($nomor, $teksClean, $user, $session);
                break;

            case 'order_pilih_varian':
                $this->handlePilihVarian($nomor, $teksClean, $user, $session);
                break;

            case 'order_pilih_addon':
                $this->handlePilihAddon($nomor, $teksClean, $user, $session);
                break;

            case 'order_input_teks_addon':
                $this->handleInputTeksAddon($nomor, $teksClean, $user, $session);
                break;

            case 'order_input_jumlah':
                $this->handleInputJumlah($nomor, $teksClean, $user, $session);
                break;

            case 'order_input_tanggal':
                $this->handleInputTanggal($nomor, $teksClean, $user, $session);
                break;

            case 'order_input_nama':
                $this->handleInputNama($nomor, $teksClean, $user, $session);
                break;

            case 'order_input_alamat':
                $this->handleInputAlamat($nomor, $teksClean, $user, $session);
                break;

            case 'order_pilih_pengiriman':
                $this->handlePilihPengiriman($nomor, $teksClean, $user, $session);
                break;

            case 'order_pilih_pembayaran':
                $this->handlePilihPembayaran($nomor, $teksClean, $user, $session);
                break;

            default:
                // Handle state dinamis seperti order_bayar_ongkir_{id} atau order_menunggu_bukti_{id}
                if (str_starts_with($state, 'order_bayar_ongkir_')) {
                    $orderId = substr($state, strlen('order_bayar_ongkir_'));
                    $this->handleBayarOngkir($nomor, $teksClean, $user, $orderId);
                } elseif (str_starts_with($state, 'order_menunggu_bukti_')) {
                    $orderId = substr($state, strlen('order_menunggu_bukti_'));
                    $this->handleMenungguBukti($nomor, $teksClean, $mediaUrl, $mediaType, $user, $orderId);
                } else {
                    // Fallback
                    $user->update(['langkah' => 'menu']);
                    $this->wa->kirimPesan($nomor, "Maaf, terjadi kesalahan sesi belanja. Silakan ketik *menu*.");
                }
                break;
        }
    }

    /**
     * Handler: Pilih Produk
     */
    protected function handlePilihProduk(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $produks = Produk::with('varians')->where('aktif', true)->orderBy('id')->get();
        // Filter lagi
        $produksTersedia = [];
        foreach ($produks as $p) {
            if ($p->varians->sum('stok') > 0) {
                $produksTersedia[] = $p;
            }
        }
        $index = ((int)$teks) - 1;

        if ($index >= 0 && $index < count($produksTersedia)) {
            $produk = $produksTersedia[$index];
            $varians = $produk->varians()->where('stok', '>', 0)->get();

            if ($varians->count() > 1) {
                // Produk punya banyak varian
                $session->update([
                    'produk_id' => $produk->id,
                    'langkah'   => 'pilih_varian'
                ]);
                $user->update(['langkah' => 'order_pilih_varian']);

                $msg = "Anda memilih:\n🛍️ *{$produk->nama}*\n\n";
                
                if ($produk->size_chart) {
                    $msg .= "ℹ️ *Info Varian/Size Chart:*\n" . $produk->size_chart . "\n\n";
                }

                $msg .= "Silakan pilih varian/ukuran:\n";
                foreach ($varians as $vIdx => $v) {
                    $vNum = $vIdx + 1;
                    $msg .= "[{$vNum}] {$v->nama_varian} (Stok: {$v->stok})\n";
                }
                
                // Kirim pesan teks dulu
                $this->wa->kirimPesan($nomor, $msg);

                // Kirim foto spesifik varian jika ada
                foreach ($varians as $vIdx => $v) {
                    if ($v->foto) {
                        $mediaUrl = env('NGROK_PUBLIC_URL') 
                            ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $v->foto
                            : url('storage/' . $v->foto);
                        $vNum = $vIdx + 1;
                        $this->wa->kirimPesan($nomor, "Foto Varian [{$vNum}]: {$v->nama_varian}", $mediaUrl, 'image');
                    }
                }

                $this->wa->kirimPesan($nomor, "Balas dengan *nomor pilihan varian (angka)* di atas:");

            } else {
                // Hanya 1 varian (All size)
                $varianId = $varians->first()->id ?? null;
                $stok = $varians->first()->stok ?? 0;
                $varianFoto = $varians->first()->foto ?? null;
                $foto = $varianFoto ?: $produk->foto;
                
                $harga = number_format($produk->harga, 0, ',', '.');
                $msg = "Anda memilih produk:\n🛍️ *{$produk->nama}*\n";
                
                if ($produk->size_chart) {
                    $msg .= "ℹ️ *Keterangan:*\n" . $produk->size_chart . "\n";
                }

                $mediaUrl = null;
                if ($foto) {
                    $mediaUrl = env('NGROK_PUBLIC_URL') 
                        ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $foto
                        : url('storage/' . $foto);
                }

                if ($produk->addons()->count() > 0) {
                    $session->update([
                        'produk_id' => $produk->id,
                        'produk_varian_id' => $varianId,
                        'langkah'   => 'pilih_addon'
                    ]);
                    $user->update(['langkah' => 'order_pilih_addon']);
                    
                    $msg .= "Harga: Rp {$harga}\nStok: {$stok} pcs\n\n";
                    $msg .= "🌟 *Pilih Menu Tambahan (Add-ons)*\n(Balas dengan *angka*. Bisa pilih lebih dari satu, pisahkan dengan koma. Contoh: *1,2*. Jika tidak perlu, balas *0*):\n";
                    
                    foreach ($produk->addons as $aIdx => $addon) {
                        $aNum = $aIdx + 1;
                        $addonHarga = number_format($addon->harga, 0, ',', '.');
                        $msg .= "[{$aNum}] {$addon->nama_addon} (+Rp {$addonHarga})\n";
                    }
                    $msg .= "[0] Tidak perlu tambahan\n";
                } else {
                    $session->update([
                        'produk_id' => $produk->id,
                        'produk_varian_id' => $varianId,
                        'langkah'   => 'input_jumlah'
                    ]);
                    $user->update(['langkah' => 'order_input_jumlah']);

                    $msg .= "Harga: Rp {$harga}\nStok: {$stok} pcs\n\nSilakan masukkan *jumlah (quantity)* yang ingin dibeli (angka):";
                }

                $mediaUrl = null;
                if ($foto) {
                    $mediaUrl = env('NGROK_PUBLIC_URL') 
                        ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $foto
                        : url('storage/' . $foto);
                }

                $this->wa->kirimPesan($nomor, $msg, $mediaUrl, $mediaUrl ? 'image' : null);
            }
        } else {
            $this->wa->kirimPesan($nomor, "❌ Pilihan tidak valid. Silakan masukkan nomor produk yang tertera pada daftar di atas.");
        }
    }

    /**
     * Handler: Pilih Varian
     */
    protected function handlePilihVarian(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $produk = $session->produk;
        if (!$produk) {
            $this->wa->kirimPesan($nomor, "Terjadi kesalahan sesi. Kembali ke pemilihan produk.");
            $this->mulaiBelanja($nomor, $user);
            return;
        }

        $varians = $produk->varians()->where('stok', '>', 0)->get();
        $index = ((int)$teks) - 1;

        if ($index >= 0 && $index < count($varians)) {
            $varian = $varians[$index];

            $harga = number_format($produk->harga, 0, ',', '.');
            $msg = "Anda memilih varian:\n" .
                   "📌 *{$varian->nama_varian}*\n" .
                   "Harga: Rp {$harga}\n" .
                   "Stok Tersedia: {$varian->stok} pcs\n\n";

            if ($produk->addons()->count() > 0) {
                $session->update([
                    'produk_varian_id' => $varian->id,
                    'langkah'          => 'pilih_addon'
                ]);
                $user->update(['langkah' => 'order_pilih_addon']);
                
                $msg .= "🌟 *Pilih Menu Tambahan (Add-ons)*\n(Balas dengan *angka*. Bisa pilih lebih dari satu, pisahkan dengan koma. Contoh: *1,2*. Jika tidak perlu, balas *0*):\n";
                
                foreach ($produk->addons as $aIdx => $addon) {
                    $aNum = $aIdx + 1;
                    $addonHarga = number_format($addon->harga, 0, ',', '.');
                    $msg .= "[{$aNum}] {$addon->nama_addon} (+Rp {$addonHarga})\n";
                }
                $msg .= "[0] Tidak perlu tambahan\n";
            } else {
                $session->update([
                    'produk_varian_id' => $varian->id,
                    'langkah'          => 'input_jumlah'
                ]);
                $user->update(['langkah' => 'order_input_jumlah']);

                $msg .= "Silakan masukkan *jumlah (quantity)* yang ingin dibeli (angka):";
            }

            $foto = $varian->foto ?: $produk->foto;
            $mediaUrl = null;
            if ($foto) {
                $mediaUrl = env('NGROK_PUBLIC_URL') 
                    ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $foto
                    : url('storage/' . $foto);
            }

            $this->wa->kirimPesan($nomor, $msg, $mediaUrl, $mediaUrl ? 'image' : null);
        } else {
            $this->wa->kirimPesan($nomor, "❌ Pilihan varian tidak valid. Silakan balas dengan angka yang ada pada daftar.");
        }
    }

    /**
     * Handler: Pilih Addon
     */
    protected function handlePilihAddon(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $produk = $session->produk;
        if (!$produk) {
            $this->wa->kirimPesan($nomor, "Terjadi kesalahan sesi. Kembali ke pemilihan produk.");
            $this->mulaiBelanja($nomor, $user);
            return;
        }

        $addonsList = $produk->addons;
        $selectedAddons = [];
        $requiresText = false;
        $addonRequiringText = null;

        if (trim($teks) !== '0') {
            $choices = explode(',', str_replace(' ', '', $teks));
            foreach ($choices as $choice) {
                $index = ((int)$choice) - 1;
                if ($index >= 0 && $index < count($addonsList)) {
                    $addonModel = $addonsList[$index];
                    $selectedAddons[] = [
                        'id' => $addonModel->id,
                        'nama_addon' => $addonModel->nama_addon,
                        'harga' => $addonModel->harga,
                        'butuh_teks' => $addonModel->butuh_teks,
                        'teks' => null
                    ];
                    if ($addonModel->butuh_teks) {
                        $requiresText = true;
                        if (!$addonRequiringText) {
                            $addonRequiringText = $addonModel->nama_addon;
                        }
                    }
                }
            }
        }

        if ($requiresText) {
            $session->update([
                'addons'  => $selectedAddons,
                'langkah' => 'input_teks_addon'
            ]);
            $user->update(['langkah' => 'order_input_teks_addon']);

            $msg = "";
            if (count($selectedAddons) > 0) {
                $msg .= "✅ *Tambahan dicatat:*\n";
                foreach ($selectedAddons as $sa) {
                    $harga = number_format($sa['harga'], 0, ',', '.');
                    $msg .= "- {$sa['nama_addon']} (+Rp {$harga})\n";
                }
                $msg .= "\n";
            }
            $msg .= "Anda memilih tambahan *{$addonRequiringText}*.\nSilakan masukkan ucapan atau teks yang ingin dituliskan:";
            $this->wa->kirimPesan($nomor, $msg);
        } else {
            $session->update([
                'addons'  => $selectedAddons,
                'langkah' => 'input_jumlah'
            ]);
            $user->update(['langkah' => 'order_input_jumlah']);

            $msg = "";
            if (count($selectedAddons) > 0) {
                $msg .= "✅ *Tambahan dicatat:*\n";
                foreach ($selectedAddons as $sa) {
                    $harga = number_format($sa['harga'], 0, ',', '.');
                    $msg .= "- {$sa['nama_addon']} (+Rp {$harga})\n";
                }
                $msg .= "\n";
            }

            $msg .= "Silakan masukkan *jumlah pesanan (quantity)* yang ingin dibeli (angka):";
            $this->wa->kirimPesan($nomor, $msg);
        }
    }

    /**
     * Handler: Input Teks Addon
     */
    protected function handleInputTeksAddon(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $addons = $session->addons ?? [];
        
        // Apply the text to all addons that require it and don't have it yet
        // For simplicity, we just apply this same text to all addons that need text.
        $updatedAddons = [];
        foreach ($addons as $addon) {
            if (isset($addon['butuh_teks']) && $addon['butuh_teks']) {
                $addon['teks'] = trim($teks);
            }
            $updatedAddons[] = $addon;
        }

        $session->update([
            'addons'  => $updatedAddons,
            'langkah' => 'input_jumlah'
        ]);
        $user->update(['langkah' => 'order_input_jumlah']);

        $this->wa->kirimPesan($nomor, "✅ Teks ucapan dicatat: _\"{$teks}\"_\n\nSilakan masukkan *jumlah pesanan (quantity)* yang ingin dibeli (angka):");
    }

    /**
     * Handler: Input Jumlah
     */
    protected function handleInputJumlah(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $jumlah = (int)$teks;
        $produk = $session->produk;
        $varian = $session->produkVarian;

        if (!$produk || !$varian) {
            $this->wa->kirimPesan($nomor, "Terjadi kesalahan sesi. Kembali ke pemilihan produk.");
            $this->mulaiBelanja($nomor, $user);
            return;
        }

        if ($jumlah <= 0) {
            $this->wa->kirimPesan($nomor, "❌ Jumlah pesanan harus berupa angka di atas 0. Silakan input ulang jumlah:");
            return;
        }

        if ($jumlah > $varian->stok) {
            $this->wa->kirimPesan($nomor, "❌ Stok tidak mencukupi. Stok tersedia saat ini: *{$varian->stok} pcs*. Silakan masukkan jumlah yang sesuai:");
            return;
        }

        $session->update([
            'jumlah'  => $jumlah,
            'langkah' => 'input_tanggal'
        ]);

        $user->update(['langkah' => 'order_input_tanggal']);
        $this->wa->kirimPesan($nomor, 
            "Jumlah pesanan dicatat: *{$jumlah} pcs*.\n\n" .
            "Kapan pesanan ini akan diambil/dikirim?\n" .
            "(Contoh balas: *Besok*, *Lusa*, *Hari ini*, atau ketik tanggal misal *15/08/2026*):"
        );
    }

    /**
     * Handler: Input Tanggal Pengambilan
     */
    protected function handleInputTanggal(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        if (empty(trim($teks))) {
            $this->wa->kirimPesan($nomor, "❌ Tanggal tidak boleh kosong. Silakan masukkan kapan pesanan akan diambil/dikirim:");
            return;
        }

        $teksLower = strtolower(trim($teks));
        $tanggalDiambil = null;

        if ($teksLower === 'hari ini' || $teksLower === 'hariini' || $teksLower === 'sekarang') {
            $tanggalDiambil = date('Y-m-d');
        } elseif ($teksLower === 'besok') {
            $tanggalDiambil = date('Y-m-d', strtotime('+1 day'));
        } elseif ($teksLower === 'lusa') {
            $tanggalDiambil = date('Y-m-d', strtotime('+2 days'));
        } else {
            // Coba parsing manual format DD/MM/YYYY atau DD-MM-YYYY
            $teksFormatted = str_replace('/', '-', $teks);
            $parsedDate = strtotime($teksFormatted);
            if ($parsedDate) {
                $tanggalDiambil = date('Y-m-d', $parsedDate);
            } else {
                $this->wa->kirimPesan($nomor, "❌ Format tanggal tidak dikenali. Silakan ketik *Besok*, *Lusa*, atau gunakan format *Hari/Bulan/Tahun* (Contoh: 15/08/2026):");
                return;
            }
        }

        $session->update([
            'tanggal_diambil' => $tanggalDiambil,
            'langkah'         => 'input_nama'
        ]);

        $user->update(['langkah' => 'order_input_nama']);
        
        $tanggalStr = date('d-m-Y', strtotime($tanggalDiambil));
        $this->wa->kirimPesan($nomor, "Jadwal dicatat: *{$tanggalStr}*.\n\nSilakan masukkan *Nama Penerima*:");
    }

    /**
     * Handler: Input Nama Penerima
     */
    protected function handleInputNama(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        if (empty(trim($teks))) {
            $this->wa->kirimPesan($nomor, "❌ Nama penerima tidak boleh kosong. Silakan masukkan nama penerima:");
            return;
        }

        $session->update([
            'nama_penerima' => $teks,
            'langkah'       => 'input_alamat'
        ]);

        $user->update(['langkah' => 'order_input_alamat']);
        $this->wa->kirimPesan($nomor, "Nama penerima: *{$teks}*.\n\nSilakan masukkan *Alamat Lengkap Pengiriman*:");
    }

    /**
     * Handler: Input Alamat Lengkap
     */
    protected function handleInputAlamat(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        if (empty(trim($teks))) {
            $this->wa->kirimPesan($nomor, "❌ Alamat tidak boleh kosong. Silakan masukkan alamat lengkap pengiriman:");
            return;
        }

        $session->update([
            'alamat_penerima' => $teks,
            'langkah'         => 'pilih_pengiriman'
        ]);

        $user->update(['langkah' => 'order_pilih_pengiriman']);
        $this->wa->kirimPesan($nomor, 
            "Alamat penerima: *{$teks}*.\n\n" .
            "Silakan pilih metode pengiriman:\n" .
            "[1] Diantar Kurir Toko (Ongkir dikonfirmasi admin)\n" .
            "[2] Diambil Kurir Anda (Gojek/Grab pesan sendiri)\n" .
            "[3] Ambil Sendiri ke Toko\n\n" .
            "Balas dengan angka pilihan Anda (1/2/3):"
        );
    }

    /**
     * Handler: Pilih Pengiriman
     */
    protected function handlePilihPengiriman(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $pilihan = trim($teks);
        $produk = $session->produk;
        $jumlah = $session->jumlah;
        $varian = null;
        if ($session->produk_varian_id) {
            $varian = \App\Models\ProdukVarian::find($session->produk_varian_id);
        }
        $hargaSatuan = ($varian && $varian->harga > 0) ? $varian->harga : $produk->harga;
        
        $addonsInfo = $session->addons ?? [];
        $hargaAddonTotal = 0;
        $addonsLabel = "";
        if (!empty($addonsInfo)) {
            foreach ($addonsInfo as $a) {
                $hargaAddonTotal += (int)$a['harga'];
                $addonsLabel .= $a['nama_addon'] . ', ';
            }
        }
        
        // Perhitungan Harga Promo Bundle (Pilihan A)
        $totalBiayaProduk = 0;
        if (!empty($produk->promo_min_qty) && !empty($produk->promo_harga) && $jumlah >= $produk->promo_min_qty) {
            $jmlPaket = (int)floor($jumlah / $produk->promo_min_qty);
            $sisaItem = $jumlah % $produk->promo_min_qty;
            $totalBiayaProduk = ($jmlPaket * $produk->promo_harga) + ($sisaItem * $hargaSatuan);
        } else {
            $totalBiayaProduk = $hargaSatuan * $jumlah;
        }

        // Addon tidak ikut didiskon, dihitung per item
        $totalBiayaAddon = $hargaAddonTotal * $jumlah;
        
        $hargaSatuan += $hargaAddonTotal; // Untuk disimpan di database sebagai harga_satuan (rata-rata)
        $biayaBarang = $totalBiayaProduk + $totalBiayaAddon;

        if ($pilihan === '1') {
            // Tipe: KURIR TOKO (ongkir ditentukan oleh driver di grup)
            $session->update([
                'tipe_pengiriman' => 'kurir_toko',
                'biaya_pengantaran' => 0
            ]);

            // Buat pesanan riil status: pending_ongkir
            $nomorOrder = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));
            
            $pesanan = null;
            DB::transaction(function () use ($nomorOrder, $nomor, $session, $biayaBarang, $hargaSatuan, $jumlah, &$pesanan) {
                $pesanan = Pesanan::create([
                    'nomor_order'        => $nomorOrder,
                    'nomor_wa'           => $nomor,
                    'nama_penerima'      => $session->nama_penerima,
                    'alamat_penerima'    => $session->alamat_penerima,
                    'tipe_pengiriman'    => 'kurir_toko',
                    'tanggal_diambil'    => $session->tanggal_diambil,
                    'biaya_barang'       => $biayaBarang,
                    'biaya_pengantaran'  => 0,
                    'total_biaya'        => $biayaBarang,
                    'status'             => 'pending_ongkir'
                ]);

                $hppSnapshot = 0;
                $varian = \App\Models\ProdukVarian::find($session->produk_varian_id);
                if ($varian) {
                    $hppSnapshot = $varian->hpp + $varian->overhead_cost;
                }

                PesananItem::create([
                    'pesanan_id'       => $pesanan->id,
                    'produk_id'        => $session->produk_id,
                    'produk_varian_id' => $session->produk_varian_id,
                    'jumlah'           => $jumlah,
                    'harga_satuan'     => $hargaSatuan,
                    'subtotal'         => $biayaBarang,
                    'hpp_snapshot'     => $hppSnapshot,
                    'addons'           => $addonsInfo
                ]);
            });

            // Hapus sesi draft
            $session->delete();
            $user->update(['langkah' => 'menu']); // Kembalikan ke menu utama sementara

            $antrianTeks = $pesanan->nomor_antrian ? " (Antrian: *{$pesanan->nomor_antrian}*)" : "";
            $this->wa->kirimPesan($nomor, 
                "✅ Pesanan Anda telah kami catat dengan nomor order: *{$nomorOrder}*{$antrianTeks}.\n\n" .
                "Mohon tunggu beberapa saat, admin sedang menghitung ongkos kirim ke alamat Anda. Kami akan segera mengirimkan rincian pembayaran setelah ongkos kirim ditentukan."
            );

            // Hubungi Seller Group
            $this->notifikasiGrupSeller(
                "🔔 *PESANAN BARU MASUK (Menunggu Ongkir)*\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "Nomor Order: *{$nomorOrder}*\n" .
                ($pesanan->nomor_antrian ? "🎫 Antrian: *{$pesanan->nomor_antrian}*\n" : "") .
                "Pelanggan: @{$nomor}\n" .
                "Nama Penerima: {$pesanan->nama_penerima}\n" .
                "Alamat Kirim: {$pesanan->alamat_penerima}\n" .
                "Tipe: Kurir Toko\n" .
                "Tanggal Kirim/Ambil: " . ($pesanan->tanggal_diambil ? date('d-m-Y', strtotime($pesanan->tanggal_diambil)) : '-') . "\n" .
                "Produk: *{$produk->nama}* ({$jumlah} pcs)\n" .
                (!empty($addonsLabel) ? "Tambahan: " . rtrim($addonsLabel, ', ') . "\n" : "") .
                "Subtotal: Rp " . number_format($biayaBarang, 0, ',', '.') . "\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "💡 Silakan tentukan ongkos kirim dengan perintah:\n" .
                "`!set-ongkir {$nomorOrder} [nominal_ongkir]`\n" .
                "Contoh: `!set-ongkir {$nomorOrder} 15000`"
            );

        } elseif ($pilihan === '2' || $pilihan === '3') {
            // Tipe: KURIR CUSTOMER ATAU AMBIL DI TEMPAT
            $tipe = $pilihan === '2' ? 'kurir_customer' : 'ambil_sendiri';
            $labelOngkir = $pilihan === '2' ? 'Kurir Anda (Rp 0)' : 'Ambil di Tempat (Rp 0)';

            $session->update([
                'tipe_pengiriman' => $tipe,
                'biaya_pengantaran' => 0,
                'langkah' => 'pilih_pembayaran'
            ]);

            $user->update(['langkah' => 'order_pilih_pembayaran']);

            $hargaBarangFmt = number_format($biayaBarang, 0, ',', '.');
            $this->wa->kirimPesan($nomor,
                "Rincian Belanja Anda:\n" .
                "🛍️ Produk: *{$produk->nama}* ({$jumlah} pcs) = Rp {$hargaBarangFmt}\n" .
                (!empty($addonsLabel) ? "➕ Tambahan: " . rtrim($addonsLabel, ', ') . "\n" : "") .
                "🚚 Ongkir: {$labelOngkir}\n" .
                "💰 Total Bayar: *Rp {$hargaBarangFmt}*\n\n" .
                "Silakan pilih metode pembayaran:\n" .
                "[1] QRIS (E-Wallet)\n" .
                "[2] Transfer Bank (Manual)\n" .
                "[3] Bayar Saat Pengambilan / COD\n\n" .
                "Balas dengan angka pilihan Anda (1/2/3):"
            );
        } else {
            $this->wa->kirimPesan($nomor, "❌ Pilihan tidak valid. Silakan balas dengan angka *1*, *2*, atau *3*.");
        }
    }

    /**
     * Handler: Pilih Pembayaran (Ambil di tempat / COD)
     */
    protected function handlePilihPembayaran(string $nomor, string $teks, ChatbotUser $user, ChatbotOrderSession $session): void
    {
        $pilihan = trim($teks);
        $produk = $session->produk;
        $jumlah = $session->jumlah;
        $varian = null;
        if ($session->produk_varian_id) {
            $varian = \App\Models\ProdukVarian::find($session->produk_varian_id);
        }
        $hargaSatuan = ($varian && $varian->harga > 0) ? $varian->harga : $produk->harga;
        
        $addonsInfo = $session->addons ?? [];
        $hargaAddonTotal = 0;
        $addonsLabel = "";
        if (!empty($addonsInfo)) {
            foreach ($addonsInfo as $a) {
                $hargaAddonTotal += (int)$a['harga'];
                $addonsLabel .= $a['nama_addon'] . ', ';
            }
        }

        // Perhitungan Harga Promo Bundle (Pilihan A)
        $totalBiayaProduk = 0;
        if (!empty($produk->promo_min_qty) && !empty($produk->promo_harga) && $jumlah >= $produk->promo_min_qty) {
            $jmlPaket = (int)floor($jumlah / $produk->promo_min_qty);
            $sisaItem = $jumlah % $produk->promo_min_qty;
            $totalBiayaProduk = ($jmlPaket * $produk->promo_harga) + ($sisaItem * $hargaSatuan);
        } else {
            $totalBiayaProduk = $hargaSatuan * $jumlah;
        }

        // Addon tidak ikut didiskon, dihitung per item
        $totalBiayaAddon = $hargaAddonTotal * $jumlah;
        
        $hargaSatuan += $hargaAddonTotal; // Untuk disimpan di database sebagai harga_satuan (rata-rata)
        $biayaBarang = $totalBiayaProduk + $totalBiayaAddon;

        if (in_array($pilihan, ['1', '2'])) {
            $metode = $pilihan === '1' ? 'qris' : 'transfer';
            $nomorOrder = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $pesanan = null;
            DB::transaction(function () use ($nomorOrder, $nomor, $session, $biayaBarang, $hargaSatuan, $jumlah, $metode, &$pesanan) {
                $pesanan = Pesanan::create([
                    'nomor_order'        => $nomorOrder,
                    'nomor_wa'           => $nomor,
                    'nama_penerima'      => $session->nama_penerima,
                    'alamat_penerima'    => $session->alamat_penerima,
                    'tipe_pengiriman'    => 'ambil_sendiri',
                    'tanggal_diambil'    => $session->tanggal_diambil,
                    'biaya_barang'       => $biayaBarang,
                    'biaya_pengantaran'  => 0,
                    'total_biaya'        => $biayaBarang,
                    'metode_pembayaran'  => $metode,
                    'status'             => 'pending_payment'
                ]);

                $hppSnapshot = 0;
                $varian = \App\Models\ProdukVarian::find($session->produk_varian_id);
                if ($varian) {
                    $hppSnapshot = $varian->hpp + $varian->overhead_cost;
                }

                PesananItem::create([
                    'pesanan_id'       => $pesanan->id,
                    'produk_id'        => $session->produk_id,
                    'produk_varian_id' => $session->produk_varian_id,
                    'jumlah'           => $jumlah,
                    'harga_satuan'     => $hargaSatuan,
                    'subtotal'         => $biayaBarang,
                    'hpp_snapshot'     => $hppSnapshot,
                    'addons'           => $session->addons
                ]);
            });

            $session->delete();
            $user->update(['langkah' => 'order_menunggu_bukti_' . $pesanan->id]);

            $this->kirimInstruksiPembayaran($nomor, $pesanan);

        } elseif ($pilihan === '3') {
            // Bayar Saat Pengambilan
            $nomorOrder = 'ORD-' . date('Ymd') . '-' . strtoupper(substr(uniqid(), -4));

            $pesanan = null;
            DB::transaction(function () use ($nomorOrder, $nomor, $session, $biayaBarang, $hargaSatuan, $jumlah, &$pesanan) {
                $pesanan = Pesanan::create([
                    'nomor_order'        => $nomorOrder,
                    'nomor_wa'           => $nomor,
                    'nama_penerima'      => $session->nama_penerima,
                    'alamat_penerima'    => $session->alamat_penerima,
                    'tipe_pengiriman'    => 'ambil_sendiri',
                    'tanggal_diambil'    => $session->tanggal_diambil,
                    'biaya_barang'       => $biayaBarang,
                    'biaya_pengantaran'  => 0,
                    'total_biaya'        => $biayaBarang,
                    'metode_pembayaran'  => 'cod',
                    'status'             => 'pending_approval' // Menunggu disetujui admin untuk COD
                ]);

                $hppSnapshot = 0;
                $varian = \App\Models\ProdukVarian::find($session->produk_varian_id);
                if ($varian) {
                    $hppSnapshot = $varian->hpp + $varian->overhead_cost;
                }

                PesananItem::create([
                    'pesanan_id'       => $pesanan->id,
                    'produk_id'        => $session->produk_id,
                    'produk_varian_id' => $session->produk_varian_id,
                    'jumlah'           => $jumlah,
                    'harga_satuan'     => $hargaSatuan,
                    'subtotal'         => $biayaBarang,
                    'hpp_snapshot'     => $hppSnapshot,
                    'addons'           => $session->addons
                ]);
            });

            $session->delete();
            $user->update(['langkah' => 'menu']);

            $hargaBarangFmt = number_format($biayaBarang, 0, ',', '.');
            $antrianTeks = $pesanan->nomor_antrian ? "Nomor Antrian: *{$pesanan->nomor_antrian}*\n" : "";
            $this->wa->kirimPesan($nomor,
                "✅ *Pesanan Berhasil Dibuat!*\n" .
                "{$antrianTeks}Nomor Order: *{$nomorOrder}*\n" .
                "Total Tagihan: *Rp {$hargaBarangFmt}*\n" .
                "Metode Pembayaran: *Bayar Saat Pengambilan (COD)*\n\n" .
                "Pesanan Anda saat ini sedang dalam proses verifikasi persetujuan admin. Kami akan segera mengirimkan konfirmasi jika pesanan siap diambil."
            );

            // Hubungi Seller Group
            $this->notifikasiGrupSeller(
                "🔔 *PESANAN COD / AMBIL DI TEMPAT BARU*\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "Nomor Order: *{$nomorOrder}*\n" .
                ($pesanan->nomor_antrian ? "🎫 Antrian: *{$pesanan->nomor_antrian}*\n" : "") .
                "Pelanggan: @{$nomor}\n" .
                "Nama Penerima: {$pesanan->nama_penerima}\n" .
                "Produk: *{$produk->nama}* ({$jumlah} pcs)\n" .
                (!empty($addonsLabel) ? "Tambahan: " . rtrim($addonsLabel, ', ') . "\n" : "") .
                "Total Bayar: Rp {$hargaBarangFmt}\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "💡 Silakan setujui pesanan ini agar stok terpotong menggunakan perintah:\n" .
                "`!setuju-order {$nomorOrder}`"
            );

        } else {
            $this->wa->kirimPesan($nomor, "❌ Pilihan tidak valid. Silakan balas dengan *1* (QRIS), *2* (Transfer Bank), atau *3* (Ambil di tempat / COD).");
        }
    }

    /**
     * Handler: Pilihan Pembayaran Setelah Ongkir Driver Ditentukan
     */
    protected function handleBayarOngkir(string $nomor, string $teks, ChatbotUser $user, $orderId): void
    {
        $pesanan = Pesanan::find($orderId);
        if (!$pesanan) {
            $this->wa->kirimPesan($nomor, "Pesanan tidak ditemukan. Kembali ke menu utama.");
            $user->update(['langkah' => 'menu']);
            return;
        }

        $pilihan = trim($teks);

        if (in_array($pilihan, ['1', '2'])) {
            $metode = $pilihan === '1' ? 'qris' : 'transfer';
            $pesanan->update([
                'metode_pembayaran' => $metode,
                'status'            => 'pending_payment'
            ]);

            $user->update(['langkah' => 'order_menunggu_bukti_' . $pesanan->id]);
            $this->kirimInstruksiPembayaran($nomor, $pesanan);
        } else {
            $this->wa->kirimPesan($nomor, "❌ Pilihan tidak valid. Silakan balas dengan *1* (QRIS) atau *2* (Transfer Bank).");
        }
    }

    /**
     * Handler: Menunggu Upload Bukti Pembayaran (Image)
     */
    protected function handleMenungguBukti(string $nomor, string $teks, ?string $mediaUrl, ?string $mediaType, ChatbotUser $user, $orderId): void
    {
        $pesanan = Pesanan::find($orderId);
        if (!$pesanan) {
            $this->wa->kirimPesan($nomor, "Pesanan tidak ditemukan. Sesi berakhir.");
            $user->update(['langkah' => 'menu']);
            return;
        }

        // Cek jika user mengunggah foto/gambar
        if ($mediaType === 'image' && !empty($mediaUrl)) {
            $pesanan->update([
                'bukti_pembayaran' => $mediaUrl,
                'status'           => 'pending' // Menunggu verifikasi pembayaran oleh admin
            ]);

            $user->update(['langkah' => 'menu']);

            $this->wa->kirimPesan($nomor,
                "✅ *Bukti pembayaran berhasil diunggah!*\n\n" .
                "Terima kasih. Bukti transfer Anda saat ini sedang diverifikasi oleh admin. Kami akan segera memberi tahu Anda jika status pembayaran telah dikonfirmasi."
            );

            // Hubungi Seller Group
            $item = $pesanan->items()->first();
            $namaBarang = $item && $item->produk ? $item->produk->nama : 'Barang';
            $jumlah = $item ? $item->jumlah : 0;
            $totalFmt = number_format($pesanan->total_biaya, 0, ',', '.');

            $this->notifikasiGrupSeller(
                "🔔 *BUKTI TRANSFER DIUNGGAH (Menunggu Konfirmasi)*\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "Nomor Order: *{$pesanan->nomor_order}*\n" .
                "Pelanggan: @{$nomor}\n" .
                "Produk: {$namaBarang} ({$jumlah} pcs)\n" .
                "Total Tagihan: Rp {$totalFmt}\n" .
                "Metode: " . strtoupper($pesanan->metode_pembayaran) . "\n" .
                "━━━━━━━━━━━━━━━━\n" .
                "💡 Gambar bukti transfer telah diunggah. Silakan verifikasi bukti tersebut di dashboard atau via Whatsapp.\n\n" .
                "Ketik perintah berikut di grup jika pembayaran valid:\n" .
                "`!konfirmasi-bayar {$pesanan->nomor_order}`"
            );
        } else {
            $this->wa->kirimPesan($nomor, "⚠️ Mohon kirimkan bukti pembayaran Anda dalam bentuk *FOTO/GAMBAR*, atau ketik *batal* jika ingin membatalkan pesanan.");
        }
    }

    /**
     * Mengirimkan detail instruksi pembayaran ke pembeli
     */
    protected function kirimInstruksiPembayaran(string $nomor, Pesanan $pesanan): void
    {
        $total = number_format($pesanan->total_biaya, 0, ',', '.');
        $identitas = \App\Models\IdentitasToko::first();

        // Cek apakah payment gateway Xendit aktif
        if ($identitas && $identitas->is_payment_gateway_active && $identitas->xendit_api_key) {
            $xenditService = new \App\Services\XenditService($identitas->xendit_api_key);
            $namaPelanggan = $pesanan->nama_penerima ?? 'Pelanggan';
            $deskripsi = "Pembayaran Tagihan {$pesanan->nomor_order}";
            
            // Prefix tenant ID ke external_id agar webhook bisa mengidentifikasi tenant
            $tenantId = app()->bound('current_tenant') ? app('current_tenant')->id : null;
            $externalId = $tenantId ? "{$tenantId}-{$pesanan->nomor_order}" : $pesanan->nomor_order;
            
            $invoiceUrl = $xenditService->createInvoice(
                $externalId,
                $pesanan->total_biaya,
                $deskripsi,
                ['name' => $namaPelanggan, 'phone' => $nomor]
            );

            if ($invoiceUrl) {
                $teks = "💳 *Pembayaran Digital Otomatis*\n\n" .
                        "Total Tagihan: *Rp {$total}*\n\n" .
                        "Silakan klik link berikut untuk melakukan pembayaran dengan berbagai metode (Virtual Account, E-Wallet, QRIS, dll):\n" .
                        "👉 {$invoiceUrl}\n\n" .
                        "Pembayaran akan terverifikasi secara otomatis oleh sistem kami. Anda tidak perlu mengirim bukti transfer.\n" .
                        "Ketik *batal* untuk membatalkan pesanan.";
                
                $this->wa->kirimPesan($nomor, $teks);
                return;
            } else {
                \Illuminate\Support\Facades\Log::error("Xendit Invoice gagal dibuat untuk {$pesanan->nomor_order}. Fallback ke manual.");
                // Jika gagal generate invoice, lanjut ke metode fallback QRIS/Transfer manual di bawah
            }
        }

        if ($pesanan->metode_pembayaran === 'qris') {
            $qrisImage = env('QRIS_IMAGE_URL', '');
            $qrisLocalPath = public_path('uploads/qris.png');
            $qrisUrl = null;

            if (!empty($qrisImage)) {
                $qrisUrl = $qrisImage;
            } elseif ($identitas && $identitas->qris_path && file_exists(public_path('storage/' . $identitas->qris_path))) {
                $qrisUrl = env('NGROK_PUBLIC_URL') 
                    ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/' . $identitas->qris_path
                    : url('storage/' . $identitas->qris_path);
            } elseif (file_exists($qrisLocalPath)) {
                $qrisUrl = env('NGROK_PUBLIC_URL') 
                    ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/uploads/qris.png'
                    : url('/uploads/qris.png');
            }

            $teksUtama = "📱 *Pembayaran via QRIS*\n\n" .
                         "Total Tagihan: *Rp {$total}*\n\n" .
                         "_(Anda dapat membayar Lunas atau Uang Muka / DP minimum sesuai kesepakatan)_\n\n" .
                         "Silakan scan kode QRIS pembayaran untuk menyelesaikan pesanan Anda.\n\n" .
                         "Setelah pembayaran berhasil, *kirim foto bukti pembayaran/transfer di sini* untuk verifikasi.\n" .
                         "Ketik *batal* untuk membatalkan.";

            // Kirim teks tagihan utama terlebih dahulu agar terjamin sampai
            $this->wa->kirimPesan($nomor, $teksUtama);

            // Kirim media QRIS secara terpisah
            if ($qrisUrl) {
                $namaToko = $identitas->nama_toko ?? 'Toko Anda';
                $this->wa->kirimPesan($nomor, "Berikut adalah QRIS Pembayaran {$namaToko}:", $qrisUrl, 'image');
            } else {
                $bankInfo = ($identitas && $identitas->nomor_rekening) 
                    ? $identitas->nomor_rekening 
                    : env('BANK_TRANSFER_INFO', "Bank BCA\nNo Rekening: 123456789\na/n Toko Tenanta.id");
                $this->wa->kirimPesan($nomor, "⚠️ QRIS Belum terkonfigurasi. Silakan transfer ke rekening bank berikut:\n\n" . $bankInfo);
            }

        } else {
            $bankInfo = ($identitas && $identitas->nomor_rekening) 
                ? $identitas->nomor_rekening 
                : env('BANK_TRANSFER_INFO', "Bank BCA\nNo Rekening: 123456789\na/n Toko Tenanta.id");
            
            $teks = "🏦 *Pembayaran via Transfer Bank*\n\n" .
                    "Silakan lakukan transfer sebesar *Rp {$total}* ke rekening berikut:\n\n" .
                    "{$bankInfo}\n\n" .
                    "_(Anda dapat membayar Lunas atau Uang Muka / DP minimum sesuai kesepakatan)_\n\n" .
                    "Setelah transfer berhasil, *kirim foto bukti transfer di sini* untuk verifikasi.\n" .
                    "Ketik *batal* untuk membatalkan.";

            $this->wa->kirimPesan($nomor, $teks);
        }
    }

    /**
     * Helper: Kirim notifikasi rincian tugas pengantaran ke kurir via WhatsApp
     */
    public function kirimNotifikasiKurir(Pesanan $pesanan): void
    {
        $pesanan->load('kurir', 'items.produk', 'items.produkVarian');

        if ($pesanan->tipe_pengiriman === 'kurir_toko' && $pesanan->kurir) {
            $daftarBarang = "";
            foreach ($pesanan->items as $item) {
                $namaProd = $item->produk ? $item->produk->nama : 'Produk';
                $varian = $item->produkVarian ? " ({$item->produkVarian->nama_varian})" : "";
                
                $addonsText = "";
                if (!empty($item->addons)) {
                    $addonDetails = [];
                    foreach ($item->addons as $addon) {
                        $addonStr = $addon['nama_addon'];
                        if (!empty($addon['teks'])) {
                            $addonStr .= ' ("' . $addon['teks'] . '")';
                        }
                        $addonDetails[] = $addonStr;
                    }
                    $addonsText = " (Tambahan: " . implode(', ', $addonDetails) . ")";
                }
                
                $daftarBarang .= "- {$item->jumlah}x {$namaProd}{$varian}{$addonsText}\n";
            }
            
            $pesanKurir = "🌸 *Halo Kak, mohon bantuannya untuk mengantarkan pesanan berikut ya:*\n" .
                          "━━━━━━━━━━━━━━━━\n" .
                          "Nomor Order: *{$pesanan->nomor_order}*\n" .
                          "Nama Pelanggan: *{$pesanan->nama_penerima}*\n" .
                          "No. HP Pelanggan: {$pesanan->nomor_hp_tampil}\n" .
                          "Alamat Pengiriman: *{$pesanan->alamat_penerima}*\n\n" .
                          "📦 *Daftar Barang yang Harus Diantar*:\n" .
                          "{$daftarBarang}\n" .
                          "💵 *Rincian Keuangan*:\n" .
                          "- Ongkos Kirim: Rp " . number_format($pesanan->biaya_pengantaran, 0, ',', '.') . "\n" .
                          "- Sisa Tagihan: *Rp " . number_format($pesanan->sisa_pembayaran, 0, ',', '.') . "*\n" .
                          "━━━━━━━━━━━━━━━━\n" .
                          "Terima kasih banyak atas bantuannya, Kak. Tetap utamakan keselamatan di jalan dan silakan periksa kembali kelengkapan produk sebelum meninggalkan toko! 😊";
                          
            $this->wa->kirimPesan($pesanan->kurir->nomor_hp, $pesanKurir);
        }
    }

    /**
     * Helper: Kirim notifikasi ke grup admin/seller
     */
    public function notifikasiGrupSeller(string $pesan): void
    {
        $groupId = env('WHATSAPP_GROUP_ID_SELLER');
        if (!empty($groupId)) {
            $this->wa->kirimPesan($groupId, $pesan);
        } else {
            Log::warning("⚠️ Seller Group ID belum diatur di .env (WHATSAPP_GROUP_ID_SELLER)");
        }
    }

    public function cekStatusOrder(string $nomor, string $teks): void
    {
        if (in_array($teks, ['cek order', 'pesanan saya', 'status order'])) {
            $pesanans = \App\Models\Pesanan::where('nomor_wa', $nomor)
                ->orderBy('id', 'desc')
                ->take(5)
                ->get();

            if ($pesanans->isEmpty()) {
                $this->wa->kirimPesan($nomor, "Anda belum memiliki riwayat pesanan.");
                return;
            }

            $msg = "📋 *Daftar Pesanan Terakhir Anda:*\n━━━━━━━━━━━━━━━━\n\n";
            foreach ($pesanans as $p) {
                $msg .= "🔹 *{$p->nomor_order}*\n";
                $msg .= "    Tanggal: " . $p->created_at->format('d/m/Y') . "\n";
                $msg .= "    Total: Rp " . number_format($p->total_biaya, 0, ',', '.') . "\n";
                $msg .= "    Status: {$p->status}\n\n";
            }
            $msg .= "Balas dengan format *CEK [NOMOR ORDER]* untuk detail.\nContoh: *CEK {$pesanans->first()->nomor_order}*";
            $this->wa->kirimPesan($nomor, $msg);
            return;
        }

        $pesanan = \App\Models\Pesanan::where('nomor_wa', $nomor)
            ->where('nomor_order', $teks)
            ->with('items.produk')
            ->first();

        if (!$pesanan) {
            $this->wa->kirimPesan($nomor, "❌ Maaf, pesanan *{$teks}* tidak ditemukan atau bukan milik Anda.");
            return;
        }

        $this->kirimStrukDigitalTeks($nomor, $pesanan);
    }

    protected function kirimStrukDigitalTeks(string $nomor, \App\Models\Pesanan $pesanan): void
    {
        $statusPembayaran = $pesanan->sisa_pembayaran <= 0 ? 'Lunas' : ($pesanan->uang_muka > 0 ? 'DP' : 'Belum Lunas');
        
        $statusProduksi = 'Menunggu';
        $statusPengiriman = 'Menunggu';
        $icon = '[⏳]';

        if (in_array($pesanan->status, ['paid', 'approved'])) {
            $statusProduksi = 'Diproses / Siap';
            $statusPengiriman = $pesanan->tipe_pengiriman === 'ambil_sendiri' ? 'Siap Diambil' : 'Siap Dikirim';
            $icon = '[✅]';
        } elseif ($pesanan->status === 'completed') {
            $statusProduksi = 'Selesai';
            $statusPengiriman = 'Selesai';
            $icon = '[✅]';
        } elseif ($pesanan->status === 'cancelled') {
            $statusProduksi = 'Dibatalkan';
            $statusPengiriman = 'Dibatalkan';
            $icon = '[❌]';
        }

        $msg = "🧾 *STATUS PESANAN*\n━━━━━━━━━━━━━━━━\n";
        $msg .= "*No. Order:* {$pesanan->nomor_order}\n";
        $msg .= "*Tanggal:* " . $pesanan->created_at->format('d/m/Y') . "\n";
        $msg .= "*Nama:* {$pesanan->nama_penerima}\n\n";

        $msg .= "🛍️ *Daftar Produk:*\n";
        foreach ($pesanan->items as $item) {
            $namaProd = $item->produk ? $item->produk->nama : 'Produk';
            $harga = number_format($item->harga_satuan, 0, ',', '.');
            
            $addonsText = "";
            if (!empty($item->addons)) {
                $addonDetails = [];
                foreach ($item->addons as $addon) {
                    $addonStr = $addon['nama_addon'];
                    if (!empty($addon['teks'])) {
                        $addonStr .= ' ("' . $addon['teks'] . '")';
                    }
                    $addonDetails[] = $addonStr;
                }
                $addonsText = "\n   ↳ Tambahan: " . implode(', ', $addonDetails);
            }
            
            $msg .= "- {$item->jumlah}x {$namaProd} (Rp {$harga}){$addonsText}\n";
        }

        $msg .= "\n💰 *Total Pembayaran:* Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "\n";
        $msg .= "💳 *Status Pembayaran:* {$statusPembayaran}";
        if ($pesanan->sisa_pembayaran > 0) {
            $msg .= " (Sisa Tagihan: Rp " . number_format($pesanan->sisa_pembayaran, 0, ',', '.') . ")";
        }
        $msg .= "\n\n📦 *Status Pesanan:*\n";
        $msg .= "{$icon} Pembayaran ({$statusPembayaran})\n";
        $msg .= "{$icon} Produksi ({$statusProduksi})\n";
        $msg .= "{$icon} Pengiriman ({$statusPengiriman})\n\n";

        if ($pesanan->sisa_pembayaran > 0 && in_array($pesanan->status, ['paid', 'approved'])) {
            $msg .= "*Info:* Anda dapat melakukan pelunasan saat mengambil pesanan.\n\n";
        }

        $msg .= "Ketik *STRUK {$pesanan->nomor_order}* jika Anda membutuhkan struk dalam format PDF.";

        $this->wa->kirimPesan($nomor, $msg);
    }

    public function kirimStrukPdf(string $nomor, string $nomorOrder): void
    {
        $pesanan = \App\Models\Pesanan::where('nomor_wa', $nomor)
            ->where('nomor_order', $nomorOrder)
            ->with('items.produk')
            ->first();

        if (!$pesanan) {
            $this->wa->kirimPesan($nomor, "❌ Maaf, pesanan *{$nomorOrder}* tidak ditemukan atau bukan milik Anda.");
            return;
        }

        try {
            $pdf = \Barryvdh\DomPDF\Facade\Pdf::loadView('invoice.pdf', compact('pesanan'));
            
            $dir = storage_path('app/public/invoices');
            if (!file_exists($dir)) {
                mkdir($dir, 0755, true);
            }
            
            $filename = "Invoice_{$nomorOrder}.pdf";
            $path = $dir . '/' . $filename;
            $pdf->save($path);

            $mediaUrl = env('NGROK_PUBLIC_URL') 
                ? rtrim(env('NGROK_PUBLIC_URL'), '/') . '/storage/invoices/' . $filename
                : url('/storage/invoices/' . $filename);

            $this->wa->kirimPesan($nomor, "Berikut adalah Struk PDF untuk pesanan *{$nomorOrder}*.", $mediaUrl, 'document');
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Gagal generate PDF: " . $e->getMessage());
            $this->wa->kirimPesan($nomor, "⚠️ Gagal membuat PDF. Silakan coba lagi nanti.");
        }
    }
}
