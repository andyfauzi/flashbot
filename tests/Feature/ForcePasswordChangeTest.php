<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ForcePasswordChangeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        
        // Buat mock koneksi tenant untuk test
        config(['database.connections.tenant' => config('database.connections.sqlite')]);
        
        // Jalankan migrasi di koneksi tenant
        $this->artisan('migrate', ['--database' => 'tenant']);
    }

    public function test_user_must_change_password_is_redirected()
    {
        // 1. Buat user dengan must_change_password = true
        $user = User::on('tenant')->create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('RandomPass123!'),
            'role' => 'owner',
            'must_change_password' => true,
        ]);

        // 2. Login
        $this->actingAs($user);

        // 3. Akses dashboard
        $response = $this->get('/pos');

        // 4. Pastikan di-redirect ke halaman paksa ganti password
        $response->assertRedirect('/password/force-change');
    }

    public function test_user_can_access_force_change_page()
    {
        $user = User::on('tenant')->create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('RandomPass123!'),
            'role' => 'owner',
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get('/password/force-change');
        $response->assertStatus(200);
        $response->assertViewIs('auth.force_change_password');
    }

    public function test_password_change_validation()
    {
        $user = User::on('tenant')->create([
            'name' => 'Test Owner',
            'email' => 'owner@test.com',
            'password' => Hash::make('RandomPass123!'),
            'role' => 'owner',
            'must_change_password' => true,
        ]);

        $this->actingAs($user);

        // Uji dengan password pendek (kurang dari 8)
        $response = $this->post('/password/force-change', [
            'password' => 'Pendek1',
            'password_confirmation' => 'Pendek1',
        ]);
        $response->assertSessionHasErrors('password');

        // Uji dengan password tanpa huruf besar
        $response = $this->post('/password/force-change', [
            'password' => 'kecilsemua123',
            'password_confirmation' => 'kecilsemua123',
        ]);
        $response->assertSessionHasErrors('password');

        // Uji dengan password tanpa angka
        $response = $this->post('/password/force-change', [
            'password' => 'TanpaAngka',
            'password_confirmation' => 'TanpaAngka',
        ]);
        $response->assertSessionHasErrors('password');

        // Uji sukses (8+ karakter, ada huruf besar, ada angka)
        $response = $this->post('/password/force-change', [
            'password' => 'Flashbot2026',
            'password_confirmation' => 'Flashbot2026',
        ]);
        $response->assertRedirect('/pos');
        
        $user->refresh();
        $this->assertFalse((bool)$user->must_change_password);
        $this->assertNotNull($user->password_changed_at);
    }

    public function test_normal_user_is_not_redirected()
    {
        $user = User::on('tenant')->create([
            'name' => 'Test Kasir',
            'email' => 'kasir@test.com',
            'password' => Hash::make('Aman1234'),
            'role' => 'kasir',
            'must_change_password' => false,
        ]);

        $this->actingAs($user);

        $response = $this->get('/pos');
        $response->assertStatus(200);
    }
}
