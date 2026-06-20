<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\LandlordExpense;
use App\Models\TenantPayment;
use Illuminate\Http\Request;
use Carbon\Carbon;

class FinanceController extends Controller
{
    public function index()
    {
        // Total Omset Keseluruhan (dari payment settlement/capture)
        $totalOmsetKeseluruhan = TenantPayment::whereIn('status', ['settlement', 'capture'])
            ->sum('gross_amount');
            
        // Total Komisi Sales Keseluruhan
        $totalKomisiKeseluruhan = TenantPayment::whereIn('status', ['settlement', 'capture'])
            ->sum('commission_amount');

        // Total Pengeluaran Keseluruhan
        $totalPengeluaranKeseluruhan = LandlordExpense::sum('nominal');

        // Laba Bersih Keseluruhan
        $labaBersihKeseluruhan = $totalOmsetKeseluruhan - $totalKomisiKeseluruhan - $totalPengeluaranKeseluruhan;

        // Metrik Bulan Ini
        $startOfMonth = Carbon::now()->startOfMonth();
        $endOfMonth = Carbon::now()->endOfMonth();

        $omsetBulanIni = TenantPayment::whereIn('status', ['settlement', 'capture'])
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('gross_amount');

        $komisiBulanIni = TenantPayment::whereIn('status', ['settlement', 'capture'])
            ->whereBetween('paid_at', [$startOfMonth, $endOfMonth])
            ->sum('commission_amount');

        $pengeluaranBulanIni = LandlordExpense::whereBetween('tanggal', [$startOfMonth->toDateString(), $endOfMonth->toDateString()])
            ->sum('nominal');

        $labaBersihBulanIni = $omsetBulanIni - $komisiBulanIni - $pengeluaranBulanIni;

        // Riwayat Pengeluaran (Terbaru di atas)
        $expenses = LandlordExpense::orderBy('tanggal', 'desc')->orderBy('id', 'desc')->get();

        // ---------------------------------------------
        // TAMBAHAN: Detail Penjualan Paket & Rekap Sales
        // ---------------------------------------------
        
        // 1. Detail Penjualan Paket (Riwayat Transaksi)
        $salesPayments = TenantPayment::with(['tenant', 'salesVoucher'])
            ->whereIn('status', ['settlement', 'capture'])
            ->orderBy('paid_at', 'desc')
            ->get();

        // 2. Rekap Komisi Sales
        // Mengelompokkan berdasarkan sales_voucher_id
        $rekapSales = $salesPayments->filter(function($payment) {
            return $payment->sales_voucher_id != null;
        })->groupBy('sales_voucher_id')->map(function ($payments, $voucherId) {
            $voucher = $payments->first()->salesVoucher;
            return [
                'nama_sales' => $voucher ? $voucher->nama_sales : 'Tanpa Nama',
                'no_wa_sales' => $voucher ? $voucher->no_wa_sales : '-',
                'kode_voucher' => $voucher ? $voucher->kode_voucher : '-',
                'total_penjualan' => $payments->sum('gross_amount'),
                'total_komisi' => $payments->sum('commission_amount'),
                'jumlah_transaksi' => $payments->count()
            ];
        })->values();

        return view('superadmin.finance.index', compact(
            'totalOmsetKeseluruhan',
            'totalKomisiKeseluruhan',
            'totalPengeluaranKeseluruhan',
            'labaBersihKeseluruhan',
            'omsetBulanIni',
            'komisiBulanIni',
            'pengeluaranBulanIni',
            'labaBersihBulanIni',
            'expenses',
            'salesPayments',
            'rekapSales'
        ));
    }

    public function store(Request $request)
    {
        $request->validate([
            'tanggal' => 'required|date',
            'nama_pengeluaran' => 'required|string|max:255',
            'kategori' => 'nullable|string|max:255',
            'nominal' => 'required|numeric|min:0',
            'keterangan' => 'nullable|string',
        ]);

        LandlordExpense::create($request->all());

        return redirect()->route('superadmin.finance.index')->with('sukses', 'Pengeluaran berhasil dicatat.');
    }

    public function destroy(LandlordExpense $expense)
    {
        $expense->delete();
        return back()->with('sukses', 'Data pengeluaran berhasil dihapus.');
    }
}
