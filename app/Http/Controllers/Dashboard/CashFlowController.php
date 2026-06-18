<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\CashFlow;
use App\Models\Shift;
use App\Models\BahanBaku;
use App\Models\StokBahanHistory;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class CashFlowController extends Controller
{
    public function index(Request $request)
    {
        $bulan = $request->get('bulan', now()->format('Y-m'));
        
        $cashFlows = CashFlow::with('user', 'shift')
            ->whereMonth('tanggal', Carbon::parse($bulan)->month)
            ->whereYear('tanggal', Carbon::parse($bulan)->year)
            ->orderBy('tanggal', 'desc')
            ->orderBy('id', 'desc')
            ->get();

        $totalPemasukan = $cashFlows->where('tipe', 'in')->sum('nominal');
        $totalPengeluaran = $cashFlows->where('tipe', 'out')->sum('nominal');
        $labaBersih = $totalPemasukan - $totalPengeluaran;

        $bahanBaku = BahanBaku::orderBy('nama_bahan')->get();

        return view('dashboard.cash_flow.index', compact('cashFlows', 'bulan', 'totalPemasukan', 'totalPengeluaran', 'labaBersih', 'bahanBaku'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'tipe' => 'required|in:in,out',
            'kategori' => 'required|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
            'bahan_baku_id' => 'nullable|exists:bahan_bakus,id',
            'qty_beli' => 'nullable|numeric|min:0.01',
        ]);

        try {
            DB::beginTransaction();

            // Jika ini adalah belanja bahan baku dan bahan dipilih
            if ($request->tipe === 'out' && $request->kategori === 'Belanja Bahan Baku' && $request->bahan_baku_id) {
                $bahan = BahanBaku::find($request->bahan_baku_id);
                if ($bahan && $request->qty_beli) {
                    $hargaPerUnitBaru = $request->nominal / $request->qty_beli;
                    
                    // Update stok dan harga rata-rata
                    $bahan->update([
                        'harga_beli' => $request->nominal,
                        'qty_beli' => $request->qty_beli,
                        'harga_per_unit' => $hargaPerUnitBaru,
                        'stok' => $bahan->stok + $request->qty_beli
                    ]);

                    // Catat riwayat stok
                    StokBahanHistory::create([
                        'bahan_baku_id' => $bahan->id,
                        'user_id' => auth()->id(),
                        'tipe' => 'tambah',
                        'qty' => $request->qty_beli,
                        'keterangan' => 'Belanja via Buku Kas'
                    ]);

                    // Tambahkan keterangan bahan ke catatan kas jika kosong
                    if (!$request->keterangan) {
                        $request->merge([
                            'keterangan' => 'Beli ' . $bahan->nama_bahan . ' sejumlah ' . $request->qty_beli . ' ' . $bahan->satuan
                        ]);
                    }
                }
            }

            CashFlow::create([
                'user_id' => auth()->id(),
                'tanggal' => $request->tanggal,
                'tipe' => $request->tipe,
                'kategori' => $request->kategori,
                'nominal' => $request->nominal,
                'keterangan' => $request->keterangan,
            ]);

            DB::commit();
            return back()->with('sukses', 'Transaksi kas berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menambahkan transaksi: ' . $e->getMessage());
        }
    }

    public function destroy(CashFlow $cashFlow)
    {
        $cashFlow->delete();
        return back()->with('sukses', 'Data arus kas berhasil dihapus!');
    }
}
