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

        return view('superadmin.index', compact('tenants'));
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

        $val = $request->has('is_payment_gateway_enabled') ? '1' : '0';
        \App\Models\LandlordSetting::set('is_payment_gateway_enabled', $val);

        AuditLogger::record('superadmin.settings_updated', "system", [
            'is_payment_gateway_enabled' => $val
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

        return redirect()->back()->with('success', 'Konfigurasi menu paket berhasil diperbarui.');
    }
}
