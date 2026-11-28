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
    // Mostrar la lista de terapeutas con sus reseñas y especialidades
    public function index()
    {
        $terapeutas = User::where('rol', 'trabajador')
            ->with(['resenasRecibidas' => function ($query) {
                $query->with('cliente')->latest(); 
            }])
            ->get()
            ->map(function ($terapeuta) {
                
                $terapeuta->promedio = round($terapeuta->resenasRecibidas->avg('puntuacion') ?? 0, 1);
                $terapeuta->total_resenas = $terapeuta->resenasRecibidas->count();

                $listaEspecialidades = DB::table('especialidades')
                    ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                    ->where('trabajador_especialidad.usuario_id', $terapeuta->id)
                    ->pluck('especialidades.nombre_especialidad')
                    ->toArray();

                $terapeuta->especialidades = implode(', ', $listaEspecialidades);

                return $terapeuta;
            });

        $userRole = auth()->check() ? auth()->user()->rol : null;

        return view('compartidas.terapeutas', compact('terapeutas', 'userRole'));
    }

    // registrar especilista, esto solo lo hace el admin
    public function store(Request $request)
    {
        $request->validate([
            'nombre'         => 'required|string|max:255',
            'email'          => 'required|email|unique:usuarios,correo',
            'telefono'       => 'nullable|string|max:20',
            'descripcion'    => 'nullable|string',
            'especialidades' => 'nullable|string',
            'password'       => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'confirmed',
            ],
            'foto'           => 'nullable|image|max:2048',
        ]);

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('terapeutas', 'public');
        }

        User::create([
            'nombre'      => $request->nombre,
            'correo'      => $request->email, 
            'telefono'    => $request->telefono,
            'descripcion' => $request->descripcion,
            'contraseña'  => Hash::make($request->password),
            'rol'         => 'trabajador',
            'foto'        => $fotoPath,
        ]);

        return redirect()->back()->with('success', 'Especialista registrado correctamente.');
    }

    public function update(Request $request, $id)
    {
        $terapeuta = User::findOrFail($id);

        $request->validate([
            'nombre'         => 'required|string|max:255',
            'email'          => ['required', 'email', Rule::unique('usuarios', 'correo')->ignore($terapeuta->id)],
            'telefono'       => 'nullable|string|max:20',
            'descripcion'    => 'nullable|string',
            'especialidades' => 'nullable|string',
            'password'       => [
                'nullable',
                'string',
                'min:8',
                'max:20',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'confirmed',
            ],
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

    // eliminar especilaista
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