<?php

use App\Http\Controllers\LoginController; // Controlador para manejar el login y registro de usuarios.
use Illuminate\Support\Facades\Route; // Importamos el controlador de Login para las rutas de autenticación.
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; // Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\RecepcionController; // Controlador para la recepción.
use App\Http\Controllers\EspecialistaController; // Controlador para el especialista (masajista/esteticista).
use App\Http\Controllers\ServicioController; // Controlador para el módulo de servicios (tratamientos).

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

// Ya no faltam las rutas de recepcionista y especialista :D.

// Aquí Andrés: Agregando rutas del controlador del admin para cada módulo (CORREGIDO) ------------------
Route::prefix('admin')->group(function () {
    // Como ya tiene el prefijo 'admin', la ruta se deja solo como '/' o '/dashboard'
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
    Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');
});

// RUTAS DEL CLIENTE ------------------------------------
Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');

// RUTAS DE LA RECEPCIÓN --------------------------------
Route::prefix('recepcion')->group(function () {
    Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
});
Route::middleware(['auth'])->prefix('recepcion')->group(function () {
    Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
});

// RUTAS DE LOS ESPECIALISTAS (TRABAJADORES) ------------
Route::prefix('especialista')->group(function () {
    Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');
});

// Rutas para el CRUD de Servicios (Solo el Admin debería poder entrar aquí).
Route::prefix('admin/servicios')->group(function () {

    // Esta ruta muestra el formulario (Falta la vista).
    Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');
    
    // Esta ruta recibe los datos del formulario y los guarda en la base de datos.
    Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');
});


















