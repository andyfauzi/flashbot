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

        return view('superadmin.landing-page.index', compact('settings'));
    }

    /**
     * Simpan pembaruan pengaturan.
     */
    public function update(Request $request)
    {
        $data = $request->except(['_token', '_method']);

        foreach ($data as $key => $value) {
            LandlordSetting::set($key, $value);
        }

        return redirect()->route('superadmin.landing_page')->with('sukses', 'Pengaturan Landing Page berhasil diperbarui!');
    }
}
