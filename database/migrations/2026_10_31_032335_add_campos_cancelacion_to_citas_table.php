<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            // Solo añade si no existe (por si acaso)
            if (!Schema::hasColumn('citas', 'motivo_cancelacion')) {
                $table->text('motivo_cancelacion')->nullable()->after('estado');
            }
            if (!Schema::hasColumn('citas', 'cancelada_por')) {
                $table->foreignId('cancelada_por')->nullable()->after('motivo_cancelacion')
                      ->constrained('usuarios')->nullOnDelete();
            }
            if (!Schema::hasColumn('citas', 'cancelada_en')) {
                $table->timestamp('cancelada_en')->nullable()->after('cancelada_por');
            }
        });
    }

    public function down(): void
    {
        Schema::table('citas', function (Blueprint $table) {
            if (Schema::hasColumn('citas', 'cancelada_por')) {
                $table->dropForeign(['cancelada_por']);
            }
            $table->dropColumn(['motivo_cancelacion', 'cancelada_por', 'cancelada_en']);
        });
    }
};