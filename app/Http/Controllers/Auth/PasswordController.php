<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;

class PasswordController extends Controller
{
    public function showForceChangeForm()
    {
        // Jika tidak dipaksa ganti, lempar balik ke dashboard
        if (!Auth::user()->must_change_password) {
            return redirect()->route('pos.index');
        }

        return view('auth.force_change_password');
    }

    public function update(Request $request)
    {
        $user = Auth::user();

        if (!$user->must_change_password) {
            return redirect()->route('pos.index');
        }

        $request->validate([
            'password' => [
                'required',
                'string',
                'min:8',             // minimal 8 karakter
                'regex:/[A-Z]/',     // harus ada minimal 1 huruf besar
                'regex:/[0-9]/',     // harus ada minimal 1 angka
                'confirmed'          // butuh konfirmasi
            ],
        ], [
            'password.min' => 'Password minimal harus 8 karakter.',
            'password.regex' => 'Password harus mengandung minimal satu huruf kapital dan satu angka.',
            'password.confirmed' => 'Konfirmasi password tidak cocok.'
        ]);

        $user->password = Hash::make($request->password);
        $user->must_change_password = false;
        $user->password_changed_at = now();
        $user->save();

        return redirect()->route('pos.index')->with('sukses', 'Password berhasil diganti. Akun Anda kini lebih aman!');
    }
}
