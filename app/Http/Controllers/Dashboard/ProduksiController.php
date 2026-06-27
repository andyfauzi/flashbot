<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\BahanBaku;
use App\Models\ResepVarian;
use App\Models\StokBahanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ProduksiController extends Controller
{
    public function index()
    {
        // Hanya ambil produk yang is_made_to_order = false (Made-to-Stock)
        $produks = Produk::with('varians.resep.bahanBaku')
            ->where('is_made_to_order', false)
            ->orderBy('nama')
            ->get();
            
        return view('dashboard.produksi.index', compact('produks'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'produk_varian_id' => 'required|exists:'.\App\Services\TenantManager::getTenantConnection().'.produk_varians,id',
            'qty_produksi' => 'required|integer|min:1',
        ]);

        $varian = ProdukVarian::with('produk', 'resep.bahanBaku')->findOrFail($request->produk_varian_id);
        
        if ($varian->produk->is_made_to_order) {
            return back()->with('error', 'Produk ini adalah produk Made-to-Order dan tidak dapat diproduksi secara massal.');
        }

        if ($varian->resep->isEmpty()) {
            // Jika tidak ada resep, langsung masukkan ke proses dapur
            try {
                DB::beginTransaction();
                $lockedProduk = Produk::lockForUpdate()->find($varian->produk_id);
                $lockedVarian = ProdukVarian::lockForUpdate()->find($varian->id);
                
                $lockedVarian->increment('stok_proses_dapur', $request->qty_produksi);
                
                $lockedProduk->stok_proses_dapur = $lockedProduk->varians()->sum('stok_proses_dapur');
                $lockedProduk->save();
                
                DB::commit();
                return back()->with('sukses', 'Produk berhasil dikirim ke dapur (Sedang Diproses).');
            } catch (\Exception $e) {
                DB::rollBack();
                return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
            }
        }

        try {
            DB::beginTransaction();

            // 1. Lock Produk (Standar: Lock Induk Dulu)
            $lockedProduk = Produk::lockForUpdate()->find($varian->produk_id);
            
            // 2. Lock Varian
            $lockedVarian = ProdukVarian::lockForUpdate()->find($varian->id);

            // 3. Cek ketersediaan bahan baku dulu dan lock (Urutkan berdasarkan bahan_baku_id untuk mencegah deadlock)
            $lockedBahanBakus = [];
            $resepSorted = $varian->resep->sortBy('bahan_baku_id');
            foreach ($resepSorted as $resep) {
                $qtyDibutuhkan = $resep->qty_dipakai * $request->qty_produksi;
                $bahanBaku = BahanBaku::lockForUpdate()->find($resep->bahan_baku_id);
                
                if (!$bahanBaku || $bahanBaku->stok < $qtyDibutuhkan) {
                    throw new \Exception("Stok {$resep->bahanBaku->nama_bahan} tidak mencukupi! Butuh {$qtyDibutuhkan}, tersedia " . ($bahanBaku ? $bahanBaku->stok : 0) . ".");
                }
                $lockedBahanBakus[$resep->bahan_baku_id] = $bahanBaku;
            }

            // Potong bahan baku dan catat riwayat
            foreach ($resepSorted as $resep) {
                $qtyDibutuhkan = $resep->qty_dipakai * $request->qty_produksi;
                $bahanBaku = $lockedBahanBakus[$resep->bahan_baku_id];
                
                $bahanBaku->decrement('stok', $qtyDibutuhkan);

                StokBahanHistory::create([
                    'bahan_baku_id' => $bahanBaku->id,
                    'user_id' => auth()->id(),
                    'tipe' => 'produksi',
                    'qty' => $qtyDibutuhkan,
                    'keterangan' => 'Produksi Massal Dapur: ' . $request->qty_produksi . ' ' . $varian->nama_varian
                ]);
            }

            // Tambah ke stok proses dapur (bukan stok aktif)
            $lockedVarian->increment('stok_proses_dapur', $request->qty_produksi);

            // Update stok proses dapur induk
            $lockedProduk->stok_proses_dapur = $lockedProduk->varians()->sum('stok_proses_dapur');
            $lockedProduk->save();

            DB::commit();
            return back()->with('sukses', "Berhasil mengirim {$request->qty_produksi} {$varian->nama_varian} ke dapur (Sedang Diproses). Bahan baku terpotong otomatis.");
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal mengirim ke dapur: ' . $e->getMessage());
        }
    }

    public function validasiSelesai(Request $request)
    {
        $request->validate([
            'produk_varian_id' => 'required|exists:'.\App\Services\TenantManager::getTenantConnection().'.produk_varians,id',
            'qty_validasi' => 'required|integer|min:1',
            'tindakan' => 'required|in:selesai,waste'
        ]);

        $varian = ProdukVarian::findOrFail($request->produk_varian_id);

        if ($varian->stok_proses_dapur < $request->qty_validasi) {
            return back()->with('error', 'Jumlah validasi melebihi jumlah produk yang sedang diproses di dapur (Proses saat ini: ' . $varian->stok_proses_dapur . ' pcs).');
        }

        try {
            DB::beginTransaction();

            // 1. Lock Produk
            $lockedProduk = Produk::lockForUpdate()->find($varian->produk_id);

            // 2. Lock Varian
            $lockedVarian = ProdukVarian::lockForUpdate()->find($varian->id);

            // 3. Pindahkan dari stok_proses_dapur ke stok aktif
            $lockedVarian->decrement('stok_proses_dapur', $request->qty_validasi);
            
            if ($request->tindakan === 'selesai') {
                $lockedVarian->increment('stok', $request->qty_validasi);
            }

            // 4. Update stok total produk
            $lockedProduk->stok = $lockedProduk->varians()->sum('stok');
            $lockedProduk->stok_proses_dapur = $lockedProduk->varians()->sum('stok_proses_dapur');
            $lockedProduk->save();

            DB::commit();
            
            $msg = $request->tindakan === 'selesai' 
                ? "Berhasil memvalidasi {$request->qty_validasi} pcs {$varian->nama_varian} ke stok aktif gudang."
                : "Berhasil mencatat {$request->qty_validasi} pcs {$varian->nama_varian} sebagai waste/cacat produksi.";
                
            return back()->with('sukses', $msg);
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal memvalidasi: ' . $e->getMessage());
        }
    }
}
