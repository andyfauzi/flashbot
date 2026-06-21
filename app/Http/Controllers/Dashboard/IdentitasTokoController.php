<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentitasToko;
use Illuminate\Support\Facades\Storage;

class IdentitasTokoController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action. Halaman ini sangat privat dan hanya dapat diakses oleh Admin.');
        }
        $identitas = IdentitasToko::first();
        return view('dashboard.pengaturan.toko', compact('identitas'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action. Halaman ini sangat privat dan hanya dapat diakses oleh Admin.');
        }
        $validated = $request->validate([
            'nama_toko' => 'required|string|max:255',
            'alamat_toko' => 'nullable|string',
            'nomor_telepon' => 'nullable|string|max:50',
            'pesan_footer' => 'nullable|string',
            'logo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            'nomor_rekening' => 'nullable|string',
            'qris' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // max 2MB
            'tema_portal' => 'required|in:warm,cool,kalem',
            'tema_desktop' => 'required|in:warm,cool,kalem',
            'jenis_layanan' => 'required|in:dine_in,take_away,keduanya',
            'jam_buka' => 'nullable|date_format:H:i',
            'jam_tutup' => 'nullable|date_format:H:i',
        ]);

        $identitas = IdentitasToko::first() ?? new IdentitasToko();
        
        $identitas->nama_toko = $validated['nama_toko'];
        $identitas->alamat_toko = $validated['alamat_toko'];
        $identitas->nomor_telepon = $validated['nomor_telepon'];
        $identitas->pesan_footer = $validated['pesan_footer'];
        $identitas->nomor_rekening = $validated['nomor_rekening'];
        $identitas->tema_portal = $validated['tema_portal'];
        $identitas->tema_desktop = $validated['tema_desktop'];
        $identitas->jenis_layanan = $validated['jenis_layanan'];
        $identitas->jam_buka = $validated['jam_buka'] ?? null;
        $identitas->jam_tutup = $validated['jam_tutup'] ?? null;

        if ($request->hasFile('logo')) {
            // Delete old logo if exists
            if ($identitas->logo_path && Storage::disk('public')->exists($identitas->logo_path)) {
                Storage::disk('public')->delete($identitas->logo_path);
            }
            
            $path = $request->file('logo')->store('identitas_toko', 'public');
            $identitas->logo_path = $path;
        }

        if ($request->hasFile('qris')) {
            // Delete old QRIS if exists
            if ($identitas->qris_path && Storage::disk('public')->exists($identitas->qris_path)) {
                Storage::disk('public')->delete($identitas->qris_path);
            }
            
            $path = $request->file('qris')->store('identitas_toko', 'public');
            $identitas->qris_path = $path;
        }

        $identitas->save();

        return redirect()->route('dashboard.pengaturan.toko')->with('sukses', 'Identitas toko berhasil diperbarui!');
    }
}
