<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ServicioController extends Controller
{
    /**
     * Muestra el catálogo público con servicios y especialistas.
     */
    public function index()
    {
        $servicios = Servicio::orderBy('id', 'desc')->get();

        $especialistas = User::whereIn('rol', ['trabajador', 'especialista', 'terapeuta'])
            ->orderBy('nombre')
            ->get();

        return view('compartidas.catalogo', compact('servicios', 'especialistas'));
    }

    /**
     * Vista de administración del catálogo (formulario admin).
     */
    public function create()
    {
        $servicios = Servicio::orderBy('id', 'desc')->get();
        $especialistas = User::whereIn('rol', ['trabajador', 'especialista', 'terapeuta'])
            ->orderBy('nombre')
            ->get();

        return view('admin.servicios', compact('servicios', 'especialistas'));
    }

    /**
     * Guarda un nuevo servicio, incluyendo la imagen.
     */
    public function store(Request $request)
    {
        // ✅ CORREGIDO: Cambiado 'users' por 'usuarios'
        $datos = $request->validate([
            'nombre_servicio'     => 'required|string|min:3|max:255',
            'descripcion'         => 'nullable|string',
            'precio'              => 'required|numeric|min:0',
            'duracion_minutos'    => 'required|integer|min:1',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'especialistas'       => 'nullable|array',
            'especialistas.*'     => 'exists:usuarios,id', // ⚠️ AQUÍ ESTABA EL ERROR
            'porcentaje_agendado' => 'nullable|integer|min:0|max:100',
        ]);

        // Guardar imagen en storage/app/public/servicios/
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('servicios', 'public');
        }

        // ⚠️ IMPORTANTE: Quitamos 'especialistas' del array para que no intente guardarlo en la tabla servicios
        unset($datos['especialistas']);

        // Crear el servicio con todos los campos validados
        Servicio::create($datos);

        // Si estás usando AJAX, esta redirección podría necesitar ser un response()->json()
        // Pero como el error era 500, esta redirección funcionará si el form es tradicional.
        return redirect()
            ->back()
            ->with('success', '¡Tratamiento registrado exitosamente en The Beauty Room!');
    }

    /**
     * Actualiza un servicio existente (y reemplaza la imagen si suben una nueva).
     */
    public function update(Request $request, $id)
    {
        $servicio = Servicio::findOrFail($id);

        // ✅ CORREGIDO: Cambiado 'users' por 'usuarios'
        $datos = $request->validate([
            'nombre_servicio'     => 'required|string|min:3|max:255',
            'descripcion'         => 'nullable|string',
            'precio'              => 'required|numeric|min:0',
            'duracion_minutos'    => 'required|integer|min:1',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'especialistas'       => 'nullable|array',
            'especialistas.*'     => 'exists:usuarios,id', // ⚠️ AQUÍ TAMBIÉN
            'porcentaje_agendado' => 'nullable|integer|min:0|max:100',
        ]);

        // Si suben nueva imagen: borrar la anterior y guardar la nueva
        if ($request->hasFile('imagen')) {
            if ($servicio->imagen && Storage::disk('public')->exists($servicio->imagen)) {
                Storage::disk('public')->delete($servicio->imagen);
            }
            $datos['imagen'] = $request->file('imagen')->store('servicios', 'public');
        }

        // ⚠️ IMPORTANTE: Quitamos 'especialistas' para que no de error de SQL
        unset($datos['especialistas']);

        $servicio->update($datos);

        return redirect()
            ->back()
            ->with('success', '¡Tratamiento actualizado exitosamente!');
    }

    /**
     * Elimina un servicio y su imagen asociada.
     */
    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);

        if ($servicio->imagen && Storage::disk('public')->exists($servicio->imagen)) {
            Storage::disk('public')->delete($servicio->imagen);
        }

        $servicio->delete();

        return redirect()
            ->back()
            ->with('success', '¡Servicio eliminado correctamente!');
    }
}