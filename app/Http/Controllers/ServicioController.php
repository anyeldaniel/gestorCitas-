<?php

namespace App\Http\Controllers;

use App\Models\Servicio;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class ServicioController extends Controller
{
    public function index()
    {
        // ⚠️ SI TU index() ORIGINAL TENÍA OTRO CUERPO, CONSÉRVALO.
        // Este es un placeholder que respeta la firma del diff.
        $servicios = Servicio::with('especialistas')->orderBy('id', 'desc')->get();
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
     * Guarda un nuevo servicio, incluyendo la imagen y especialistas.
     */
    public function store(Request $request)
    {
        $datos = $request->validate([
            'nombre_servicio'     => 'required|string|min:3|max:255',
            'descripcion'         => 'nullable|string',
            'precio'              => 'required|numeric|min:0',
            'duracion_minutos'    => 'required|integer|min:1',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'especialistas'       => 'nullable|array',
            'especialistas.*'     => 'exists:usuarios,id',
            'porcentaje_agendado' => 'nullable|integer|min:0|max:100',
        ]);

        // Guardar imagen en storage/app/public/servicios/
        if ($request->hasFile('imagen')) {
            $datos['imagen'] = $request->file('imagen')->store('servicios', 'public');
        }

        // Extraer especialistas antes de crear el servicio
        $especialistas = $datos['especialistas'] ?? [];
        unset($datos['especialistas']);

        // Remapear porcentaje_agendado → porcentaje_abono (columna real en BD)
        if (isset($datos['porcentaje_agendado'])) {
            $datos['porcentaje_abono'] = $datos['porcentaje_agendado'];
            unset($datos['porcentaje_agendado']);
        }

        $servicio = Servicio::create($datos);

        // Sincronizar especialistas en la tabla pivote
        if (!empty($especialistas)) {
            $servicio->especialistas()->sync($especialistas);
        }

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

        $datos = $request->validate([
            'nombre_servicio'     => 'required|string|min:3|max:255',
            'descripcion'         => 'nullable|string',
            'precio'              => 'required|numeric|min:0',
            'duracion_minutos'    => 'required|integer|min:1',
            'imagen'              => 'nullable|image|mimes:jpeg,png,jpg,webp|max:4096',
            'especialistas'       => 'nullable|array',
            'especialistas.*'     => 'exists:usuarios,id',
            'porcentaje_agendado' => 'nullable|integer|min:0|max:100',
        ]);

        // Imagen: si suben nueva, borrar la anterior
        if ($request->hasFile('imagen')) {
            if ($servicio->imagen && Storage::disk('public')->exists($servicio->imagen)) {
                Storage::disk('public')->delete($servicio->imagen);
            }
            $datos['imagen'] = $request->file('imagen')->store('servicios', 'public');
        }

        // Extraer especialistas (null = no venía en el form → detach)
        $especialistas = $datos['especialistas'] ?? null;
        unset($datos['especialistas']);

        // Remapear porcentaje_agendado → porcentaje_abono
        if (isset($datos['porcentaje_agendado'])) {
            $datos['porcentaje_abono'] = $datos['porcentaje_agendado'];
            unset($datos['porcentaje_agendado']);
        }

        $servicio->update($datos);

        // Sincronizar especialistas
        if ($especialistas !== null) {
            $servicio->especialistas()->sync($especialistas);
        } else {
            $servicio->especialistas()->detach();
        }

        return redirect()
            ->back()
            ->with('success', '¡Tratamiento actualizado exitosamente!');
    }

    /**
     * Elimina un servicio, sus citas asociadas y su imagen.
     */
    public function destroy($id)
    {
        $servicio = Servicio::findOrFail($id);

        // Borrar citas asociadas (necesario si hay FK constraint sin cascade)
        DB::table('citas')->where('servicio_id', $id)->delete();

        // Borrar imagen física si existe
        if ($servicio->imagen && Storage::disk('public')->exists($servicio->imagen)) {
            Storage::disk('public')->delete($servicio->imagen);
        }

        $servicio->delete();

        return redirect()
            ->back()
            ->with('success', '¡Servicio eliminado correctamente!');
    }
}