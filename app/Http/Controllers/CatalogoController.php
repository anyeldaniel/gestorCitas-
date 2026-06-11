<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class CatalogoController extends Controller
{
    public function index()
    {
        // De esta manera el controlador busca el archivo físico en su nueva ubicación
        return view('compartidas.catalogo');
    }
}