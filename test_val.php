<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$validator = Illuminate\Support\Facades\Validator::make([
    'nama_penerima' => 'Shanum',
    'nomor_wa' => '082246270958',
    'tipe_pengiriman' => 'ambil_sendiri',
    'meja_id' => null,
    'alamat_penerima' => null,
    'tanggal_diambil' => '2026-06-21T16:30',
    'metode_pembayaran' => 'manual',
    'cart' => [
        [
            'id' => 1,
            'qty' => 1
        ]
    ]
], [
    'nama_penerima'     => 'required|string|max:100',
    'nomor_wa'          => 'nullable|string|max:20', 
    'tipe_pengiriman'   => 'required|in:kurir_toko,kurir_customer,ambil_sendiri,dine_in',
    'meja_id'           => 'nullable|exists:mejas,id',
    'alamat_penerima'   => 'required_if:tipe_pengiriman,kurir_toko,kurir_customer|nullable|string',
    'tanggal_diambil'   => 'required_unless:tipe_pengiriman,dine_in|nullable|date',
    'metode_pembayaran' => 'required|in:cod,transfer,manual,midtrans',
    'cart'              => 'required|array|min:1',
    'cart.*.id'         => 'required|exists:produks,id',
    'cart.*.varian_id'  => 'nullable|exists:produk_varians,id',
    'cart.*.qty'        => 'required|integer|min:1',
    'cart.*.addons'     => 'nullable|array',
]);

if ($validator->fails()) {
    echo json_encode($validator->errors()->toArray(), JSON_PRETTY_PRINT);
} else {
    echo 'Validation passed!';
}
