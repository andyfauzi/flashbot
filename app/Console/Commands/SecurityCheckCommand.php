<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SecurityCheckCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'flashbot:security-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Perform runtime security check on the Flashbot application environment and configuration.';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info("==========================================");
        $this->info("🛡️  FLASHBOT SECURITY RUNTIME CHECKER 🛡️");
        $this->info("==========================================");

        $score = 100;
        $checks = [];

        // 1. Check APP_DEBUG
        if (config('app.debug')) {
            $checks[] = ['❌', 'APP_DEBUG', 'is TRUE. Should be false in production. (-20 points)'];
            $score -= 20;
        } else {
            $checks[] = ['✅', 'APP_DEBUG', 'is FALSE (Safe)'];
        }

        // 2. Check APP_ENV
        if (config('app.env') !== 'production') {
            $checks[] = ['❌', 'APP_ENV', 'is not production (' . config('app.env') . '). (-10 points)'];
            $score -= 10;
        } else {
            $checks[] = ['✅', 'APP_ENV', 'is production (Safe)'];
        }

        // 3. Check SESSION_DRIVER
        if (config('session.driver') === 'array') {
            $checks[] = ['❌', 'SESSION_DRIVER', "is 'array'. Cannot persist sessions safely. (-10 points)"];
            $score -= 10;
        } else {
            $checks[] = ['✅', 'SESSION_DRIVER', 'is ' . config('session.driver') . ' (Safe)'];
        }

        // 4. Check DB Credentials
        $dbPassword = env('DB_PASSWORD');
        if (empty($dbPassword) || $dbPassword === 'root' || $dbPassword === '') {
            $checks[] = ['❌', 'DB_PASSWORD', 'is weak or empty. Set a strong database password! (-20 points)'];
            $score -= 20;
        } else {
            $checks[] = ['✅', 'DB_PASSWORD', 'is configured securely.'];
        }

        // 5. Check Audit Logs Migration
        try {
            DB::connection('landlord')->table('audit_logs')->limit(1)->get();
            $checks[] = ['✅', 'Audit Logs', 'Table exists and is accessible.'];
        } catch (\Exception $e) {
            $checks[] = ['❌', 'Audit Logs', 'Table not found. Please run migrations! (-15 points)'];
            $score -= 15;
        }

        // Display results
        foreach ($checks as $check) {
            $this->line("{$check[0]} <options=bold>{$check[1]}</> : {$check[2]}");
        }

        $this->info("------------------------------------------");
        
        // Calculate equivalent security score out of 10.0
        $finalScore = max(0, $score / 10);
        $color = $finalScore >= 8.0 ? 'green' : ($finalScore >= 5.0 ? 'yellow' : 'red');
        
        $this->line("<options=bold,reverse;fg={$color}> SECURITY SCORE: {$finalScore} / 10.0 </>");
        $this->info("------------------------------------------");

        if ($finalScore < 8.0) {
            $this->error('Security check failed! Please fix the red items above before deploying to production.');
            return 1;
        }

        $this->info('Your Flashbot application is secure and ready for production! 🚀');
        return 0;
    }
}
