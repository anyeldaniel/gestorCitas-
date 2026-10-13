<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;

class AgendaController extends Controller
{
    public function index()
    {
        $user = auth()->user();

        // Query base con relaciones
        $query = Cita::with(['paciente', 'terapeuta', 'servicio'])
            ->orderBy('fecha', 'asc')
            ->orderBy('hora', 'asc');

        // Si es trabajador, solo ve SUS citas
        if ($user->rol === 'trabajador') {
            $query->where('trabajador_id', $user->id);
        }

        $citas = $query->get();

        // Terapeutas para el filtro (solo admin/recepcionista)
        $terapeutas = collect();
        if (in_array($user->rol, ['admin', 'recepcionista'])) {
            $terapeutas = User::where('rol', 'trabajador')
                ->orderBy('nombre')
                ->get();
        }

        // ⚠️ La vista está en compartidas/
        return view('compartidas.agenda', compact('citas', 'terapeutas'));
    }
}