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
        Schema::create('citas', function (Blueprint $table) {
            $table->id();
            //relaciones
            $table->foreignId('cliente_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('trabajador_id')->constrained('usuarios')->onDelete('cascade');
            $table->foreignId('servicio_id')->constrained('servicios');
            $table->foreignId('cabina_id')->nullable()->constrained('cabinas')->onDelete('set null');
            $table->decimal('monto_total', 10, 2)->nullable(); //precio total
            $table->date('fecha');
            $table->time('hora');
            // control de estado
            $table->enum('estado',['pendiente','confirmada','completada','cancelada'])->default('pendiente');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('citas');
    }
};
