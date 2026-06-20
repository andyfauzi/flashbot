<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\SalesVoucher;
use Illuminate\Http\Request;

class SalesVoucherController extends Controller
{
    public function index()
    {
        $vouchers = SalesVoucher::withSum('payments', 'commission_amount')
            ->withCount('payments')
            ->orderBy('created_at', 'desc')
            ->get();
            
        return view('superadmin.vouchers.index', compact('vouchers'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kode_voucher' => 'required|string|unique:landlord.sales_vouchers,kode_voucher',
            'nama_sales' => 'required|string|max:255',
            'no_wa_sales' => 'nullable|string|max:20',
            'diskon_persen' => 'required|integer|min:0|max:100',
            'komisi_persen' => 'required|integer|min:0|max:100',
        ]);

        SalesVoucher::create([
            'kode_voucher' => strtoupper($request->kode_voucher),
            'nama_sales' => $request->nama_sales,
            'no_wa_sales' => $request->no_wa_sales,
            'diskon_persen' => $request->diskon_persen,
            'komisi_persen' => $request->komisi_persen,
            'is_active' => true,
        ]);

        return redirect()->route('superadmin.vouchers.index')->with('sukses', 'Voucher berhasil ditambahkan.');
    }

    public function toggle(SalesVoucher $voucher)
    {
        $voucher->update(['is_active' => !$voucher->is_active]);
        return back()->with('sukses', 'Status voucher berhasil diubah.');
    }

    public function destroy(SalesVoucher $voucher)
    {
        if ($voucher->payments()->count() > 0) {
            return back()->with('error', 'Voucher tidak dapat dihapus karena sudah pernah digunakan. Silakan nonaktifkan saja.');
        }
        $voucher->delete();
        return back()->with('sukses', 'Voucher berhasil dihapus.');
    }
}
