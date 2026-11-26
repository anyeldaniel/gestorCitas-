<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->string('estado_reembolso')->nullable()->after('estado_pago');
            $table->decimal('monto_reembolso', 10, 2)->nullable()->after('estado_reembolso');
            $table->text('motivo_reembolso')->nullable()->after('monto_reembolso');
            $table->text('motivo_rechazo')->nullable()->after('motivo_reembolso');
        });
    }

    public function down(): void
    {
        Schema::table('pagos', function (Blueprint $table) {
            $table->dropColumn([
                'estado_reembolso',
                'monto_reembolso',
                'motivo_reembolso',
                'motivo_rechazo',
            ]);
        });
    }
};
