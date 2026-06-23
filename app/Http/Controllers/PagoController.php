<?php

namespace App\Http\Controllers;

class PagoController extends Controller
{
    public function index()
    {
        // Así debe quedar:
        return view('compartidas.verificar-pago');
    }
}
