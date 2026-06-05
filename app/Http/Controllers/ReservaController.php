<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ReservaController extends Controller
{
    //Muestro el formulario de reserva para el cliente
    public function index()
    {
        return view('clientes.reserva');
    }

    //Proceso la reservación 
    public function store(Request $request)
    {
        // Validación estricta siguiendo las reglas del negocio del Spa
        $request->validate([
            'servicio_id'    => 'required|integer',
            'fecha'          => 'required|date|after_or_equal:today',
            'hora'           => 'required',
            'adjunto_receta' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048', // Máximo 2MB
        ]);

        // Simulación de manejo de archivo si el cliente sube una indicación médica
        if ($request->hasFile('adjunto_receta')) {
            $archivo = $request->file('adjunto_receta');
            // Aquí se guardaría en: storage/app/public/recetas
            // $ruta = $archivo->store('recetas', 'public');
        }

        // Aquí se retorna al catálogo con un mensaje de éxito 
        return redirect()->route('catalogo')->with('success', '¡Su sesión ha sido agendada con éxito en nuestro santuario de bienestar!');
    }
}
