<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Services\TenantManager;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class PaymentController extends Controller
{
    /**
     * Resolve tenant dari berbagai konteks (subdomain, IoC, atau owner_email).
     * Selalu bekerja pada koneksi landlord.
     */
    private function resolveTenant(Request $request = null): ?Tenant
    {
        // 1. Coba dari IoC container (jika ada subdomain tenant yang aktif)
        if (app()->bound('current_tenant')) {
            $t = app('current_tenant');
            if ($t instanceof Tenant) {
                return $t;
            }
        }

        // 2. Coba dari email user yang sedang login (akses via domain utama)
        if (auth()->check() && auth()->user()->email) {
            $tenant = DB::connection('landlord')
                ->table('tenants')
                ->where('owner_email', auth()->user()->email)
                ->first();
            if ($tenant) {
                return Tenant::find($tenant->id);
            }
        }

        // 3. Fallback ke subdomain dari Host header
        if ($request) {
            $host = $request->getHost();
            $parts = explode('.', $host);
            if (count($parts) > 1 && $parts[0] !== 'www' && $parts[0] !== 'localhost') {
                return Tenant::where('subdomain', $parts[0])->first();
            }
        }

        return null;
    }

    public function index()
    {
        // Pastikan kita di landlord context sebelum query
        TenantManager::switchToLandlord();

        $tenant = $this->resolveTenant();
        if (!$tenant) {
            abort(403, 'Toko tidak ditemukan. Pastikan Anda login dengan email yang benar.');
        }

        $payments = TenantPayment::where('tenant_id', $tenant->id)
            ->orderBy('created_at', 'desc')
            ->get();

        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
        $priceStarter = (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000');
        $pricePro = (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000');
        $priceBusiness = (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000');

        $featuresStarter = array_filter(array_map('trim', explode("\n", $settings['features_starter'] ?? "1 Cabang Toko\nMaks 50 Produk\nMaks 3 Kasir")));
        $featuresPro = array_filter(array_map('trim', explode("\n", $settings['features_pro'] ?? "5 Cabang Toko\nMaks 500 Produk\nMaks 10 Kasir")));
        $featuresBusiness = array_filter(array_map('trim', explode("\n", $settings['features_business'] ?? "Unlimited Cabang\nUnlimited Produk\nUnlimited Kasir")));

        return view('dashboard.billing.index', compact('tenant', 'payments', 'priceStarter', 'pricePro', 'priceBusiness', 'featuresStarter', 'featuresPro', 'featuresBusiness'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:starter,pro,business',
        ]);

        // Pastikan kita di landlord context sebelum query apapun
        TenantManager::switchToLandlord();

        // Resolve tenant dengan metode yang aman
        $tenant = $this->resolveTenant($request);
        if (!$tenant) {
            Log::error('Billing checkout: Tenant tidak ditemukan.', [
                'user_email' => auth()->user()->email ?? 'unknown',
                'host' => $request->getHost(),
            ]);
            return response()->json(['error' => 'Toko tidak ditemukan. Pastikan Anda login dengan akun pemilik toko.'], 403);
        }

        // Determine price
        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
        $priceMap = [
            'starter'  => (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000'),
            'pro'      => (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000'),
            'business' => (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000'),
        ];

        $amount  = $priceMap[$request->plan];
        $orderId = 'TRX-' . $tenant->id . '-' . time();

        // Create Payment Record
        $payment = TenantPayment::create([
            'tenant_id'    => $tenant->id,
            'order_id'     => $orderId,
            'plan_name'    => $request->plan,
            'gross_amount' => $amount,
            'status'       => 'pending',
        ]);

        // Config Midtrans
        $serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';

        if (empty($serverKey)) {
            Log::error('Midtrans Server Key belum dikonfigurasi.');
            return response()->json(['error' => 'Konfigurasi pembayaran belum lengkap. Hubungi administrator.'], 500);
        }

        \Midtrans\Config::$serverKey   = $serverKey;
        \Midtrans\Config::$isProduction = $isProduction;
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds       = true;

        $params = [
            'transaction_details' => [
                'order_id'     => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $tenant->name,
                'email'      => $tenant->owner_email,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $payment->update(['snap_token' => $snapToken]);
            Log::info('Midtrans Snap Token berhasil dibuat.', ['order_id' => $orderId, 'tenant_id' => $tenant->id]);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage(), ['order_id' => $orderId]);
            return response()->json(['error' => 'Gagal terhubung ke server pembayaran Midtrans. Pesan: ' . $e->getMessage()], 500);
        }
    }

    public function webhook(Request $request)
    {
        TenantManager::switchToLandlord();

        $serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';

        \Midtrans\Config::$serverKey    = $serverKey;
        \Midtrans\Config::$isProduction = $isProduction;

        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        $transaction = $notif->transaction_status;
        $type        = $notif->payment_type;
        $order_id    = $notif->order_id;
        $fraud       = $notif->fraud_status;

        $payment = TenantPayment::where('order_id', $order_id)->first();
        if (!$payment) {
            Log::warning("Midtrans Webhook: Order tidak ditemukan.", ['order_id' => $order_id]);
            // Midtrans mewajibkan response 200 OK agar tidak melakukan retry berkali-kali.
            return response()->json(['status' => 'ignored', 'message' => 'Order not found, but acknowledged'], 200);
        }

        if ($transaction == 'capture' || $transaction == 'settlement') {
            if ($type == 'credit_card' && $fraud == 'challenge') {
                $payment->update(['status' => 'challenge']);
            } else {
                $payment->update([
                    'status'       => 'settlement',
                    'payment_type' => $type,
                    'paid_at'      => now(),
                ]);

                // Update Tenant Plan
                $tenant = Tenant::find($payment->tenant_id);
                if ($tenant) {
                    $tenant->update([
                        'plan'           => $payment->plan_name,
                        'is_active'      => true,
                        'plan_expires_at' => ($tenant->plan_expires_at && $tenant->plan_expires_at > now())
                            ? $tenant->plan_expires_at->addDays(30)
                            : now()->addDays(30),
                    ]);

                    // Send Email Notification
                    try {
                        if (!empty($tenant->owner_email)) {
                            \Illuminate\Support\Facades\Mail::to($tenant->owner_email)
                                ->send(new \App\Mail\PaymentSuccessMail($payment, $tenant));
                        }
                    } catch (\Exception $e) {
                        Log::error('Gagal mengirim email konfirmasi: ' . $e->getMessage());
                    }
                }
            }
        } elseif ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
            $payment->update(['status' => $transaction]);
        } elseif ($transaction == 'pending') {
            $payment->update(['status' => 'pending']);
        }

        return response()->json(['status' => 'success']);
    }
}
