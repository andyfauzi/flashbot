<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class StokController extends Controller
{
    public function index()
    {
        // Ambil semua produk beserta variannya
        $produks = Produk::with('varians')->orderBy('nama', 'asc')->get();
        return view('dashboard.stok.index', compact('produks'));
    }

    public function updateBulk(Request $request)
    {
        $request->validate([
            'stok' => 'required|array',
            'stok.*' => 'required|integer|min:0',
        ]);

        try {
            DB::beginTransaction();

            // Prevent Deadlocks: Lock Produk -> ProdukVarian
            $varianIds = array_keys($request->stok);
            sort($varianIds);
            
            $produkIds = ProdukVarian::whereIn('id', $varianIds)->pluck('produk_id')->unique()->sort();
            
            // 1. Lock Products
            foreach ($produkIds as $pId) {
                Produk::lockForUpdate()->find($pId);
            }
            
            // 2. Lock Variants and update
            foreach ($varianIds as $varianId) {
                $jumlahStok = $request->stok[$varianId];
                $varian = ProdukVarian::lockForUpdate()->find($varianId);
                if ($varian) {
                    $varian->stok = $jumlahStok;
                    $varian->save();
                }
            }

            // Update stok total produk
            $produks = Produk::whereIn('id', $produkIds)->get();
            foreach ($produks as $produk) {
                $totalStok = $produk->varians()->sum('stok');
                if ($produk->stok !== $totalStok) {
                    $produk->stok = $totalStok;
                    $produk->save();
                }
            }

            DB::commit();
            return back()->with('sukses', 'Stok seluruh produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
