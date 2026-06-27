<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\SatuanKonversi;

class SatuanKonversiSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $konversis = [
            ['satuan_awal' => 'Kilogram', 'satuan_akhir' => 'Gram', 'nilai_konversi' => 1000],
            ['satuan_awal' => 'Gram', 'satuan_akhir' => 'Kilogram', 'nilai_konversi' => 0.001],
            ['satuan_awal' => 'Liter', 'satuan_akhir' => 'mL', 'nilai_konversi' => 1000],
            ['satuan_awal' => 'Galon', 'satuan_akhir' => 'mL', 'nilai_konversi' => 19000],
            ['satuan_awal' => 'mL', 'satuan_akhir' => 'Liter', 'nilai_konversi' => 0.001],
        ];

        foreach ($konversis as $konversi) {
            SatuanKonversi::firstOrCreate([
                'satuan_awal' => $konversi['satuan_awal'],
                'satuan_akhir' => $konversi['satuan_akhir'],
            ], $konversi);
        }
    }
}
