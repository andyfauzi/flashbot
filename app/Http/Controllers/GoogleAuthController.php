<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Services\AuditLogger;
use App\Jobs\ProvisionTenantDatabaseJob;

class GoogleAuthController extends Controller
{
    public function redirectToGoogle(Request $request)
    {
        TenantManager::switchToLandlord();
        $state = Str::random(40);
        session([
            'oauth_state' => $state,
            'google_reg_plan' => $request->query('plan', 'starter'),
            'google_reg_trial' => $request->query('trial', '0')
        ]);
        
        // Menggunakan stateless(false) default Socialite + custom state validation
        return Socialite::driver('google')->with([
            'state' => $state,
            'prompt' => 'select_account'
        ])->redirect();
    }

    public function handleGoogleCallback(Request $request)
    {
        TenantManager::switchToLandlord();

        // Security Hardening: Validasi CSRF OAuth State
        if (!$request->has('state') || session('oauth_state') !== $request->state) {
            abort(403, 'Invalid OAuth state. Possible CSRF attack detected.');
        }

        try {
            $googleUser = Socialite::driver('google')->stateless(false)->user();
        } catch (\Exception $e) {
            Log::error("Google Login Callback Error: " . $e->getMessage());
            return redirect('/')->withErrors(['error' => 'Gagal login menggunakan Google. Silakan coba lagi.']);
        }

        // Check if tenant already exists with this owner email
        $existingTenant = Tenant::where('owner_email', $googleUser->getEmail())->first();

        if ($existingTenant) {
            // Already registered, redirect to their subdomain dashboard login
            $appHost = parse_url(config('app.url'), PHP_URL_HOST) ?? request()->getHost();
            $scheme   = request()->getScheme();
            $port     = request()->getPort();
            $portStr  = ($port && $port != 80 && $port != 443) ? ':' . $port : '';
            $url      = $scheme . '://' . $existingTenant->subdomain . '.' . $appHost . $portStr . '/login';
            return redirect($url)->with('sukses', 'Silakan login menggunakan akun Google Anda.');
        }

        // New registration: save Google details in session and show store details modal (Step 2)
        session([
            'google_reg_email' => $googleUser->getEmail(),
            'google_reg_name'  => $googleUser->getName(),
            'google_reg_id'    => $googleUser->getId(),
        ]);

        return redirect('/')->with('show_google_step2', true);
    }

    public function cancelRegistration()
    {
        session()->forget(['google_reg_email', 'google_reg_name', 'google_reg_id']);
        return redirect('/')->with('info', 'Pendaftaran toko dibatalkan.');
    }

    public function completeRegistration(Request $request)
    {
        TenantManager::switchToLandlord();

        $request->validate([
            'store_name' => 'required|string|max:255',
            'subdomain' => ['required', 'alpha_dash', 'max:50', 'regex:/^[a-z0-9]+$/', 'not_in:www,api,admin,app,super-admin,superadmin,mail,ftp', 'unique:landlord.tenants,subdomain'],
            'owner_name' => 'required|string|max:255',
            'store_address' => 'required|string',
            'jenis_layanan' => 'required|in:dine_in,take_away,keduanya',
            'whatsapp_number' => 'required|string|max:50',
            'terms_accepted' => 'accepted',
        ]);

        $email = session('google_reg_email');
        $googleId = session('google_reg_id');
        $name = $request->owner_name; // Use custom name from form instead of Google name

        if (!$email || !$googleId) {
            return redirect('/')->withErrors(['error' => 'Sesi pendaftaran Google kedaluwarsa. Silakan mulai ulang.']);
        }

        $subdomain = strtolower($request->subdomain);
        $dbName = 'tenant_' . $subdomain;

        try {
            // 1. Determine Plan Expiration
            $plan = session('google_reg_plan', 'starter');
            $isTrial = session('google_reg_trial', '0') === '1';
            
            if ($isTrial) {
                $days = $plan === 'pro' ? 30 : 15;
                $expiresAt = now()->addDays($days);
            } else {
                // If not trial, it expires immediately (subDay) so they must pay
                $expiresAt = now()->subDay();
            }

            // 2. Create landlord tenant record but mark it as NOT active yet
            TenantManager::switchToLandlord();
            $tenant = Tenant::create([
                'name' => $request->store_name,
                'owner_email' => $email,
                'subdomain' => $subdomain,
                'database_name' => $dbName,
                'plan' => $plan,
                'is_active' => false, // Will be true after provisioning
                'plan_expires_at' => $expiresAt,
            ]);

            AuditLogger::record('tenant.registered_via_google', "tenant:{$tenant->id}", [
                'name' => $request->store_name,
                'subdomain' => $subdomain,
                'email' => $email,
            ]);

            // Clear registration session
            session()->forget(['google_reg_email', 'google_reg_name', 'google_reg_id', 'google_reg_plan', 'google_reg_trial']);

            // 2. Dispatch the Provisioning Job
            ProvisionTenantDatabaseJob::dispatch(
                $tenant->id,
                $dbName,
                $name,
                $email,
                $googleId,
                $request->store_address,
                $request->whatsapp_number,
                $request->jenis_layanan
            );

            // Redirect to loading screen
            return redirect()->route('auth.google.provisioning', ['tenant_id' => $tenant->id]);

        } catch (\Exception $e) {
            Log::error("Google Registration Completion Error: " . $e->getMessage());
            
            TenantManager::switchToLandlord();

            return redirect('/')->withErrors(['error' => 'Gagal memulai pendaftaran toko: ' . $e->getMessage()]);
        }
    }
}
