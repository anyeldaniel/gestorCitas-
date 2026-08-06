<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth; // Importamos la clase Auth para manejar la autenticación de usuarios.
use App\Models\Cita; // Importamos el modelo de Cita.
use App\Models\User; // Importamos el modelo de User para los especialistas.


class ReservaController extends Controller
{
    // Muestro el formulario de reserva para el cliente.
    public function index(Request $request)
    {
        // Atrapamos el ID si viene por la URL (si no viene, será null)
        $servicioSeleccionado = $request->query('servicio_id');

        return view('clientes.reserva', compact('servicioSeleccionado'));
    }
    // Función para manejar la creación de una nueva cita.
    public function store(Request $request)
    {
        // Validación de los datos recibidos del formulario.
        $request->validate([
            'servicio_id'    => 'required|integer',
            'trabajador_id'  => 'required', // Recibe 'aleatorio' o el ID numérico.
            'fecha'          => 'required|date|after_or_equal:today',
            'hora'           => 'required',
            'adjunto_receta' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:2048',
        ]);

        // Lógica de asignación de especialista (Aleatorio o Específico).
        $trabajadorId = $request->trabajador_id;
        if ($trabajadorId === 'aleatorio') {
            // Si se selecciona 'aleatorio', se obtiene un especialista al azar de la base de datos.
            $trabajadorId = User::where('rol', 'trabajador')->inRandomOrder()->first()->id;
        }

        // Manejo del archivo adjunto de receta si existe.
        $rutaReceta = null;
        if ($request->hasFile('adjunto_receta')) {
            $rutaReceta = $request->file('adjunto_receta')->store('recetas', 'public');
        }
        // Creación de la cita en la base de datos.
        Cita::create([
            'cliente_id'    => Auth::id(), // Guarda el ID del cliente logueado
            'trabajador_id' => $trabajadorId,
            'servicio_id'   => $request->servicio_id,
            'fecha'         => $request->fecha,
            'hora'          => $request->hora,
            'estado'        => 'pendiente',
        ]);

        // Redireccionar con éxito.
        return redirect()->route('catalogo')->with('success', '¡Su sesión ha sido agendada con éxito en nuestro santuario de bienestar!');
    }

    // Obtener especialistas filtrados por AJAX
    public function getEspecialistas($id)
    {
        // Buscamos el servicio
        $servicio = \App\Models\Servicio::find($id);

        // Si el servicio no existe, devolvemos un array vacío
        if (!$servicio) {
            return response()->json([]);
        }

        // ¡Aquí está la magia! Laravel busca automáticamente en la tabla pivote
        // usando la relación que definimos en el Modelo Servicio.
        return response()->json($servicio->especialistas);
    }
}
