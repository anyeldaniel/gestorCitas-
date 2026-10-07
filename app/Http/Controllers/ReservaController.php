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

        // Solo ofrecer usuarios registrados con el rol de trabajador.
        $terapeutas = User::where('id', '!=', auth()->id())
            ->where('rol', 'trabajador')
            ->orderBy('nombre')
            ->get();

        return view('clientes.reserva', compact('servicioData', 'terapeutas'));
    }

    /**
     * Guarda la reserva y redirige al checkout de pago.
     */
    public function store(Request $request)
    {
        // 1. Validación
        $request->validate([
            'servicio_id'   => 'required|integer|exists:servicios,id',
            'trabajador_id' => [
                'required',
                'integer',
                Rule::exists('usuarios', 'id')->where('rol', 'trabajador'),
            ],
            'fecha'         => 'required|date|after_or_equal:today',
            'hora'          => 'required',
        ]);

        // 2. Obtener el servicio
        $servicio = Servicio::findOrFail($request->servicio_id);

        // 3. Crear la cita
        $cita = Cita::create([
            'cliente_id'    => auth()->id(),
            'trabajador_id' => $request->trabajador_id,
            'servicio_id'   => $servicio->id,
            'cabina_id'     => null,
            'monto_total'   => $servicio->precio,
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'estado'        => 'pendiente',
        ]);

        // 4. Redirigir al CHECKOUT DE PAGO
        return redirect()
            ->route('pago.checkout', ['cita' => $cita->id])
            ->with('success', 'Cita apartada. Reporta tu pago para confirmarla.');
    }
}