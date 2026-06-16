<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void // esta tabla se hizo como intermedia para la relacion de muchos a muchos
    { // ya que un especialista puede tener varias especialidades
        Schema::create('trabajador_especialidad', function (Blueprint $table) {
            $table->id();
            //relaciones con usuario y especialidades
            $table->foreignId('usuario_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('especialidad_id')->constrained('especialidades')->onDelete('cascade');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trabajador_especialidad');
    }
};
