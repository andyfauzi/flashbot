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
            'tema_desktop' => 'required|in:warm,cool,kalem',
            'jenis_layanan' => 'required|in:dine_in,take_away,keduanya',
            'jam_buka' => 'nullable|date_format:H:i',
            'jam_tutup' => 'nullable|date_format:H:i',
            'zona_waktu' => 'required|in:Asia/Jakarta,Asia/Makassar,Asia/Jayapura',
        ]);

        $identitas = IdentitasToko::first() ?? new IdentitasToko();
        
        $identitas->nama_toko = $validated['nama_toko'];
        $identitas->alamat_toko = $validated['alamat_toko'];
        $identitas->nomor_telepon = $validated['nomor_telepon'];
        $identitas->pesan_footer = $validated['pesan_footer'];
        $identitas->nomor_rekening = $validated['nomor_rekening'];
        $identitas->tema_desktop = $validated['tema_desktop'];
        $identitas->jenis_layanan = $validated['jenis_layanan'];
        $identitas->jam_buka = $validated['jam_buka'] ?? null;
        $identitas->jam_tutup = $validated['jam_tutup'] ?? null;
        $identitas->zona_waktu = $validated['zona_waktu'];



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

    public function landingPage()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action. Halaman ini sangat privat dan hanya dapat diakses oleh Admin.');
        }
        $identitas = IdentitasToko::first();
        return view('dashboard.pengaturan.landing_page', compact('identitas'));
    }

    public function updateLandingPage(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action.');
        }

        $validated = $request->validate([
            'tema_portal' => 'required|in:warm,cool,kalem',
            'syarat_ketentuan_portal' => 'nullable|string',
            'kontak_portal' => 'nullable|string',
            'deskripsi_toko' => 'nullable|string',
            'hero_image' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:5120',
            'galeri.*' => 'nullable|image|mimes:jpeg,png,jpg,webp|max:3072',
        ]);

        $identitas = IdentitasToko::first() ?? new IdentitasToko();

        $identitas->tema_portal = $validated['tema_portal'];
        $identitas->syarat_ketentuan_portal = $validated['syarat_ketentuan_portal'] ?? null;
        $identitas->kontak_portal = $validated['kontak_portal'] ?? null;
        $identitas->deskripsi_toko = $validated['deskripsi_toko'] ?? null;

        if ($request->hasFile('hero_image')) {
            if ($identitas->hero_image_path && Storage::disk('public')->exists($identitas->hero_image_path)) {
                Storage::disk('public')->delete($identitas->hero_image_path);
            }
            $path = $request->file('hero_image')->store('identitas_toko/hero', 'public');
            $identitas->hero_image_path = $path;
        }

        if ($request->hasFile('galeri')) {
            $existingGaleri = is_array($identitas->galeri_paths) ? $identitas->galeri_paths : [];
            $newGaleriPaths = [];
            foreach ($request->file('galeri') as $file) {
                if ($file->isValid()) {
                    $newGaleriPaths[] = $file->store('identitas_toko/galeri', 'public');
                }
            }
            if (count($existingGaleri) > 0) {
                foreach ($existingGaleri as $oldPath) {
                    if (Storage::disk('public')->exists($oldPath)) {
                        Storage::disk('public')->delete($oldPath);
                    }
                }
            }
            $identitas->galeri_paths = $newGaleriPaths;
        }

        $identitas->save();

        return redirect()->route('dashboard.pengaturan.landing_page')->with('sukses', 'Pengaturan Landing Page berhasil diperbarui!');
    }
}
