<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;

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
     * Validar la seleccion de cita y guardarla temporalmente para el checkout.
     */
    public function store(Request $request)
    {
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
        $trabajadoresArray = array_map('intval', $request->trabajadores_por_servicio);

        // Si el cliente seleccionó una alternativa sugerida por el sistema anti-choques
        if ($request->filled('trabajador_id_alternativo')) {
            $firstKey = array_key_first($trabajadoresArray);
            $trabajadoresArray[$firstKey] = (int) $request->trabajador_id_alternativo;
        }

        $primerEspecialistaId = reset($trabajadoresArray);
        $primerServicioId = $servicios->first()->id;

        // 2. SISTEMA ANTI-CHOQUES
        $citaOcupada = Cita::where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('trabajador_id', $primerEspecialistaId)
            ->whereIn('estado', ['pendiente', 'confirmada', 'en curso'])
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
                    ->whereIn('estado', ['pendiente', 'confirmada', 'en curso']);
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

        $checkoutUrl = route('checkout.view');

        session()->put('reserva_pendiente', [
            'cliente_id' => (int) auth()->id(),
            'fecha' => $request->fecha,
            'hora' => $request->hora,
            'trabajador_id' => (int) $primerEspecialistaId,
            'servicio_id' => (int) $primerServicioId,
            'trabajadores_por_servicio' => $trabajadoresArray,
        ]);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Selección guardada. Continúa al pago.',
                'redirect_url' => $checkoutUrl,
            ]);
        }

        return redirect()
            ->to($checkoutUrl)
            ->with('success', 'Selección guardada. Continúa al pago.');
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