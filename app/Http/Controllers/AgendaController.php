<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\User;
use Carbon\Carbon;
use App\Services\WeatherService;
use App\Services\NotificacionService;
use App\Services\CancelacionCitaService;
use Illuminate\Support\Facades\Mail;
use App\Mail\CitaCancelada;
use App\Mail\ReembolsoPorDefinir;
use Illuminate\Support\Facades\Log;

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

        // 👇 CORRECCIÓN: Evitamos que las zonas horarias resten un día 👇
        $fechaInput = $request->input('fecha');

        if ($fechaInput) {
            // Tomamos solo los primeros 10 caracteres (YYYY-MM-DD) ignorando horas y zonas horarias
            $fechaStr = substr($fechaInput, 0, 10);
            $fecha = Carbon::parse($fechaStr)->toDateString();
        } else {
            $fecha = Carbon::today()->toDateString();
        }
        // 👆 FIN DE LA CORRECCIÓN 👆

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
        $base = Cita::with(['cliente', 'trabajador', 'servicio', 'pagos']);

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

        // Carga al cliente y al servicio para usarlos en el correo sin consultas adicionales.
        $cita = Cita::with(['cliente', 'servicio'])->findOrFail($id);
        $nuevoEstado = $request->estado;

        if ($cita->estaCancelada()) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta cita está cancelada. No se puede reactivar.',
            ], 422);
        }

        if ($cita->estado === 'completada') {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Esta cita ya fue completada y no puede modificarse.',
            ], 422);
        }

        if ($cita->estado === $nuevoEstado) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'La cita ya se encuentra en ese estado.',
            ], 422);
        }

        // Flujo estricto de transiciones permitidas
        $transicionesPermitidas = [
            'pendiente'  => ['confirmada'],
            'confirmada' => ['en curso', 'completada'],
            'en curso'   => ['completada'],
        ];

        $permitidos = $transicionesPermitidas[$cita->estado] ?? [];
        if (!in_array($nuevoEstado, $permitidos, true)) {
            return response()->json([
                'ok'      => false,
                'mensaje' => "No se puede cambiar una cita de '{$cita->estado}' a '{$nuevoEstado}'. Primero debe estar confirmada.",
            ], 422);
        }

        // Pago aprobado obligatorio para confirmar, iniciar o completar
        $tienePagoAprobado = $cita->pagos()->where('estado_pago', 'aprobado')->exists();
        if (in_array($nuevoEstado, ['confirmada', 'en curso', 'completada'], true) && !$tienePagoAprobado) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'Primero hay que autorizar el pago para avanzar el estado de esta cita.',
            ], 422);
        }

        // No permitir completar una cita que todavía no ha ocurrido
        if ($nuevoEstado === 'completada') {
            $fechaHoraCita = Carbon::parse(
                Carbon::parse($cita->fecha)->format('Y-m-d') . ' ' . $cita->hora
            );

            if (Carbon::now()->lt($fechaHoraCita)) {
                return response()->json([
                    'ok'      => false,
                    'mensaje' => 'No puedes marcar como completada una cita cuya fecha y hora aún no han llegado.',
                ], 422);
            }
        }

        // Asigna a la cita el nuevo estado validado que llegó desde la agenda.
        $cita->estado = $nuevoEstado;
        // Persiste el cambio de estado en la base de datos.
        $cita->save();

        NotificacionService::cambioEstado($cita, $nuevoEstado);

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
    public function cancelar(Request $request, $id, CancelacionCitaService $cancelacionCita)
    {
        $user = auth()->user();

        $data = $request->validate([
            'motivo' => 'required|string|min:5|max:500',
            'reembolsos' => 'sometimes|array',
            'reembolsos.*' => 'required|numeric|decimal:0,2|min:0.01',
        ], [
            'motivo.required' => 'Necesitamos un motivo para dejar constancia.',
            'motivo.min'      => 'El motivo debe tener al menos 5 caracteres.',
        ]);

        $cita = Cita::with(['cliente', 'servicio'])->findOrFail($id);

        // Verificar también la tabla pivote cita_servicio
        // (para multi-servicio, el trabajador puede no ser el $cita->trabajador_id principal)
        $esTrabajadorDeLaCita = $user->rol === 'trabajador' && (
            $cita->trabajador_id === $user->id ||
            $cita->servicios()
                ->wherePivot('trabajador_id', $user->id)
                ->exists()
        );

        $puedeCancelar =
            in_array($user->rol, ['admin', 'recepcion']) ||
            $esTrabajadorDeLaCita;

        if (!$puedeCancelar) {
            return response()->json([
                'ok'      => false,
                'mensaje' => 'No tienes permiso para cancelar esta cita.',
            ], 403);
        }

        $resultado = $cancelacionCita->cancelarCita(
            (int) $id,
            $user,
            $data['motivo'],
            $data['reembolsos'] ?? []
        );
        $cita = $resultado['cita'];

        // 1. Notificación interna (campanita)
        NotificacionService::citaCancelada($cita, $data['motivo']);

        // 2. Correo electrónico
        Mail::to($cita->cliente->correo)->send(new CitaCancelada($cita, $data['motivo']));

        // 3. Notificación Push al cliente
        try {
            $cliente = \App\Models\User::find($cita->cliente_id);
            if ($cliente) {
                $cliente->notify(new \App\Notifications\CitaCanceladaNotification($cita));
            }
        } catch (\Exception $e) {
            // Si falla el envío (ej. sin internet), no rompemos la cancelación.
            Log::error("Error al enviar Push de cancelación: " . $e->getMessage());
        }

        if ($resultado['reembolso_por_definir']) {
            $destinatarios = NotificacionService::reembolsoPorDefinir($cita);
            foreach ($destinatarios as $destinatario) {
                Mail::to($destinatario->correo)->send(new ReembolsoPorDefinir($cita));
            }
        }

        return response()->json([
            'ok'      => true,
            'mensaje' => $resultado['reembolso_por_definir']
                ? 'Cita cancelada. Se notificó a administración y recepción para definir el reembolso.'
                : 'Cita cancelada. Administración hará seguimiento.',
        ]);
    }
}