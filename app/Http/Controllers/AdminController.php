<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Cita;    
use App\Models\Servicio; 
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AdminController extends Controller
{
    // ==========================================
    //   DASHBOARD
    // ==========================================
    public function dashboard()
    {
        $trabajadores = User::where('rol', 'trabajador')->get();

        $mesActual  = now()->month;
        $anioActual = now()->year;

        foreach ($trabajadores as $trabajador) {
            // 1. Obtener especialidades
            $listaEspecialidades = DB::table('especialidades')
                ->join('trabajador_especialidad', 'especialidades.id', '=', 'trabajador_especialidad.especialidad_id')
                ->where('trabajador_especialidad.usuario_id', $trabajador->id)
                ->pluck('especialidades.nombre_especialidad')
                ->toArray();

            $trabajador->especialidades = implode(', ', $listaEspecialidades);

            // 2. Contar citas del mes actual (excluyendo canceladas)
            $trabajador->citas_mes = Cita::where('trabajador_id', $trabajador->id)
                ->whereMonth('fecha', $mesActual)
                ->whereYear('fecha', $anioActual)
                ->where('estado', '!=', 'cancelada')
                ->count();
        }

        $recepcionistas = User::where('rol', 'recepcion')->get();

        // Total de citas del mes para la tarjeta de arriba
        $totalCitas = Cita::whereMonth('fecha', $mesActual)
            ->whereYear('fecha', $anioActual)
            ->where('estado', '!=', 'cancelada')
            ->count();

        $ingresosEstimados = 0.00;

        return view('admin.dashboard', compact('trabajadores', 'recepcionistas', 'totalCitas', 'ingresosEstimados'));
    }

    // ==========================================
    //   REPORTES
    // ==========================================
    public function reportes()
    {
        return view('admin.reportes');
    }

    /**
     * Devuelve los datos dinámicos para las gráficas de reportes.
     * - Servicios: TODOS los servicios de la BD con su conteo de citas.
     * - Flujo: citas agrupadas por semanas dentro del rango.
     */
    public function reportesDatos(Request $request)
    {
        $fechaInicio = $request->input('fecha_inicio', now()->startOfMonth()->toDateString());
        $fechaFin    = $request->input('fecha_fin', now()->endOfMonth()->toDateString());

        // Validación básica del rango
        if ($fechaInicio > $fechaFin) {
            [$fechaInicio, $fechaFin] = [$fechaFin, $fechaInicio];
        }

        // 1. TODOS los servicios con su conteo de citas en el rango
        $servicios = Servicio::withCount(['citas' => function ($q) use ($fechaInicio, $fechaFin) {
            $q->whereBetween('fecha', [$fechaInicio, $fechaFin])
              ->where('estado', '!=', 'cancelada');
        }])
            ->orderBy('nombre_servicio')
            ->get();

        // 2. Citas del rango para calcular el flujo semanal
        $citas = Cita::whereBetween('fecha', [$fechaInicio, $fechaFin])
            ->where('estado', '!=', 'cancelada')
            ->get();

        $flujo = $this->agruparPorSemana($citas, $fechaInicio, $fechaFin);

        return response()->json([
            'servicios' => [
                'labels' => $servicios->pluck('nombre_servicio'),
                'data'   => $servicios->pluck('citas_count'),
            ],
            'flujo' => $flujo,
        ]);
    }

    /**
     * Agrupa las citas en bloques de 7 días desde fechaInicio.
     */
    private function agruparPorSemana($citas, $fechaInicio, $fechaFin)
    {
        $inicio = \Carbon\Carbon::parse($fechaInicio);
        $fin    = \Carbon\Carbon::parse($fechaFin);

        $labels     = [];
        $data       = [];
        $numSemana  = 1;

        $cursor = $inicio->copy();
        while ($cursor->lte($fin)) {
            $finBloque = $cursor->copy()->addDays(6);
            if ($finBloque->gt($fin)) {
                $finBloque = $fin->copy();
            }

            $count = $citas->filter(function ($cita) use ($cursor, $finBloque) {
                $f = \Carbon\Carbon::parse($cita->fecha);
                return $f->gte($cursor) && $f->lte($finBloque);
            })->count();

            $labels[] = "Semana {$numSemana}";
            $data[]   = $count;

            $cursor->addDays(7);
            $numSemana++;
        }

        return ['labels' => $labels, 'data' => $data];
    }

    // ==========================================
    //   CREAR TRABAJADOR (ESPECIALISTA)
    // ==========================================
    public function CreateTrabajador(Request $request)
    {
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
            'foto.image'         => 'El archivo debe ser una imagen válida.',
            'foto.max'           => 'La imagen no debe superar los 2 MB.',
        ]);

        $rutaFoto = null;
        if ($request->hasFile('foto')) {
            $rutaFoto = $request->file('foto')->store('terapeutas', 'public');
        }

        $trabajador = User::create([
            'nombre'      => $validatedData['nombre'],
            'correo'      => $validatedData['email'],
            'telefono'    => $validatedData['telefono'],
            'contraseña'  => Hash::make($validatedData['password']),
            'descripcion' => $validatedData['descripcion'] ?? null,
            'rol'         => 'trabajador',
            'foto'        => $rutaFoto,
        ]);

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
        $usuario = User::findOrFail($id);

        if ($usuario->foto && Storage::disk('public')->exists($usuario->foto)) {
            Storage::disk('public')->delete($usuario->foto);
        }

        $usuario->delete();

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
            'foto'        => 'nullable|image|max:2048',
        ], [
            'foto.image' => 'El archivo debe ser una imagen válida.',
            'foto.max'   => 'La imagen no debe superar los 2 MB.',
        ]);

        $datosActualizar = [
            'nombre'      => $validatedData['nombre'],
            'correo'      => $validatedData['email'],
            'telefono'    => $validatedData['telefono'],
            'descripcion' => $validatedData['descripcion'] ?? null,
        ];

        if ($request->hasFile('foto')) {
            if ($trabajador->foto && Storage::disk('public')->exists($trabajador->foto)) {
                Storage::disk('public')->delete($trabajador->foto);
            }
            $datosActualizar['foto'] = $request->file('foto')->store('terapeutas', 'public');
        }

        if ($request->filled('password')) {
            $datosActualizar['contraseña'] = Hash::make($validatedData['password']);
        }

        $trabajador->update($datosActualizar);

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