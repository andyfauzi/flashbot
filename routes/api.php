<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

Route::get('/whatsapp/whitelist', function (Request $request) {
    // Basic security check (only accept if x-api-key matches)
    $expectedKey = config('chatbot.webhook_secret');
    if (empty($expectedKey) || $request->header('x-api-key') !== $expectedKey) {
        return response()->json(['error' => 'Unauthorized'], 403);
    }

    $groups = [];
    
    // Iterasi melalui semua tenant untuk mengumpulkan grup yang diizinkan
    $tenants = \App\Models\Tenant::all();
    foreach ($tenants as $tenant) {
        \App\Services\TenantManager::switchTo($tenant);
        
        $tenantGroups = \App\Models\GrupSetting::where('kunci', 'is_whitelisted')
            ->where('nilai', '1')
            ->pluck('grup_id')
            ->toArray();
            
        $groups = array_merge($groups, $tenantGroups);
    }
    
    // Kembalikan konteks ke landlord setelah selesai
    \App\Services\TenantManager::switchToLandlord();
    
    // Pastikan nilai unique
    $groups = array_values(array_unique($groups));
    
    // Baca fallback WHATSAPP_GROUP_ID_SELLER dari .env
    $envContent = @file_get_contents(base_path('.env')) ?: '';
    $sellerGroup = '';
    if (preg_match('/^WHATSAPP_GROUP_ID_SELLER="?([^"\n]+)"?/m', $envContent, $matches)) {
        $sellerGroup = trim($matches[1]);
    }

    if ($sellerGroup && !in_array($sellerGroup, $groups)) {
        $groups[] = $sellerGroup;
    }

    return response()->json(['status' => 'success', 'data' => $groups]);
});

Route::middleware('web')->post('/heartbeat', function () {
    if (auth()->check()) {
        return response()->json(['status' => 'ok', 'time' => now()->toIso8601String()]);
    }
    return response()->json(['error' => 'Unauthenticated'], 401);
});

Route::post('/webhook/midtrans', [\App\Http\Controllers\PaymentController::class, 'webhook']);
Route::post('/webhook/xendit', [\App\Http\Controllers\Api\XenditWebhookController::class, 'handle']);
Route::match(['get', 'post'], '/webhook/meta', [\App\Http\Controllers\Api\MetaWebhookController::class, 'handle']);
