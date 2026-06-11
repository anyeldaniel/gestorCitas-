<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class EspecialistaController extends Controller
{
    // Esta función carga el tablero de trabajo del masajista/esteticista
    public function tablero()
    {
        // Apunta al archivo resources/views/especialistas/tablero.blade.php
        return view('especialistas.tablero');
    }
}