<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class MakeSuperAdmin extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashbot:make-super-admin
                            {email : Email pengguna yang akan dijadikan Super Admin}
                            {--revoke : Cabut hak Super Admin dari pengguna ini}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tetapkan atau cabut hak Super Admin dari pengguna di database landlord Flashbot';

    public function handle(): int
    {
        $email   = $this->argument('email');
        $revoke  = $this->option('revoke');

        // ── Validate email format ───────────────────────────────────────────
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            $this->error("Email tidak valid: {$email}");
            return self::FAILURE;
        }

        // ── Find user in landlord DB ────────────────────────────────────────
        $user = DB::connection('landlord')
            ->table('users')
            ->where('email', $email)
            ->first();

        if (!$user) {
            // If no user exists and we are granting, offer to create one
            if ($revoke) {
                $this->error("Pengguna dengan email {$email} tidak ditemukan di database landlord.");
                return self::FAILURE;
            }

            $this->warn("Pengguna dengan email {$email} tidak ditemukan.");
            if (!$this->confirm('Apakah Anda ingin membuat akun Super Admin baru dengan email ini?')) {
                return self::FAILURE;
            }

            $name     = $this->ask('Nama lengkap Super Admin');
            $password = $this->secret('Password (minimal 12 karakter)');

            if (strlen($password) < 12) {
                $this->error('Password terlalu pendek! Minimal 12 karakter.');
                return self::FAILURE;
            }

            DB::connection('landlord')->table('users')->insert([
                'name'            => $name,
                'email'           => $email,
                'password'        => Hash::make($password),
                'is_super_admin'  => true,
                'created_at'      => now(),
                'updated_at'      => now(),
            ]);

            $this->info("✅ Akun Super Admin baru berhasil dibuat untuk: {$email}");
            $this->warn('⚠️  Simpan password ini — tidak akan ditampilkan lagi!');
            return self::SUCCESS;
        }

        // ── Toggle super admin flag ─────────────────────────────────────────
        if ($revoke) {
            DB::connection('landlord')
                ->table('users')
                ->where('email', $email)
                ->update([
                    'is_super_admin' => false,
                    'updated_at'     => now(),
                ]);

            $this->info("✅ Hak Super Admin berhasil DICABUT dari: {$email}");
            $this->line("   Nama: {$user->name}");
        } else {
            // Safety confirmation before granting super admin
            $this->warn("⚠️  Anda akan memberikan hak SUPER ADMIN kepada:");
            $this->line("   Nama  : {$user->name}");
            $this->line("   Email : {$email}");
            $this->line("   ID    : {$user->id}");

            if (!$this->confirm('Lanjutkan?')) {
                $this->info('Dibatalkan.');
                return self::SUCCESS;
            }

            DB::connection('landlord')
                ->table('users')
                ->where('email', $email)
                ->update([
                    'is_super_admin' => true,
                    'updated_at'     => now(),
                ]);

            $this->info("✅ Hak Super Admin berhasil DIBERIKAN kepada: {$email}");
            $this->line("   Sekarang pengguna ini dapat mengakses /super-admin");
            $this->line("   (pastikan IP mereka ada di SUPER_ADMIN_IPS di .env)");
        }

        return self::SUCCESS;
    }
}
