<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\LoginController;
use App\Http\Controllers\PasswordController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ReservaController;
use App\Http\Controllers\RecepcionController;
use App\Http\Controllers\EspecialistaController;
use App\Http\Controllers\ServicioController;
use App\Http\Controllers\AgendaController;
use App\Http\Controllers\TerapeutaController;
use App\Http\Controllers\SalaEsperaController;
use App\Http\Controllers\PagoController;

// ==========================================
//   ACCESO PÚBLICO Y AUTENTICACIÓN
// ==========================================

Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::get('/login-view', [LoginController::class, 'index'])->name('login.view');

Route::view('/Registro', 'auth.Registro')->name('registro.view');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// ==========================================
//   RECUPERACIÓN DE CONTRASEÑA
// ==========================================

Route::view('/olvide-contrasena', 'auth.password.request')->name('passwordRequest.view');
Route::post('/olvide-contrasena', [PasswordController::class, 'VerifiCorreo'])->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.password.reset', ['token' => $token]);
})->name('password.reset');

Route::get('/reset-password', function () {
    return redirect()->route('passwordRequest.view');
});

Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');


// ==========================================
//   RUTAS PROTEGIDAS (SOLO USUARIOS LOGUEADOS)
// ==========================================
Route::middleware(['auth'])->group(function () {

    // ==========================================
    //   RUTAS COMPARTIDAS (Multirrol)
    // ==========================================

    // Agenda compartida (admin, recepcionista y trabajador)
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

    Route::patch('/agenda/cita/{id}/estado', [AgendaController::class, 'updateStatus'])
        ->name('agenda.cita.estado');

    Route::post('/agenda/cita/{id}/cancelar', [AgendaController::class, 'cancelar'])
        ->name('agenda.cita.cancelar');

    // ----------------------------------------------------------

    Route::get('/catalogo', [ServicioController::class, 'index'])->name('catalogo');
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');


    // ==========================================
    //   RUTAS DEL CLIENTE
    // ==========================================

    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');

    // Pasarela de pago (cliente)
    Route::get('/pago/checkout/{cita}',  [PagoController::class, 'checkout'])->name('pago.checkout');
    Route::post('/pago/procesar/{cita}', [PagoController::class, 'procesar'])->name('pago.procesar');

    // Conectar especialistas con servicios
    Route::get('/especialistas-por-servicio/{id}', [ReservaController::class, 'getEspecialistas']);


    // ==========================================
    //   MÓDULO ADMINISTRADOR (Prefijo 'admin')
    // ==========================================
    Route::prefix('admin')->group(function () {

        // --- Dashboard y Vistas Principales ---
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Reportes (vista + endpoint AJAX con datos)
        Route::get('/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
        Route::get('/reportes/datos', [AdminController::class, 'reportesDatos'])->name('admin.reportes.datos');

        Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');

        // --- CRUD de Trabajadores (Especialistas y Recepcionistas) ---
        Route::post('/crear-trabajador', [AdminController::class, 'CreateTrabajador'])->name('admin.create-trabajador');
        Route::post('/crear-recepcionista', [AdminController::class, 'CreateRecepcionista'])->name('admin.create-recepcionista');

        Route::put('/trabajador/{id}', [AdminController::class, 'updateTrabajador'])->name('admin.update-trabajador');
        Route::put('/recepcionista/{id}/actualizar', [AdminController::class, 'updateRecepcionista'])->name('admin.update-recepcionista');

        Route::delete('/usuario/{id}', [AdminController::class, 'destroyUsuario'])->name('admin.destroy-usuario');

        // --- CRUD de Servicios de Spa ---
        Route::prefix('servicios')->group(function () {
            Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');
            Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');
            Route::put('/{id}/actualizar', [ServicioController::class, 'update'])->name('servicios.actualizar');
            Route::delete('/{id}/eliminar', [ServicioController::class, 'destroy'])->name('servicios.eliminar');
        });
    });


    // ==========================================
    //   VERIFICAR PAGOS
    // ==========================================
    Route::get('/verificar-pagos', [PagoController::class, 'index'])->name('pago.verificar');


    // ==========================================
    //   MÓDULO ESPECIALISTAS / TRABAJADORES
    // ==========================================
    Route::prefix('especialista')->group(function () {
        Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');
        Route::get('/gestion-interna', [TerapeutaController::class, 'tablero'])->name('terapeuta.gestion-interna');
    });


    // ==========================================
    //   OTROS MÓDULOS DE CONTROL
    // ==========================================
    Route::get('/sala-espera', [SalaEsperaController::class, 'index'])->name('sala.espera');
});