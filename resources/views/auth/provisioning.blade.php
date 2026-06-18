<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menyiapkan Toko Anda - Tenanta.id</title>
    <script src="https://cdn.tailwindcss.com"></script>
    @if(!$tenant->is_active)
        <meta http-equiv="refresh" content="3;url={{ route('auth.google.provisioning', ['tenant_id' => $tenant->id]) }}">
    @endif
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
    <div class="bg-white p-8 rounded-xl shadow-lg max-w-md w-full text-center">
        @if($tenant->is_active)
            <div class="mb-4 text-green-500">
                <svg class="w-16 h-16 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Toko Anda Siap!</h2>
            
            <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 mb-6 text-left text-sm mt-4">
                <p class="font-bold mb-2">PENTING! Simpan Kredensial Login Anda:</p>
                <p>Email: <span class="font-mono bg-white px-1 rounded border">{{ $tenant->owner_email }}</span></p>
                <p>Password: <span class="font-mono bg-white px-2 py-0.5 rounded border font-bold" id="tempPassword">{{ \Illuminate\Support\Facades\Cache::get('tenant_provision_password_' . $tenant->id) }}</span></p>
                <p class="mt-2 text-xs text-yellow-600">Password sementara ini tidak akan bisa Anda lihat lagi setelah menutup halaman ini.</p>
            </div>

            <button onclick="copyAndRedirect()" id="btnLanjut" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-bold py-3 px-4 rounded-lg transition-colors shadow">
                Salin Password & Buka Dashboard
            </button>

            <script>
                function copyAndRedirect() {
                    const pwd = document.getElementById('tempPassword').innerText;
                    navigator.clipboard.writeText(pwd).then(() => {
                        const btn = document.getElementById('btnLanjut');
                        btn.innerText = 'Mengarahkan...';
                        btn.classList.replace('bg-blue-600', 'bg-gray-500');
                        window.location.href = "{{ request()->getScheme() . '://' . $tenant->subdomain . '.localhost' . (request()->getPort() && request()->getPort() != 80 && request()->getPort() != 443 ? ':' . request()->getPort() : '') . '/login' }}";
                    }).catch(err => {
                        window.location.href = "{{ request()->getScheme() . '://' . $tenant->subdomain . '.localhost' . (request()->getPort() && request()->getPort() != 80 && request()->getPort() != 443 ? ':' . request()->getPort() : '') . '/login' }}";
                    });
                }
            </script>
        @else
            <div class="mb-6">
                <div class="animate-spin rounded-full h-16 w-16 border-b-2 border-blue-600 mx-auto"></div>
            </div>
            <h2 class="text-2xl font-bold text-gray-800 mb-2">Menyiapkan Toko Anda...</h2>
            <p class="text-gray-600">Sistem sedang membuat database, mengkonfigurasi pengaturan, dan menyiapkan modul untuk toko Anda. Ini hanya memakan waktu beberapa detik.</p>
            <p class="text-sm text-gray-400 mt-4">Halaman akan memuat ulang otomatis.</p>
        @endif
    </div>
</body>
</html>
