<?php

namespace App\Http\Controllers\Dashboard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\IdentitasToko;

class PaymentGatewayController extends Controller
{
    public function index()
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action. Halaman ini sangat privat dan hanya dapat diakses oleh Admin.');
        }
        $identitas = IdentitasToko::first();
        return view('dashboard.pengaturan.payment', compact('identitas'));
    }

    public function update(Request $request)
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized action. Halaman ini sangat privat dan hanya dapat diakses oleh Admin.');
        }

        $validated = $request->validate([
            'xendit_api_key' => 'nullable|string',
            'xendit_webhook_token' => 'nullable|string',
            'is_payment_gateway_active' => 'nullable|boolean',
            'midtrans_server_key' => 'nullable|string',
            'midtrans_client_key' => 'nullable|string',
            'midtrans_is_production' => 'nullable|boolean',
            'is_midtrans_active' => 'nullable|boolean',
        ]);

        $identitas = IdentitasToko::first();
        if (!$identitas) {
            return back()->with('error', 'Silakan lengkapi Identitas Toko terlebih dahulu sebelum mengatur payment gateway.');
        }

        $identitas->xendit_api_key = $validated['xendit_api_key'] ?? null;
        $identitas->xendit_webhook_token = $validated['xendit_webhook_token'] ?? null;
        $identitas->is_payment_gateway_active = $request->has('is_payment_gateway_active');

        $identitas->midtrans_server_key = $validated['midtrans_server_key'] ?? null;
        $identitas->midtrans_client_key = $validated['midtrans_client_key'] ?? null;
        $identitas->midtrans_is_production = $request->has('midtrans_is_production');
        $identitas->is_midtrans_active = $request->has('is_midtrans_active');

        $identitas->save();

        return redirect()->route('dashboard.pengaturan.payment')->with('sukses', 'Pengaturan Payment Gateway berhasil diperbarui!');
    }
}
