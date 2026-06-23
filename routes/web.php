<?php

use Illuminate\Support\Facades\Route; // Importamos el controlador de Login para las rutas de autenticación.
use App\Http\Controllers\LoginController; // Controlador para manejar el login y registro de usuarios.
use App\Http\Controllers\PasswordController; // Controlador para manejar la recuperación de contraseña.
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; ; //Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\RecepcionController; ; // Controlador para la recepción. 
use App\Http\Controllers\EspecialistaController; ; // Controlador para el especialista (masajista/esteticista).
use App\Http\Controllers\ServicioController; // Controlador para el módulo de servicios (tratamientos).
use App\Http\Controllers\AgendaController; ; // Controlador para manejar la agenda de citas en la recepción.
use App\Http\Controllers\TerapeutaController; ; // Controlador para manejar las funciones del terapeuta (masajista/esteticista).
use App\Http\Controllers\SalaEsperaController; // Controlador para manejar la sala de espera (si es que se implementa esa funcionalidad).
use App\Http\Controllers\PagoController; // Controlador para manejar la verificación de pagos (si es que se implementa esa funcionalidad).

// --- ACCESO PÚBLICO Y AUTENTICACIÓN 
// La raíz redirige o carga directamente el método index del Login para mantener la consistencia.
Route::get('/', [LoginController::class, 'index']); // Redirige a la vista de login por defecto.
Route::get('/login', [LoginController::class, 'index'])->name('login'); // Redirige a la vista de login por defecto.
Route::get('/login-view', [LoginController::class, 'index'])->name('login.view'); // Redirige a la vista de login por defecto.

Route::view('/Registro', 'auth.Registro')->name('registro.view'); // Redirige a la vista de registro por defecto.
Route::post('/login', [LoginController::class, 'login'])->name('login.post'); // Redirige al método de login para procesar la autenticación.
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create'); // Redirige al método de registro para procesar la creación de un nuevo usuario.
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTAS DE RECUPERACIÓN DE CONTRASEÑA ---

// 1. Mostrar el formulario para solicitar el correo (La vista que acabamos de crear)
Route::view('/olvide-contrasena', 'auth.password.request')->name('passwordRequest.view');

// 2. Procesar el envío del correo de recuperación
Route::post('/olvide-contrasena', [PasswordController::class, 'VerifiCorreo'])->name('password.email');

// 3. Vista para que el usuario ingrese la nueva contraseña (Recibe el token por URL)
Route::get('/reset-password/{token}', function ($token) {
    return view('auth.password.reset', ['token' => $token]);
})->name('password.reset');

// 3b. Redirige accesos GET directos sin token al formulario de solicitud de recuperación
Route::get('/reset-password', function () {
    return redirect()->route('passwordRequest.view');
});

// 4. Procesar el cambio de contraseña real
Route::post('/reset-password', [PasswordController::class, 'resetPassword'])->name('password.update');


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
        Route::put('/recepcionista/{id}/actualizar', [AdminController::class, 'updateRecepcionista'])->name('admin.update-recepcionista');
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

    /* ==========================================
    // 4. MÓDULO RECEPCIÓN (Prefijo 'recepcion')
    // ==========================================
    Route::prefix('recepcion')->group(function () {
        Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');
        Route::post('/cita/{id}/actualizar', [AgendaController::class, 'updateStatus'])->name('cita.update');
        Route::get('/pagos', [PagoController::class, 'index'])->name('pago.verificar');
        LO COMENTO PORQUE CREO QUE VOY A DESCARTARLO, DE MOMENTO, ASÍ QUEDA
    });*/

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
    
    // -----------------------------------------------------------------
        // MÓDULO DE VERIFICACIÓN DE PAGOS MÓVILES (FRONTEND - EILYN)
        // -----------------------------------------------------------------
        // Apunta directamente a la nueva función creada en el controlador
        Route::get('/recepcion/pagos', [PagoController::class, 'mostrarVistaAdmin'])->name('compartidas.pago.verificar');
});