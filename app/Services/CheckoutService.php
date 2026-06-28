<?php

namespace App\Services;

use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\BahanBaku;
use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\StokBahanHistory;
use App\Models\Shift;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CheckoutService
{
    /**
     * Memproses checkout pesanan dari Kasir (POS)
     */
    public function processPosCheckout(array $data, $userId)
    {
        return DB::transaction(function () use ($data, $userId) {
            $totalBiaya = 0;
            $itemsData = [];

            $nomor_wa = '-';
            if (!empty($data['nomor_wa'])) {
                $nomor_wa = preg_replace('/[^0-9]/', '', $data['nomor_wa']);
                if (str_starts_with($nomor_wa, '0')) {
                    $nomor_wa = '62' . substr($nomor_wa, 1);
                }
            }

            // Validasi stok & kumpulkan data item (Urutkan ID untuk mencegah MySQL Deadlock)
            usort($data['cart'], fn($a, $b) => $a['id'] <=> $b['id']);
            foreach ($data['cart'] as $item) {
                // 1. Lock Produk
                $produk = Produk::lockForUpdate()->find($item['id']);
                $varian = null;

                // 2. Lock Varian
                if (!empty($item['varian_id'])) {
                    $varian = ProdukVarian::lockForUpdate()->find($item['varian_id']);
                    if (!$produk->is_made_to_order && $varian && $varian->stok < $item['qty']) {
                        throw new \Exception("Stok {$produk->nama} ({$varian->nama_varian}) tidak mencukupi (Sisa: {$varian->stok})");
                    }
                } else {
                    if (!$produk->is_made_to_order && $produk->stok < $item['qty']) {
                        throw new \Exception("Stok {$produk->nama} tidak mencukupi (Sisa: {$produk->stok})");
                    }
                }

                $addonsArray = $item['addons'] ?? [];
                $hargaAddons = 0;
                $formattedAddons = [];
                foreach($addonsArray as $addon) {
                    $hargaAddons += $addon['harga'];
                    // Format to match Chatbot JSON structure (id, nama_addon, harga)
                    $formattedAddons[] = [
                        'id' => $addon['id'],
                        'nama_addon' => $addon['nama'],
                        'harga' => $addon['harga']
                    ];
                }

                $hargaSatuan = ($varian && $varian->harga > 0) ? $varian->harga : $produk->harga;
                
                // Perhitungan Promo Bundle (Pilihan A)
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
                
                $hargaSatuan += $hargaAddons; // Disimpan sbg rata-rata untuk kompatibilitas UI
                $totalBiaya += $subtotal;

                $itemsData[] = [
                    'produk' => $produk,
                    'varian' => $varian,
                    'qty' => $item['qty'],
                    'harga_satuan' => $hargaSatuan,
                    'subtotal' => $subtotal,
                    'addons' => $formattedAddons
                ];
            }

            $isPreorder = $data['is_preorder'] ?? false;
            $mejaId = $data['meja_id'] ?? null;
            $gunakanDp = $data['gunakan_dp'] ?? false;
            
            $tanggalDiambil = $isPreorder ? ($data['tanggal_diambil'] ?? null) : null;
            $uangMuka = $isPreorder ? (float)($data['uang_muka'] ?? 0) : $totalBiaya;

            $reservasiTerpakai = null;
            $potonganDp = 0;

            if ($gunakanDp && ($mejaId || $nomor_wa !== '-')) {
                $query = \App\Models\Reservasi::where('status_pembayaran_dp', 'lunas')
                    ->whereNotIn('status', ['selesai', 'batal']);
                
                if ($mejaId) {
                    $query->where('meja_id', $mejaId);
                } else {
                    $query->where('nomor_telepon', 'like', "%{$nomor_wa}%");
                }
                
                $reservasiTerpakai = $query->first();

                if ($reservasiTerpakai) {
                    $potonganDp = $reservasiTerpakai->nominal_dp;
                    $uangMuka = max(0, $uangMuka - $potonganDp);
                    $totalBiaya = max(0, $totalBiaya - $potonganDp); // Sisa yang harus dibayar customer
                }
            }

            $statusPesanan = 'completed';
            $tipePengiriman = 'ambil_sendiri';
            $alamatPenerima = 'Pembelian Langsung di Toko';

            if ($mejaId) {
                $tipePengiriman = 'dine_in';
                // Jika pesanan dine-in via kasir, maka pesanan tersebut harus disiapkan oleh dapur.
                // Atur tanggal diambil menjadi sekarang agar muncul di Daftar Pesanan.
                $tanggalDiambil = now();
                $statusPesanan = 'paid';
                $alamatPenerima = 'Makan di Tempat';
            }

            if ($isPreorder) {
                if ($uangMuka >= $totalBiaya) {
                    $statusPesanan = 'paid';
                    $uangMuka = $totalBiaya;
                } elseif ($uangMuka > 0) {
                    $statusPesanan = 'paid_sebagian';
                } else {
                    $statusPesanan = 'pending';
                }
            }

            // Simpan Pesanan
            $pesanan = Pesanan::create([
                'nomor_order' => 'POS-' . strtoupper(Str::random(8)),
                'nomor_wa' => $nomor_wa,
                'nama_penerima' => $data['nama_penerima'] ?? 'Pelanggan Toko',
                'alamat_penerima' => $alamatPenerima,
                'tipe_pengiriman' => $tipePengiriman,
                'tanggal_diambil' => $tanggalDiambil,
                'biaya_barang' => $totalBiaya,
                'biaya_pengantaran' => 0,
                'total_biaya' => $totalBiaya,
                'uang_muka' => $uangMuka,
                'metode_pembayaran' => $data['metode_pembayaran'],
                'status' => $statusPesanan,
                'source' => 'pos_offline',
                'meja_id' => $mejaId,
                'reservasi_id' => $reservasiTerpakai ? $reservasiTerpakai->id : null,
            ]);

            if ($reservasiTerpakai) {
                $reservasiTerpakai->update(['status' => 'selesai']);
            }

            // Simpan Pesanan Item & Kurangi Stok
            foreach ($itemsData as $itemData) {
                $hppSnapshot = 0;
                if ($itemData['varian']) {
                    $hppSnapshot = $itemData['varian']->hpp + $itemData['varian']->overhead_cost;
                }
                
                PesananItem::create([
                    'pesanan_id' => $pesanan->id,
                    'produk_id' => $itemData['produk']->id,
                    'produk_varian_id' => $itemData['varian'] ? $itemData['varian']->id : null,
                    'jumlah' => $itemData['qty'],
                    'harga_satuan' => $itemData['harga_satuan'],
                    'subtotal' => $itemData['subtotal'],
                    'hpp_snapshot' => $hppSnapshot,
                    'addons' => $itemData['addons']
                ]);

                // Implementasi Smart Deduction (Hibrida)
                $isMadeToOrder = $itemData['produk']->is_made_to_order;

                if ($itemData['produk']->is_bundle && $itemData['produk']->bundleItems) {
                    // JIKA BUNDLE: Looping isi bundle, lalu potong stok per komponen
                    foreach ($itemData['produk']->bundleItems as $bItem) {
                        $komponenVarian = $bItem->varian;
                        $komponenProduk = $komponenVarian ? $komponenVarian->produk : null;
                        $qtyKomponen = $bItem->qty * $itemData['qty'];

                        if ($komponenProduk && $komponenVarian) {
                            if ($komponenProduk->is_made_to_order) {
                                $yield = max(1, $komponenVarian->resep_yield ?? 1);
                                $resep = \App\Models\ResepVarian::where('produk_varian_id', $komponenVarian->id)->get();
                                foreach ($resep as $r) {
                                    $qtyDibutuhkan = ($r->qty_dipakai / $yield) * $qtyKomponen;
                                    $bahanBaku = BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                    if ($bahanBaku) {
                                        $bahanBaku->decrement('stok', $qtyDibutuhkan);
                                        StokBahanHistory::create([
                                            'bahan_baku_id' => $bahanBaku->id,
                                            'user_id' => $userId,
                                            'tipe' => 'produksi',
                                            'qty' => $qtyDibutuhkan,
                                            'keterangan' => 'Terjual di POS (Bundle ' . $itemData['produk']->nama . ') Struk #' . $pesanan->nomor_order . ' - ' . $komponenVarian->nama_varian
                                        ]);
                                    }
                                }
                            } else {
                                $komponenVarian->decrement('stok', $qtyKomponen);
                            }
                        }
                    }
                } elseif ($isMadeToOrder) {
                    // JIKA MADE-TO-ORDER: HANYA potong bahan baku
                    if ($itemData['varian']) {
                        $yield = max(1, $itemData['varian']->resep_yield ?? 1);
                        $resep = \App\Models\ResepVarian::where('produk_varian_id', $itemData['varian']->id)->get();
                        foreach ($resep as $r) {
                            $qtyDibutuhkan = ($r->qty_dipakai / $yield) * $itemData['qty'];
                            // 3. Lock Bahan Baku
                            $bahanBaku = BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                            if ($bahanBaku) {
                                $bahanBaku->decrement('stok', $qtyDibutuhkan);
                                StokBahanHistory::create([
                                    'bahan_baku_id' => $bahanBaku->id,
                                    'user_id' => $userId,
                                    'tipe' => 'produksi',
                                    'qty' => $qtyDibutuhkan,
                                    'keterangan' => 'Terjual di POS (Made-to-Order) Struk #' . $pesanan->nomor_order . ' - ' . $itemData['varian']->nama_varian
                                ]);
                            }
                        }
                    }
                } else {
                    // JIKA MADE-TO-STOCK: HANYA potong stok produk jadi
                    if ($itemData['varian']) {
                        $itemData['varian']->decrement('stok', $itemData['qty']);
                    } else {
                        $itemData['produk']->decrement('stok', $itemData['qty']);
                    }
                }
            }

            // --- INTEGRASI ARUS KAS & SHIFT ---
            $nominalMasuk = ($statusPesanan === 'completed') ? $totalBiaya : $uangMuka;

            if ($nominalMasuk > 0) {
                $shift = Shift::where('user_id', $userId)->where('status', 'aktif')->first();
                
                if (config('flashbot.features.finance')) {
                    CashFlow::create([
                        'user_id' => $userId,
                        'shift_id' => $shift ? $shift->id : null,
                        'tanggal' => now()->toDateString(),
                        'tipe' => 'in',
                        'kategori' => 'Penjualan ' . ($statusPesanan === 'completed' ? 'POS' : 'PO DP'),
                        'nominal' => $nominalMasuk,
                        'keterangan' => 'Penjualan otomatis dari struk #' . $pesanan->nomor_order,
                    ]);
                }

                if ($shift && $data['metode_pembayaran'] === 'cash') {
                    $shift->increment('total_penjualan_tunai', $nominalMasuk);
                }
            }

            return $pesanan;
        });
    }
}
