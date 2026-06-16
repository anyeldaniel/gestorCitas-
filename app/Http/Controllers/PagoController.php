<?php

namespace App\Http\Controllers;

class PagoController extends Controller
{
    public function index()
    {
        return response()->json([
            'status' => 'success',
            'modulo' => 'Verificar Pagos',
            'mensaje' => 'El backend de Verificar Pagos responde correctamente'
        ]);
    }
}