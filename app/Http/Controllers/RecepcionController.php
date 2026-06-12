<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class RecepcionController extends Controller
{
    // Esta función carga la vista principal de la secretaria/recepción
    public function agenda()
    {
        // Apunta al archivo resources/views/recepcion/agenda.blade.php
        return view('recepcion.agenda');
    }
}