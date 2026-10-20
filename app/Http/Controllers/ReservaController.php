<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Cita;
use App\Models\User;
use App\Models\Servicio;

class ReservaController extends Controller
{
    // Muestra el formulario de reserva para el cliente.
    public function index(Request $request)
    {
        $servicioSeleccionado = $request->query('servicio_id');
        return view('clientes.reserva', compact('servicioSeleccionado'));
    }

    // Maneja la creación de una nueva cita.
    public function store(Request $request)
    {
        $request->validate([
            'servicio_id'    => 'required|integer',
            'trabajador_id'  => 'required',
            'fecha'          => 'required|date|after_or_equal:today',
            'hora'           => 'required',
            'adjunto_receta' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        $trabajadorId = $request->trabajador_id;
        if ($trabajadorId === 'aleatorio') {
            $trabajadorId = User::where('rol', 'trabajador')->inRandomOrder()->first()->id;
        }

        $rutaReceta = null;
        if ($request->hasFile('adjunto_receta')) {
            $rutaReceta = $request->file('adjunto_receta')->store('recetas', 'public');
        }

        $servicio = Servicio::findOrFail($request->servicio_id);

        $cita = Cita::create([
            'cliente_id'    => Auth::id(),
            'trabajador_id' => $trabajadorId,
            'servicio_id'   => $request->servicio_id,
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'monto_total'   => $servicio->precio,
            'estado'        => 'pendiente',
        ]);

        return redirect()->route('checkout.view', ['cita_id' => $cita->id])
                        ->with('success', '¡Su sesión ha sido agendada! Por favor, procese su pago para confirmarla.');
    }

    // Mostrar las reservas y pagos del cliente autenticado
    public function misReservas()
    {
        $citas = Cita::with(['servicio', 'trabajador', 'pagos'])
                    ->where('cliente_id', Auth::id())
                    ->orderBy('fecha', 'DESC')
                    ->get();

        return view('clientes.mis-reservas', compact('citas'));
    }

    public function getEspecialistas($id)
    {
        $servicio = Servicio::find($id);
        if (!$servicio) {
            return response()->json([]);
        }
        return response()->json($servicio->especialistas);
    }

}   