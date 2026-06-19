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
    public function index()
    {
        $tenantId = app('current_tenant')->id ?? null;
        if (!$tenantId) {
            abort(403, 'Unauthorized');
        }
        $tenant = Tenant::find($tenantId);
        $payments = TenantPayment::where('tenant_id', $tenantId)->orderBy('created_at', 'desc')->get();
        return view('dashboard.billing.index', compact('tenant', 'payments'));
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:starter,pro,business',
        ]);

        // Let's rely on the current tenant in the session or app context
        $tenantId = app('current_tenant')->id ?? null;
        if (!$tenantId) {
            // Find tenant by subdomain
            $host = $request->getHost();
            $subdomain = explode('.', $host)[0];
            $tenant = Tenant::where('subdomain', $subdomain)->firstOrFail();
            $tenantId = $tenant->id;
        } else {
            $tenant = Tenant::find($tenantId);
        }

        TenantManager::switchToLandlord();

        // Determine price
        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
        $priceMap = [
            'starter' => (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000'),
            'pro' => (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000'),
            'business' => (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000'),
        ];
        
        $amount = $priceMap[$request->plan];
        $orderId = 'TRX-' . $tenantId . '-' . time();

        // Create Payment Record
        $payment = TenantPayment::create([
            'tenant_id' => $tenantId,
            'order_id' => $orderId,
            'plan_name' => $request->plan,
            'gross_amount' => $amount,
            'status' => 'pending',
        ]);

        // Config Midtrans
        \Midtrans\Config::$serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        \Midtrans\Config::$isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';
        \Midtrans\Config::$isSanitized = true;
        \Midtrans\Config::$is3ds = true;

        $params = [
            'transaction_details' => [
                'order_id' => $orderId,
                'gross_amount' => $amount,
            ],
            'customer_details' => [
                'first_name' => $tenant->name,
                'email' => $tenant->owner_email,
            ],
        ];

        try {
            $snapToken = \Midtrans\Snap::getSnapToken($params);
            $payment->update(['snap_token' => $snapToken]);
            return response()->json(['snap_token' => $snapToken]);
        } catch (\Exception $e) {
            Log::error('Midtrans Snap Error: ' . $e->getMessage());
            return response()->json(['error' => 'Gagal mendapatkan token pembayaran.'], 500);
        }
    }

    public function webhook(Request $request)
    {
        TenantManager::switchToLandlord();

        \Midtrans\Config::$serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        \Midtrans\Config::$isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';
        
        try {
            $notif = new \Midtrans\Notification();
        } catch (\Exception $e) {
            Log::error('Midtrans Webhook Error: ' . $e->getMessage());
            return response()->json(['status' => 'error', 'message' => $e->getMessage()], 400);
        }

        $transaction = $notif->transaction_status;
        $type = $notif->payment_type;
        $order_id = $notif->order_id;
        $fraud = $notif->fraud_status;

        $payment = TenantPayment::where('order_id', $order_id)->first();
        if (!$payment) {
            return response()->json(['status' => 'error', 'message' => 'Order not found'], 404);
        }

        if ($transaction == 'capture' || $transaction == 'settlement') {
            if ($type == 'credit_card' && $fraud == 'challenge') {
                $payment->update(['status' => 'challenge']);
            } else {
                $payment->update([
                    'status' => 'settlement',
                    'payment_type' => $type,
                    'paid_at' => now(),
                ]);

                // Update Tenant Plan
                $tenant = Tenant::find($payment->tenant_id);
                if ($tenant) {
                    $tenant->update([
                        'plan' => $payment->plan_name,
                        'is_active' => true,
                        // Add 30 days to existing or current date
                        'plan_expires_at' => ($tenant->plan_expires_at && $tenant->plan_expires_at > now()) 
                            ? $tenant->plan_expires_at->addDays(30) 
                            : now()->addDays(30),
                    ]);
                }
            }
        } else if ($transaction == 'cancel' || $transaction == 'deny' || $transaction == 'expire') {
            $payment->update(['status' => $transaction]);
        } else if ($transaction == 'pending') {
            $payment->update(['status' => 'pending']);
        }

        return response()->json(['status' => 'success']);
    }
}
