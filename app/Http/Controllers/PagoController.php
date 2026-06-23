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
      // Creamos esta función separada para renderizar tu HTML sin chocar con el JSON de arriba.
    public function mostrarVistaAdmin()
    {
        return view('compartidas.verificar-pago');
    }
}