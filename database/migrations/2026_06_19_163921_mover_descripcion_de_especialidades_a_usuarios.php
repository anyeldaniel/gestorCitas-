<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoverDescripcionDeEspecialidadesAUsuarios extends Migration
{
    public function up(): void
    {
        // Modificamos especialidades de forma segura
        Schema::table('especialidades', function (Blueprint $table) {
            // Si la columna existe, la borra. Si no, no hace nada y no da error.
            if (Schema::hasColumn('especialidades', 'descripcion')) {
                $table->dropColumn('descripcion'); 
            }
        });

        // Modificamos usuarios de forma segura
        Schema::table('usuarios', function (Blueprint $table) {
            // Si la columna descripcion NO existe en usuarios, la crea.
            if (!Schema::hasColumn('usuarios', 'descripcion')) {
                $table->text('descripcion')->nullable()->after('rol'); 
            }
        });
    }

    public function down(): void
    {
        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });

        Schema::table('especialidades', function (Blueprint $table) {
            $table->text('descripcion')->nullable();
        });
    }
}