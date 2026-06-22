<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use Carbon\Carbon;

class AgendaController extends Controller
{
    // Vista Global de la Agenda.
public function index()
    {
        // Traemos todas las citas cruzando los datos para obtener los nombres reales.
        $citas = Cita::join('usuarios as clientes', 'citas.cliente_id', '=', 'clientes.id')
            ->join('usuarios as trabajadores', 'citas.trabajador_id', '=', 'trabajadores.id')
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

        // Agrupamos las citas por hora (Ej: "02:00 PM") para la grilla.
        $citasPorHora = $citas->groupBy(function($cita) {
            return Carbon::parse($cita->hora)->format('h:i A');
        });

        // Enviamos los datos a la vista.
        return view('recepcion.agenda', compact('citasPorHora'));
    }
}