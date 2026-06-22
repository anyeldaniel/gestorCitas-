<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Carbon\Carbon;

class RecepcionController extends Controller
{
    // Esta función carga la vista principal de la recepción.
    public function agenda()
    {
        // Traemos todas las citas cruzando los datos para obtener los nombres reales.
        $citas = Cita::join('users as clientes', 'citas.cliente_id', '=', 'clientes.id')
            ->join('users as trabajadores', 'citas.trabajador_id', '=', 'trabajadores.id')
            ->join('servicios', 'citas.servicio_id', '=', 'servicios.id')
            ->select(
                'citas.*',
                'clientes.nombre as cliente_nombre',
                'trabajadores.nombre as especialista_nombre',
                'servicios.nombre_servicio'
            )
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc')
            ->get();

        // Agrupamos las citas por hora (Ej: "12:00 PM") para facilitar la visualización en la vista.
        $citasPorHora = $citas->groupBy(function($cita) {
            return Carbon::parse($cita->hora)->format('h:i A');
        });

        // Enviamos los datos a la vista.
        return view('recepcion.agenda', compact('citasPorHora'));
    }
}