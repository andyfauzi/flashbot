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
        $produksAktif = Produk::where('aktif', true)->get();
        if ($produksAktif->isEmpty()) {
            return view('dashboard.kalkulator.index', [
                'produks' => []
            ]);
        }
        
        $pesananItems = \App\Models\PesananItem::whereHas('pesanan', function($q) {
            $q->whereIn('status', ['selesai', 'lunas'])
              ->whereMonth('created_at', now()->month)
              ->whereYear('created_at', now()->year);
        })
        ->join('pesanans', 'pesanan_items.pesanan_id', '=', 'pesanans.id')
        ->select('pesanan_items.produk_id', DB::raw('DATE(pesanans.created_at) as date'), DB::raw('SUM(pesanan_items.jumlah) as total_terjual'))
        ->groupBy('pesanan_items.produk_id', DB::raw('DATE(pesanans.created_at)'))
        ->get();
        
        $salesDataByDate = [];
        $soldThisMonth = [];
        
        foreach($pesananItems as $item) {
            $date = $item->date;
            $pid = $item->produk_id;
            $qty = (int) $item->total_terjual;
            
            if(!isset($salesDataByDate[$date])) {
                $salesDataByDate[$date] = [];
            }
            $salesDataByDate[$date][$pid] = $qty;
            $soldThisMonth[$pid] = ($soldThisMonth[$pid] ?? 0) + $qty;
        }
        
        $produks = Produk::with('varians')->where('aktif', true)->get()->map(function($p) use ($soldThisMonth) {
            $avgVarianHpp = $p->varians->where('hpp', '>', 0)->avg('hpp');
            if (!$avgVarianHpp || $avgVarianHpp == 0) {
                $avgVarianHpp = $p->harga * 0.4; // fallback
            }
            return [
                'id' => $p->id,
                'nama' => $p->nama,
                'harga' => $p->harga,
                'hpp' => $avgVarianHpp,
                'margin' => $p->harga - $avgVarianHpp,
                'terjual' => $soldThisMonth[$p->id] ?? 0
            ];
        })->filter(function($p) {
            return $p['harga'] > 0;
        })->values();
        
        return view('dashboard.kalkulator.index', [
            'produks' => array_values($produks->toArray()),
            'salesDataByDate' => $salesDataByDate
        ]);
    }
}
