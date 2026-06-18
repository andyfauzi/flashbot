<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MenuLayananSeeder extends Seeder
{
    public function run(): void
    {
        $deviceId = 1; // Kanwil Sulbar

        // Hapus menu lama (opsional, komentari jika tidak ingin reset)
        DB::table('chatbot_menu')->where('device_id', $deviceId)->delete();

        $now = now();

        // =============================================
        // MENU SAMBUTAN / MENU UTAMA
        // Trigger: halo, hi, hello, menu, 0
        // =============================================
        $layananList = [
            ['id' => '1',  'text' => 'Layanan Pengaduan Masyarakat'],
            ['id' => '2',  'text' => 'Fasilitasi dan Pendampingan Kekayaan Intelektual'],
            ['id' => '3',  'text' => 'Fasilitasi dan Penanganan Penegakan Hukum Kekayaan Intelektual'],
            ['id' => '4',  'text' => 'Konsultasi Hukum'],
            ['id' => '5',  'text' => 'Pembentukan Desa/Kelurahan Sadar Hukum'],
            ['id' => '6',  'text' => 'Penyuluhan Hukum'],
            ['id' => '7',  'text' => 'Pengharmonisasian, Pembulatan, dan Pemantapan Konsepsi Rancangan Peraturan Daerah dan Peraturan Kepala Daerah'],
            ['id' => '8',  'text' => 'Permohonan Pendaftaran Pewarganegaraan RI (Naturalisasi)'],
            ['id' => '9',  'text' => 'Permohonan Pelantikan Notaris Baru dan Pengganti'],
            ['id' => '10', 'text' => 'Permohonan Pelantikan dan Pengambilan Sumpah PPNS'],
            ['id' => '11', 'text' => 'Pengambilan Sumpah/Janji Setia Pewarganegaraan RI'],
            ['id' => '12', 'text' => 'Pengawasan dan Pembinaan Notaris oleh Majelis Pengawasan Notaris dan Majelis Kehormatan Notaris'],
            ['id' => '13', 'text' => 'Layanan Konsultasi dan Pencetakan Sertifikat Apostille'],
            ['id' => '14', 'text' => 'Layanan Perpustakaan'],
        ];

        $isiMenuUtama  = "Selamat datang di *Layanan Kantor Wilayah Kementerian Hukum Sulawesi Barat* 🏛️\n\n";
        $isiMenuUtama .= "Silakan pilih layanan yang Anda butuhkan:\n\n";
        foreach ($layananList as $item) {
            $isiMenuUtama .= "*{$item['id']}.* {$item['text']}\n";
        }
        $isiMenuUtama .= "\n_Balas dengan nomor layanan yang Anda pilih_";

        DB::table('chatbot_menu')->insert([
            'device_id'           => $deviceId,
            'kode'                => 'halo, hi, hello, hai, menu, 0, mulai, start',
            'judul'               => 'Menu Utama',
            'isi'                 => $isiMenuUtama,
            'aktif'               => true,
            'urutan'              => 0,
            'tipe_pesan'          => 'text',
            'media_url'           => null,
            'media_type'          => null,
            'pilihan_interaktif'  => null,
            'created_at'          => $now,
            'updated_at'          => $now,
        ]);

        // =============================================
        // 14 SUB-MENU LAYANAN
        // =============================================
        $subMenus = [
            [
                'kode'   => '1',
                'judul'  => 'Layanan Pengaduan Masyarakat',
                'isi'    => "📋 *Layanan Pengaduan Masyarakat*\n\nAnda dapat menyampaikan pengaduan terkait layanan hukum melalui:\n\n• Datang langsung ke kantor\n• Email: pengaduan@kemenkumham.go.id\n• Hotline: 1500120\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 1,
            ],
            [
                'kode'   => '2',
                'judul'  => 'Fasilitasi dan Pendampingan Kekayaan Intelektual',
                'isi'    => "💡 *Fasilitasi dan Pendampingan Kekayaan Intelektual*\n\nKami menyediakan layanan:\n• Konsultasi hak cipta, paten, merek\n• Pendampingan proses pendaftaran KI\n• Sosialisasi perlindungan KI\n\nUntuk informasi lebih lanjut, silakan datang ke loket layanan KI.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 2,
            ],
            [
                'kode'   => '3',
                'judul'  => 'Penanganan Penegakan Hukum KI',
                'isi'    => "⚖️ *Fasilitasi dan Penanganan Penegakan Hukum Kekayaan Intelektual*\n\nLayanan ini mencakup:\n• Penanganan pelanggaran KI\n• Koordinasi dengan penegak hukum\n• Fasilitasi mediasi sengketa KI\n\nUntuk pelaporan, silakan hubungi bidang KI kami.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 3,
            ],
            [
                'kode'   => '4',
                'judul'  => 'Konsultasi Hukum',
                'isi'    => "🏛️ *Konsultasi Hukum*\n\nLayanan konsultasi hukum gratis tersedia untuk masyarakat.\n\nJadwal: Senin - Jumat, 08.00 - 15.00 WITA\n📍 Lokasi: Loket Konsultasi Hukum, lantai 1\n\nBawa dokumen terkait untuk konsultasi yang lebih efektif.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 4,
            ],
            [
                'kode'   => '5',
                'judul'  => 'Pembentukan Desa/Kelurahan Sadar Hukum',
                'isi'    => "🏘️ *Pembentukan Desa/Kelurahan Sadar Hukum*\n\nProgram ini bertujuan meningkatkan kesadaran hukum masyarakat desa/kelurahan.\n\nPersyaratan:\n• Surat permohonan dari kepala desa/lurah\n• Profil desa/kelurahan\n• Data kegiatan penyuluhan hukum\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 5,
            ],
            [
                'kode'   => '6',
                'judul'  => 'Penyuluhan Hukum',
                'isi'    => "📢 *Penyuluhan Hukum*\n\nKami menyediakan penyuluhan hukum untuk:\n• Instansi pemerintah\n• Sekolah/universitas\n• Masyarakat umum\n\nUntuk permintaan penyuluhan, ajukan surat permohonan ke kantor kami.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 6,
            ],
            [
                'kode'   => '7',
                'judul'  => 'Harmonisasi Rancangan Peraturan Daerah',
                'isi'    => "📜 *Pengharmonisasian Rancangan Peraturan Daerah dan Peraturan Kepala Daerah*\n\nLayanan ini meliputi:\n• Harmonisasi Raperda\n• Harmonisasi Raperkada\n• Pembulatan dan pemantapan konsepsi\n\nAjukan permohonan melalui surat resmi instansi.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 7,
            ],
            [
                'kode'   => '8',
                'judul'  => 'Permohonan Pewarganegaraan (Naturalisasi)',
                'isi'    => "🌏 *Permohonan Pendaftaran Pewarganegaraan RI (Naturalisasi)*\n\nPersyaratan umum:\n• Formulir permohonan\n• Paspor dan dokumen identitas\n• Surat keterangan tempat tinggal\n• Bukti kemampuan bahasa Indonesia\n\nSilakan datang langsung ke loket layanan untuk informasi lengkap.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 8,
            ],
            [
                'kode'   => '9',
                'judul'  => 'Pelantikan Notaris Baru dan Pengganti',
                'isi'    => "📝 *Permohonan Pelantikan Notaris Baru dan Pengganti*\n\nDokumen yang diperlukan:\n• SK pengangkatan dari Menkumham\n• Surat permohonan pelantikan\n• Dokumen pendukung lainnya\n\nHubungi bagian Yankumham untuk informasi lebih lanjut.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 9,
            ],
            [
                'kode'   => '10',
                'judul'  => 'Pelantikan dan Sumpah PPNS',
                'isi'    => "👮 *Permohonan Pelantikan dan Pengambilan Sumpah PPNS*\n\nLayanan pelantikan Penyidik Pegawai Negeri Sipil (PPNS).\n\nDokumen yang diperlukan:\n• SK pengangkatan PPNS\n• Surat permohonan\n• Dokumen pendukung\n\nHubungi bidang terkait untuk jadwal pelantikan.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 10,
            ],
            [
                'kode'   => '11',
                'judul'  => 'Pengambilan Sumpah Pewarganegaraan RI',
                'isi'    => "🇮🇩 *Pengambilan Sumpah/Janji Setia Pewarganegaraan RI*\n\nLayanan ini diperuntukkan bagi pemohon yang telah mendapat keputusan pewarganegaraan dari Presiden RI.\n\nHubungi kantor kami untuk penjadwalan dan persyaratan lengkap.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 11,
            ],
            [
                'kode'   => '12',
                'judul'  => 'Pengawasan dan Pembinaan Notaris',
                'isi'    => "🔍 *Pengawasan dan Pembinaan Notaris*\n\nDilaksanakan oleh:\n• Majelis Pengawasan Notaris (MPN)\n• Majelis Kehormatan Notaris (MKN)\n\nLayanan ini mencakup pemeriksaan, pembinaan, dan penanganan pengaduan terkait Notaris.\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 12,
            ],
            [
                'kode'   => '13',
                'judul'  => 'Layanan Apostille',
                'isi'    => "📄 *Layanan Konsultasi dan Pencetakan Sertifikat Apostille*\n\nApostille adalah legalisasi dokumen untuk keperluan internasional.\n\nCara mengajukan:\n1. Daftar di apostille.ahu.go.id\n2. Upload dokumen\n3. Bayar PNBP\n4. Cetak sertifikat di loket kami\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 13,
            ],
            [
                'kode'   => '14',
                'judul'  => 'Layanan Perpustakaan',
                'isi'    => "📚 *Layanan Perpustakaan*\n\nPerpustakaan Kanwil Kemenkumham Sulbar menyediakan:\n• Koleksi buku hukum\n• Peraturan perundang-undangan\n• Akses jurnal hukum\n\nJam operasional: Senin - Jumat, 08.00 - 16.00 WITA\n📍 Lantai 2 Gedung Kanwil\n\n_Balas *0* untuk kembali ke menu utama_",
                'urutan' => 14,
            ],
        ];

        foreach ($subMenus as $menu) {
            DB::table('chatbot_menu')->insert([
                'device_id'          => $deviceId,
                'kode'               => $menu['kode'],
                'judul'              => $menu['judul'],
                'isi'                => $menu['isi'],
                'aktif'              => true,
                'urutan'             => $menu['urutan'],
                'tipe_pesan'         => 'text',
                'media_url'          => null,
                'media_type'         => null,
                'pilihan_interaktif' => null,
                'created_at'         => $now,
                'updated_at'         => $now,
            ]);
        }

        $this->command->info('✅ 15 menu layanan Kanwil Sulbar berhasil dimasukkan! (1 menu utama + 14 sub-menu)');
    }
}
