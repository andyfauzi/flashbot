<?php

$file = __DIR__ . '/resources/views/layouts/app.blade.php';
$content = file_get_contents($file);

$replacements = [
    // Navbar
    'fa-solid fa-bars' => 'data-lucide="menu"',
    'fa-solid fa-rotate-right' => 'data-lucide="refresh-cw"',
    'fa-solid fa-right-from-bracket' => 'data-lucide="log-out"',
    'fa-solid fa-circle-check' => 'data-lucide="check-circle"',
    'fa-solid fa-triangle-exclamation' => 'data-lucide="alert-triangle"',
    
    // Sidebar Group Icons
    'fa-solid fa-cart-shopping' => 'data-lucide="shopping-cart"',
    'fa-solid fa-box-open' => 'data-lucide="package-open"',
    'fa-solid fa-kitchen-set' => 'data-lucide="utensils"',
    'fa-solid fa-wallet' => 'data-lucide="wallet"',
    'fa-solid fa-robot' => 'data-lucide="bot"',
    'fa-solid fa-people-group' => 'data-lucide="users"',
    'fa-solid fa-gears' => 'data-lucide="settings"',
    
    // Kasir & Penjualan
    'fa-solid fa-cash-register' => 'data-lucide="banknote"',
    'fa-solid fa-calendar-check' => 'data-lucide="calendar-check"',
    
    // Produk & Inventori
    'fa-solid fa-tags' => 'data-lucide="tags"',
    'fa-solid fa-cubes' => 'data-lucide="blocks"',
    'fa-solid fa-boxes-stacked' => 'data-lucide="boxes"',
    
    // Produksi & HPP
    'fa-solid fa-seedling' => 'data-lucide="leaf"',
    'fa-solid fa-calculator' => 'data-lucide="calculator"',
    'fa-solid fa-industry' => 'data-lucide="factory"',
    
    // Keuangan & Laporan
    'fa-solid fa-file-invoice-dollar' => 'data-lucide="receipt"',
    
    // Chatbot
    'fa-solid fa-chart-pie' => 'data-lucide="pie-chart"',
    'fa-solid fa-sliders' => 'data-lucide="sliders"',
    'fa-solid fa-comments' => 'data-lucide="message-square"',
    'fa-solid fa-users' => 'data-lucide="users"',
    'fa-solid fa-mobile-screen' => 'data-lucide="smartphone"',
    
    // Grup & Settings
    'fa-solid fa-users-viewfinder' => 'data-lucide="scan-search"',
    'fa-solid fa-id-card-clip' => 'data-lucide="contact"',
    'fa-solid fa-user-shield' => 'data-lucide="shield-check"',
    
    // Others
    'fa-solid fa-xmark' => 'data-lucide="x"',
    'fa-solid fa-shop' => 'data-lucide="store"',
    'fa-solid fa-house' => 'data-lucide="home"'
];

foreach ($replacements as $old => $new) {
    // We want to replace `<i class="fa-solid fa-icon class2"></i>`
    // with `<i data-lucide="icon" class="class2"></i>`
    // But since regex is tricky, we can just replace the class names first
    
    $content = preg_replace_callback('/class="([^"]*)' . preg_quote($old) . '([^"]*)"/', function ($matches) use ($new) {
        $classes = trim($matches[1] . ' ' . $matches[2]);
        if (empty($classes)) {
            return $new;
        } else {
            return $new . ' class="' . $classes . '"';
        }
    }, $content);
}

// Add script for Lucide
if (strpos($content, 'unpkg.com/lucide@latest') === false) {
    $content = str_replace(
        '</body>',
        "    <script src=\"https://unpkg.com/lucide@latest\"></script>\n    <script>\n      lucide.createIcons();\n    </script>\n</body>",
        $content
    );
}

// Replace font awesome cdn with nothing (if needed) but keep it for now as some components might still use it until fully migrated.

file_put_contents($file, $content);
echo "Replaced icons successfully!";
