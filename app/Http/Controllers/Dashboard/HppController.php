<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\BahanBaku;
use App\Models\ProdukVarian;
use App\Models\Produk;
use App\Models\ResepVarian;
use App\Models\CashFlow;
use App\Models\StokBahanHistory;
use App\Models\Shift;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class HppController extends Controller
{
    // ==========================================
    // BAHAN BAKU
    // ==========================================
    public function indexBahanBaku()
    {
        $bahan = BahanBaku::orderBy('nama_bahan')->get();
        $konversis = \App\Models\SatuanKonversi::orderBy('satuan_awal')->get();
        return view('dashboard.hpp.bahan_baku', compact('bahan', 'konversis'));
    }

    public function storeBahanBaku(Request $request)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'kategori' => 'required|in:bahan_baku,packaging',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'qty_beli' => 'required|numeric|min:0.01',
        ]);

        $hargaPerUnit = $request->harga_beli / $request->qty_beli;

        try {
            DB::beginTransaction();

            $bahan = BahanBaku::create([
                'nama_bahan' => $request->nama_bahan,
                'kategori' => $request->kategori,
                'satuan' => $request->satuan,
                'harga_beli' => $request->harga_beli,
                'qty_beli' => $request->qty_beli,
                'harga_per_unit' => $hargaPerUnit,
                'stok' => $request->qty_beli // Stok otomatis terisi sejumlah barang yang dibeli pertama kali
            ]);

            // Catat ke Stok History
            StokBahanHistory::create([
                'bahan_baku_id' => $bahan->id,
                'user_id' => auth()->id(),
                'tipe' => 'tambah',
                'qty' => $request->qty_beli,
                'keterangan' => 'Pembelian Awal Master Bahan Baku'
            ]);

            // Catat Pengeluaran ke CashFlow
            $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
            CashFlow::create([
                'user_id' => auth()->id(),
                'shift_id' => $shift ? $shift->id : null,
                'tanggal' => now()->toDateString(),
                'tipe' => 'out',
                'kategori' => 'Belanja Bahan Baku',
                'nominal' => $request->harga_beli,
                'keterangan' => 'Beli ' . $bahan->nama_bahan . ' sejumlah ' . $request->qty_beli . ' ' . $bahan->satuan,
            ]);

            DB::commit();
            return back()->with('sukses', 'Bahan baku berhasil ditambahkan & Pengeluaran kas telah dicatat otomatis.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan bahan baku: ' . $e->getMessage());
        }
    }

    public function updateBahanBaku(Request $request, BahanBaku $bahan)
    {
        $request->validate([
            'nama_bahan' => 'required|string|max:255',
            'kategori' => 'required|in:bahan_baku,packaging',
            'satuan' => 'required|string|max:50',
            'harga_beli' => 'required|numeric|min:0',
            'qty_beli' => 'required|numeric|min:0.01',
        ]);

        $hargaPerUnit = $request->harga_beli / $request->qty_beli;

        $bahan->update([
            'nama_bahan' => $request->nama_bahan,
            'kategori' => $request->kategori,
            'satuan' => $request->satuan,
            'harga_beli' => $request->harga_beli,
            'qty_beli' => $request->qty_beli,
            'harga_per_unit' => $hargaPerUnit
        ]);

        // Rekalkulasi semua HPP yang menggunakan bahan ini
        $reseps = ResepVarian::where('bahan_baku_id', $bahan->id)->get();
        foreach ($reseps as $resep) {
            $resep->varian->hitungHpp();
        }

        return back()->with('sukses', 'Bahan baku diperbarui dan HPP Varian telah disesuaikan.');
    }

    public function destroyBahanBaku(BahanBaku $bahan)
    {
        $bahan->delete();
        return back()->with('sukses', 'Bahan baku berhasil dihapus.');
    }

    public function koreksiStokBahanBaku(Request $request, BahanBaku $bahan)
    {
        $request->validate([
            'stok_aktual' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $stokLama = $bahan->stok;
            $stokBaru = $request->stok_aktual;
            $selisih = $stokBaru - $stokLama;

            if ($selisih != 0) {
                $bahan->update([
                    'stok' => $stokBaru
                ]);

                StokBahanHistory::create([
                    'bahan_baku_id' => $bahan->id,
                    'user_id' => auth()->id(),
                    'tipe' => $selisih > 0 ? 'tambah' : 'kurang',
                    'qty' => abs($selisih),
                    'keterangan' => 'Koreksi Stok Awal/Manual (Dari ' . $stokLama . ' menjadi ' . $stokBaru . ')'
                ]);
            }

            DB::commit();
            return back()->with('sukses', 'Stok bahan baku berhasil dikoreksi.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan koreksi stok: ' . $e->getMessage());
        }
    }

    public function restockBahanBaku(Request $request, BahanBaku $bahan)
    {
        $request->validate([
            'harga_beli' => 'required|numeric|min:0',
            'qty_beli' => 'required|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            $hargaPerUnitBaru = $request->harga_beli / $request->qty_beli;

            // Update data bahan baku (Stok ditambahkan)
            $bahan->update([
                'harga_beli' => $request->harga_beli,
                'qty_beli' => $request->qty_beli,
                'harga_per_unit' => $hargaPerUnitBaru,
                'stok' => $bahan->stok + $request->qty_beli
            ]);

            // Catat History Stok
            StokBahanHistory::create([
                'bahan_baku_id' => $bahan->id,
                'user_id' => auth()->id(),
                'tipe' => 'tambah',
                'qty' => $request->qty_beli,
                'keterangan' => 'Restock Bahan Baku'
            ]);

            // Catat CashFlow
            $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
            if (config('flashbot.features.finance')) {
                CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift ? $shift->id : null,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'out',
                    'kategori' => 'Belanja Bahan Baku',
                    'nominal' => $request->harga_beli,
                    'keterangan' => 'Restock ' . $bahan->nama_bahan . ' (' . $request->qty_beli . ' ' . $bahan->satuan . ')',
                ]);
            }

            DB::commit();
            return back()->with('sukses', 'Restock berhasil. Stok bahan bertambah & Pengeluaran kas dicatat.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melakukan restock: ' . $e->getMessage());
        }
    }

    public function laporBahanRusak(Request $request, BahanBaku $bahan)
    {
        $request->validate([
            'qty_rusak' => 'required|numeric|min:0.01',
            'alasan' => 'required|string|max:255',
        ]);

        if ($request->qty_rusak > $bahan->stok) {
            return back()->with('error', 'Jumlah rusak tidak boleh melebihi stok yang ada!');
        }

        try {
            DB::beginTransaction();

            // Susutkan stok
            $bahan->decrement('stok', $request->qty_rusak);

            // Catat History
            StokBahanHistory::create([
                'bahan_baku_id' => $bahan->id,
                'user_id' => auth()->id(),
                'tipe' => 'rusak',
                'qty' => $request->qty_rusak,
                'keterangan' => 'Lapor Bahan Rusak: ' . $request->alasan
            ]);

            // Hitung kerugian (qty_rusak * harga_per_unit)
            $kerugian = $request->qty_rusak * $bahan->harga_per_unit;

            // Catat CashFlow Kerugian
            $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
            if (config('flashbot.features.finance')) {
                CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift ? $shift->id : null,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'out',
                    'kategori' => 'Kerugian Bahan Rusak',
                    'nominal' => $kerugian,
                    'keterangan' => 'Bahan rusak/susut: ' . $bahan->nama_bahan . ' (' . $request->qty_rusak . ' ' . $bahan->satuan . ') | ' . $request->alasan,
                ]);
            }

            DB::commit();
            return back()->with('sukses', 'Laporan bahan rusak berhasil disimpan. Stok telah disesuaikan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal melapor bahan rusak: ' . $e->getMessage());
        }
    }

    // ==========================================
    // KALKULATOR HPP
    // ==========================================
    public function indexKalkulator()
    {
        // Ambil produk yang punya varian
        $produks = Produk::with(['varians.resep.bahanBaku', 'kategori'])
                    ->whereHas('varians')
                    ->orderBy('nama')
                    ->get();
        
        $semuaBahan = BahanBaku::orderBy('nama_bahan')->get();

        return view('dashboard.hpp.kalkulator', compact('produks', 'semuaBahan'));
    }

    public function simpanResep(Request $request, ProdukVarian $varian)
    {
        $request->validate([
            'bahan_baku_id' => 'required|exists:'.\App\Services\TenantManager::getTenantConnection().'.bahan_bakus,id',
            'qty_dipakai' => 'required|numeric|min:0.01'
        ]);

        ResepVarian::create([
            'produk_varian_id' => $varian->id,
            'bahan_baku_id' => $request->bahan_baku_id,
            'qty_dipakai' => $request->qty_dipakai
        ]);

        $varian->hitungHpp();

        return back()->with('sukses', 'Resep berhasil ditambahkan. HPP telah diperbarui.');
    }

    public function hapusResep(ResepVarian $resep)
    {
        $varian = $resep->varian;
        $resep->delete();
        $varian->hitungHpp();
        return back()->with('sukses', 'Bahan resep dihapus.');
    }

    public function updateKonfigurasiHarga(Request $request, ProdukVarian $varian)
    {
        $request->validate([
            'overhead_cost' => 'nullable|numeric|min:0',
            'resep_yield' => 'nullable|integer|min:1',
            'harga_kompetitor' => 'nullable|numeric|min:0',
            'target_margin' => 'nullable|numeric|min:0|max:100'
        ]);

        $varian->overhead_cost = $request->overhead_cost ?: 0;
        $varian->resep_yield = $request->resep_yield ?: 1;
        $varian->harga_kompetitor = $request->harga_kompetitor;
        $varian->target_margin = $request->target_margin ?: 0;
        $varian->save();

        $varian->hitungHpp();

        return back()->with('sukses', 'Konfigurasi Harga berhasil diperbarui.');
    }

    public function terapkanHargaRekomendasi(ProdukVarian $varian)
    {
        if ($varian->harga_rekomendasi > 0) {
            $varian->harga = $varian->harga_rekomendasi;
            $varian->save();

            // Opsional: Update harga induk jika ini adalah satu-satunya varian, 
            // Tapi untuk amannya kita tidak sentuh produk induk karena kita sudah dukung $varian->harga
            
            return back()->with('sukses', 'Harga Jual Varian berhasil diperbarui sesuai Harga Rekomendasi!');
        }
        return back()->with('error', 'Harga rekomendasi tidak valid.');
    }
}
