<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use App\Models\Kurir;
use Illuminate\Http\Request;

class KurirController extends Controller
{
    public function index()
    {
        $kurirs = Kurir::orderBy('nama', 'asc')->get();
        return view('dashboard.kurir.index', compact('kurirs'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
        ]);

        // Normalisasi nomor HP kurir ke format 62xxx
        $nomorHp = preg_replace('/\D/', '', $request->nomor_hp);
        if (str_starts_with($nomorHp, '0')) {
            $nomorHp = '62' . substr($nomorHp, 1);
        }
        if (!str_starts_with($nomorHp, '62')) {
            $nomorHp = '62' . $nomorHp;
        }

        Kurir::create([
            'nama' => $request->nama,
            'nomor_hp' => $nomorHp,
        ]);

        return back()->with('sukses', 'Data kurir berhasil ditambahkan!');
    }

    public function update(Request $request, Kurir $kurir)
    {
        $request->validate([
            'nama' => 'required|string|max:255',
            'nomor_hp' => 'required|string|max:20',
        ]);

        // Normalisasi nomor HP kurir ke format 62xxx
        $nomorHp = preg_replace('/\D/', '', $request->nomor_hp);
        if (str_starts_with($nomorHp, '0')) {
            $nomorHp = '62' . substr($nomorHp, 1);
        }
        if (!str_starts_with($nomorHp, '62')) {
            $nomorHp = '62' . $nomorHp;
        }

        $kurir->update([
            'nama' => $request->nama,
            'nomor_hp' => $nomorHp,
        ]);

        return back()->with('sukses', 'Data kurir berhasil diperbarui!');
    }

    public function destroy(Kurir $kurir)
    {
        $kurir->delete();
        return back()->with('sukses', 'Data kurir berhasil dihapus!');
    }
}
