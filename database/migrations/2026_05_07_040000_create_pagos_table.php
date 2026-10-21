<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
public function up(): void
    {
        Schema::create('pagos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cita_id')->constrained('citas')->onDelete('cascade');
            
            $table->decimal('monto_anticipado', 10, 2);
            
            // --- NUEVOS CAMPOS PARA C2P Y MANUAL ---
            $table->string('metodo_pago')->default('manual'); // Puede ser 'manual' o 'c2p'
            $table->string('banco_origen')->nullable(); // Ejemplo: '0102 - Banco de Venezuela'
            $table->string('referencia')->nullable(); // Los últimos 4 dígitos o el token
            $table->string('telefono_pago')->nullable(); // El número que envía el pago
            // ---------------------------------------

            $table->string('comprobante_img')->nullable(); // Lo dejamos por si en el manual suben captura
            $table->enum('estado_pago', ['revision', 'aprobado', 'rechazado'])->default('revision');
            
            $table->timestamps();
        });
    }
};