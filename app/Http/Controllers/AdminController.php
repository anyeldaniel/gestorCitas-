<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AdminController extends Controller
{
    // Muestra el panel principal (Módulo Dashboard)
    public function dashboard()
    {
        return view('admin.dashboard');
    }

    // Muestra las gráficas (Módulo Reportes)
    public function reportes()
    {
        return view('admin.reportes');
    }

    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }

}