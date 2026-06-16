<?php

namespace App\Http\Controllers;

use App\Models\User; // Importar el modelo User para crear especialistas y recepcionistas.
use App\Http\Requests\CreateTrabajadorRequest; // Importar el Form Request para validar la creación de trabajadores..
use Illuminate\Http\Request; // Agregado para validar al recepcionista.

class AdminController extends Controller
{

    public function CreateTrabajador(CreateTrabajadorRequest $request)
    {
        $validatedData = $request->validated();

        $trabajador = User::create([
            'nombre'     => $validatedData['nombre'] ?? $validatedData['name'],
            'correo'     => $validatedData['email'],
            'telefono'   => $validatedData['telefono'] ?? null,
            // Quitamos el Hash::make porque Anyel ya lo configuró directo en el modelo
            'contraseña' => $validatedData['password'],
            'rol'        => 'trabajador' // CAMBIO: Usamos 'trabajador' porque es lo único que la BD acepta actualmente
        ]);

        return redirect()->route('admin.dashboard')->with('success', 'Especialista registrado exitosamente.');
    }

    public function CreateRecepcionista(Request $request)
    {
        // Validamos que los datos vengan completos y el correo no se repita
        $validatedData = $request->validate([
            'nombre'   => 'required|string|max:255',
            'email'    => 'required|email|unique:usuarios,correo',
            'telefono' => 'required|string',
            'password' => 'required|min:8',
        ]);

        // Guardamos en la base de datos
        $recepcionista = User::create([
            'nombre'     => $validatedData['nombre'],
            'correo'     => $validatedData['email'],
            'telefono'   => $validatedData['telefono'],
            'contraseña' => $validatedData['password'],
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
