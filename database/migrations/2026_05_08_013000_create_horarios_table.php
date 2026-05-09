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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            //relacion con el trabajor o recepcionista creado por el admin
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            // dia de la semana
            $table->string('dia');
            //rango de horas
            $table->time('hora_entrada');
            $table->time('hora_salida');
            //para saber si el trabajador esta disponible
            $table->boolean('disponible')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
