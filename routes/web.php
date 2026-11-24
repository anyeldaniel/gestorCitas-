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
use App\Http\Controllers\ResenaController;
use App\Http\Controllers\NotificacionController;


// ==========================================
//   ACCESO PÚBLICO Y AUTENTICACIÓN
// ==========================================

Route::get('/', [LoginController::class, 'index']);
Route::get('/login', [LoginController::class, 'index'])->name('login');
Route::get('/login-view', [LoginController::class, 'index'])->name('login.view');
Route::view('/Registro', 'auth.Registro')->name('registro.view');
Route::post('/login', [LoginController::class, 'login'])->name('login.post');
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');

Route::view('/olvide-contrasena', 'auth.password.request')->name('passwordRequest.view');
Route::post('/olvide-contrasena', [PasswordController::class, 'VerifiCorreo'])->name('password.email');

Route::get('/reset-password/{token}', function ($token) {
    return view('auth.password.reset', ['token' => $token]);
})->name('password.reset');

Route::get('/reset-password', function () {
    return redirect()->route('passwordRequest.view');
});

Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');

// Catálogos de consulta pública.
Route::get('/catalogo', [ServicioController::class, 'index'])->name('catalogo');
Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');


// ==========================================
//   RUTAS COMPARTIDAS (CUALQUIER USUARIO AUTENTICADO)
// ==========================================
// Este grupo exige sesión, pero permite cualquier rol.
Route::middleware(['auth'])->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');
    Route::post('/push/subscribe', [App\Http\Controllers\WebPushController::class, 'subscribe'])->name('push.subscribe');

    Route::get('/notificaciones/lista', [NotificacionController::class, 'lista'])->name('notificaciones.lista');
    Route::post('/notificaciones/{notificacion}/leer', [NotificacionController::class, 'marcarLeida'])->name('notificaciones.leer');
    Route::post('/notificaciones/leer-todas', [NotificacionController::class, 'marcarTodas'])->name('notificaciones.leerTodas');
});


// ==========================================
//   AGENDA DE ADMINISTRACIÓN, RECEPCIÓN Y TRABAJADORES
// ==========================================
// Agenda y cancelación: los tres roles pueden acceder; el controlador valida la asignación al cancelar.
Route::middleware(['auth', 'role:admin,recepcion,trabajador'])->group(function () {
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

    Route::post('/agenda/cita/{id}/cancelar', [AgendaController::class, 'cancelar'])
        ->name('agenda.cita.cancelar');
});

// Cambiar el estado de una cita está limitado a administración y recepción.
Route::middleware(['auth', 'role:admin,recepcion'])->group(function () {
    Route::patch('/agenda/cita/{id}/estado', [AgendaController::class, 'updateStatus'])
        ->name('agenda.cita.estado');
});


// ==========================================
//   RUTAS DEL CLIENTE
// ==========================================
// Estas operaciones de reserva y pago son exclusivas de clientes autenticados.
Route::middleware(['auth', 'role:cliente'])->group(function () {
    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');

    Route::get('/checkout/{cita_id}', [PagoController::class, 'checkout'])->name('checkout.view');
    Route::post('/checkout/procesar', [PagoController::class, 'procesarPago'])->name('checkout.procesar');

    Route::get('/pago/checkout/{cita}', [PagoController::class, 'checkout'])->name('pago.checkout');
    Route::post('/pago/procesar/{cita}', [PagoController::class, 'procesar'])->name('pago.procesar');

    Route::get('/get-especialistas/{id}', [ReservaController::class, 'getEspecialistas']);
    Route::get('/especialistas-por-servicio/{id}', [ReservaController::class, 'getEspecialistas']);

    Route::get('/mis-reservas', [ReservaController::class, 'misReservas'])->name('clientes.mis.reservas');
    Route::post('/mis-reservas/{cita}/resena', [ResenaController::class, 'store'])->name('resenas.store');
    Route::put('/mis-reservas/resena/{resena}', [ResenaController::class, 'update'])->name('resenas.update');
    Route::delete('/mis-reservas/resena/{resena}', [ResenaController::class, 'destroy'])->name('resenas.destroy');
});


// ==========================================
//   MÓDULO ADMINISTRADOR
// ==========================================
// Todas las rutas bajo /admin requieren autenticación y rol admin.
Route::middleware(['auth', 'role:admin'])->prefix('admin')->group(function () {
    Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');
    Route::get('/reportes', [AdminController::class, 'reportes'])->name('admin.reportes');
    Route::get('/reportes/datos', [AdminController::class, 'reportesDatos'])->name('admin.reportes.datos');
    Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');

    Route::post('/crear-trabajador', [AdminController::class, 'CreateTrabajador'])->name('admin.create-trabajador');
    Route::post('/crear-recepcionista', [AdminController::class, 'CreateRecepcionista'])->name('admin.create-recepcionista');

    Route::put('/trabajador/{id}', [AdminController::class, 'updateTrabajador'])->name('admin.update-trabajador');
    Route::put('/recepcionista/{id}/actualizar', [AdminController::class, 'updateRecepcionista'])->name('admin.update-recepcionista');
    Route::delete('/usuario/{id}', [AdminController::class, 'destroyUsuario'])->name('admin.destroy-usuario');

    Route::prefix('servicios')->group(function () {
        Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');
        Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');
        Route::put('/{id}/actualizar', [ServicioController::class, 'update'])->name('servicios.actualizar');
        Route::delete('/{id}/eliminar', [ServicioController::class, 'destroy'])->name('servicios.eliminar');
    });
});

// La actualización del catálogo también es una acción exclusiva de administración.
Route::middleware(['auth', 'role:admin'])->group(function () {
    Route::put('/catalogo/actualizar/{id}', [ServicioController::class, 'update'])->name('catalogo.update');
});


// ==========================================
//   VERIFICACIÓN Y GESTIÓN DE PAGOS
// ==========================================
// Verificación, acciones de recepción y sala de espera: admin o recepción.
Route::middleware(['auth', 'role:admin,recepcion'])->group(function () {
    Route::get('/verificar-pagos', [PagoController::class, 'index'])->name('pago.verificar');

    Route::prefix('recepcion')->group(function () {
        Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
        Route::post('/cita/{id}/actualizar', [AgendaController::class, 'updateStatus'])->name('cita.update');
        Route::post('/aprobar-pago/{id}', [PagoController::class, 'aprobarPago'])->name('recepcion.aprobar.pago');
        Route::post('/rechazar-pago/{id}', [PagoController::class, 'rechazarPago'])->name('recepcion.rechazar.pago');
        Route::post('/definir-reembolso/{id}', [PagoController::class, 'definirReembolso'])->name('recepcion.definir.reembolso');
    });

    Route::get('/sala-espera', [SalaEsperaController::class, 'index'])->name('sala.espera');
});


// ==========================================
//   PANEL DE TRABAJADORES
// ==========================================
// Ambos paneles bajo /especialista requieren una sesión de trabajador.
Route::middleware(['auth', 'role:trabajador'])->prefix('especialista')->group(function () {
    Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');
    Route::get('/gestion-interna', [TerapeutaController::class, 'tablero'])->name('terapeuta.gestion-interna');
});
