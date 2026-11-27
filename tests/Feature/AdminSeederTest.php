<?php

namespace Tests\Feature;

use App\Models\User;
use Database\Seeders\AdminSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Config;
use Tests\TestCase;

class AdminSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_seeder_does_not_create_an_account_when_configuration_is_incomplete(): void
    {
        Config::set('services.admin', [
            'name' => 'Admin',
            'email' => '',
            'password' => '',
        ]);

        $this->seed(AdminSeeder::class);

        $this->assertDatabaseCount('usuarios', 0);
    }

    public function test_admin_seeder_creates_an_admin_with_a_hashed_environment_password(): void
    {
        Config::set('services.admin', [
            'name' => 'Admin seguro',
            'email' => 'admin-test@example.test',
            'password' => 'test-password-123',
        ]);

        $this->seed(AdminSeeder::class);

        $admin = User::query()->where('correo', 'admin-test@example.test')->sole();

        $this->assertSame('admin', $admin->rol);
        $this->assertSame('Admin seguro', $admin->nombre);
        $this->assertTrue(Hash::check('test-password-123', $admin->contraseña));
    }
}
