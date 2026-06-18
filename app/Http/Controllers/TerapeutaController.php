<?php

namespace App\Http\Controllers;

use App\Models\Cita; // Importamos el modelo de Cita para interactuar con la base de datos de citas.
use App\Models\User; // Importamos el modelo de User para obtener información del terapeuta.
use Illuminate\Support\Facades\Auth; // Para obtener el usuario autenticado.

class TerapeutaController extends Controller
{



    // Mostrar el tablero del terapeuta con sus citas asignadas
    public function index()
    {
        $userRole = Auth::user()->rol; // Obtener el rol del usuario autenticado.
        $terapeutas = User::where('rol', 'trabajador')->get();

        foreach ($terapeutas as $terapeuta) {
            $listaEspecialidades = \Illuminate\Support\Facades\DB::table('especialidades')
                ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                ->where('trabajador_especialidad.usuario_id', $terapeuta->id)
                ->pluck('especialidades.nombre_especialidad') // Extraemos solo el nombre
                ->toArray();

            // Unimos con comas y lo asignamos al objeto
            $terapeuta->especialidades = implode(', ', $listaEspecialidades);
        }
        return view('compartidas.terapeutas', compact('terapeutas', 'userRole'));
    }

    // Ver los servicios que el terapeuta tiene asignados.
    public function tablero()
    {
        // Traeremos solo las citas que le corresponden al terapeuta logueado.
        // cambiamos "user_id" por "trabajador_id" para que busque las citas asignadas al terapeuta.
        $misCitas = Cita::where('trabajador_id', Auth::id())->get();
        return view('especialistas.tablero', compact('misCitas'));
    }
}
