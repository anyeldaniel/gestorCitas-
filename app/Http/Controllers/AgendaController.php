<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\User;

class AgendaController extends Controller
{
    public function index()
    {
        // Renombramos la variable a $terapeutas para consistencia
        $terapeutas = User::where('rol', 'trabajador')->get();
        $user = auth()->user();

        if ($user->rol === 'trabajador') {
            $citas = Cita::where('trabajador_id', $user->id)->get();
        } else {
            $citas = Cita::all();
        }

        // Enviamos 'terapeutas' para que la vista pueda iterar correctamente
        return view('compartidas.agenda', compact('citas', 'terapeutas'));
    }

    public function updateStatus(Request $request, $id)
    {
        $cita = Cita::findOrFail($id);
        $cita->estado = $request->estado;
        $cita->save();

        return redirect()->back()->with('success', 'Estado de la cita actualizado.');
    }
}