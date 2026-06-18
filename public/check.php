<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

use Illuminate\Support\Facades\Schema;

echo "chatbot_broadcasts columns: \n";
print_r(Schema::getColumnListing('chatbot_broadcasts'));

echo "\nidentitas_tokos columns: \n";
print_r(Schema::getColumnListing('identitas_tokos'));
