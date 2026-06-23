<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class MoverDescripcionDeEspecialidadesAUsuarios extends Migration
{
    /**
     *aplicar los cambios en la base de datos
     */
    public function up(): void
    {
        // se modifica la tabla de especialidades
        Schema::table('especialidades', function (Blueprint $table) {
            // se elimina la columna sin tocar nada de los datos
           if (Schema::hasColumn('especialidades', 'descripcion')) {
            $table->dropColumn('descripcion');
           }
        });

        // se modifica la tabla usuarios
        Schema::table('usuarios', function (Blueprint $table) {
            // se crea la columna nueva
            // se pone 'nullable()' porque los usuarios antiguos (clientes/admins) 
            // no tienen descripción y así la base de datos no da error por dejarla vacía
            // 'after('rol')' la acomoda visualmente al lado del campo rol en phpMyAdmin
           if (!Schema::hasColumn('usuarios', 'descripcion')) {
            $table->text('descripcion')->nullable()->after('rol');
        }
        });
    }

    /**
     * esto es por si acaso para revertir cambios
     */
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
