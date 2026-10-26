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
        // Validación
        $request->validate([
            'servicios'                     => 'required|array|min:1',
            'servicios.*'                   => 'exists:servicios,id',
            'trabajadores_por_servicio'     => 'required|array',
            'trabajadores_por_servicio.*'   => 'integer|exists:usuarios,id',
            'fecha'                         => 'required|date|after_or_equal:today',
            'hora'                          => 'required',
        ]);

        // Servicios seleccionados y monto total
        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $montoTotal = $servicios->sum('precio');

        // Guardamos el arreglo en una variable antes de usar reset() para evitar el error de PHP
        $trabajadoresArray = $request->trabajadores_por_servicio;
        $primerEspecialistaId = reset($trabajadoresArray);

        // Crear la cita principal
        $cita = Cita::create([
            'cliente_id'    => auth()->id(),
            'trabajador_id' => $primerEspecialistaId, 
            'servicio_id'   => $servicios->first()->id,
            'cabina_id'     => null,
            'monto_total'   => $montoTotal,
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'estado'        => 'pendiente',
        ]);

        // Guardar TODOS los servicios en la tabla intermedia (cita_servicio)
        foreach ($trabajadoresArray as $servicioId => $especialistaId) {
            $cita->servicios()->attach($servicioId, [
                'trabajador_id' => $especialistaId
            ]);
        }

        // Redirigir al checkout de pago
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
     * Obtener especialistas disponibles para un servicio.
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