<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Produk;
use App\Models\ProdukVarian;
use Illuminate\Support\Facades\DB;

class KalkulatorBisnisController extends Controller
{
    public function index()
    {
        // 1. Get average selling price
        $avgHargaJual = Produk::where('aktif', true)->avg('harga') ?? 0;
        
        // 2. Get average HPP (from varians if available, otherwise 0 or a fallback)
        $avgHpp = ProdukVarian::where('hpp', '>', 0)->avg('hpp') ?? 0;
        
        // If no HPP data exists, fallback to an estimate (e.g., 40% of selling price)
        if ($avgHpp == 0) {
            $avgHpp = $avgHargaJual * 0.4;
        }
        
        $produks = Produk::with('varians')->where('aktif', true)->get()->map(function($p) {
            $avgVarianHpp = $p->varians->where('hpp', '>', 0)->avg('hpp');
            if (!$avgVarianHpp || $avgVarianHpp == 0) {
                $avgVarianHpp = $p->harga * 0.4; // fallback
            }
            return [
                'nama' => $p->nama,
                'harga' => $p->harga,
                'hpp' => $avgVarianHpp,
                'margin' => $p->harga - $avgVarianHpp
            ];
        })->filter(function($p) {
            return $p['harga'] > 0;
        })->values();
        
        return view('dashboard.kalkulator.index', compact('avgHargaJual', 'avgHpp', 'produks'));
    }
}
