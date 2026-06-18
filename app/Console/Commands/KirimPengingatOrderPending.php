<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Pesanan;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class KirimPengingatOrderPending extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'order:pengingat-pending';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Kirim pengingat rutin ke Grup Admin untuk pesanan yang belum selesai diproses (pending/diproses)';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle(WhatsAppService $waService)
    {
        $groupId = env('WHATSAPP_GROUP_ID_SELLER');

        if (empty($groupId)) {
            $this->error('WHATSAPP_GROUP_ID_SELLER belum diatur di .env');
            Log::warning('Cron Pengingat Order Pending: WHATSAPP_GROUP_ID_SELLER kosong.');
            return 1;
        }

        // Ambil pesanan yang belum selesai
        $pesanans = Pesanan::with('items.produk')
            ->whereIn('status', ['pending_approval', 'diproses'])
            ->orderBy('created_at', 'asc')
            ->get();

        if ($pesanans->isEmpty()) {
            $this->info("Tidak ada pesanan pending/diproses saat ini.");
            return 0;
        }

        $pendingCount = $pesanans->where('status', 'pending_approval')->count();
        $prosesCount  = $pesanans->where('status', 'diproses')->count();

        $teks = "⚠️ *PENGINGAT ORDERAN BELUM SELESAI*\n";
        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "Terdapat total *{$pesanans->count()}* pesanan aktif:\n";
        $teks .= "⏳ Menunggu Persetujuan: {$pendingCount}\n";
        $teks .= "🍳 Sedang Diproses: {$prosesCount}\n\n";

        // Batasi maksimal 10 pesanan terlama agar pesan tidak terlalu panjang
        $pesananTerlama = $pesanans->take(10);

        foreach ($pesananTerlama as $i => $p) {
            $no = $i + 1;
            $namaProduk = [];
            foreach ($p->items as $item) {
                $namaProduk[] = ($item->produk->nama ?? 'Barang') . " (" . $item->jumlah . "x)";
            }
            $produkStr = implode(', ', $namaProduk);

            $statusStr = $p->status === 'pending_approval' ? '⏳ PENDING' : '🍳 DIPROSES';

            $teks .= "*{$no}. {$p->nama_penerima}* ({$p->nomor_order})\n";
            $teks .= "   📦 Produk: {$produkStr}\n";
            $teks .= "   📅 Tanggal: " . \Carbon\Carbon::parse($p->tanggal_diambil)->format('d/m/Y H:i') . "\n";
            $teks .= "   🔖 Status: {$statusStr}\n\n";
        }

        if ($pesanans->count() > 10) {
            $sisa = $pesanans->count() - 10;
            $teks .= "... dan {$sisa} pesanan lainnya.\n\n";
        }

        $teks .= "━━━━━━━━━━━━━━━━\n";
        $teks .= "💡 _Gunakan perintah `!orderan-pending` atau `!orderan-proses` di grup ini untuk mengelola pesanan._";

        $waService->kirimPesan($groupId, $teks);

        $this->info("Pengingat berhasil dikirim untuk {$pesanans->count()} pesanan pending.");
        return 0;
    }
}
