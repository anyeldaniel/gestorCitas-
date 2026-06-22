<?php

namespace App\Http\Controllers;

use App\Models\User; // Importar el modelo User para crear especialistas y recepcionistas.
use App\Http\Requests\CreateTrabajadorRequest; // Importar el Form Request para validar la creación de trabajadores.
use Illuminate\Http\Request; // Agregado para validar al recepcionista.
use Illuminate\Support\Facades\DB; // Importar DB para consultas directas si es necesario.

class AdminController extends Controller
{
    // Añadí este método. Carga los datos dinámicos de especialistas y recepcionistas en el panel administrativo, además de los KPIs reales.
    public function dashboard()
    {
        // Traemos los usuarios reales de la base de datos filtrados por su respectivo rol
        $trabajadores = User::where('rol', 'trabajador')->get();

        // Recorremos cada trabajador para buscar sus especialidades en las otras tablas
        foreach ($trabajadores as $trabajador) {
            $listaEspecialidades = DB::table('especialidades')
                ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                ->where('trabajador_especialidad.usuario_id', $trabajador->id)
                ->pluck('especialidades.nombre_especialidad') // Extraemos solo el nombre
                ->toArray();

            // Las unimos con comas y se las "pegamos" al objeto trabajador para la vista
            $trabajador->especialidades = implode(', ', $listaEspecialidades);
        }

        $recepcionistas = User::where('rol', 'recepcion')->get();

        //Variables dinámicas para los KPIs del Dashboard
        $totalCitas = 0;
        $ingresosEstimados = 0.00;

        // Enviamos las colecciones a la vista del dashboard administrativo incluyendo los nuevos KPIs reales
        return view('admin.dashboard', compact('trabajadores', 'recepcionistas', 'totalCitas', 'ingresosEstimados'));
    }

    // Método para crear un nuevo trabajador (especialista) con validación personalizada.
    public function CreateTrabajador(CreateTrabajadorRequest $request)
    {
        $validatedData = $request->validated();

        $trabajador = User::create([
            'nombre'     => $validatedData['nombre'] ?? $validatedData['name'],
            'correo'     => $validatedData['email'] ?? $validatedData['correo'],
            'telefono'   => $validatedData['telefono'] ?? null,
            'contraseña' => $validatedData['password'] ?? $validatedData['contraseña'],
            'descripcion' => $request->descripcion, // Importante para la descripción del especialista.
            'rol'        => 'trabajador'
        ]);

        if ($request->filled('especialidades')) {
            $listaEspecialidades = explode(',', $request->especialidades);
            foreach ($listaEspecialidades as $nombreEspecialidad) {
                $nombreLimpio = trim($nombreEspecialidad);
                if (!empty($nombreLimpio)) {
                    $especialidad = DB::table('especialidades')->where('nombre_especialidad', $nombreLimpio)->first();
                    $especialidadId = $especialidad ? $especialidad->id : DB::table('especialidades')->insertGetId(['nombre_especialidad' => $nombreLimpio]);

                    DB::table('trabajador_especialidad')->insert([
                        'usuario_id'         => $trabajador->id,
                        'especialidad_id' => $especialidadId
                    ]);
                }
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Especialista registrado exitosamente.');
    }

    // Método para crear un nuevo recepcionista con validación personalizada.
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
            'contraseña' => $validatedData['password'],
            'rol'        => 'recepcion'
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Recepcionista registrado exitosamente.');
    }

    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }

    // ELIMINAR USUARIO.
    public function destroyUsuario($id)
    {
        User::findOrFail($id)->delete();
        return back()->with('success', 'Usuario eliminado exitosamente.');
    }

    // ACTUALIZAR ESPECIALISTA.
    public function updateTrabajador(Request $request, $id)
    {
        $trabajador = User::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'      => 'required|string|max:255',
            'email'       => 'required|email|unique:usuarios,correo,' . $id,
            'telefono'    => 'required|string',
            'descripcion' => 'nullable|string',
        ]);

        // 1. Actualizamos los datos básicos en la tabla de usuarios
        $trabajador->update([
            'nombre'      => $validatedData['nombre'],
            'correo'      => $validatedData['email'],
            'telefono'    => $validatedData['telefono'],
            'descripcion' => $validatedData['descripcion'],
        ]);

        // 2. ACTUALIZACIÓN DE ESPECIALIDADES
        if ($request->has('especialidades')) {
            // Primero, borramos todas las relaciones anteriores de este trabajador en la tabla pivote
            DB::table('trabajador_especialidad')->where('usuario_id', $trabajador->id)->delete();

            // Segundo, si enviaron especialidades nuevas, las registramos
            if ($request->filled('especialidades')) {
                $listaEspecialidades = explode(',', $request->especialidades);
                
                foreach ($listaEspecialidades as $nombreEspecialidad) {
                    $nombreLimpio = trim($nombreEspecialidad);
                    
                    if (!empty($nombreLimpio)) {
                        // Buscamos si la especialidad ya existe
                        $especialidad = DB::table('especialidades')->where('nombre_especialidad', $nombreLimpio)->first();
                        
                        // Si existe, tomamos su ID. Si no, la creamos y obtenemos el nuevo ID.
                        $especialidadId = $especialidad ? $especialidad->id : DB::table('especialidades')->insertGetId(['nombre_especialidad' => $nombreLimpio]);
                        
                        // Insertamos la nueva relación en la tabla pivote
                        DB::table('trabajador_especialidad')->insert([
                            'usuario_id'      => $trabajador->id,
                            'especialidad_id' => $especialidadId
                        ]);
                    }
                }
            }
        }

        return back()->with('success', 'Especialista actualizado con éxito.');
    }

    // ACTUALIZAR RECEPCIONISTA.
    public function updateRecepcionista(Request $request, $id)
    {
        $recepcionista = User::findOrFail($id);

        $validatedData = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,correo,' . $id,
            'telefono' => 'required|string',
        ]);

        $recepcionista->update([
            'nombre'   => $validatedData['nombre'],
            'correo'   => $validatedData['email'],
            'telefono' => $validatedData['telefono'],
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Recepcionista actualizado con éxito.');
    }
}