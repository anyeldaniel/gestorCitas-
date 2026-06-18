<?php

namespace App\Http\Controllers;

use App\Models\User; // Importar el modelo User para crear especialistas y recepcionistas.
use App\Http\Requests\CreateTrabajadorRequest; // Importar el Form Request para validar la creación de trabajadores..
use Illuminate\Http\Request; // Agregado para validar al recepcionista.
use Illuminate\Support\Facades\DB; // Importar DB para consultas directas si es necesario.

class AdminController extends Controller
{

    // Añadí este método. Carga los datos dinámicos de especialistas y recepcionistas en el panel administrativo, además de los KPIs reales (aunque por ahora con datos de prueba hasta que conectemos con los modelos reales).
    public function dashboard()
    {
        // Traemos los usuarios reales de la base de datos filtrados por su respectivo rol
        $trabajadores = User::where('rol', 'trabajador')->get();
        $recepcionistas = User::where('rol', 'recepcion')->get();

        //Variables dinámicas para los KPIs del Dashboard (reemplazan los datos de prueba estáticos) 
        // NOTA: podemos sustituir estos ceros por conteos de los modelos reales en el futuro (ej. Cita::count()) para mostrar datos reales en el dashboard. OJO ANYEL Y WLADIMIR, LEAN ESTO POR FAVOR, ES IMPORTANTE PARA QUE EL DASHBOARD NO SE VEA VACÍO Y LOS BOTONES DE ADMINISTRACIÓN NO SE VEAN SIN SENTIDO.
        $totalCitas = 0;
        $ingresosEstimados = 0.00;

        // Enviamos las colecciones a la vista del dashboard administrativo incluyendo los nuevos KPIs reales
        return view('admin.dashboard', compact('trabajadores', 'recepcionistas', 'totalCitas', 'ingresosEstimados'));
    }

    public function CreateTrabajador(CreateTrabajadorRequest $request)
    {
        $validatedData = $request->validated();

        // NOTA TÉCNICA: Si el Form Request falla diciendo que falta "contraseña", 
        // asegúrate de que en 'CreateTrabajadorRequest' la regla use 'password' y no 'contraseña'.
        $trabajador = User::create([
            'nombre'     => $validatedData['nombre'] ?? $validatedData['name'],
            'correo'     => $validatedData['email'] ?? $validatedData['correo'],
            'telefono'   => $validatedData['telefono'] ?? null,
            // Quitamos el Hash::make porque Anyel ya lo configuró directo en el modelo
            'contraseña' => $validatedData['password'] ?? $validatedData['contraseña'],
            'rol'        => 'trabajador' // CAMBIO: Usamos 'trabajador' porque es lo único que la BD acepta actualmente
        ]);

        // Verificamos si el formulario envió datos en el campo "especialidades"
        if ($request->filled('especialidades')) {
            // Separamos el texto que viene junto (ej: "Limpieza, Masaje") por las comas
            $listaEspecialidades = explode(',', $request->especialidades);

            foreach ($listaEspecialidades as $nombreEspecialidad) {
                // Limpiamos espacios en blanco al principio o al final
                $nombreLimpio = trim($nombreEspecialidad); 
                
                if (!empty($nombreLimpio)) {
                    // Buscamos si la especialidad ya existe en la tabla 'especialidades'
                    $especialidad = DB::table('especialidades')->where('nombre_especialidad', $nombreLimpio)->first();

                    if (!$especialidad) {
                        // Si NO existe, la guardamos nueva y obtenemos su ID recién creado
                        $especialidadId = DB::table('especialidades')->insertGetId([
                            'nombre_especialidad' => $nombreLimpio
                        ]);
                    } else {
                        // Si YA existe, simplemente agarramos el ID que ya tiene
                        $especialidadId = $especialidad->id;
                    }

                    // Guardamos la conexión Terapeuta-Especialidad en la tabla pivote
                    DB::table('trabajador_especialidad')->insert([
                        'usuario_id'         => $trabajador->id, // El ID del terapeuta que acabamos de crear arriba
                        'especialidad_id' => $especialidadId  // El ID de la especialidad (nueva o vieja)
                    ]);
                }
            }
        }

        return redirect()->route('admin.dashboard')->with('success', 'Especialista registrado exitosamente.');
    }

    public function CreateRecepcionista(Request $request)
    {
        // AJUSTE DE VALIDACIÓN: Corregido para que valide directo en la tabla 'users' y columna 'correo'
        $validatedData = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,correo',
            'telefono' => 'required|string',
            'password' => 'required|min:8',
        ]);

        // Guardamos en la base de datos mapeando los campos del formulario con las columnas correspondientes
        $recepcionista = User::create([
            'nombre'     => $validatedData['nombre'],
            'correo'     => $validatedData['email'],
            'telefono'   => $validatedData['telefono'],
            'contraseña' => $validatedData['password'], // Se envía como password y el modelo lo procesa
            'rol'        => 'recepcion' // Asignación automática del rol
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Recepcionista registrado exitosamente.');
    }

    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }
}
