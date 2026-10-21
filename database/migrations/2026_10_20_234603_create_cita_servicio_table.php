<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cita_servicio', function (Blueprint $table) {
            $table->id();
            // Relación con la cita (si se borra la cita, se borran sus servicios)
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            // Relación con el servicio (si se borra el servicio, se borra de la cita)
            $table->foreignId('servicio_id')->constrained('servicios')->onDelete('cascade');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cita_servicio');
    }
};