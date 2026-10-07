<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('servicios', function (Blueprint $table) {

            // Agregamos la columna. La ponemos 'nullable' para que los servicios.
            // que ya tienes en la base de datos no den error por estar vacíos.
            $table->unsignedBigInteger('especialidad_id')->nullable()->after('id');

            // Creamos la relación (Llave foránea).
            $table->foreign('especialidad_id')
                ->references('id')->on('especialidades')
                ->onDelete('set null'); // Si borran una especialidad, el servicio no se borra, solo queda en null.
        });
    }

    public function down()
    {
        Schema::table('servicios', function (Blueprint $table) {
            // Pasos para revertir en caso de emergencia
            $table->dropForeign(['especialidad_id']);
            $table->dropColumn('especialidad_id');
        });
    }
};