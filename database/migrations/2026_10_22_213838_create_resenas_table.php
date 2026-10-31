<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('resenas', function (Blueprint $table) {
            $table->id();

            // usuarios.id es BIGINT UNSIGNED → usamos foreignId() para que coincida
            $table->foreignId('user_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('terapeuta_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('cita_id')->nullable()->constrained('citas')->nullOnDelete();

            // Puntuación del 1 al 5
            $table->tinyInteger('puntuacion');
            $table->text('comentario')->nullable();

            $table->timestamps();

            // Evitar que un cliente puntúe dos veces la misma cita
            $table->unique(['user_id', 'cita_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};