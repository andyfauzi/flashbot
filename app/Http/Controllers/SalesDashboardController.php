<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\SalesVoucher;
use App\Models\TenantPayment;

class SalesDashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        
        // Ambil semua kode voucher milik sales ini
        $vouchers = SalesVoucher::where('user_id', $user->id)->get();
        $voucherIds = $vouchers->pluck('id')->toArray();
        
        // Jika sales belum diikat dengan voucher apapun
        if (empty($voucherIds)) {
            return view('sales.dashboard', [
                'totalPenjualan' => 0,
                'totalKomisi' => 0,
                'payments' => [],
                'vouchers' => collect()
            ])->with('warning', 'Akun Anda belum ditautkan dengan kode voucher manapun. Hubungi Super Admin.');
        }

        // Ambil riwayat pembayaran yang menggunakan kode voucher sales ini
        $payments = TenantPayment::with(['tenant', 'salesVoucher'])
            ->whereIn('sales_voucher_id', $voucherIds)
            ->whereIn('status', ['settlement', 'capture'])
            ->orderBy('paid_at', 'desc')
            ->get();

        $totalPenjualan = $payments->sum('gross_amount');
        $totalKomisi = $payments->sum('commission_amount');

        return view('sales.dashboard', compact('totalPenjualan', 'totalKomisi', 'payments', 'vouchers'));
    }
}
