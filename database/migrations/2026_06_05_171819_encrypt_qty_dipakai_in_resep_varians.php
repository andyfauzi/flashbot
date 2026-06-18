<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Crypt;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Ubah tipe kolom qty_dipakai menjadi TEXT
        DB::statement('ALTER TABLE resep_varians MODIFY qty_dipakai TEXT');

        // 2. Enkripsi data lama jika masih berupa desimal (belum dienkripsi)
        $reseps = DB::table('resep_varians')->get();
        foreach ($reseps as $resep) {
            $value = $resep->qty_dipakai;
            // Cek apakah belum dienkripsi (biasanya nilai mentah tidak diawali eyJ)
            if (is_numeric($value)) {
                $encrypted = Crypt::encryptString((string)$value);
                DB::table('resep_varians')
                    ->where('id', $resep->id)
                    ->update(['qty_dipakai' => $encrypted]);
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Jika di-rollback, coba decrypt ke angka lagi, lalu ubah kolom jadi decimal
        $reseps = DB::table('resep_varians')->get();
        foreach ($reseps as $resep) {
            $value = $resep->qty_dipakai;
            try {
                $decrypted = (float) Crypt::decryptString($value);
                DB::table('resep_varians')
                    ->where('id', $resep->id)
                    ->update(['qty_dipakai' => $decrypted]);
            } catch (\Exception $e) {
                // Ignore if not decryptable
            }
        }

        DB::statement('ALTER TABLE resep_varians MODIFY qty_dipakai DECIMAL(15, 2)');
    }
};
