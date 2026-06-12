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
        Schema::create('cabinas', function (Blueprint $table) {
            $table->id();
            $table->string('nombre de la cabina'); //ejemp: sala masajes vip, area de faciales, etc
            $table->string('ubicacion')->nullable();
            $table->boolean('esta_activa')->default(true); //para inhabilitar la cabila si esta en mantenimiento
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cabinas');
    }
};
