<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Pesanan;
use App\Models\Produk;
use App\Models\ProdukVarian;
use App\Models\BahanBaku;
use App\Models\StokBahanHistory;
use App\Models\Shift;
use App\Models\CashFlow;
use Illuminate\Support\Facades\DB;

class TransaksiController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        // Get all transactions, newest first
        $query = Pesanan::with(['items.produk', 'items.produkVarian']);

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('nomor_order', 'like', '%' . $search . '%')
                  ->orWhere('nama_penerima', 'like', '%' . $search . '%')
                  ->orWhere('nomor_wa', 'like', '%' . $search . '%');
            });
        }

        $transaksis = $query->orderBy('created_at', 'desc')->paginate(20)->withQueryString();
        
        $statistik = [
            'pesanan_hari_ini' => Pesanan::whereDate('created_at', today())->count(),
            'omzet_hari_ini'   => Pesanan::whereDate('created_at', today())->whereIn('status', ['lunas', 'selesai', 'dikirim', 'paid', 'completed'])->sum('total_biaya'),
        ];

        $range = $request->input('range', '7');
        $days = $range == '30' ? 30 : 7;
        
        // Generate continuous date array for the chart
        $dates = [];
        for ($i = $days - 1; $i >= 0; $i--) {
            $dates[now()->subDays($i)->format('Y-m-d')] = 0;
        }

        $penjualan = Pesanan::selectRaw('DATE(created_at) as tanggal, SUM(total_biaya) as total')
            ->where('created_at', '>=', now()->subDays($days)->startOfDay())
            ->whereIn('status', ['lunas', 'selesai', 'dikirim', 'paid', 'completed'])
            ->groupBy('tanggal')
            ->orderBy('tanggal')
            ->get();

        foreach ($penjualan as $p) {
            $dates[$p->tanggal] = (float) $p->total;
        }

        $grafikPenjualan = collect($dates)->map(function ($total, $tanggal) {
            return (object) ['tanggal' => $tanggal, 'total' => $total];
        })->values();

        return view('dashboard.transaksi.index', compact('transaksis', 'search', 'statistik', 'grafikPenjualan', 'range'));
    }

    public function cancel(Request $request, $id)
    {
        $request->validate([
            'alasan_batal' => 'required|string|max:255',
            'tindakan_stok' => 'required|in:restock,waste'
        ]);

        try {
            DB::transaction(function () use ($request, $id) {
                $pesanan = Pesanan::with(['items.produk', 'items.produkVarian'])->lockForUpdate()->findOrFail($id);

                if ($pesanan->status === 'batal' || $pesanan->status === 'cancelled') {
                    throw new \Exception('Transaksi ini sudah dibatalkan sebelumnya.');
                }

                $tindakanStok = $request->tindakan_stok;
                $userId = auth()->id();

                // 1. Tangani Stok
                if ($tindakanStok === 'restock') {
                    foreach ($pesanan->items as $item) {
                        $produk = $item->produk;
                        if (!$produk) continue;

                        $isMadeToOrder = $produk->is_made_to_order;

                        if ($isMadeToOrder) {
                            // Kembalikan bahan baku
                            if ($item->produkVarian) {
                                $resep = \App\Models\ResepVarian::where('produk_varian_id', $item->produkVarian->id)->get();
                                foreach ($resep as $r) {
                                    $qtyKembali = $r->qty_dipakai * $item->jumlah;
                                    $bahanBaku = BahanBaku::lockForUpdate()->find($r->bahan_baku_id);
                                    if ($bahanBaku) {
                                        $bahanBaku->increment('stok', $qtyKembali);
                                        StokBahanHistory::create([
                                            'bahan_baku_id' => $bahanBaku->id,
                                            'user_id' => $userId,
                                            'tipe' => 'penyesuaian',
                                            'qty' => $qtyKembali,
                                            'keterangan' => 'Pengembalian Batal (Restock) Struk #' . $pesanan->nomor_order
                                        ]);
                                    }
                                }
                            }
                        } else {
                            // Kembalikan stok produk jadi
                            if ($item->produkVarian) {
                                ProdukVarian::where('id', $item->produkVarian->id)->increment('stok', $item->jumlah);
                            } else {
                                Produk::where('id', $produk->id)->increment('stok', $item->jumlah);
                            }
                        }
                    }
                }

                // 2. Tangani Keuangan (Laci Kasir & CashFlow)
                $nominalRefund = ($pesanan->status === 'completed' || $pesanan->status === 'paid') ? $pesanan->total_biaya : $pesanan->uang_muka;

                if ($nominalRefund > 0) {
                    $shift = Shift::where('user_id', $userId)->where('status', 'aktif')->first();

                    // Kurangi total tunai di laci jika bayar pakai cash
                    if ($shift && $pesanan->metode_pembayaran === 'cash') {
                        $shift->decrement('total_penjualan_tunai', $nominalRefund);
                    }

                    // Catat Pengeluaran/Refund di CashFlow
                    if (config('flashbot.features.finance')) {
                        CashFlow::create([
                            'user_id' => $userId,
                            'shift_id' => $shift ? $shift->id : null,
                            'tanggal' => now()->toDateString(),
                            'tipe' => 'out',
                            'kategori' => 'Refund Pembatalan',
                            'nominal' => $nominalRefund,
                            'keterangan' => 'Refund Batal Struk #' . $pesanan->nomor_order . ' (' . $request->alasan_batal . ')',
                        ]);
                    }
                }

                // 3. Update Status Pesanan
                $pesanan->status = 'batal';
                $pesanan->catatan = "Dibatalkan oleh: " . auth()->user()->name . "\nAlasan: " . $request->alasan_batal . "\nTindakan Stok: " . strtoupper($tindakanStok);
                $pesanan->save();
            });

            if ($request->input('is_edit') == '1') {
                return redirect()->route('pos.index', ['edit_order' => $id])->with('sukses', 'Order berhasil di-VOID. Silakan edit pesanan di bawah ini.');
            }

            return redirect()->back()->with('sukses', 'Transaksi berhasil dibatalkan.');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal membatalkan: ' . $e->getMessage());
        }
    }
}
