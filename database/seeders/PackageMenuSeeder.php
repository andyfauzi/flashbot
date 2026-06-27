<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\PackageMenu;

class PackageMenuSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $menus = [
            // Kasir & Penjualan
            ['menu_key' => 'riwayat_transaksi', 'menu_label' => 'Riwayat Transaksi', 'category' => 'Kasir & Penjualan', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'kasir_pos', 'menu_label' => 'Kasir POS', 'category' => 'Kasir & Penjualan', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'jadwal_pesanan', 'menu_label' => 'Jadwal Pesanan (Pre-Order)', 'category' => 'Kasir & Penjualan', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
            ['menu_key' => 'portal_customer', 'menu_label' => 'Katalog Online (Portal Customer)', 'category' => 'Kasir & Penjualan', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],

            // Dine-in & Reservasi
            ['menu_key' => 'manajemen_meja', 'menu_label' => 'Manajemen Meja', 'category' => 'Dine-in & Reservasi', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
            ['menu_key' => 'jadwal_reservasi', 'menu_label' => 'Jadwal Reservasi', 'category' => 'Dine-in & Reservasi', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],

            // Produk & Inventori
            ['menu_key' => 'kategori_produk', 'menu_label' => 'Kategori Produk', 'category' => 'Produk & Inventori', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'produk_varian', 'menu_label' => 'Produk & Varian', 'category' => 'Produk & Inventori', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'pengelolaan_stok', 'menu_label' => 'Pengelolaan Stok', 'category' => 'Produk & Inventori', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],

            // Produksi & HPP
            ['menu_key' => 'master_bahan_baku', 'menu_label' => 'Master Bahan Baku', 'category' => 'Produksi & HPP', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
            ['menu_key' => 'kalkulator_hpp', 'menu_label' => 'Kalkulator HPP', 'category' => 'Produksi & HPP', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],
            ['menu_key' => 'produksi_dapur', 'menu_label' => 'Produksi Dapur', 'category' => 'Produksi & HPP', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],

            // Keuangan & Laporan
            ['menu_key' => 'buku_kas_laporan', 'menu_label' => 'Buku Kas dan Laporan', 'category' => 'Keuangan & Laporan', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],

            // Chatbot & WhatsApp
            ['menu_key' => 'dashboard_chatbot', 'menu_label' => 'Dashboard Chatbot', 'category' => 'Chatbot & WhatsApp', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],
            ['menu_key' => 'riwayat_pesan', 'menu_label' => 'Riwayat Pesan', 'category' => 'Chatbot & WhatsApp', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],
            ['menu_key' => 'data_pengguna', 'menu_label' => 'Data Pengguna', 'category' => 'Chatbot & WhatsApp', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'dashboard_grup', 'menu_label' => 'Dashboard Grup', 'category' => 'Chatbot & WhatsApp', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],
            ['menu_key' => 'pengaturan_device', 'menu_label' => 'Pengaturan Device', 'category' => 'Chatbot & WhatsApp', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],

            // Pengaturan Sistem
            ['menu_key' => 'hak_akses_karyawan', 'menu_label' => 'Hak Akses Karyawan', 'category' => 'Pengaturan Sistem', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
            ['menu_key' => 'identitas_toko', 'menu_label' => 'Identitas Toko', 'category' => 'Pengaturan Sistem', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'tagihan_paket', 'menu_label' => 'Tagihan dan Paket', 'category' => 'Pengaturan Sistem', 'gratis' => true, 'starter' => true, 'pro' => true, 'business' => true],
            ['menu_key' => 'admin_whatsapp', 'menu_label' => 'Admin WhatsApp', 'category' => 'Pengaturan Sistem', 'gratis' => false, 'starter' => false, 'pro' => false, 'business' => true],
            ['menu_key' => 'manajemen_kurir', 'menu_label' => 'Manajemen Kurir', 'category' => 'Pengaturan Sistem', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
            ['menu_key' => 'pengaturan_payment_gateway', 'menu_label' => 'Pengaturan Payment Gateway', 'category' => 'Pengaturan Sistem', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],

            // Transaksi & Pembayaran
            ['menu_key' => 'payment_gateway', 'menu_label' => 'Payment Gateway (Midtrans/Xendit)', 'category' => 'Transaksi & Pembayaran', 'gratis' => false, 'starter' => false, 'pro' => true, 'business' => true],
        ];

        foreach ($menus as $menu) {
            PackageMenu::updateOrCreate(
                ['menu_key' => $menu['menu_key']],
                [
                    'menu_label' => $menu['menu_label'],
                    'category' => $menu['category'],
                    'gratis_enabled' => $menu['gratis'],
                    'starter_enabled' => $menu['starter'],
                    'pro_enabled' => $menu['pro'],
                    'business_enabled' => $menu['business'],
                ]
            );
        }
    }
}
