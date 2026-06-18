<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\Kategori;
use App\Models\Produk;
use App\Models\ProdukVarian;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::with(['varians', 'kategori'])->orderBy('id', 'desc')->get();
        return view('chatbot.produk.index', compact('produks'));
    }

    public function create()
    {
        $kategoris = \App\Models\Kategori::where('aktif', true)->orderBy('nama')->get();
        
        // Generate Auto Kode Produk (e.g. PRD-0001)
        $lastProduk = Produk::orderBy('id', 'desc')->first();
        $nextId = $lastProduk ? $lastProduk->id + 1 : 1;
        $autoKode = 'PRD-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
        
        return view('chatbot.produk.form', compact('kategoris', 'autoKode'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'kategori_id' => ['required', Rule::exists(Kategori::class, 'id')],
            'kode' => ['required', Rule::unique(Produk::class, 'kode')],
            'nama' => 'required|string|max:150',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'size_chart' => 'nullable|string',
            'foto' => 'nullable|mimes:jpeg,png,jpg,webp|max:2048',
            'varians' => 'required|array|min:1',
            'varians.*.nama' => 'required|string|max:100',
            'varians.*.stok' => 'required|integer|min:0',
            'varians.*.foto' => 'nullable|mimes:jpeg,png,jpg,webp|max:2048',
            'addons' => 'nullable|array',
            'addons.*.nama_addon' => 'required|string|max:100',
            'addons.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalStok = 0;
            foreach ($request->varians as $varian) {
                $totalStok += $varian['stok'];
            }

            $fotoPath = null;
            if ($request->hasFile('foto')) {
                $fotoPath = $request->file('foto')->store('produks', 'public');
            }

            $produk = Produk::create([
                'kategori_id' => $request->kategori_id,
                'kode' => $request->kode,
                'nama' => $request->nama,
                'harga' => $request->harga,
                'stok' => $totalStok, // cache stok total
                'deskripsi' => $request->deskripsi,
                'size_chart' => $request->size_chart,
                'foto' => $fotoPath,
                'aktif' => $request->has('aktif'),
                'is_made_to_order' => $request->has('is_made_to_order'),
                'promo_min_qty' => $request->promo_min_qty,
                'promo_harga' => $request->promo_harga
            ]);

            foreach ($request->varians as $idx => $varian) {
                $varianFotoPath = null;
                if (isset($varian['foto']) && $request->hasFile("varians.{$idx}.foto")) {
                    $varianFotoPath = $request->file("varians.{$idx}.foto")->store('varians', 'public');
                }

                $produk->varians()->create([
                    'nama_varian' => $varian['nama'],
                    'stok' => $varian['stok'],
                    'foto' => $varianFotoPath
                ]);
            }

            if ($request->has('addons')) {
                foreach ($request->addons as $addon) {
                    $produk->addons()->create([
                        'nama_addon' => $addon['nama_addon'],
                        'harga' => $addon['harga'],
                        'butuh_teks' => isset($addon['butuh_teks']) ? true : false
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('chatbot.produk.index')->with('sukses', 'Produk berhasil ditambahkan!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal menambahkan produk: ' . $e->getMessage());
        }
    }

    public function edit(Produk $produk)
    {
        $produk->load(['varians', 'addons']);
        $kategoris = \App\Models\Kategori::where('aktif', true)->orderBy('nama')->get();
        return view('chatbot.produk.form', compact('produk', 'kategoris'));
    }

    public function update(Request $request, Produk $produk)
    {
        $request->validate([
            'kategori_id' => ['required', Rule::exists(Kategori::class, 'id')],
            'kode' => ['required', Rule::unique(Produk::class, 'kode')->ignore($produk->id)],
            'nama' => 'required|string|max:150',
            'harga' => 'required|numeric|min:0',
            'deskripsi' => 'nullable|string',
            'size_chart' => 'nullable|string',
            'foto' => 'nullable|mimes:jpeg,png,jpg,webp|max:2048',
            'promo_min_qty' => 'nullable|integer|min:2',
            'promo_harga' => 'nullable|numeric|min:0',
            'varians' => 'required|array|min:1',
            'varians.*.nama' => 'required|string|max:100',
            'varians.*.stok' => 'required|integer|min:0',
            'varians.*.foto' => 'nullable|mimes:jpeg,png,jpg,webp|max:2048',
            'addons' => 'nullable|array',
            'addons.*.nama_addon' => 'required|string|max:100',
            'addons.*.harga' => 'required|numeric|min:0',
        ]);

        try {
            DB::beginTransaction();

            $totalStok = 0;
            $varianIdsKept = [];

            foreach ($request->varians as $idx => $varian) {
                $totalStok += $varian['stok'];
                
                $varianFotoPath = null;
                $hasNewFoto = false;
                if (isset($varian['foto']) && $request->hasFile("varians.{$idx}.foto")) {
                    $varianFotoPath = $request->file("varians.{$idx}.foto")->store('varians', 'public');
                    $hasNewFoto = true;
                }

                if (isset($varian['id']) && $varian['id']) {
                    // Update existing
                    $existingVarian = ProdukVarian::find($varian['id']);
                    if ($existingVarian) {
                        $updateData = [
                            'nama_varian' => $varian['nama'],
                            'stok' => $varian['stok']
                        ];
                        if ($hasNewFoto) {
                            if ($existingVarian->foto) {
                                Storage::disk('public')->delete($existingVarian->foto);
                            }
                            $updateData['foto'] = $varianFotoPath;
                        }

                        $existingVarian->update($updateData);
                        $varianIdsKept[] = $existingVarian->id;
                    }
                } else {
                    // Create new
                    $newVarian = $produk->varians()->create([
                        'nama_varian' => $varian['nama'],
                        'stok' => $varian['stok'],
                        'foto' => $varianFotoPath
                    ]);
                    $varianIdsKept[] = $newVarian->id;
                }
            }

            // Delete varians that were removed from the form
            $removedVarians = $produk->varians()->whereNotIn('id', $varianIdsKept)->get();
            foreach ($removedVarians as $rv) {
                if ($rv->foto) {
                    Storage::disk('public')->delete($rv->foto);
                }
                $rv->delete();
            }

            // Sync Addons
            $addonIdsKept = [];
            if ($request->has('addons')) {
                foreach ($request->addons as $addonData) {
                    if (isset($addonData['id']) && $addonData['id']) {
                        $existingAddon = \App\Models\ProdukAddon::find($addonData['id']);
                        if ($existingAddon) {
                            $existingAddon->update([
                                'nama_addon' => $addonData['nama_addon'],
                                'harga' => $addonData['harga'],
                                'butuh_teks' => isset($addonData['butuh_teks']) ? true : false
                            ]);
                            $addonIdsKept[] = $existingAddon->id;
                        }
                    } else {
                        $newAddon = $produk->addons()->create([
                            'nama_addon' => $addonData['nama_addon'],
                            'harga' => $addonData['harga'],
                            'butuh_teks' => isset($addonData['butuh_teks']) ? true : false
                        ]);
                        $addonIdsKept[] = $newAddon->id;
                    }
                }
            }
            // Delete addons that were removed
            $produk->addons()->whereNotIn('id', $addonIdsKept)->delete();

            $fotoPath = $produk->foto;
            if ($request->hasFile('foto')) {
                if ($produk->foto) {
                    Storage::disk('public')->delete($produk->foto);
                }
                $fotoPath = $request->file('foto')->store('produks', 'public');
            }

            $produk->update([
                'kategori_id' => $request->kategori_id,
                'kode' => $request->kode,
                'nama' => $request->nama,
                'harga' => $request->harga,
                'stok' => $totalStok, // cache stok total
                'deskripsi' => $request->deskripsi,
                'size_chart' => $request->size_chart,
                'foto' => $fotoPath,
                'aktif' => $request->has('aktif'),
                'is_made_to_order' => $request->has('is_made_to_order'),
                'promo_min_qty' => $request->promo_min_qty,
                'promo_harga' => $request->promo_harga
            ]);

            DB::commit();
            return redirect()->route('chatbot.produk.index')->with('sukses', 'Produk berhasil diperbarui!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Gagal memperbarui produk: ' . $e->getMessage());
        }
    }

    public function destroy(Produk $produk)
    {
        if ($produk->foto) {
            Storage::disk('public')->delete($produk->foto);
        }
        foreach ($produk->varians as $varian) {
            if ($varian->foto) {
                Storage::disk('public')->delete($varian->foto);
            }
        }
        $produk->delete();
        return redirect()->route('chatbot.produk.index')->with('sukses', 'Produk berhasil dihapus!');
    }

    public function duplicate($id)
    {
        try {
            DB::beginTransaction();
            $produk = Produk::with(['varians', 'addons'])->findOrFail($id);
            
            $newProduk = $produk->replicate();
            $newProduk->nama = $produk->nama . ' - Copy';
            
            // Generate new auto code
            $lastProduk = Produk::orderBy('id', 'desc')->first();
            $nextId = $lastProduk ? $lastProduk->id + 1 : 1;
            $newProduk->kode = 'PRD-' . str_pad($nextId, 4, '0', STR_PAD_LEFT);
            $newProduk->stok = 0; // reset stock
            $newProduk->promo_min_qty = $produk->promo_min_qty;
            $newProduk->promo_harga = $produk->promo_harga;
            $newProduk->save();

            // Duplicate Varians
            foreach ($produk->varians as $varian) {
                $newVarian = $varian->replicate();
                $newVarian->produk_id = $newProduk->id;
                $newVarian->stok = 0; // reset stock
                $newVarian->save();
            }

            // Duplicate Addons
            foreach ($produk->addons as $addon) {
                $newAddon = $addon->replicate();
                $newAddon->produk_id = $newProduk->id;
                $newAddon->save();
            }

            DB::commit();
            return redirect()->route('chatbot.produk.index')->with('sukses', 'Produk berhasil diduplikasi!');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->with('error', 'Gagal menduplikasi produk: ' . $e->getMessage());
        }
    }
}
