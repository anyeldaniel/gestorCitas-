<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;
use App\Services\NotificacionService;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Log;

class ReservaController extends Controller
{
    /**
     * Muestra el formulario de reserva.
     */
    public function index(Request $request)
    {
        $servicio = null;
        if ($request->filled('servicio_id')) {
            $servicio = Servicio::find($request->query('servicio_id'));
        }

        $servicioData = [
            'id'       => $servicio->id ?? $request->query('servicio_id', 1),
            'nombre'   => $servicio->nombre_servicio ?? $request->query('nombre', 'Servicio Spa'),
            'precio'   => $servicio->precio ?? $request->query('precio', 50),
            'duracion' => $servicio->duracion_minutos ?? 60,
        ];

        $serviciosAgrupados = Servicio::with(['especialidad', 'especialistas'])
            ->get()
            ->groupBy(function ($item) {
                return $item->especialidad ? $item->especialidad->nombre_especialidad : 'Otros Servicios';
            });

        $terapeutas = User::where('id', '!=', auth()->id())
            ->where('rol', 'trabajador')
            ->orderBy('nombre')
            ->get();

        return view('clientes.reserva', compact('servicioData', 'terapeutas', 'serviciosAgrupados'));
    }

    /**
     * Crear una nueva cita (multi-servicio).
     */
    public function store(Request $request)
    {
        // 1. VALIDACIÓN (fusionada: base remota + refuerzos de rol y fecha)
        $request->validate([
            'servicios'                     => 'required|array|min:1',
            'servicios.*'                   => 'exists:servicios,id',
            'trabajadores_por_servicio'     => 'required|array',
            'trabajadores_por_servicio.*'   => 'integer|exists:usuarios,id,rol,trabajador',
            'trabajador_id_alternativo'     => 'nullable|integer|exists:usuarios,id,rol,trabajador',
            'fecha'                         => 'required|date|after_or_equal:today',
            'hora'                          => 'required',
        ]);

        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $montoTotal = $servicios->sum('precio');
        $trabajadoresArray = $request->trabajadores_por_servicio;

        // Si el cliente seleccionó una alternativa sugerida por el sistema anti-choques
        if ($request->filled('trabajador_id_alternativo')) {
            $firstKey = array_key_first($trabajadoresArray);
            $trabajadoresArray[$firstKey] = $request->trabajador_id_alternativo;
        }

        $primerEspecialistaId = reset($trabajadoresArray);
        $primerServicioId = $servicios->first()->id;

        // 2. SISTEMA ANTI-CHOQUES
        $citaOcupada = Cita::where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('trabajador_id', $primerEspecialistaId)
            ->whereIn('estado', ['pendiente', 'confirmada', 'revision'])
            ->first();

        if ($citaOcupada) {
            $terapeutasAlternativos = User::where('rol', 'trabajador')
                ->where('id', '!=', $primerEspecialistaId)
                ->whereHas('servicios', function ($query) use ($primerServicioId) {
                    $query->where('servicio_id', $primerServicioId);
                })
                ->whereDoesntHave('citas', function ($query) use ($request) {
                    $query->where('fecha', $request->fecha)
                          ->where('hora', $request->hora)
                          ->whereIn('estado', ['pendiente', 'confirmada', 'revision']);
                })
                ->get();

            // Si es petición AJAX, devolvemos JSON para el SweetAlert
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'message' => 'El especialista seleccionado ya tiene una reserva asignada en este horario.',
                    'alternativas' => $terapeutasAlternativos
                ], 422);
            }

            // Fallback tradicional
            return back()->withInput()->withErrors([
                'hora' => 'El especialista seleccionado ya tiene una reserva asignada en este horario.'
            ])->with('terapeutas_alternativos', $terapeutasAlternativos);
        }

        // 3. GUARDADO DE LA CITA PRINCIPAL
        $cita = Cita::create([
            'cliente_id'    => auth()->id(),
            'trabajador_id' => $primerEspecialistaId,
            'servicio_id'   => $primerServicioId,
            'cabina_id'     => null,
            'monto_total'   => $montoTotal,
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'estado'        => 'pendiente',
        ]);

        // Guardar TODOS los servicios en la tabla pivote
        foreach ($trabajadoresArray as $servicioId => $especialistaId) {
            $cita->servicios()->attach($servicioId, [
                'trabajador_id' => $especialistaId
            ]);
        }

        // Notificaciones (Manejadas con try/catch para no romper la reserva si falla el correo)
        try {
            NotificacionService::citaNueva($cita);

            $cliente = auth()->user();
            if ($cliente) {
                // $cliente->notify(new \App\Notifications\NuevaCitaNotification($cita));
            }
            $terapeutasNotificar = array_unique(array_values($trabajadoresArray));
            foreach ($terapeutasNotificar as $terapeutaId) {
                $terapeuta = User::find($terapeutaId);
                if ($terapeuta) {
                    // $terapeuta->notify(new \App\Notifications\NuevaCitaNotification($cita));
                }
            }
        } catch (\Exception $e) {
            Log::error("Error al enviar notificación: " . $e->getMessage());
        }

        // 4. RESPUESTA FINAL (Soporta AJAX y tradicional)
        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => '¡Cita apartada con éxito! Redirigiendo al pago...',
                'redirect_url' => route('checkout.view', ['cita_id' => $cita->id])
            ]);
        }

        return redirect()
            ->route('checkout.view', ['cita_id' => $cita->id])
            ->with('success', 'Cita apartada. Asignamos a tus especialistas seleccionados.');
    }

    /**
     * Mostrar las reservas y pagos del cliente autenticado.
     */
    public function misReservas()
    {
        $citas = Cita::with(['servicio', 'trabajador', 'pagos', 'servicios'])
                    ->where('cliente_id', auth()->id())
                    ->orderBy('fecha', 'DESC')
                    ->get();

        return view('clientes.mis-reservas', compact('citas'));
    }

    /**
     * Obtener especialistas disponibles para un servicio (Para peticiones AJAX).
     */
    public function getEspecialistas($id)
    {
        $servicio = Servicio::find($id);
        if (!$servicio) {
            return response()->json([]);
        }
        return response()->json($servicio->especialistas);
    }
}