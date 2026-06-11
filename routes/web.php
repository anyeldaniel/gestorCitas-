<?php

use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; //Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\AgendaController; //Añadí el controlador de agenda para enrutarlo 
use App\Http\Controllers\TerapeutaController; // Añadí el controlador de terapeutas 

// La raíz redirige o carga directamente el método index del Login para mantener la consistencia
Route::get('/', [LoginController::class, 'index'])->name('login');


// Rutas de autenticación (auth) --------------------------------------------------------------------------

Route::view('/Registro', 'auth.Registro')->name('registro.view'); 
Route::post('/login', [LoginController::class, 'login']);
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); // Ruta para procesar el cierre de sesión

// Corregi ruta compartida, ahora apunta a compartidas.catalogo en vez de clientes.catalogo
Route::view('/catalogo', 'compartidas.catalogo')->name('catalogo');


// TODAS LAS RUTAS DE ABAJO SE PROTEGEN PARA USUARIOS AUTENTICADOS ----------------------------------------
Route::middleware(['auth'])->group(function () {

    //         RUTAS DE COMPARTIDAS (Movidas a su nueva carpeta)
    
    // Agrego la ruta de la agenda, es una ruta global para que funcione con los otros roles
    Route::get('/agenda', [AgendaController::class, 'agenda'])->name('agenda'); 
    //Ruta compartida de terapeutas, para que tanto admin como recepcionista puedan verla (y el admin pueda gestionar desde ahí)
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');
    // faltarian las dos rutas del recepcionista y especialista pero
    // hay que esperar a que el admin los pueda registrar


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

});







