<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class KategoriController extends Controller
{
    public function index()
    {
        $kategoris = \App\Models\Kategori::withCount('produks')->orderBy('nama')->get();
        return view('chatbot.kategori.index', compact('kategoris'));
    }

    public function create()
    {
        return view('chatbot.kategori.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris',
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aktif' => 'boolean'
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('kategoris', 'public');
        }

        \App\Models\Kategori::create([
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'foto' => $fotoPath,
            'aktif' => $request->has('aktif') ? true : false,
        ]);

        return redirect()->route('chatbot.kategori.index')->with('success', 'Kategori berhasil ditambahkan.');
    }

    public function edit(\App\Models\Kategori $kategori)
    {
        return view('chatbot.kategori.edit', compact('kategori'));
    }

    public function update(Request $request, \App\Models\Kategori $kategori)
    {
        $request->validate([
            'nama' => 'required|string|max:255|unique:kategoris,nama,' . $kategori->id,
            'deskripsi' => 'nullable|string',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'aktif' => 'boolean'
        ]);

        $data = [
            'nama' => $request->nama,
            'deskripsi' => $request->deskripsi,
            'aktif' => $request->has('aktif') ? true : false,
        ];

        if ($request->hasFile('foto')) {
            // Hapus foto lama jika ada
            if ($kategori->foto && Storage::disk('public')->exists($kategori->foto)) {
                Storage::disk('public')->delete($kategori->foto);
            }
            $data['foto'] = $request->file('foto')->store('kategoris', 'public');
        }

        $kategori->update($data);

        return redirect()->route('chatbot.kategori.index')->with('success', 'Kategori berhasil diperbarui.');
    }

    public function destroy(\App\Models\Kategori $kategori)
    {
        // Pindahkan produk ke kategori Umum sebelum dihapus
        $kategoriUmumId = \App\Models\Kategori::where('nama', 'Umum')->first()->id ?? 1;
        if ($kategori->id != $kategoriUmumId) {
            $kategori->produks()->update(['kategori_id' => $kategoriUmumId]);
            
            if ($kategori->foto && Storage::disk('public')->exists($kategori->foto)) {
                Storage::disk('public')->delete($kategori->foto);
            }
            
            $kategori->delete();
            return redirect()->route('chatbot.kategori.index')->with('success', 'Kategori berhasil dihapus.');
        } else {
            return redirect()->route('chatbot.kategori.index')->with('error', 'Kategori Umum tidak dapat dihapus.');
        }
    }
}
