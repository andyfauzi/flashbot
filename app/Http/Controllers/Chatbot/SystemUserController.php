<?php

namespace App\Http\Controllers\Chatbot;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ChatbotDevice;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class SystemUserController extends Controller
{
    public function index()
    {
        $systemUsers = User::with('device')->get();
        $devices = ChatbotDevice::all();
        return view('chatbot.system_users.index', compact('systemUsers', 'devices'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'role' => 'required|in:admin,user,kasir,gudang',
            'device_id' => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.chatbot_devices,id',
            'permissions' => 'nullable|array',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'device_id' => in_array($request->role, ['user', 'kasir', 'gudang']) ? $request->device_id : null,
        ]);

        if ($request->has('permissions')) {
            $mappedPerms = collect($request->permissions)->map(function($p) {
                $map = ['pos' => 'akses_pos', 'jadwal' => 'akses_laporan', 'produk' => 'akses_produk', 'stok' => 'akses_hpp', 'keuangan' => 'akses_kas'];
                return $map[$p] ?? 'akses_' . $p;
            })->toArray();
            $user->syncPermissions($mappedPerms);
        }

        return back()->with('sukses', 'User sistem berhasil ditambahkan!');
    }

    public function update(Request $request, User $user)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email,' . $user->id,
            'role' => 'required|in:admin,user,kasir,gudang',
            'password' => ['nullable', 'string', 'min:8', 'regex:/[A-Z]/', 'regex:/[0-9]/'],
            'device_id' => 'nullable|exists:'.\App\Services\TenantManager::getTenantConnection().'.chatbot_devices,id',
            'permissions' => 'nullable|array',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'role' => $request->role,
            'device_id' => in_array($request->role, ['user', 'kasir', 'gudang']) ? $request->device_id : null,
        ];

        if ($request->filled('password')) {
            $data['password'] = Hash::make($request->password);
        }

        $user->update($data);

        if ($request->has('permissions')) {
            $mappedPerms = collect($request->permissions)->map(function($p) {
                $map = ['pos' => 'akses_pos', 'jadwal' => 'akses_laporan', 'produk' => 'akses_produk', 'stok' => 'akses_hpp', 'keuangan' => 'akses_kas'];
                return $map[$p] ?? 'akses_' . $p;
            })->toArray();
            $user->syncPermissions($mappedPerms);
        } else {
            $user->syncPermissions([]);
        }

        return back()->with('sukses', 'User sistem berhasil diperbarui!');
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Anda tidak dapat menghapus akun Anda sendiri.');
        }

        $user->delete();
        return back()->with('sukses', 'User sistem berhasil dihapus!');
    }
}
