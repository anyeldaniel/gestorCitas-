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

            // ⚠️ Usamos unsignedInteger (INT UNSIGNED) porque el "id" de la tabla
            // "usuarios" fue creado así (no como BIGINT). Si usáramos foreignId()
            // Laravel intentaría crear BIGINT UNSIGNED y la FK fallaría con error 150.
            $table->unsignedInteger('user_id');
            $table->unsignedInteger('terapeuta_id');
            $table->unsignedInteger('cita_id')->nullable();

            // Puntuación del 1 al 5
            $table->tinyInteger('puntuacion');
            $table->text('comentario')->nullable();

            $table->timestamps();

            // Llaves foráneas con los tipos correctos
            $table->foreign('user_id')
                  ->references('id')->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('terapeuta_id')
                  ->references('id')->on('usuarios')
                  ->onDelete('cascade');

            $table->foreign('cita_id')
                  ->references('id')->on('citas')
                  ->onDelete('set null');

            // Evitar que un cliente puntúe dos veces la misma cita
            $table->unique(['user_id', 'cita_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('resenas');
    }
};