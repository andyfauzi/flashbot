<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use App\Models\TenantRequest;
use App\Services\TenantManager;
use Illuminate\Support\Facades\Mail;
use App\Mail\TenantRegistrationReceivedMail;
use App\Services\AuditLogger;

class ManualRegistrationController extends Controller
{
    public function showRegistrationForm(Request $request)
    {
        TenantManager::switchToLandlord();
        $plan = $request->query('plan', 'starter');
        $trial = $request->query('trial', '0');
        
        return view('auth.register_manual', compact('plan', 'trial'));
    }

    public function submitRegistration(Request $request)
    {
        TenantManager::switchToLandlord();

        $request->validate([
            'store_name' => 'required|string|max:255',
            'subdomain' => ['required', 'alpha_dash', 'max:50', 'regex:/^[a-z0-9]+$/', 'not_in:www,api,admin,app,super-admin,superadmin,mail,ftp', 'unique:landlord.tenants,subdomain', 'unique:landlord.tenant_requests,subdomain'],
            'owner_name' => 'required|string|max:255',
            'email' => 'required|email|max:255|unique:landlord.tenant_requests,email',
            'store_address' => 'required|string',
            'jenis_layanan' => 'required|in:dine_in,take_away,keduanya',
            'skala_bisnis' => 'nullable|string|max:50',
            'whatsapp_number' => 'required|string|max:50',
            'terms_accepted' => 'accepted',
            'plan' => 'required|string',
            'trial' => 'required|in:0,1',
            'g-recaptcha-response' => 'required',
        ], [
            'g-recaptcha-response.required' => 'Silakan centang kotak "Saya bukan robot".',
        ]);

        $recaptchaResponse = $request->input('g-recaptcha-response');
        $recaptchaSecret = env('RECAPTCHA_SECRET_KEY');

        if ($recaptchaSecret) {
            $verifyResponse = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
                'secret' => $recaptchaSecret,
                'response' => $recaptchaResponse,
                'remoteip' => $request->ip()
            ]);

            if (!$verifyResponse->json('success')) {
                return redirect()->back()->withInput()->withErrors(['g-recaptcha-response' => 'Verifikasi reCAPTCHA gagal. Silakan coba lagi.']);
            }
        }

        $subdomain = strtolower($request->subdomain);
        $isTrial = $request->trial === '1';

        try {
            $tenantRequest = TenantRequest::create([
                'store_name' => $request->store_name,
                'subdomain' => $subdomain,
                'owner_name' => $request->owner_name,
                'email' => $request->email,
                'whatsapp_number' => $request->whatsapp_number,
                'store_address' => $request->store_address,
                'jenis_layanan' => $request->jenis_layanan,
                'skala_bisnis' => $request->skala_bisnis,
                'plan' => $request->plan,
                'is_trial' => $isTrial,
                'status' => 'pending',
            ]);

            AuditLogger::record('tenant_request.created_manually', "tenant_request:{$tenantRequest->id}", [
                'name' => $request->store_name,
                'subdomain' => $subdomain,
                'email' => $request->email,
            ]);

            // Send Email
            Mail::to($request->email)->send(new TenantRegistrationReceivedMail($tenantRequest));

            return redirect()->route('auth.pending_approval')->with('success', 'Pendaftaran berhasil dikirim dan sedang menunggu persetujuan.');

        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Manual Registration Error: " . $e->getMessage());
            return redirect()->back()->withInput()->withErrors(['error' => 'Gagal mengirim pendaftaran: ' . $e->getMessage()]);
        }
    }
}
