<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; //Añadí el controlador de reservas para enrutarlooo

Route::get('/', function () { 
    return view('auth.login');
});


// Rutas de autenticación (auth) --------------------------------------------------------------------------


Route::view('/Registro', 'auth.Registro')->name('registro.view'); 
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::post('/login', [LoginController::class, 'login']);
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');
Route::view('/catalogo', 'clientes.catalogo')->name('catalogo');
// Ruta para procesar el cierre de sesión
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// faltarian las dos rutas del recepcionista y especialista pero
// hay que esperar a que el admin los pueda registrar



// Aquí Andrés: Agregando rutas del controlador del admin para cada módulo (CORREGIDO) ------------------
Route::prefix('admin')->group(function () {
    // Como ya tiene el prefijo 'admin', la ruta se deja solo como '/' o '/dashboard'
    Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
    Route::view('/reportes', 'admin.reportes')->name('admin.reportes');
    //hagan asi para las vistas, no es necesario crear un método en el controlador si solo van a retornar una vista, con la función Route::view es suficiente
    //solo si muestra vista si la vista incluye lógica o datos dinámicos, entonces sí es necesario crear un método en el controlador para procesar esa lógica y pasarle los datos a la vista
    Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');
});


// RUTAS DEL CLIENTE ------------------------------------
Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');



















