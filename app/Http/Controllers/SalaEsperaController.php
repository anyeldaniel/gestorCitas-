<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class SalaEsperaController extends Controller
{
    public function index()
    {
        // En vez de view(), devolvemos texto para probar el Backend
        return response()->json([
            'status' => 'success',
            'modulo' => 'Sala de Espera',
            'mensaje' => 'El backend de Sala de Espera responde correctamente'
        ]);
    }
}