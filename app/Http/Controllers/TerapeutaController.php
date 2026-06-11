<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TerapeutaController extends Controller
{
    public function index()
    {
        // Datos simulados de los especialistas, pa probar mis vistas
        $terapeutas = [
            [
                'id' => 1,
                'nombre' => 'Dra. Alana Ramos',
                'especialidad' => 'Medicina Estética & Dermatología',
                'experiencia' => '8 años de experiencia',
                'descripcion' => 'Especialista en rejuvenecimiento facial, aplicación de Plasma Rico en Plaquetas (PRP) y terapias avanzadas de hidratación dérmica.',
                'imagen' => 'alana_ramos.jpg',
                'disponibilidad' => 'Lunes a Viernes'
            ],
            [
                'id' => 2,
                'nombre' => 'Lic. Andrés García',
                'especialidad' => 'Fisioterapia & Cosmiatría',
                'experiencia' => '5 años de experiencia',
                'descripcion' => 'Experto en masajes descontracturantes profundos, terapias de relajación spa y tratamientos corporales reductores.',
                'imagen' => 'andres_garcia.jpg',
                'disponibilidad' => 'Miércoles a Sábado'
            ]
        ];

        // Simulamos temporalmente el rol para desarrollo (puedes cambiarlo  anyel a 'admin' para probar las herramientas de gestión)
        $userRole = 'admin'; 

        return view('compartidas.terapeutas', compact('terapeutas', 'userRole'));
    }
}