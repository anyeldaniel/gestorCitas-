<?php

use App\Http\Controllers\LoginController; // Controlador para manejar el login y registro de usuarios.
use Illuminate\Support\Facades\Route; // Importamos el controlador de Login para las rutas de autenticación.
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; //Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\AgendaController; //Añadí el controlador de agenda para enrutarlo 
use App\Http\Controllers\TerapeutaController; // Añadí el controlador de terapeutas 
// (daban error, no se porque)use App\Http\Controllers\ReservaController; // Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\RecepcionController; // Controlador para la recepción.
use App\Http\Controllers\EspecialistaController; // Controlador para el especialista (masajista/esteticista).
use App\Http\Controllers\ServicioController; // Controlador para el módulo de servicios (tratamientos).

// La raíz redirige o carga directamente el método index del Login para mantener la consistencia
Route::get('/', [LoginController::class, 'index'])->name('login');


// Rutas de autenticación (auth) --------------------------------------------------------------------------

Route::view('/Registro', 'auth.Registro')->name('registro.view'); 

// SE CORRIGIÓ: Se añadió la ruta GET para que no dé error al cargar el login
Route::get('/login', [LoginController::class, 'index'])->name('login.view'); 
Route::post('/login', [LoginController::class, 'login'])->name('login.post'); // Ruta para procesar el inicio de sesión
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); // Ruta para procesar el cierre de sesión

// Corregi ruta compartida, ahora apunta a compartidas.catalogo en vez de clientes.catalogo
Route::view('/catalogo', 'compartidas.catalogo')->name('catalogo');

// TODAS LAS RUTAS DE ABAJO SE PROTEGEN PARA USUARIOS AUTENTICADOS ----------------------------------------
Route::middleware(['auth'])->group(function () {

    // RUTAS DE COMPARTIDAS
    Route::get('/agenda', [AgendaController::class, 'agenda'])->name('agenda'); 
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');

    // RUTAS DEL CLIENTE 
    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');
    
    // RUTAS DE LA RECEPCIÓN 
    Route::prefix('recepcion')->group(function () {
        Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
    });

    // RUTAS DEL ADMIN
    Route::prefix('admin')->group(function () {
        Route::view('/dashboard', 'admin.dashboard')->name('admin.dashboard');
        Route::view('/reportes', 'admin.reportes')->name('admin.reportes');
        Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');
        
        // Rutas para el CRUD de Servicios
        Route::prefix('servicios')->group(function () {
            Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');
            Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');
        });
    });

    // RUTAS DE LOS ESPECIALISTAS (TRABAJADORES)
    Route::prefix('especialista')->group(function () {
        Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');
    });

});