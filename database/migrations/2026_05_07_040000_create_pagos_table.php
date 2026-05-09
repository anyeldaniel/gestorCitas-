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
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            //relaciones
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            $table->decimal('monto_anticipado', 10, 2); //porcentaje inicial
            $table->string('comprobante_img')->nullable(); //capture del pago
            $table->enum('estado_pago',['revision','aprobado','rechazado'])->default('revision');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pagos');
    }
};
