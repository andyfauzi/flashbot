<?php

namespace App\Http\Controllers;

use App\Models\Tenant;
use App\Models\TenantPayment;
use App\Models\SalesVoucher;
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

    public function index(Request $request)
    {
        $tenant = $this->resolveTenant($request);

        // Pastikan kita di landlord context sebelum query
        TenantManager::switchToLandlord();

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

        $discountPercentStarter = (int) ($settings['discount_yearly_starter'] ?? 20);
        $discountPercentPro = (int) ($settings['discount_yearly_pro'] ?? 20);
        $discountPercentBusiness = (int) ($settings['discount_yearly_business'] ?? 20);

        $priceStarterYearly = $priceStarter * 12 * (1 - ($discountPercentStarter / 100));
        $priceProYearly = $pricePro * 12 * (1 - ($discountPercentPro / 100));
        $priceBusinessYearly = $priceBusiness * 12 * (1 - ($discountPercentBusiness / 100));

        $featuresStarter = array_filter(array_map('trim', explode("\n", $settings['features_starter'] ?? "1 Cabang Toko\nMaks 50 Produk\nMaks 3 Kasir")));
        $featuresPro = array_filter(array_map('trim', explode("\n", $settings['features_pro'] ?? "5 Cabang Toko\nMaks 500 Produk\nMaks 10 Kasir")));
        $featuresBusiness = array_filter(array_map('trim', explode("\n", $settings['features_business'] ?? "Unlimited Cabang\nUnlimited Produk\nUnlimited Kasir")));

        $packageMenus = \App\Models\PackageMenu::all();

        return view('dashboard.billing.index', compact('tenant', 'payments', 'priceStarter', 'pricePro', 'priceBusiness', 'priceStarterYearly', 'priceProYearly', 'priceBusinessYearly', 'discountPercentStarter', 'discountPercentPro', 'discountPercentBusiness', 'featuresStarter', 'featuresPro', 'featuresBusiness', 'settings', 'packageMenus'));
    }

    public function startTrial(Request $request)
    {
        TenantManager::switchToLandlord();
        $tenant = $this->resolveTenant($request);
        if (!$tenant) {
            return back()->with('error', 'Toko tidak ditemukan.');
        }

        if ($tenant->plan_expires_at && $tenant->plan_expires_at > now()) {
            return back()->with('error', 'Anda sudah pernah mengaktifkan layanan atau masa percobaan.');
        }

        $tenant->update([
            'is_active' => true,
            'plan_expires_at' => now()->addDays(30),
        ]);

        return redirect()->route('dashboard.billing.index')->with('sukses', 'Masa percobaan gratis 30 hari berhasil diaktifkan!');
    }

    public function checkVoucher(Request $request)
    {
        $request->validate([
            'voucher_code' => 'required|string',
            'plan' => 'required|in:starter,pro,business'
        ]);

        $tenant = $this->resolveTenant($request);
        TenantManager::switchToLandlord();

        if (!$tenant) {
            return response()->json(['error' => 'Toko tidak ditemukan.'], 403);
        }

        $voucher = SalesVoucher::where('kode_voucher', strtoupper($request->voucher_code))
            ->where('is_active', true)
            ->first();

        if (!$voucher) {
            return response()->json(['error' => 'Kode voucher tidak valid atau sudah tidak aktif.'], 404);
        }

        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
        $priceMap = [
            'starter'  => (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000'),
            'pro'      => (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000'),
            'business' => (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000'),
        ];

        $amount = $priceMap[$request->plan];
        $discountAmount = ($voucher->diskon_persen / 100) * $amount;
        $finalAmount = max(0, $amount - $discountAmount);

        $targetMsg = $voucher->target_paket == 'semua' ? 'Semua Paket' : 'Paket ' . ucfirst($voucher->target_paket);

        return response()->json([
            'valid' => true,
            'discount_percent' => $voucher->diskon_persen,
            'discount_amount' => $discountAmount,
            'original_price' => $amount,
            'final_price' => $finalAmount,
            'message' => "Voucher valid! Diskon {$voucher->diskon_persen}% (Khusus $targetMsg)"
        ]);
    }

    public function checkout(Request $request)
    {
        $request->validate([
            'plan' => 'required|in:starter,pro,business',
            'duration' => 'nullable|in:monthly,yearly',
            'payment_method' => 'nullable|in:midtrans,manual',
        ]);

        $duration = $request->input('duration', 'monthly');

        // Resolve tenant dengan metode yang aman sebelum switch context
        $tenant = $this->resolveTenant($request);

        // Pastikan kita di landlord context sebelum query apapun
        TenantManager::switchToLandlord();

        if (!$tenant) {
            Log::error('Billing checkout: Tenant tidak ditemukan.', [
                'user_email' => auth()->user()->email ?? 'unknown',
                'host' => $request->getHost(),
            ]);
            return response()->json(['error' => 'Toko tidak ditemukan. Pastikan Anda login dengan akun pemilik toko.'], 403);
        }

        $settings = \App\Models\LandlordSetting::pluck('value', 'key')->toArray();
        $discountPercentStarter = (int) ($settings['discount_yearly_starter'] ?? 20);
        $discountPercentPro = (int) ($settings['discount_yearly_pro'] ?? 20);
        $discountPercentBusiness = (int) ($settings['discount_yearly_business'] ?? 20);

        if ($duration === 'yearly') {
            $priceMap = [
                'starter'  => (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000') * 12 * (1 - ($discountPercentStarter / 100)),
                'pro'      => (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000') * 12 * (1 - ($discountPercentPro / 100)),
                'business' => (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000') * 12 * (1 - ($discountPercentBusiness / 100)),
            ];
        } else {
            $priceMap = [
                'starter'  => (int) preg_replace('/[^0-9]/', '', $settings['price_starter'] ?? '99000'),
                'pro'      => (int) preg_replace('/[^0-9]/', '', $settings['price_pro'] ?? '199000'),
                'business' => (int) preg_replace('/[^0-9]/', '', $settings['price_business'] ?? '499000'),
            ];
        }

        $amount  = $priceMap[$request->plan];
        
        // Handle Voucher
        $voucher = null;
        $discountAmount = 0;
        $commissionAmount = 0;

        if ($request->filled('voucher_code')) {
            $voucher = SalesVoucher::where('kode_voucher', strtoupper($request->voucher_code))
                ->where('is_active', true)
                ->first();

            if ($voucher) {
                if ($voucher->target_paket !== 'semua' && $voucher->target_paket !== $request->plan) {
                    return response()->json(['error' => 'Kode voucher ini tidak berlaku untuk paket ' . ucfirst($request->plan) . '.'], 400);
                }
                
                $discountAmount = ($voucher->diskon_persen / 100) * $amount;
                $amount = max(0, $amount - $discountAmount);
                $commissionAmount = ($voucher->komisi_persen / 100) * $amount;
            }
        }

        $orderId = 'TRX-' . $tenant->id . '-' . time();

        // Create Payment Record
        $payment = TenantPayment::create([
            'tenant_id'    => $tenant->id,
            'order_id'     => $orderId,
            'plan_name'    => $request->plan . '|' . $duration,
            'gross_amount' => $amount,
            'status'       => 'pending',
            'sales_voucher_id'  => $voucher ? $voucher->id : null,
            'discount_amount'   => $discountAmount,
            'commission_amount' => $commissionAmount,
        ]);

        // Jika pengguna memilih manual, langsung kembalikan fallback JSON
        if ($request->input('payment_method') === 'manual') {
            return response()->json([
                'fallback' => true,
                'order_id' => $orderId,
                'message' => 'Silakan lakukan pembayaran manual sesuai instruksi.'
            ], 200);
        }

        // Config Midtrans
        $serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';

        if (empty($serverKey)) {
            Log::error('Midtrans Server Key belum dikonfigurasi.');
            return response()->json([
                'fallback' => true,
                'order_id' => $orderId,
                'message' => 'Sistem pembayaran otomatis belum tersedia saat ini. Silakan gunakan metode Transfer Manual.'
            ], 200);
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
            'item_details' => [
                [
                    'id'       => 'PLAN-' . strtoupper($request->plan) . '-' . $duration,
                    'price'    => $amount,
                    'quantity' => 1,
                    'name'     => 'Langganan Tenanta POS & Chatbot - Paket ' . ucfirst($request->plan) . ' (' . $duration . ' Bulan)',
                ]
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
            return response()->json([
                'fallback' => true,
                'order_id' => $orderId,
                'message' => 'Gagal terhubung ke server pembayaran Midtrans. Pesan: ' . $e->getMessage()
            ], 200);
        }
    }

    public function webhook(Request $request)
    {
        TenantManager::switchToLandlord();

        $serverKey = \App\Models\LandlordSetting::get('midtrans_server_key', env('MIDTRANS_SERVER_KEY'));
        $isProduction = \App\Models\LandlordSetting::get('midtrans_is_production', env('MIDTRANS_IS_PRODUCTION', false)) == '1';

        \Midtrans\Config::$serverKey    = $serverKey;
        \Midtrans\Config::$isProduction = $isProduction;

        // Handle fitur "Tes URL Notifikasi" dari Dasbor Midtrans
        if ($request->order_id == 'test-1234' || \Illuminate\Support\Str::startsWith($request->order_id, 'payment_notif_test')) {
            return response()->json(['status' => 'ok', 'message' => 'Test notification received successfully'], 200);
        }

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
                    $parts = explode('|', $payment->plan_name);
                    $actualPlan = $parts[0];
                    $duration = $parts[1] ?? 'monthly';
                    $daysToAdd = ($duration === 'yearly') ? 365 : 30;

                    $tenant->update([
                        'plan'           => $actualPlan,
                        'is_active'      => true,
                        'plan_expires_at' => ($tenant->plan_expires_at && $tenant->plan_expires_at > now())
                            ? $tenant->plan_expires_at->addDays($daysToAdd)
                            : now()->addDays($daysToAdd),
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
