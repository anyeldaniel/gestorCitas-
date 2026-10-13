<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class AdminController extends Controller
{
    // ==========================================
    //   DASHBOARD
    // ==========================================
    public function dashboard()
    {
        $trabajadores = User::where('rol', 'trabajador')->get();

        foreach ($trabajadores as $trabajador) {
            $listaEspecialidades = DB::table('especialidades')
                ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                ->where('trabajador_especialidad.usuario_id', $trabajador->id)
                ->pluck('especialidades.nombre_especialidad')
                ->toArray();

            $trabajador->especialidades = implode(', ', $listaEspecialidades);
        }

        $recepcionistas = User::where('rol', 'recepcion')->get();

        $totalCitas = 0;
        $ingresosEstimados = 0.00;

        return view('admin.dashboard', compact('trabajadores', 'recepcionistas', 'totalCitas', 'ingresosEstimados'));
    }

    // ==========================================
    //   CREAR TRABAJADOR (ESPECIALISTA)
    // ==========================================
    public function CreateTrabajador(Request $request)
    {
        // ✅ Validación manual (más confiable)
        $validatedData = $request->validate([
            'nombre'         => 'required|string|min:3|max:100',
            'email'          => 'required|string|email|max:255|unique:usuarios,correo',
            'telefono'       => 'required|string|min:7|max:20',
            'descripcion'    => 'nullable|string|max:500',
            'password'       => 'required|string|min:8|confirmed',
            'especialidades' => 'nullable|string',
            'foto'           => 'nullable|image|max:2048',
        ], [
            'nombre.required'    => 'El nombre es obligatorio.',
            'nombre.min'         => 'El nombre debe tener al menos 3 caracteres.',
            'email.required'     => 'El correo es obligatorio.',
            'email.unique'       => 'Este correo ya está registrado.',
            'telefono.required'  => 'El teléfono es obligatorio.',
            'telefono.min'       => 'El teléfono debe tener al menos 7 caracteres.',
            'password.required'  => 'La contraseña es obligatoria.',
            'password.min'       => 'La contraseña debe tener al menos 8 caracteres.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
        ]);

        // ✅ Encriptar contraseña
        $trabajador = User::create([
            'nombre'      => $validatedData['nombre'],
            'correo'      => $validatedData['email'],
            'telefono'    => $validatedData['telefono'],
            'contraseña'  => Hash::make($validatedData['password']),
            'descripcion' => $validatedData['descripcion'] ?? null,
            'rol'         => 'trabajador',
        ]);

        // ✅ Guardar especialidades
        if ($request->filled('especialidades')) {
            $listaEspecialidades = explode(',', $request->especialidades);
            foreach ($listaEspecialidades as $nombreEspecialidad) {
                $nombreLimpio = trim($nombreEspecialidad);
                if (!empty($nombreLimpio)) {
                    $especialidad = DB::table('especialidades')->where('nombre_especialidad', $nombreLimpio)->first();
                    $especialidadId = $especialidad
                        ? $especialidad->id
                        : DB::table('especialidades')->insertGetId(['nombre_especialidad' => $nombreLimpio]);

                    DB::table('trabajador_especialidad')->insert([
                        'usuario_id'      => $trabajador->id,
                        'especialidad_id' => $especialidadId,
                    ]);
                }
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Especialista registrado exitosamente.');
    }

    // ==========================================
    //   CREAR RECEPCIONISTA
    // ==========================================
    public function CreateRecepcionista(Request $request)
    {
        $validatedData = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,correo',
            'telefono' => 'required|string',
            'password' => 'required|min:8',
        ]);

        User::create([
            'nombre'     => $validatedData['nombre'],
            'correo'     => $validatedData['email'],
            'telefono'   => $validatedData['telefono'],
            'contraseña' => Hash::make($validatedData['password']),
            'rol'        => 'recepcion',
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Recepcionista registrado exitosamente.');
    }

    // ==========================================
    //   SERVICIOS
    // ==========================================
    public function servicios()
    {
        return view('admin.servicios');
    }

    // ==========================================
    //   ELIMINAR USUARIO
    // ==========================================
    public function destroyUsuario($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Usuario eliminado exitosamente.');
    }

    // ==========================================
    //   ACTUALIZAR TRABAJADOR
    // ==========================================
    public function updateTrabajador(Request $request, $id)
    {
        $trabajador = User::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'      => 'required|string|max:255',
            'email'       => 'required|email|unique:usuarios,correo,' . $id,
            'telefono'    => 'required|string',
            'descripcion' => 'nullable|string',
            'password'    => 'nullable|min:8|confirmed',
        ]);

        $datosActualizar = [
            'nombre'      => $validatedData['nombre'],
            'correo'      => $validatedData['email'],
            'telefono'    => $validatedData['telefono'],
            'descripcion' => $validatedData['descripcion'] ?? null,
        ];

        if ($request->filled('password')) {
            $datosActualizar['contraseña'] = Hash::make($validatedData['password']);
        }

        $trabajador->update($datosActualizar);

        // ✅ Actualizar especialidades
        if ($request->has('especialidades')) {
            DB::table('trabajador_especialidad')->where('usuario_id', $trabajador->id)->delete();

            if ($request->filled('especialidades')) {
                $listaEspecialidades = explode(',', $request->especialidades);
                foreach ($listaEspecialidades as $nombreEspecialidad) {
                    $nombreLimpio = trim($nombreEspecialidad);
                    if (!empty($nombreLimpio)) {
                        $especialidad = DB::table('especialidades')->where('nombre_especialidad', $nombreLimpio)->first();
                        $especialidadId = $especialidad
                            ? $especialidad->id
                            : DB::table('especialidades')->insertGetId(['nombre_especialidad' => $nombreLimpio]);

                        DB::table('trabajador_especialidad')->insert([
                            'usuario_id'      => $trabajador->id,
                            'especialidad_id' => $especialidadId,
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Especialista actualizado con éxito.');
    }

    // ==========================================
    //   ACTUALIZAR RECEPCIONISTA
    // ==========================================
    public function updateRecepcionista(Request $request, $id)
    {
        $recepcionista = User::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,correo,' . $id,
            'telefono' => 'required|string',
            'password' => 'nullable|min:8|confirmed',
        ]);

        $datosActualizar = [
            'nombre'   => $validatedData['nombre'],
            'correo'   => $validatedData['email'],
            'telefono' => $validatedData['telefono'],
        ];

        if ($request->filled('password')) {
            $datosActualizar['contraseña'] = Hash::make($validatedData['password']);
        }

        $recepcionista->update($datosActualizar);

        return redirect()->route('admin.dashboard')->with('success', 'Recepcionista actualizado con éxito.');
    }
}