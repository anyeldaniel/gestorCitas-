<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('servicio_user', function (Blueprint $table) {
            $table->id();
            
            // Conecta con tu tabla servicios
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            
            // Conecta con tu tabla usuarios 
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade'); 
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('servicio_user');
    }
};