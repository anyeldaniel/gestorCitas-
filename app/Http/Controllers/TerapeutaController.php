<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;

class TerapeutaController extends Controller
{
    // Ver los servicios que el terapeuta tiene asignados
    public function tablero()
    {
        // Traeremos solo las citas que le corresponden al terapeuta logueado
        $misCitas = \App\Models\Cita::where('user_id', auth()->id())->get();
        return view('especialistas.tablero', compact('misCitas'));
    }
}