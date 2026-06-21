<?php

namespace App\Http\Controllers;

use App\Models\LandlordSetting;
use Illuminate\Http\Request;

class LandingPageSettingController extends Controller
{
    /**
     * Tampilkan form pengaturan landing page.
     */
    public function index()
    {
        // Ambil semua pengaturan dan jadikan key-value pair array
        $settings = LandlordSetting::pluck('value', 'key')->toArray();
        
        // Ganti null dengan string kosong agar form tidak merender nilai default
        foreach ($settings as $key => $value) {
            if ($value === null) {
                $settings[$key] = '';
            }
        }

        return view('superadmin.landing-page.index', compact('settings'));
    }

    /**
     * Simpan pembaruan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method', 'hero_image', 'user_guide_image']);

        // Handle image upload
        if ($request->hasFile('hero_image')) {
            $request->validate([
                'hero_image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $path = $request->file('hero_image')->store('landing', 'public');
            LandlordSetting::set('hero_image', $path);
        }

        if ($request->hasFile('user_guide_image')) {
            $request->validate([
                'user_guide_image' => 'image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            ]);

            $path = $request->file('user_guide_image')->store('landing', 'public');
            LandlordSetting::set('user_guide_image', $path);
        }

        foreach ($data as $key => $value) {
            LandlordSetting::set($key, $value);
        }

        return redirect()->route('superadmin.landing_page')->with('sukses', 'Pengaturan Landing Page berhasil diperbarui!');
    }
}
