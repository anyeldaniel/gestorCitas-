<?php

namespace App\Http\Controllers;

use App\Models\Cita;
use App\Models\Resena;
use Illuminate\Http\Request;

class ResenaController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'cita_id'    => 'required|exists:citas,id',
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $cita = Cita::findOrFail($request->cita_id);

        // 1. Validar que la cita sea del cliente logueado
        if ($cita->user_id !== auth()->id()) {
            return back()->with('error', 'No puedes puntuar una cita que no es tuya.');
        }

        // 2. Validar que la cita esté completada
        if ($cita->estado !== 'completada') {
            return back()->with('error', 'Solo puedes puntuar citas completadas.');
        }

        // 3. Validar que no haya puntuado ya esta cita
        if (Resena::where('user_id', auth()->id())->where('cita_id', $cita->id)->exists()) {
            return back()->with('error', 'Ya has puntuado esta cita.');
        }

        // 4. Guardar la reseña
        Resena::create([
            'user_id'      => auth()->id(),
            'terapeuta_id' => $cita->trabajador_id, // En tu BD se llama trabajador_id
            'cita_id'      => $cita->id,
            'puntuacion'   => $request->puntuacion,
            'comentario'   => $request->comentario,
        ]);

        return back()->with('success', '¡Gracias por tu calificación!');
    }
}