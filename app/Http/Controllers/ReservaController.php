<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Cita;
use App\Models\Servicio;
use App\Models\User;
use Illuminate\Validation\Rule;

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

        // Servicios agrupados por especialidad (Agregamos la relación especialistas)
        $serviciosAgrupados = Servicio::with(['especialidad', 'especialistas'])
            ->get()
            ->groupBy(function ($item) {
                return $item->especialidad ? $item->especialidad->nombre_especialidad : 'Otros Servicios';
            });

        // Solo ofrecer usuarios registrados con el rol de trabajador
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
        // 1. VALIDACIÓN ORIGINAL DE TU SISTEMA
        $request->validate([
            'servicios'                     => 'required|array|min:1',
            'servicios.*'                   => 'exists:servicios,id',
            'trabajadores_por_servicio'     => 'required|array',
            'trabajadores_por_servicio.*'   => 'integer|exists:usuarios,id',
            'fecha'                         => 'required|date', //'required|date|after_or_equal:today',
            'hora'                          => 'required',
        ]);

        // Servicios seleccionados y monto total
        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $montoTotal = $servicios->sum('precio');

        // Guardamos el arreglo original que envía el formulario
        $trabajadoresArray = $request->trabajadores_por_servicio;

        // Si el cliente seleccionó una alternativa sugerida por el sistema anti-choques
        if ($request->filled('trabajador_id_alternativo')) {
            $firstKey = array_key_first($trabajadoresArray);
            $trabajadoresArray[$firstKey] = $request->trabajador_id_alternativo;
        }

        // Extraemos los IDs principales para la validación de solapamiento
        $primerEspecialistaId = reset($trabajadoresArray);
        $primerServicioId = $servicios->first()->id;

        // 2. SISTEMA ANTI-CHOQUES (OVERLAPPING)
        $citaOcupada = Cita::where('fecha', $request->fecha)
            ->where('hora', $request->hora)
            ->where('trabajador_id', $primerEspecialistaId)
            ->whereIn('estado', ['pendiente', 'confirmada', 'revision'])
            ->first();

        if ($citaOcupada) {
            // Si el horario está ocupado, buscamos alternativas de terapeutas libres
            $terapeutasAlternativos = User::where('rol', 'trabajador')
                ->where('id', '!=', $primerEspecialistaId)
                ->whereHas('servicios', function($query) use ($primerServicioId) {
                    $query->where('servicio_id', $primerServicioId);
                })
                ->whereDoesntHave('citas', function($query) use ($request) {
                    $query->where('fecha', $request->fecha)
                          ->where('hora', $request->hora)
                          ->whereIn('estado', ['pendiente', 'confirmada', 'revision']);
                })
                ->get();

            // Abortamos y mostramos las alternativas en la vista sin borrar datos
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

        // Guardar TODOS los servicios seleccionados en la tabla intermedia (cita_servicio)
        foreach ($trabajadoresArray as $servicioId => $especialistaId) {
            $cita->servicios()->attach($servicioId, [
                'trabajador_id' => $especialistaId
            ]);
        }

        // Redirigir a la pasarela de pago (checkout)
        return redirect()
            ->route('checkout.view', ['cita_id' => $cita->id])
            ->with('success', 'Cita apartada. Asignamos a tus especialistas seleccionados.');
    }

    /**
     * Mostrar las reservas y pagos del cliente autenticado.
     */
    public function misReservas()
    {
        // Agregamos 'servicios' para poder leer los especialistas de la tabla pivote
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