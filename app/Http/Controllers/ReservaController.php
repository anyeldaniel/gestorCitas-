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

        // --- NUEVO: Obtener servicios agrupados por especialidad ---
        $serviciosAgrupados = Servicio::with('especialidad')
            ->get()
            ->groupBy(function($item) {
                return $item->especialidad ? $item->especialidad->nombre_especialidad : 'Otros Servicios';
            });

        // Solo ofrecer usuarios registrados con el rol de trabajador.
        $terapeutas = User::where('id', '!=', auth()->id())
            ->where('rol', 'trabajador')
            ->orderBy('nombre')
            ->get();

        return view('clientes.reserva', compact('servicioData', 'terapeutas', 'serviciosAgrupados'));
    }

        public function store(Request $request)
    {
        // 1. Validación (servicios ahora es un array)
        $request->validate([
            'servicios'     => 'required|array|min:1',
            'servicios.*'   => 'exists:servicios,id',
            'trabajador_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id')->where('rol', 'trabajador'),
            ],
            'fecha'         => 'required|date|after_or_equal:today',
            'hora'          => 'required',
        ]);

        // 2. Obtener los servicios seleccionados y calcular el total
        $servicios = Servicio::whereIn('id', $request->servicios)->get();
        $montoTotal = $servicios->sum('precio');

        // 3. Crear la cita principal
        $cita = Cita::create([
            'cliente_id'    => auth()->id(),
            'trabajador_id' => $request->trabajador_id,
            'servicio_id'   => $servicios->first()->id, // Guardamos el primero por compatibilidad con tu tabla
            'cabina_id'     => null,
            'monto_total'   => $montoTotal, // El precio total de todos los servicios
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'estado'        => 'pendiente',
        ]);

        // 4. Guardar TODOS los servicios en la nueva tabla intermedia
        $cita->servicios()->attach($request->servicios);

        // 5. Redirigir al CHECKOUT DE PAGO
        return redirect()
            ->route('pago.checkout', ['cita' => $cita->id])
            ->with('success', 'Cita apartada con múltiples servicios. Reporta tu pago para confirmarla.');
    }
}