<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    public function run(): void
    {
        $name = trim((string) config('services.admin.name'));
        $email = trim((string) config('services.admin.email'));
        $password = (string) config('services.admin.password');

        if ($name === '' || $email === '' || $password === '') {
            $this->command?->warn(
                'Admin no creado: configura ADMIN_NAME, ADMIN_EMAIL y ADMIN_PASSWORD en el entorno.'
            );

            return;
        }

        User::updateOrCreate(
            ['correo' => $email],
            [
                'nombre' => $name,
                'contraseña' => Hash::make($password),
                'rol' => 'admin',
            ]
        );

        $this->command?->info('Administrador configurado correctamente.');
    }
}
