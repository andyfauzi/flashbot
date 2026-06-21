<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use App\Services\AuditLogger;

class SuperAdminController extends Controller
{
    public function index()
    {
        // Ensure we are on landlord connection
        TenantManager::switchToLandlord();

        $tenants = Tenant::orderBy('created_at', 'desc')->get();
        $totalTenants = $tenants->count();
        $activeTenants = $tenants->where('is_active', true)->count();
        $suspendedTenants = $totalTenants - $activeTenants;
        
        // Estimasi Pendapatan dari TenantPayment (jika ada)
        $monthlyRevenue = 0;
        if (DB::connection('landlord')->getSchemaBuilder()->hasTable('tenant_payments')) {
            $monthlyRevenue = DB::connection('landlord')->table('tenant_payments')
                ->whereIn('status', ['settlement', 'capture'])
                ->whereMonth('created_at', now()->month)
                ->sum('gross_amount');
        }
        
        // Cek status broadcast dan gateway untuk setiap tenant
        foreach ($tenants as $tenant) {
            try {
                $dbName = 'tenant_' . strtolower($tenant->subdomain);
                config(['database.connections.tenant.database' => $dbName]);
                DB::purge('tenant');
                $identitas = DB::connection('tenant')->table('identitas_tokos')->first();
                $tenant->is_broadcast_approved = $identitas ? (bool)$identitas->is_broadcast_approved : false;
                $tenant->whatsapp_gateway = $identitas ? $identitas->whatsapp_gateway : 'baileys';
                $tenant->is_payment_gateway_active = $identitas ? (bool)$identitas->is_payment_gateway_active : false;
            } catch (\Exception $e) {
                $tenant->is_broadcast_approved = false;
                $tenant->whatsapp_gateway = 'baileys';
                $tenant->is_payment_gateway_active = false;
            }
        }
        
        TenantManager::switchToLandlord();

        return view('superadmin.index', compact('tenants', 'totalTenants', 'activeTenants', 'suspendedTenants', 'monthlyRevenue'));
    }

    public function showMetaSettings()
    {
        TenantManager::switchToLandlord();
        $metaPhoneNumberId = \App\Models\LandlordSetting::get('meta_phone_number_id');
        $metaAccessToken = \App\Models\LandlordSetting::get('meta_access_token');
        
        return view('superadmin.meta', compact('metaPhoneNumberId', 'metaAccessToken'));
    }

    public function showMidtransSettings()
    {
        TenantManager::switchToLandlord();
        $midtransServerKey = \App\Models\LandlordSetting::get('midtrans_server_key');
        $midtransClientKey = \App\Models\LandlordSetting::get('midtrans_client_key');
        $midtransIsProduction = \App\Models\LandlordSetting::get('midtrans_is_production', '0');
        
        return view('superadmin.midtrans', compact('midtransServerKey', 'midtransClientKey', 'midtransIsProduction'));
    }

    public function updateMetaSettings(Request $request)
    {
        TenantManager::switchToLandlord();
        $request->validate([
            'meta_phone_number_id' => 'required|string',
            'meta_access_token' => 'required|string',
        ]);

        \App\Models\LandlordSetting::set('meta_phone_number_id', $request->meta_phone_number_id);
        \App\Models\LandlordSetting::set('meta_access_token', $request->meta_access_token);

        return redirect()->route('superadmin.meta')->with('success', 'Pengaturan Meta WhatsApp Pusat berhasil disimpan.');
    }

    public function updateMidtransSettings(Request $request)
    {
        TenantManager::switchToLandlord();
        $request->validate([
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
        ]);

        \App\Models\LandlordSetting::set('midtrans_server_key', $request->midtrans_server_key);
        \App\Models\LandlordSetting::set('midtrans_client_key', $request->midtrans_client_key);
        \App\Models\LandlordSetting::set('midtrans_is_production', $request->has('midtrans_is_production') ? '1' : '0');

        return redirect()->route('superadmin.midtrans')->with('success', 'Pengaturan Midtrans berhasil disimpan.');
    }

    public function store(Request $request)
    {
        TenantManager::switchToLandlord();

        $request->validate([
            'name' => 'required|string|max:255',
            'subdomain' => 'required|alpha_dash|unique:landlord.tenants,subdomain|max:50',
            'plan' => 'required|in:starter,pro,business',
        ]);

        $subdomain = strtolower($request->subdomain);
        $dbName = 'tenant_' . $subdomain;

        try {
            // 1. Create database in MySQL
            // NOTE: Using a PDO raw statement to avoid SQL injection on database name.
            // Since we validate that $subdomain is alpha_dash, $dbName is safe.
            DB::connection('landlord')->statement("CREATE DATABASE `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");

            // 2. Set temporary config for dynamic migration
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');

            // 3. Run migrations on the new database
            Artisan::call('migrate', [
                '--database' => 'tenant',
                '--path' => 'database/migrations',
                '--force' => true,
            ]);

            // Create default admin user in the new tenant database
            $tempPassword = \Illuminate\Support\Str::random(12);

            DB::connection('tenant')->table('users')->insert([
                'name' => 'Admin ' . $request->name,
                'email' => 'admin@' . $subdomain . '.localhost',
                'password' => bcrypt($tempPassword),
                'role' => 'owner',
                'must_change_password' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);

            // 4. Create tenant record in landlord database
            $tenant = Tenant::create([
                'name' => $request->name,
                'subdomain' => $subdomain,
                'database_name' => $dbName,
                'plan' => $request->plan,
                'is_active' => true,
            ]);

            AuditLogger::record('tenant.created', "tenant:{$tenant->id}", [
                'name' => $request->name,
                'subdomain' => $subdomain,
            ]);

            // Restore landlord connection just in case
            TenantManager::switchToLandlord();

            // Simpan password sementara ke flash session agar bisa ditampilkan di UI satu kali
            session()->flash('tenant_temp_password', [
                'toko'     => $request->name,
                'email'    => 'admin@' . $subdomain . '.localhost',
                'password' => $tempPassword,
            ]);

            return redirect()->back()->with('success', "Tenant {$request->name} (subdomain: {$subdomain}) successfully created with database {$dbName}!");
        } catch (\Exception $e) {
            Log::error("SuperAdmin Tenant Creation Error: " . $e->getMessage());
            
            // Cleanup database if created but migration failed
            try {
                DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `{$dbName}`");
            } catch (\Exception $cleanupEx) {
                Log::error("SuperAdmin Tenant Cleanup Error: " . $cleanupEx->getMessage());
            }

            TenantManager::switchToLandlord();

            return redirect()->back()->withErrors(['error' => 'Failed to create tenant database or run migrations: ' . $e->getMessage()]);
        }
    }

    public function toggleActive($id)
    {
        TenantManager::switchToLandlord();

        $tenant = Tenant::findOrFail($id);
        $tenant->is_active = !$tenant->is_active;
        $tenant->save();

        AuditLogger::record('tenant.toggled', "tenant:{$tenant->id}", [
            'is_active' => $tenant->is_active
        ]);

        return redirect()->back()->with('success', "Tenant {$tenant->name} status updated successfully.");
    }

    public function toggleBroadcast($id)
    {
        TenantManager::switchToLandlord();
        $tenant = Tenant::findOrFail($id);

        try {
            // Kita perlu mengubah is_broadcast_approved di database tenant terkait
            $dbName = 'tenant_' . strtolower($tenant->subdomain);
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');

            $identitas = DB::connection('tenant')->table('identitas_tokos')->first();
            if ($identitas) {
                $newValue = !$identitas->is_broadcast_approved;
                DB::connection('tenant')->table('identitas_tokos')->update(['is_broadcast_approved' => $newValue]);
                
                AuditLogger::record('tenant.broadcast_toggled', "tenant:{$tenant->id}", [
                    'is_broadcast_approved' => $newValue
                ]);
                
                TenantManager::switchToLandlord();
                $status = $newValue ? 'diaktifkan' : 'dinonaktifkan';
                return redirect()->back()->with('success', "Fitur Broadcast Promosi untuk Tenant {$tenant->name} berhasil {$status}.");
            }
            
            TenantManager::switchToLandlord();
            return redirect()->back()->withErrors(['error' => 'Identitas toko tidak ditemukan di database tenant.']);
        } catch (\Exception $e) {
            TenantManager::switchToLandlord();
            return redirect()->back()->withErrors(['error' => 'Gagal mengubah status broadcast: ' . $e->getMessage()]);
        }
    }

    public function togglePaymentGateway($id)
    {
        TenantManager::switchToLandlord();
        $tenant = Tenant::findOrFail($id);

        try {
            $dbName = 'tenant_' . strtolower($tenant->subdomain);
            config(['database.connections.tenant.database' => $dbName]);
            DB::purge('tenant');

            $identitas = DB::connection('tenant')->table('identitas_tokos')->first();
            if ($identitas) {
                $newValue = !$identitas->is_payment_gateway_active;
                DB::connection('tenant')->table('identitas_tokos')->update(['is_payment_gateway_active' => $newValue]);
                
                AuditLogger::record('tenant.payment_gateway_toggled', "tenant:{$tenant->id}", [
                    'is_payment_gateway_active' => $newValue
                ]);
                
                TenantManager::switchToLandlord();
                $status = $newValue ? 'diaktifkan' : 'dinonaktifkan';
                return redirect()->back()->with('success', "Fitur Payment Gateway untuk Tenant {$tenant->name} berhasil {$status}.");
            }
            
            TenantManager::switchToLandlord();
            return redirect()->back()->withErrors(['error' => 'Identitas toko tidak ditemukan di database tenant.']);
        } catch (\Exception $e) {
            TenantManager::switchToLandlord();
            return redirect()->back()->withErrors(['error' => 'Gagal mengubah status payment gateway: ' . $e->getMessage()]);
        }
    }

    public function updatePlan(Request $request, $id)
    {
        TenantManager::switchToLandlord();

        $request->validate([
            'plan' => 'required|in:starter,pro,business',
            'plan_expires_at' => 'nullable|date',
            'features' => 'nullable|array',
        ]);

        $tenant = Tenant::findOrFail($id);
        $tenant->plan = $request->plan;
        $tenant->plan_expires_at = $request->plan_expires_at ? \Carbon\Carbon::parse($request->plan_expires_at) : null;

        // Set feature flags based on checkbox inputs
        $features = [
            'chatbot' => isset($request->features['chatbot']),
            'pos' => isset($request->features['pos']),
            'erp' => isset($request->features['erp']),
            'finance' => isset($request->features['finance']),
            'gemini_ai' => isset($request->features['gemini_ai']),
        ];
        $tenant->feature_flags = $features;
        $tenant->save();

        AuditLogger::record('tenant.plan_updated', "tenant:{$tenant->id}", [
            'plan' => $request->plan,
            'features' => $features
        ]);

        return redirect()->back()->with('success', "Tenant {$tenant->name} subscription plan updated successfully.");
    }

    public function destroy($id)
    {
        TenantManager::switchToLandlord();

        $tenant = Tenant::findOrFail($id);
        $dbName = $tenant->database_name;
        $tenantName = $tenant->name;

        try {
            // Drop tenant database
            DB::connection('landlord')->statement("DROP DATABASE IF EXISTS `{$dbName}`");

            // Delete tenant record
            $tenant->delete();

            AuditLogger::record('tenant.deleted', "tenant:{$id}", [
                'name' => $tenantName,
                'database' => $dbName
            ]);

            return redirect()->back()->with('success', "Tenant {$tenantName} dan seluruh datanya berhasil dihapus permanen.");
        } catch (\Exception $e) {
            Log::error("SuperAdmin Tenant Deletion Error: " . $e->getMessage());
            return redirect()->back()->withErrors(['error' => 'Gagal menghapus tenant: ' . $e->getMessage()]);
        }
    }

    public function updateSettings(Request $request)
    {
        TenantManager::switchToLandlord();

        if ($request->has('is_payment_gateway_enabled')) {
            \App\Models\LandlordSetting::set('is_payment_gateway_enabled', '1');
        } else {
            // Only update if it was submitted in a form that contains it
            // if we are updating other settings, we shouldn't overwrite it unless explicitly provided
            // To be safe, let's just check if it's present.
        }

        if ($request->has('global_announcement_text')) {
            \App\Models\LandlordSetting::set('global_announcement_text', $request->global_announcement_text);
        }

        AuditLogger::record('superadmin.settings_updated', "system", [
            'updated_by' => auth()->id()
        ]);

        return redirect()->back()->with('success', 'Pengaturan global berhasil disimpan.');
    }

    public function packageMenus()
    {
        TenantManager::switchToLandlord();
        $menus = \App\Models\PackageMenu::all()->groupBy('category');
        return view('superadmin.package-menus.index', compact('menus'));
    }

    public function updatePackageMenus(Request $request)
    {
        TenantManager::switchToLandlord();
        
        // Reset all menus first
        \App\Models\PackageMenu::query()->update([
            'gratis_enabled' => false,
            'starter_enabled' => false,
            'pro_enabled' => false,
            'business_enabled' => false,
        ]);

        $plans = ['gratis', 'starter', 'pro', 'business'];

        foreach ($plans as $plan) {
            if ($request->has($plan) && is_array($request->$plan)) {
                $menuKeys = array_keys($request->$plan);
                \App\Models\PackageMenu::whereIn('menu_key', $menuKeys)->update([
                    "{$plan}_enabled" => true
                ]);
            }
        }

        // Clear all plan caches
        \Illuminate\Support\Facades\Cache::forget('plan_menus_gratis');
        \Illuminate\Support\Facades\Cache::forget('plan_menus_starter');
        \Illuminate\Support\Facades\Cache::forget('plan_menus_pro');
        \Illuminate\Support\Facades\Cache::forget('plan_menus_business');

        // Save Employee Limits
        if ($request->has('limit_karyawan_gratis')) {
            \App\Models\LandlordSetting::set('limit_karyawan_gratis', $request->limit_karyawan_gratis);
        }
        if ($request->has('limit_karyawan_starter')) {
            \App\Models\LandlordSetting::set('limit_karyawan_starter', $request->limit_karyawan_starter);
        }
        if ($request->has('limit_karyawan_pro')) {
            \App\Models\LandlordSetting::set('limit_karyawan_pro', $request->limit_karyawan_pro);
        }
        if ($request->has('limit_karyawan_business')) {
            \App\Models\LandlordSetting::set('limit_karyawan_business', $request->limit_karyawan_business);
        }

        // Save WA Quota Limits
        if ($request->has('limit_wa_gratis')) {
            \App\Models\LandlordSetting::set('limit_wa_gratis', $request->limit_wa_gratis);
        }
        if ($request->has('limit_wa_starter')) {
            \App\Models\LandlordSetting::set('limit_wa_starter', $request->limit_wa_starter);
        }
        if ($request->has('limit_wa_pro')) {
            \App\Models\LandlordSetting::set('limit_wa_pro', $request->limit_wa_pro);
        }
        if ($request->has('limit_wa_business')) {
            \App\Models\LandlordSetting::set('limit_wa_business', $request->limit_wa_business);
        }

        // Save Device Limits
        if ($request->has('limit_device_gratis')) {
            \App\Models\LandlordSetting::set('limit_device_gratis', $request->limit_device_gratis);
        }
        if ($request->has('limit_device_starter')) {
            \App\Models\LandlordSetting::set('limit_device_starter', $request->limit_device_starter);
        }
        if ($request->has('limit_device_pro')) {
            \App\Models\LandlordSetting::set('limit_device_pro', $request->limit_device_pro);
        }
        if ($request->has('limit_device_business')) {
            \App\Models\LandlordSetting::set('limit_device_business', $request->limit_device_business);
        }

        // Save Yearly Discount Percent
        if ($request->has('discount_yearly_percent')) {
            \App\Models\LandlordSetting::set('discount_yearly_percent', $request->discount_yearly_percent);
        }

        // Save Show Package Menus
        \App\Models\LandlordSetting::set('show_package_menus_on_pricing', $request->has('show_package_menus_on_pricing') ? '1' : '0');

        // Save Fallback Payment
        if ($request->has('payment_instructions_fallback')) {
            \App\Models\LandlordSetting::set('payment_instructions_fallback', $request->payment_instructions_fallback);
        }
        if ($request->has('whatsapp_confirmation_number')) {
            \App\Models\LandlordSetting::set('whatsapp_confirmation_number', $request->whatsapp_confirmation_number);
        }

        return redirect()->back()->with('success', 'Konfigurasi menu paket dan batasan fitur berhasil diperbarui.');
    }

    public function helpGuides()
    {
        TenantManager::switchToLandlord();
        
        // Auto-sync jika masih kosong
        if (\App\Models\LandlordHelpGuide::count() == 0) {
            $oldSettings = \Illuminate\Support\Facades\DB::connection('landlord')->table('landlord_settings')->where('key', 'user_guide_text')->first();
            if ($oldSettings && !empty($oldSettings->value)) {
                \App\Models\LandlordHelpGuide::create([
                    'pertanyaan' => 'Panduan Penggunaan Tenanta.id',
                    'jawaban' => $oldSettings->value,
                    'urutan' => 1
                ]);
            }
        }
        
        $guides = \App\Models\LandlordHelpGuide::orderBy('urutan')->get();
        return view('superadmin.help_guides', compact('guides'));
    }

    public function storeHelpGuide(Request $request)
    {
        TenantManager::switchToLandlord();
        $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'jawaban' => 'required|string',
            'urutan' => 'nullable|integer',
        ]);

        \App\Models\LandlordHelpGuide::create([
            'pertanyaan' => $request->pertanyaan,
            'jawaban' => $request->jawaban,
            'urutan' => $request->urutan ?? 0,
        ]);

        return redirect()->route('superadmin.help_guides')->with('success', 'Panduan berhasil ditambahkan.');
    }

    public function updateHelpGuide(Request $request, $id)
    {
        TenantManager::switchToLandlord();
        $request->validate([
            'pertanyaan' => 'required|string|max:255',
            'jawaban' => 'required|string',
            'urutan' => 'nullable|integer',
        ]);

        $guide = \App\Models\LandlordHelpGuide::findOrFail($id);
        $guide->update([
            'pertanyaan' => $request->pertanyaan,
            'jawaban' => $request->jawaban,
            'urutan' => $request->urutan ?? 0,
        ]);

        return redirect()->route('superadmin.help_guides')->with('success', 'Panduan berhasil diperbarui.');
    }

    public function destroyHelpGuide($id)
    {
        TenantManager::switchToLandlord();
        $guide = \App\Models\LandlordHelpGuide::findOrFail($id);
        $guide->delete();

        return redirect()->route('superadmin.help_guides')->with('success', 'Panduan berhasil dihapus.');
    }

    public function logs()
    {
        TenantManager::switchToLandlord();
        $logPath = storage_path('logs/laravel.log');
        $logs = '';
        if (file_exists($logPath)) {
            // Get last 1000 lines
            $file = new \SplFileObject($logPath, 'r');
            $file->seek(PHP_INT_MAX);
            $totalLines = $file->key();
            $file->seek(max(0, $totalLines - 1000));
            while (!$file->eof()) {
                $logs .= $file->current();
                $file->next();
            }
        }
        return view('superadmin.logs', compact('logs'));
    }

    public function audits()
    {
        TenantManager::switchToLandlord();
        $audits = DB::connection('landlord')->table('audit_logs')
            ->orderBy('created_at', 'desc')
            ->paginate(50);
        return view('superadmin.audits', compact('audits'));
    }

    public function broadcast()
    {
        TenantManager::switchToLandlord();
        return view('superadmin.broadcast');
    }

    public function sendBroadcast(Request $request)
    {
        TenantManager::switchToLandlord();
        $request->validate([
            'message' => 'required|string',
            'channel' => 'required|array'
        ]);

        $tenants = Tenant::where('is_active', true)->get();
        $successCount = 0;
        $messageText = $request->message;

        foreach ($tenants as $tenant) {
            // Get owner user from tenant DB
            try {
                $dbName = 'tenant_' . strtolower($tenant->subdomain);
                config(['database.connections.tenant.database' => $dbName]);
                DB::purge('tenant');
                
                $owner = DB::connection('tenant')->table('users')->where('role', 'owner')->first();
                $identitas = DB::connection('tenant')->table('identitas_tokos')->first();

                if (!$owner) continue;

                // Send via Email
                if (in_array('email', $request->channel) && $owner->email) {
                    \Illuminate\Support\Facades\Mail::raw($messageText, function($msg) use ($owner) {
                        $msg->to($owner->email)
                            ->subject('Pemberitahuan Penting: Flashbot');
                    });
                    $successCount++;
                }

                // Send via WhatsApp
                if (in_array('whatsapp', $request->channel)) {
                    // Try to get phone number from identitas or owner
                    $phone = null;
                    if ($identitas && !empty($identitas->nomor_hp)) {
                        $phone = $identitas->nomor_hp;
                    } elseif ($owner->phone ?? false) {
                        $phone = $owner->phone;
                    }

                    if ($phone) {
                        // Use landlord session for Baileys
                        $baileysUrl = env('BAILEYS_API_URL', 'http://127.0.0.1:3000');
                        \Illuminate\Support\Facades\Http::post($baileysUrl . '/api/send-message', [
                            'sessionId' => 'landlord',
                            'jid' => $phone . '@s.whatsapp.net',
                            'message' => ['text' => $messageText]
                        ]);
                        $successCount++;
                    }
                }
            } catch (\Exception $e) {
                Log::error("Broadcast failed for tenant {$tenant->id}: " . $e->getMessage());
            }
        }

        AuditLogger::record('superadmin.broadcast', "system", [
            'channels' => $request->channel,
            'success_count' => $successCount
        ]);

        return redirect()->back()->with('success', "Pesan broadcast berhasil dikirim ke antrean ({$successCount} penerima).");
    }
}
