<?php
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Http\Kernel::class);
$response = $kernel->handle(
    $request = Illuminate\Http\Request::capture()
);

if (!\Illuminate\Support\Facades\Schema::connection('landlord')->hasColumn('package_menus', 'show_on_landing_page')) {
    \Illuminate\Support\Facades\Schema::connection('landlord')->table('package_menus', function (\Illuminate\Database\Schema\Blueprint $table) {
        $table->boolean('show_on_landing_page')->default(true)->after('business_enabled');
    });
    echo "Column added.";
} else {
    echo "Column already exists.";
}
