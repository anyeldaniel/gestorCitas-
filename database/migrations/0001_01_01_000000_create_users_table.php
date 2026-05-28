<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->string('nombre');
            $table->string('correo')->unique(); // correo unico
            $table->string('telefono')-> nullable();
            $table->timestamp('verificacion')->nullable(); // verificacion de seguridad
            $table->string('contraseña'); // contraseña encriptada
            $table->enum('rol',['admin','recepcion','trabajador','cliente'])->default('cliente'); //por defecto todos entran como cliente
            $table->rememberToken(); // para mantener la sesion iniciada
            $table->timestamps(); // fechas de registro
        });

        DB::table('usuarios')-> insert([ //Usuario administrador
            'nombre' => 'administrador',
            'correo' => 'syncrostyle732@gmail.com',
            'contraseña' => Hash:: make ('admin1829'),
            'rol' => 'admin',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        Schema::create('password_reset_tokens', function (Blueprint $table) {
            $table->string('email')->primary();
            $table->string('token');
            $table->timestamp('created_at')->nullable();
        });

        Schema::create('sessions', function (Blueprint $table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->longText('payload');
            $table->integer('last_activity')->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
        Schema::dropIfExists('password_reset_tokens');
        Schema::dropIfExists('sessions');
    }
};
