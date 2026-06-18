<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;
use Spatie\Permission\Models\Permission;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index()
    {
        $users = User::with('permissions')->get();
        $permissions = Permission::all();
        
        return view('dashboard.users.index', compact('users', 'permissions'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:owner,admin,kasir,user',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
        ]);

        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        }

        return back()->with('sukses', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,'.$user->id,
            'role' => 'required|in:owner,admin,kasir,user',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('permissions')) {
            $user->syncPermissions($request->permissions);
        } else {
            // Jika tidak ada checkbox yang dicentang, hapus semua permission
            $user->syncPermissions([]);
        }

        return back()->with('sukses', 'Pengguna dan hak akses berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        if ($user->role === 'owner' || $user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun Owner atau akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('sukses', 'Pengguna berhasil dihapus.');
    }

    public function toggleUiMode(Request $request)
    {
        $user = auth()->user();
        if ($user) {
            $user->ui_mode = $user->ui_mode === 'list' ? 'grid' : 'list';
            $user->save();
            return back()->with('sukses', 'Tampilan berhasil diubah menjadi mode ' . ucfirst($user->ui_mode) . '.');
        }
        return back()->with('error', 'Terjadi kesalahan.');
    }
}
