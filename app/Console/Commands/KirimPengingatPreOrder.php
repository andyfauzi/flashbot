<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesanan;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class KirimPengingatPreOrder extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'preorder:pengingat';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat H-1 ke Grup Admin untuk pesanan yang akan diambil besok';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WhatsAppService $waService)
    {
        $besok = date('Y-m-d', strtotime('+1 day'));
        $groupId = env('WHATSAPP_GROUP_ID_SELLER');

        if (empty($groupId)) {
            $this->error('WHATSAPP_GROUP_ID_SELLER belum diatur di .env');
            Log::warning('Cron Pengingat Pre-Order: WHATSAPP_GROUP_ID_SELLER kosong.');
            return 1;
        }

        $pesanans = Pesanan::with('items.produk')
            ->whereDate('tanggal_diambil', $besok)
            ->whereNotIn('status', ['cancelled'])
            ->get();

        if ($pesanans->isEmpty()) {
            $this->info("Tidak ada pesanan untuk tanggal {$besok}");
            return 0;
        }

        $teks = "🔔 *PENGINGAT PESANAN BESOK*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Ada {$pesanans->count()} pesanan yang harus disiapkan untuk besok ({$besok}):\n\n";

        foreach ($pesanans as $i => $p) {
            $no = $i + 1;
            $namaProduk = [];
            foreach ($p->items as $item) {
                $namaProduk[] = ($item->produk->nama ?? 'Barang') . " (" . $item->jumlah . "x)";
            }
            $produkStr = implode(', ', $namaProduk);

            $dpFmt = number_format($p->uang_muka, 0, ',', '.');
            $sisaFmt = number_format($p->sisa_pembayaran, 0, ',', '.');
            
            $status = $p->sisa_pembayaran <= 0 ? 'LUNAS' : 'Belum Lunas';

            $teks .= "*{$no}. {$p->nama_penerima}* ({$p->nomor_order})\n";
            $teks .= "   📦 Produk: {$produkStr}\n";
            $teks .= "   🚚 Tipe: " . ($p->tipe_pengiriman === 'kurir_toko' ? 'Kurir Toko' : ($p->tipe_pengiriman === 'kurir_customer' ? 'Kurir Customer (Ojol)' : 'Ambil Sendiri')) . "\n";
            $teks .= "   💵 DP: Rp {$dpFmt} | Sisa: Rp {$sisaFmt} ({$status})\n\n";
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "💡 _Pastikan stok bahan siap dan pesanan segera dibuat._";

        $waService->kirimPesan($groupId, $teks);

        $this->info("Pengingat berhasil dikirim untuk {$pesanans->count()} pesanan.");
        return 0;
    }
}
