<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

use App\Models\Shift;
use Illuminate\Support\Facades\DB;

class ShiftController extends Controller
{
    public function buka(Request $request)
    {
        $request->validate([
            'modal_awal' => 'required|numeric|min:0',
        ]);

        $activeShift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
        if ($activeShift) {
            return back()->with('error', 'Anda masih memiliki shift aktif. Silakan tutup shift sebelumnya terlebih dahulu.');
        }

        Shift::create([
            'user_id' => auth()->id(),
            'waktu_buka' => now(),
            'modal_awal' => $request->modal_awal,
            'status' => 'aktif',
        ]);

        return redirect()->route('pos.index')->with('sukses', 'Shift berhasil dibuka. Selamat bertugas!');
    }

    public function tutup(Request $request)
    {
        $request->validate([
            'uang_fisik' => 'required|numeric|min:0',
        ]);

        $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
        if (!$shift) {
            return back()->with('error', 'Tidak ada shift aktif untuk ditutup.');
        }

        DB::beginTransaction();
        try {
            $shift->waktu_tutup = now();
            
            $uangSeharusnya = $shift->modal_awal + $shift->total_penjualan_tunai + $shift->penambahan_kasir - $shift->pengeluaran_kasir;
            $shift->selisih_uang = $request->uang_fisik - $uangSeharusnya;
            $shift->status = 'selesai';
            $shift->save();

            DB::commit();
            return redirect()->route('dashboard.cash_flow.index')->with('sukses', 'Shift berhasil ditutup. Laporan tersimpan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menutup shift: ' . $e->getMessage());
        }
    }

    public function pengeluaranKasir(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:255',
        ]);

        $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
        if (!$shift) {
            return back()->with('error', 'Anda harus membuka shift terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $shift->pengeluaran_kasir += $request->nominal;
            $shift->save();

            if (config('flashbot.features.finance')) {
                \App\Models\CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift->id,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'out',
                    'kategori' => 'Pengeluaran Kasir',
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                ]);
            }

            DB::commit();
            return back()->with('sukses', 'Pengeluaran berhasil dicatat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function penambahanKasir(Request $request)
    {
        $request->validate([
            'nominal' => 'required|numeric|min:1',
            'keterangan' => 'required|string|max:255',
        ]);

        $shift = Shift::where('user_id', auth()->id())->where('status', 'aktif')->first();
        if (!$shift) {
            return back()->with('error', 'Anda harus membuka shift terlebih dahulu.');
        }

        DB::beginTransaction();
        try {
            $shift->penambahan_kasir += $request->nominal;
            $shift->save();

            if (config('flashbot.features.finance')) {
                \App\Models\CashFlow::create([
                    'user_id' => auth()->id(),
                    'shift_id' => $shift->id,
                    'tanggal' => now()->toDateString(),
                    'tipe' => 'in',
                    'kategori' => 'Penambahan Kasir',
                    'nominal' => $request->nominal,
                    'keterangan' => $request->keterangan,
                ]);
            }

            DB::commit();
            return back()->with('sukses', 'Penambahan uang kasir berhasil dicatat!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }
}
