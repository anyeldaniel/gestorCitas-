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
        $comentario = $request->input('comentario');

        // validaciones de seguridad
        if ((int) $cita->cliente_id !== (int) auth()->id()) {
            return back()->with('error', 'No puedes puntuar una cita que no es tuya.');
        }

        if ($cita->estado !== 'completada') {
            return back()->with('error', 'Solo puedes puntuar citas completadas.');
        }

        if (Resena::where('user_id', auth()->id())->where('cita_id', $cita->id)->exists()) {
            return back()->with('error', 'Ya has puntuado esta cita.');
        }

        //Guardar la reseña
        Resena::create([
            'user_id'      => auth()->id(),
            'terapeuta_id' => $cita->trabajador_id,
            'cita_id'      => $cita->id,
            'puntuacion'   => $request->puntuacion,
            'comentario'   => $comentario === null ? null : strip_tags($comentario),
        ]);

        return back()->with('success', '¡Gracias por tu calificación!');
    }

    public function update(Request $request, Resena $resena)
    {
        abort_unless(
            (int) $resena->user_id === (int) auth()->id(),
            403,
            'No tienes permiso para editar esta reseña.'
        );

        $request->validate([
            'puntuacion' => 'required|integer|min:1|max:5',
            'comentario' => 'nullable|string|max:500',
        ]);

        $comentario = $request->input('comentario');

        $resena->update([
            'puntuacion' => $request->puntuacion,
            'comentario' => $comentario === null ? null : strip_tags($comentario),
        ]);

        return back()->with('success', 'Tu reseña fue actualizada correctamente.');
    }

    public function destroy(Resena $resena)
    {
        abort_unless(
            (int) $resena->user_id === (int) auth()->id(),
            403,
            'No tienes permiso para eliminar esta reseña.'
        );

        $resena->delete();

        return back()->with('success', 'Tu reseña fue eliminada.');
    }
}