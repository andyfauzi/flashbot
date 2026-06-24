<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Meja;

class MejaController extends Controller
{
    public function index()
    {
        $mejas = Meja::orderBy('nomor_meja', 'asc')->paginate(20);
        return view('dashboard.meja.index', compact('mejas'));
    }

    public function create()
    {
        // Not used, using modal in index
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:mejas,nomor_meja',
            'nama_meja'  => 'nullable|string|max:100',
            'kapasitas'  => 'required|integer|min:1',
            'deskripsi'  => 'nullable|string|max:255',
            'status'     => 'required|in:tersedia,direservasi,terisi',
        ]);

        $validated['is_active'] = $request->has('is_active');

        Meja::create($validated);

        return redirect()->route('dashboard.meja.index')->with('sukses', 'Meja berhasil ditambahkan.');
    }

    public function edit(Meja $meja)
    {
        // Not used, using modal in index
    }

    public function update(Request $request, Meja $meja)
    {
        $validated = $request->validate([
            'nomor_meja' => 'required|string|max:50|unique:mejas,nomor_meja,' . $meja->id,
            'nama_meja'  => 'nullable|string|max:100',
            'kapasitas'  => 'required|integer|min:1',
            'deskripsi'  => 'nullable|string|max:255',
            'status'     => 'required|in:tersedia,direservasi,terisi',
        ]);

        $validated['is_active'] = $request->has('is_active');

        $meja->update($validated);

        return redirect()->route('dashboard.meja.index')->with('sukses', 'Meja berhasil diperbarui.');
    }

    public function destroy(Meja $meja)
    {
        if ($meja->reservasis()->count() > 0) {
            return redirect()->route('dashboard.meja.index')->with('error', 'Meja tidak dapat dihapus karena memiliki riwayat reservasi.');
        }

        $meja->delete();

        return redirect()->route('dashboard.meja.index')->with('sukses', 'Meja berhasil dihapus.');
    }
}
