<?php

namespace Tests\Unit\Middleware;

use App\Http\Middleware\SuperAdminOnly;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\TestCase;

class SuperAdminOnlyTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        // Set the allowed IP for testing
        putenv('SUPER_ADMIN_IPS=127.0.0.1,::1');
    }

    // ────────────────────────────────────────────────────────────────────────
    // Helper: build a fake Request with a given IP
    // ────────────────────────────────────────────────────────────────────────
    private function makeRequest(string $ip = '127.0.0.1'): Request
    {
        $request = Request::create('/super-admin', 'GET');
        $request->server->set('REMOTE_ADDR', $ip);
        return $request;
    }

    // ────────────────────────────────────────────────────────────────────────
    // Test 1: Unauthenticated request → 403
    // ────────────────────────────────────────────────────────────────────────
    public function test_unauthenticated_request_returns_403(): void
    {
        Auth::shouldReceive('check')->once()->andReturn(false);

        $middleware = new SuperAdminOnly();
        $request    = $this->makeRequest();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, fn($req) => response('ok'));
    }

    public function test_unauthenticated_request_is_logged_as_warning(): void
    {
        Log::shouldReceive('warning')->once()->with(
            '[SuperAdmin] Unauthenticated access attempt.',
            \Mockery::type('array')
        );

        Auth::shouldReceive('check')->once()->andReturn(false);

        $middleware = new SuperAdminOnly();
        $request    = $this->makeRequest();

        try {
            $middleware->handle($request, fn($req) => response('ok'));
        } catch (\Symfony\Component\HttpKernel\Exception\HttpException $e) {
            $this->assertEquals(403, $e->getStatusCode());
        }
    }

    // ────────────────────────────────────────────────────────────────────────
    // Test 2: Authenticated regular user (is_super_admin = false) → 403
    // ────────────────────────────────────────────────────────────────────────
    public function test_regular_auth_user_returns_403(): void
    {
        // Create a regular user in landlord DB
        $userId = DB::connection('landlord')->table('users')->insertGetId([
            'name'           => 'Regular User',
            'email'          => 'regular@example.com',
            'password'       => bcrypt('password'),
            'is_super_admin' => false,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $fakeUser = (object) ['id' => $userId, 'email' => 'regular@example.com'];
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn($userId);
        Auth::shouldReceive('user')->andReturn($fakeUser);

        $middleware = new SuperAdminOnly();
        $request    = $this->makeRequest();

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, fn($req) => response('ok'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Test 3: Super admin from a NON-whitelisted IP → 403
    // ────────────────────────────────────────────────────────────────────────
    public function test_super_admin_from_unlisted_ip_returns_403(): void
    {
        $userId = DB::connection('landlord')->table('users')->insertGetId([
            'name'           => 'Super Admin',
            'email'          => 'super@flashbot.id',
            'password'       => bcrypt('securepassword123'),
            'is_super_admin' => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $fakeUser = (object) ['id' => $userId, 'email' => 'super@flashbot.id'];
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn($userId);
        Auth::shouldReceive('user')->andReturn($fakeUser);

        $middleware = new SuperAdminOnly();
        // Requesting from an IP NOT in the whitelist
        $request = $this->makeRequest('203.0.113.99');

        $this->expectException(\Symfony\Component\HttpKernel\Exception\HttpException::class);

        $middleware->handle($request, fn($req) => response('ok'));
    }

    // ────────────────────────────────────────────────────────────────────────
    // Test 4: Valid super admin + whitelisted IP → 200 (passes through)
    // ────────────────────────────────────────────────────────────────────────
    public function test_valid_super_admin_from_whitelisted_ip_passes(): void
    {
        $userId = DB::connection('landlord')->table('users')->insertGetId([
            'name'           => 'Platform Owner',
            'email'          => 'owner@flashbot.id',
            'password'       => bcrypt('verySecurePass123!'),
            'is_super_admin' => true,
            'created_at'     => now(),
            'updated_at'     => now(),
        ]);

        $fakeUser = (object) ['id' => $userId, 'email' => 'owner@flashbot.id'];
        Auth::shouldReceive('check')->once()->andReturn(true);
        Auth::shouldReceive('id')->once()->andReturn($userId);
        Auth::shouldReceive('user')->andReturn($fakeUser);
        Log::shouldReceive('info')->once(); // Access granted log

        $middleware = new SuperAdminOnly();
        // IP is whitelisted
        $request  = $this->makeRequest('127.0.0.1');
        $response = $middleware->handle($request, fn($req) => response('ok'));

        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('ok', $response->getContent());
    }

    // ────────────────────────────────────────────────────────────────────────
    // Test 5: Feature test — /super-admin route redirects to login when guest
    // ────────────────────────────────────────────────────────────────────────
    public function test_super_admin_route_redirects_guest_to_login(): void
    {
        $response = $this->get('/super-admin');
        $response->assertRedirect('/login');
    }
}
