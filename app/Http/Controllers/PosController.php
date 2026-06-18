<?php

namespace App\Http\Controllers;

use App\Models\Pesanan;
use App\Models\PesananItem;
use App\Models\Produk;
use App\Models\BahanBaku;
use App\Models\StokBahanHistory;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class PosController extends Controller
{
    public function index(Request $request)
    {
        $tenantId = app('current_tenant')->id;
        $produks = \Illuminate\Support\Facades\Cache::tags(["tenant_{$tenantId}_produk"])->remember("pos_produks_{$tenantId}", 3600, function () {
            return Produk::with(['varians', 'kategori', 'addons'])->where('aktif', true)->orderBy('nama')->get();
        });
        $kategoris = \App\Models\Kategori::orderBy('nama')->get();
        $activeShift = \App\Models\Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
        
        $editOrder = null;
        if ($request->has('edit_order')) {
            $editOrder = \App\Models\Pesanan::with('items')->find($request->edit_order);
        }

        return view('pos.index', compact('produks', 'kategoris', 'activeShift', 'editOrder'));
    }

    public function store(Request $request, \App\Services\CheckoutService $checkoutService)
    {
        $validated = $request->validate([
            'nama_penerima' => 'nullable|string|max:100',
            'nomor_wa' => 'nullable|string|max:20',
            'metode_pembayaran' => 'required|string',
            'is_preorder' => 'nullable|boolean',
            'tanggal_diambil' => 'nullable|date',
            'uang_muka' => 'nullable|numeric|min:0',
            'cart' => 'required|array',
            'cart.*.id' => 'required|exists:produks,id',
            'cart.*.varian_id' => 'nullable|exists:produk_varians,id',
            'cart.*.qty' => 'required|integer|min:1',
            'cart.*.addons' => 'nullable|array',
            'cart.*.addons.*.id' => 'required|integer',
            'cart.*.addons.*.nama' => 'required|string',
            'cart.*.addons.*.harga' => 'required|numeric',
        ]);

        try {
            $pesanan = $checkoutService->processPosCheckout($validated, auth()->id());

            if ($pesanan->nomor_wa !== '-') {
                try {
                    $waService = app(\App\Services\WhatsAppService::class);
                    $tenant = app('current_tenant');
                    $namaToko = $tenant->identitas_toko->nama_toko ?? $tenant->name ?? 'Toko Kami';
                    $pesan = "Halo *" . ($pesanan->nama_penerima ?: 'Pelanggan') . "*,\n\nTerima kasih telah berbelanja di *{$namaToko}*.\n\nBerikut adalah rincian pesanan Anda:\nNomor Order: *{$pesanan->nomor_order}*\nTotal Bayar: *Rp " . number_format($pesanan->total_biaya, 0, ',', '.') . "*\n\nIni adalah struk digital bukti sah pembayaran Anda.\nTerima kasih atas kunjungan Anda! 🌸";
                    $waService->kirimPesan($pesanan->nomor_wa, $pesan);
                } catch (\Exception $e) {
                    // Abaikan jika gagal mengirim WA agar transaksi tetap sukses
                }
            }

            return response()->json([
                'status' => 'success',
                'message' => 'Transaksi berhasil!',
                'pesanan_id' => $pesanan->id
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ], 400);
        }
    }

    public function printReceipt(Pesanan $pesanan)
    {
        // Load relasi item dan produk beserta varian
        $pesanan->load('items.produk', 'items.produkVarian');
        return view('pos.print', compact('pesanan'));
    }
}
