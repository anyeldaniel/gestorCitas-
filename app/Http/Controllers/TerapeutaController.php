<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Resena;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class TerapeutaController extends Controller
{
    /**
     * Vista pública de terapeutas con promedio de reseñas y especialidades dinámicas.
     * GET /terapeutas
     */
    public function index()
    {
        // 1. Traemos a los trabajadores con sus reseñas
        $terapeutas = User::where('rol', 'trabajador')
            ->with('resenasRecibidas')
            ->get()
            ->map(function ($terapeuta) {
                // 2. Calcular promedio y total de reseñas
                $terapeuta->promedio = round($terapeuta->resenasRecibidas->avg('puntuacion') ?? 0, 1);
                $terapeuta->total_resenas = $terapeuta->resenasRecibidas->count();

                // 3. 🆕 BUSCAR LAS ESPECIALIDADES REALES EN LA BD
                // Hacemos el cruce de tablas igual que en AdminController
                $listaEspecialidades = DB::table('especialidades')
                    ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                    ->where('trabajador_especialidad.usuario_id', $terapeuta->id)
                    ->pluck('especialidades.nombre_especialidad')
                    ->toArray();

                // 4. Asignamos el string separado por comas al objeto (ej: "Masajista, Facial")
                $terapeuta->especialidades = implode(', ', $listaEspecialidades);

                return $terapeuta;
            });

        $userRole = auth()->check() ? auth()->user()->rol : null;

        return view('compartidas.terapeutas', compact('terapeutas', 'userRole'));
    }

    /**
     * Registrar un nuevo especialista (admin).
     * POST /admin/crear-trabajador
     */
    public function store(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'email'          => 'required|email|unique:usuarios,correo',
            'telefono'       => 'nullable|string|max:20',
            'descripcion'    => 'nullable|string',
            'especialidades' => 'nullable|string',
            'password'       => 'required|string|min:8|confirmed',
            'foto'           => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('terapeutas', 'public');
        }

        User::create([
            'nombre'      => $request->nombre,
            'correo'      => $request->email, // se guarda en la columna "correo"
            'telefono'    => $request->telefono,
            'descripcion' => $request->descripcion,
            'contraseña'  => Hash::make($request->password),
            'rol'         => 'trabajador',
            'foto'        => $fotoPath,
        ]);

        return redirect()->back()->with('success', 'Especialista registrado correctamente.');
    }

    /**
     * Actualizar un especialista (admin).
     * PUT /admin/trabajador/{id}
     */
    public function update(Request $request, $id)
    {
        $terapeuta = User::findOrFail($id);

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('usuarios', 'correo')->ignore($terapeuta->id)],
            'telefono'       => 'nullable|string|max:20',
            'descripcion'    => 'nullable|string',
            'especialidades' => 'nullable|string',
            'password'       => 'nullable|string|min:8|confirmed',
            'foto'           => 'nullable|image|max:2048',
        ]);

        $data = [
            'nombre'      => $request->nombre,
            'correo'      => $request->email,
            'telefono'    => $request->telefono,
            'descripcion' => $request->descripcion,
        ];

        if ($request->filled('password')) {
            $data['contraseña'] = Hash::make($request->password);
        }

        if ($request->hasFile('foto')) {
            if ($terapeuta->foto && Storage::disk('public')->exists($terapeuta->foto)) {
                Storage::disk('public')->delete($terapeuta->foto);
            }
            $data['foto'] = $request->file('foto')->store('terapeutas', 'public');
        }

        $terapeuta->update($data);

        return redirect()->back()->with('success', 'Especialista actualizado correctamente.');
    }

    /**
     * Eliminar un especialista (admin).
     * DELETE /admin/usuario/{id}
     */
    public function destroy($id)
    {
        $terapeuta = User::findOrFail($id);

        if ($terapeuta->foto && Storage::disk('public')->exists($terapeuta->foto)) {
            Storage::disk('public')->delete($terapeuta->foto);
        }

        $terapeuta->delete();

        return redirect()->back()->with('success', 'Especialista eliminado correctamente.');
    }
}