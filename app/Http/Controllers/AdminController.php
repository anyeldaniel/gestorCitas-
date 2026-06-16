<?php

namespace App\Http\Controllers;
use App\Models\User;
use Illuminate\Http\Request;

class AdminController extends Controller
{

    public function CreateTrabajador(Request $request)
    {
    
    $validatedData = $request->validated();

    $trabajador = User::create([
        'nombre' => $validatedData['username'],
        'correo' => $validatedData['email'],
        'telefono' => $validatedData['telefono'],
        'contraseña' => $validatedData['password'], 
        
        // AQUÍ ESTÁ LA MAGIA:
        // Tomas la variable 'role' que viene de tu <select> en el HTML
        'rol' => $validatedData['role'], 
    ]);

    return redirect()->route('admin.dashboard')->with('success', 'Trabajador registrado exitosamente.');
}
            


    // Muestra la gestión del catálogo (Módulo Servicios)
    public function servicios()
    {
        return view('admin.servicios');
    }

}