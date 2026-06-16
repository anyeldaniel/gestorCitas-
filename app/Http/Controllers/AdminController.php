<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Requests\CreateTrabajadorRequest; // Importamos el request personalizado para validar la creación de trabajadores
use Illuminate\Support\Facades\Hash; // Importado para garantizar la seguridad de la contraseña encriptada

class AdminController extends Controller
{

    public function CreateTrabajador( CreateTrabajadorRequest $request)
    {
    
    $validatedData = $request->validated();

    // Sincronizamos las claves del array validado según las reglas del CreateTrabajadorRequest (soportando tanto 'name' como 'nombre' de ambos formularios)
    $trabajador = User::create([
        'nombre' => $validatedData['nombre'] ?? $validatedData['name'],
        'correo' => $validatedData['email'],
        'telefono' => $validatedData['telefono'] ?? null,
        'contraseña' => Hash::make($validatedData['password']), // Encriptamos la contraseña antes de guardarla en la base de datos para garantizar la seguridad, utilizando el facade Hash de Laravel.
    
        // lo guardamos como trabajador 
        'rol' => 'trabajador'
    ]);

    return redirect()->route('admin.dashboard')->with('success', 'Trabajador registrado exitosamente.');
}
            


    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }

}