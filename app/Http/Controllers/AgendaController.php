<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;   // Controlador para manejar la agenda de citas en la recepción.
use App\Models\Cita; // Importamos el modelo de Cita para interactuar con la base de datos de citas.


class AgendaController extends Controller // Controlador para manejar la agenda de citas en la recepción.
{
    // Muestra todas las citas programadas.
    public function index()
    {
        // Traemos todas las citas con los datos del usuario que la pidió.
        $citas = Cita::all(); 
        return view('recepcion.agenda', compact('citas'));
    }

    // Cambiar el estado de una cita (ejemplo: de pendiente a confirmada).
    public function updateStatus(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->estado = $request->estado;
        $cita->save();

        return redirect()->back()->with('success', 'Estado de la cita actualizado.');
    }
}
