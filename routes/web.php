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
Route::post('/login', [LoginController::class, 'login'])->name('login.post'); // Ruta para procesar el inicio de sesión
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout'); // Ruta para procesar el cierre de sesión

// Corregi ruta compartida, ahora apunta a compartidas.catalogo en vez de clientes.catalogo
Route::view('/catalogo', 'compartidas.catalogo')->name('catalogo');

// Ya no faltam las rutas de recepcionista y especialista :D.


// TODAS LAS RUTAS DE ABAJO SE PROTEGEN PARA USUARIOS AUTENTICADOS ----------------------------------------
Route::middleware(['auth'])->group(function () {

    //         RUTAS DE COMPARTIDAS (Movidas a su nueva carpeta)
    
    // Agrego la ruta de la agenda, es una ruta global para que funcione con los otros roles
    Route::get('/agenda', [AgendaController::class, 'agenda'])->name('agenda'); 
    //Ruta compartida de terapeutas, para que tanto admin como recepcionista puedan verla (y el admin pueda gestionar desde ahí)
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');
    // faltarian las dos rutas del recepcionista y especialista pero
    // hay que esperar a que el admin los pueda registrar


   /*   ESTA ERAN LAS RUTAS QUE TENIAS VIEJAS, EN LA OTRA QUE DICE (aqui andres) ESA ES LAS QUE SON 
   
   // Aquí Andrés: Agregando rutas del controlador del admin para cada módulo (CORREGIDO) ------------------
    Route::prefix('admin')->group(function () {
        // Como ya tiene el prefijo 'admin', la ruta se deja solo como '/' o '/dashboard'
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
        Route::get('/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
        Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');
    });*/


    // RUTAS DEL CLIENTE ------------------------------------
    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');

});

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


















