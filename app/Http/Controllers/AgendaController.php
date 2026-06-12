<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class AgendaController extends Controller
{
     public function agenda() //Agrego la función para mostrar la agenda del admin 
    {
        // Estoy inyectando estos datos en el controlador, para poder mostrarlos y ver que tal
        $citas = [
            [
                'id' => 1,
                'hora' => '08:00 AM',
                'cliente' => 'Yennifer Pérez',
                'terapeuta' => 'Dra. Alana Ramos',
                'servicio' => 'Plasma Rico en Plaquetas (PRP)',
                'estado' => 'Confirmado'
            ],
            [
                'id' => 2,
                'hora' => '09:30 AM',
                'cliente' => 'Carlos Mendoza',
                'terapeuta' => 'Dra. Alana Ramos',
                'servicio' => 'Masaje Descontracturante Profundo',
                'estado' => 'Pendiente'
            ],
            [
                'id' => 3,
                'hora' => '11:00 AM',
                'cliente' => 'María Valentina',
                'terapeuta' => 'Lic. Andrés García',
                'servicio' => 'Ritual Supremo Beauty & Luxury',
                'estado' => 'Confirmado'
            ]
        ];

        return view('compartidas.agenda', compact('citas'));
    }
}
