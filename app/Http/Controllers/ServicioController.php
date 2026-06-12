<?php

namespace App\Http\Controllers;

use App\Models\Servicio; // Importamos el modelo que acabamos de crear.
use Illuminate\Http\Request;

class ServicioController extends Controller
{
    // Esta función simplemente muestra la vista con el formulario HTML.
    public function create()
    {
        // Más adelante crearemos este archivo blade para que el admin vea el formulario.
        return view('admin.servicios_crear'); 
    }

    // Esta función recibe los datos del formulario al darle al botón "Guardar".
    public function store(Request $request)
    {
        // Validación. 
        // Revisamos que el admin no deje campos vacíos o ponga letras en los precios.
        $request->validate([
            'nombre_servicio' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'precio' => 'required|numeric|min:0', // Numeric asegura que sean números (enteros o decimales).
            'duracion_minutos' => 'required|integer|min:1',
            'imagen' => 'nullable|image|mimes:jpeg,png,jpg|max:2048', // Valida que si suben un archivo, sea una imagen que pese máximo 2MB.
        ]);

        // Manejar la Imagen.
        $rutaImagen = null; 
        if ($request->hasFile('imagen')) {
            // Si el admin subió una imagen, la guardamos en la carpeta "storage/app/public/servicios".
            // y guardamos la ruta de texto en la variable $rutaImagen.
            $rutaImagen = $request->file('imagen')->store('servicios', 'public');
        }

        // Guardar en la Base de Datos.
        // Usamos el Modelo Servicio para insertar una nueva fila en la tabla.
        Servicio::create([
            'nombre_servicio' => $request->nombre_servicio,
            'descripcion' => $request->descripcion,
            'precio' => $request->precio,
            'duracion_minutos' => $request->duracion_minutos,
            'imagen' => $rutaImagen, // Guardamos la ruta, no la imagen como tal.
        ]);

        // Redireccionar.
        // Devolvemos al usuario a la página anterior con un mensajito de que todo salió bien.
        return redirect()->back()->with('success', '¡Tratamiento registrado exitosamente en The Beauty Room!');
    }
}