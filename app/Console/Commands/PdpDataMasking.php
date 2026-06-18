<?php

namespace App\Console\Commands;

use App\Models\Pesanan;
use App\Models\ChatbotHistory;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class PdpDataMasking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:pdp-data-masking';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Masking data pribadi pelanggan (UU PDP) untuk pesanan yang sudah lebih dari 30 hari selesai/dibatalkan.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Memulai proses masking data pribadi (PDP)...');

        $cutoffDate = now()->subDays(30);

        // Ambil pesanan yang sudah selesai atau cancelled, dan umurnya > 30 hari, serta belum dimasking
        $pesanans = Pesanan::whereIn('status', ['selesai', 'cancelled'])
            ->where('updated_at', '<', $cutoffDate)
            ->where('nomor_wa', '!=', '08xxxxxxxxxx')
            ->get();

        $count = 0;
        $historyDeleted = 0;

        foreach ($pesanans as $pesanan) {
            $nomorLama = $pesanan->nomor_wa;

            // Hapus riwayat chat pelanggan tersebut dari tabel ChatbotHistory
            if ($nomorLama && $nomorLama !== '-' && $nomorLama !== '08xxxxxxxxxx') {
                $deleted = ChatbotHistory::where('nomor_wa', $nomorLama)->delete();
                $historyDeleted += $deleted;
            }

            // Masking data
            $pesanan->update([
                'nomor_wa' => '08xxxxxxxxxx',
                'nama_penerima' => '(Disensor otomatis oleh sistem - UU PDP)',
                'alamat_penerima' => '(Disensor otomatis oleh sistem - UU PDP)'
            ]);

            $count++;
        }

        $msg = "Proses masking PDP selesai. Berhasil menyensor {$count} pesanan dan menghapus {$historyDeleted} log chat AI.";
        $this->info($msg);
        Log::info("Command pdp-data-masking: " . $msg);
    }
}
