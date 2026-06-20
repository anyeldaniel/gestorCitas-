<?php

use Illuminate\Support\Facades\Route; // Importamos los controladores necesarios para manejar las rutas de cada módulo.
use App\Http\Controllers\LoginController; // Controlador para manejar la autenticación de usuarios (login, registro, logout).
use App\Http\Controllers\AdminController; // Controlador para manejar las funciones administrativas, incluyendo la gestión de trabajadores, recepcionistas y el dashboard.
use App\Http\Controllers\ReservaController;  // Controlador para manejar las reservas de citas por parte de los clientes, incluyendo la creación y visualización de reservas.
use App\Http\Controllers\RecepcionController; // Controlador para manejar las funciones específicas del módulo de recepción, como la gestión de citas y pagos.
use App\Http\Controllers\EspecialistaController; // Controlador para manejar las funciones específicas de los especialistas/trabajadores, como la visualización de su tablero operativo interno.
use App\Http\Controllers\ServicioController; // Controlador para manejar la gestión de servicios de spa, incluyendo la creación y visualización de servicios disponibles.
use App\Http\Controllers\AgendaController; // Controlador para manejar la visualización de la agenda/calendario global, accesible para todos los roles.
use App\Http\Controllers\TerapeutaController; // Controlador para manejar la visualización y gestión de terapeutas desde el módulo de administración, pero accesible para todos los roles (con botones de acción visibles solo para admin).
use App\Http\Controllers\SalaEsperaController; // Controlador para manejar la visualización de la sala de espera virtual, accesible para todos los roles.
use App\Http\Controllers\PagoController; // Controlador para manejar la gestión de pagos, incluyendo la verificación de pagos por parte del personal de recepción.

// --- ACCESO PÚBLICO Y AUTENTICACIÓN 
// La raíz redirige o carga directamente el método index del Login para mantener la consistencia.
Route::get('/', [LoginController::class, 'index']); // Redirige a la vista de login por defecto.
Route::get('/login', [LoginController::class, 'index'])->name('login'); // Redirige a la vista de login por defecto.
Route::get('/login-view', [LoginController::class, 'index'])->name('login.view'); // Redirige a la vista de login por defecto.

Route::view('/Registro', 'auth.Registro')->name('registro.view'); // Redirige a la vista de registro por defecto.
Route::post('/login', [LoginController::class, 'login'])->name('login.post'); // Redirige al método de login para procesar la autenticación.
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create'); // Redirige al método de registro para procesar la creación de un nuevo usuario.
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTAS PROTEGIDAS (SOLO USUARIOS LOGUEADOS) ---
Route::middleware(['auth'])->group(function () {

    // ==========================================
    // 1. RUTAS COMPARTIDAS (Multirrol)
    // ==========================================

    // Vista Global de la Agenda / Calendario
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

    // El Catálogo Zen dinámico con lógica de botones por rol
    Route::view('/catalogo', 'compartidas.catalogo')->name('catalogo');

    // RECUPERADA: Vista para mostrar y gestionar terapeutas desde el módulo de administración, pero accesible para todos los roles (con botones de acción visibles solo para admin)
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');


    // ==========================================
    // 2. RUTAS DEL CLIENTE
    // ==========================================
    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');


    // ==========================================
    // 3. MÓDULO ADMINISTRADOR (Prefijo 'admin')
    // ==========================================
    Route::prefix('admin')->group(function () {
        // Rutas de edición y eliminación correctamente ubicadas aquí
        Route::put('/trabajador/{id}', [AdminController::class, 'updateTrabajador'])->name('admin.update-trabajador');
        Route::put('/recepcionista/{id}', [AdminController::class, 'updateRecepcionista'])->name('admin.update-recepcionista');
        Route::delete('/usuario/{id}', [AdminController::class, 'destroyUsuario'])->name('admin.destroy-usuario');

        // Cambio de Route::view a Route::get apuntando al controlador 
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        Route::view('/reportes', 'admin.reportes')->name('admin.reportes');
        Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');

        // Endpoints que subió Wladimir para persistir usuarios en la Base de Datos
        Route::post('/crear-trabajador', [AdminController::class, 'CreateTrabajador'])->name('admin.create-trabajador');
        Route::post('/crear-recepcionista', [AdminController::class, 'CreateRecepcionista'])->name('admin.create-recepcionista');

        // CRUD de Servicios de Spa
        Route::prefix('servicios')->group(function () {
            Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');
            Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');
        });
    });

    // ==========================================
    // 4. MÓDULO RECEPCIÓN (Prefijo 'recepcion')
    // ==========================================
    Route::prefix('recepcion')->group(function () {
        Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
        Route::post('/cita/{id}/actualizar', [AgendaController::class, 'updateStatus'])->name('cita.update');
        Route::get('/pagos', [PagoController::class, 'index'])->name('pago.verificar');
    });

    // ==========================================
    // 5. MÓDULO ESPECIALISTAS / TRABAJADORES
    // ==========================================
    Route::prefix('especialista')->group(function () {
        Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');
        // REUBICADA: La ruta de Wladimir para el tablero operativo interno del terapeuta con URL única
        Route::get('/gestion-interna', [TerapeutaController::class, 'tablero'])->name('terapeuta.gestion-interna');
    });

    // ==========================================
    // 6. OTROS MÓDULOS DE CONTROL
    // ==========================================
    Route::get('/sala-espera', [SalaEsperaController::class, 'index'])->name('sala.espera');
});