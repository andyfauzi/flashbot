<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

$pesanans = \App\Models\Pesanan::latest()->take(5)->get();
foreach($pesanans as $p) {
    echo $p->nomor_order . ' - Antrian: ' . $p->nomor_antrian . "\n";
}
