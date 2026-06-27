<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\SatuanKonversi;
use App\Models\IdentitasToko;
use Illuminate\Support\Facades\DB;

class SatuanKonversiController extends Controller
{
    public function index()
    {
        $identitas = IdentitasToko::first();
        $konversis = SatuanKonversi::orderBy('satuan_awal')->get();
        return view('dashboard.hpp.satuan.index', compact('identitas', 'konversis'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'satuan_awal' => 'required|string|max:50',
            'satuan_akhir' => 'required|string|max:50',
            'nilai_konversi' => 'required|numeric|min:0.0001',
            'keterangan' => 'nullable|string|max:255',
        ]);

        // prevent duplicate awal-akhir pair
        $exists = SatuanKonversi::where('satuan_awal', $request->satuan_awal)
                                ->where('satuan_akhir', $request->satuan_akhir)
                                ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Konversi satuan tersebut sudah ada.');
        }

        SatuanKonversi::create($request->all());

        return redirect()->route('dashboard.hpp.satuan.index')->with('success', 'Konversi satuan berhasil ditambahkan!');
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'satuan_awal' => 'required|string|max:50',
            'satuan_akhir' => 'required|string|max:50',
            'nilai_konversi' => 'required|numeric|min:0.0001',
            'keterangan' => 'nullable|string|max:255',
        ]);

        $konversi = SatuanKonversi::findOrFail($id);
        
        $exists = SatuanKonversi::where('satuan_awal', $request->satuan_awal)
                                ->where('satuan_akhir', $request->satuan_akhir)
                                ->where('id', '!=', $id)
                                ->exists();
        if ($exists) {
            return redirect()->back()->with('error', 'Konversi satuan tersebut sudah ada.');
        }

        $konversi->update($request->all());

        return redirect()->route('dashboard.hpp.satuan.index')->with('success', 'Konversi satuan berhasil diperbarui!');
    }

    public function destroy($id)
    {
        $konversi = SatuanKonversi::findOrFail($id);
        $konversi->delete();

        return redirect()->route('dashboard.hpp.satuan.index')->with('success', 'Konversi satuan berhasil dihapus!');
    }
}
