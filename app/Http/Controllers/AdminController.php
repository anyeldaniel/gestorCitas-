<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
  /*
   ESTAS DOS FUNCIONES YA NO HACEN FALTA, LAS REDERIZO DE UNA EN LA RUTAS
  
  // Muestra el panel principal (Módulo Dashboard)
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Muestra las gráficas (Módulo Reportes)
   /* public function reportes()
    {
        return view('admin.reportes');
    }   ya no es necesaria esta funcion (lo dejare por si acaso) */

    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }
}