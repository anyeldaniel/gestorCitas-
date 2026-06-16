<?php

namespace App\Http\Controllers;
use App\Models\User;
use App\Http\Requests\CreateTrabajadorRequest; // <--- Agrega esta línea

class AdminController extends Controller
{

    public function CreateTrabajador( CreateTrabajadorRequest $request)
    {
    
    $validatedData = $request->validated();

    $trabajador = User::create([
        'nombre' => $validatedData['nombre'],
        'correo' => $validatedData['email'],
        'telefono' => $validatedData['telefono'],
        'contraseña' => $validatedData['password'], 
        
        // AQUÍ ESTÁ LA MAGIA:
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