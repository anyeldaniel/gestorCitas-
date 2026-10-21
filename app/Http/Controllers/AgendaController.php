<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use App\Services\WeatherService;

class AgendaController extends Controller
{
    /**
     * Devuelve la lista de terapeutas para el filtro.
     * Solo admin y recepción pueden filtrar por terapeuta.
     */
    private function terapeutasDisponibles(User $user)
    {
        if (!in_array($user->rol, ['admin', 'recepcion'])) {
            return collect();
        }

        return User::where('rol', 'trabajador')
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    /**
     * Top 5 de terapeutas por citas completadas.
     * Solo devuelve las que tienen al menos 1 cita completada.
     */
    private function topTerapeutas()
    {
        return User::where('rol', 'trabajador')
            ->withCount(['citasComoTrabajador as citas_completadas' => function ($q) {
                $q->where('estado', 'completada');
            }])
            ->having('citas_completadas', '>', 0)
            ->orderByDesc('citas_completadas')
            ->orderBy('nombre')
            ->limit(5)
            ->get(['id', 'nombre']);
    }

    /**
     * Vista principal de la agenda compartida.
     */
    public function index(Request $request)
    {
        $user = auth()->user();

        // El cliente no entra aquí.
        if ($user->rol === 'cliente') {
            return redirect()->route('clientes.reserva')
                ->with('info', 'Tu agenda personal está en "Mis reservas".');
        }

        // Fecha seleccionada (por defecto hoy).
        $fecha = $request->filled('fecha')
            ? Carbon::parse($request->fecha)->toDateString()
            : Carbon::today()->toDateString();

        // Rango lunes → sábado de la semana de esa fecha.
        $diaSemana  = Carbon::parse($fecha)->dayOfWeekIso; // 1 = lunes
        $lunes      = Carbon::parse($fecha)->subDays($diaSemana - 1)->startOfDay();
        $semanaDias = [];

        for ($i = 0; $i < 6; $i++) {
            $dia = $lunes->copy()->addDays($i);
            $semanaDias[] = [
                'fecha'  => $dia->toDateString(),
                'dia'    => mb_strtoupper(mb_substr($dia->locale('es')->dayName, 0, 3)),
                'numero' => $dia->day,
                'es_hoy' => $dia->isToday(),
                'activo' => $dia->toDateString() === $fecha,
            ];
        }

        // Query base.
        $base = Cita::with(['cliente', 'trabajador', 'servicio']);

        if ($user->rol === 'trabajador') {
            $base->where('trabajador_id', $user->id);
        }

        // Filtro por fecha.
        $query = (clone $base)->whereDate('fecha', $fecha);

        // Búsqueda por cliente o servicio.
        if ($request->filled('search')) {
            $busqueda = trim($request->search);
            $query->where(function ($q) use ($busqueda) {
                $q->whereHas('cliente', fn($qq) => $qq->where('nombre', 'like', "%{$busqueda}%"))
                  ->orWhereHas('servicio', fn($qq) => $qq->where('nombre_servicio', 'like', "%{$busqueda}%"));
            });
        }

        // Filtro por terapeuta (solo admin/recepción).
        if (in_array($user->rol, ['admin', 'recepcion']) && $request->filled('terapeuta_id')) {
            $query->where('trabajador_id', $request->terapeuta_id);
        }

        // Filtro por estado.
        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        $citas = $query->orderBy('hora')->get();

        // Conteo de citas por día de la semana.
        $conteoSemana = [];
        for ($i = 0; $i < 6; $i++) {
            $dia = $lunes->copy()->addDays($i);
            $conteoSemana[$dia->toDateString()] = (clone $base)
                ->whereDate('fecha', $dia->toDateString())
                ->count();
        }

        // Stats del día.
        $stats = [
            'confirmadas' => $citas->where('estado', 'confirmada')->count(),
            'pendientes'  => $citas->where('estado', 'pendiente')->count(),
            'en_curso'    => $citas->where('estado', 'en curso')->count(),
            'completadas' => $citas->where('estado', 'completada')->count(),
            'canceladas'  => $citas->where('estado', 'cancelada')->count(),
        ];

        // Tiempo total reservado (sin contar canceladas).
        $minutosReservados = $citas
            ->where('estado', '!=', 'cancelada')
            ->sum(fn($c) => (int) ($c->servicio->duracion_minutos ?? 0));

        $terapeutas    = $this->terapeutasDisponibles($user);
        $topTerapeutas = $this->topTerapeutas();

        // Clima actual (se cachea 30 min en el servicio).
        $clima = app(WeatherService::class)->obtenerClima();

        return view('compartidas.agenda', compact(
            'user',
            'fecha',
            'semanaDias',
            'conteoSemana',
            'citas',
            'stats',
            'minutosReservados',
            'terapeutas',
            'topTerapeutas',
            'clima'
        ));
    }

    /**
     * Cambia el estado de una cita.
     * Solo admin y recepción.
     */
    public function updateStatus(Request $request, $id)
    {
        $user = auth()->user();

        if (!in_array($user->rol, ['admin', 'recepcion'])) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No tienes permiso para cambiar el estado de esta cita.',
            ], 403);
        }

        $request->validate([
            'estado' => 'required|in:pendiente,confirmada,en curso,completada',
        ]);

        $cita = Cita::findOrFail($id);

        if ($cita->estaCancelada()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta cita está cancelada. No se puede reactivar.',
            ], 422);
        }

        $cita->estado = $request->estado;
        $cita->save();

        return response()->json([
            'ok'       => true,
            'mensaje'  => 'Estado actualizado correctamente.',
            'estado'   => $cita->estado,
            'etiqueta' => $cita->etiquetaEstado(),
            'clase'    => $cita->claseEstado(),
        ]);
    }

    /**
     * Cancela una cita con motivo.
     * Admin, recepción y el trabajador asignado.
     */
    public function cancelar(Request $request, $id)
    {
        $user = auth()->user();

        $request->validate([
            'motivo' => 'required|string|min:5|max:500',
        ], [
            'motivo.required' => 'Necesitamos un motivo para dejar constancia.',
            'motivo.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $cita = Cita::findOrFail($id);

        $puedeCancelar =
            in_array($user->rol, ['admin', 'recepcion']) ||
            ($user->rol === 'trabajador' && $cita->trabajador_id === $user->id);

        if (!$puedeCancelar) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No tienes permiso para cancelar esta cita.',
            ], 403);
        }

        if ($cita->estaCancelada()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta cita ya estaba cancelada.',
            ], 422);
        }

        $cita->estado              = 'cancelada';
        $cita->motivo_cancelacion  = $request->motivo;
        $cita->cancelada_por       = $user->id;
        $cita->cancelada_en        = now();
        $cita->save();

        return response()->json([
            'ok'      => true,
            'mensaje' => 'Cita cancelada. Administración hará seguimiento.',
        ]);
    }
}