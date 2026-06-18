<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Flashbot Feature Flags (Sistem Paket Modular)
    |--------------------------------------------------------------------------
    |
    | Konfigurasi ini mengatur modul mana saja yang aktif dalam sistem.
    | Sangat berguna untuk skema penjualan SaaS (Software as a Service)
    | di mana pelanggan dapat membeli paket tertentu (Kasir Saja, Chatbot Saja,
    | atau Full ERP).
    |
    */

    'features' => [
        // Paket Kasir Dasar (POS, Produk, Stok, Jadwal Pesanan)
        'pos' => env('FEATURE_POS', true),

        // Paket Chatbot (WhatsApp Gateway, Auto-Reply, Order via WA, Notifikasi)
        'chatbot' => env('FEATURE_CHATBOT', true),

        // Paket ERP - Produksi & HPP (Dapur, Resep, Kalkulator HPP, Bahan Baku)
        'erp' => env('FEATURE_ERP', true),

        // Paket ERP - Keuangan (Buku Kas, Laba Rugi, Cash Flow)
        'finance' => env('FEATURE_FINANCE', true),
    ]
];
