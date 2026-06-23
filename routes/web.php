<?php

use Illuminate\Support\Facades\Route; // Importamos el controlador de Login para las rutas de autenticación.
use App\Http\Controllers\LoginController; // Controlador para manejar el login y registro de usuarios.
use App\Http\Controllers\PasswordController; // Controlador para manejar la recuperación de contraseña.
use App\Http\Controllers\AdminController; // Corregí mi regada con la A mayúscula xd att: Andrés
use App\Http\Controllers\ReservaController; //Añadí el controlador de reservas para enrutarlooo
use App\Http\Controllers\RecepcionController; // Controlador para la recepción. 
use App\Http\Controllers\EspecialistaController; // Controlador para el especialista (masajista/esteticista).
use App\Http\Controllers\ServicioController; // Controlador para el módulo de servicios (tratamientos).
use App\Http\Controllers\AgendaController; // Controlador para manejar la agenda de citas en la recepción.
use App\Http\Controllers\TerapeutaController; // Controlador para manejar las funciones del terapeuta (masajista/esteticista).
use App\Http\Controllers\SalaEsperaController; // Controlador para manejar la sala de espera (si es que se implementa esa funcionalidad).
use App\Http\Controllers\PagoController; // Controlador para manejar la verificación de pagos (si es que se implementa esa funcionalidad).

// --- ACCESO PÚBLICO Y AUTENTICACIÓN ---
// La raíz redirige o carga directamente el método index del Login para mantener la consistencia.

// Redirige a la vista de login por defecto.
Route::get('/', [LoginController::class, 'index']);

// Redirige a la vista de login por defecto.
Route::get('/login', [LoginController::class, 'index'])->name('login');

// Redirige a la vista de login por defecto.
Route::get('/login-view', [LoginController::class, 'index'])->name('login.view');
// Rutas para el registro y autenticación de usuarios.
// Redirige a la vista de registro por defecto.
Route::view('/Registro', 'auth.Registro')->name('registro.view');

// Procesa el formulario de login, apuntando al método login del LoginController.
Route::post('/login', [LoginController::class, 'login'])->name('login.post');

// Procesa el formulario de registro, apuntando al método RegistroController del LoginController.
Route::post('/registrado', [LoginController::class, 'RegistroController'])->name('registro.create');

// Ruta para cerrar sesión, apuntando al método logout del LoginController.
Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

// --- RUTAS DE RECUPERACIÓN DE CONTRASEÑA ---

// Mostrar el formulario para solicitar el correo (La vista que acabamos de crear).
Route::view('/olvide-contrasena', 'auth.password.request')->name('passwordRequest.view');

// Procesar el envío del correo de recuperación.
Route::post('/olvide-contrasena', [PasswordController::class, 'VerifiCorreo'])->name('password.email');

// Vista para que el usuario ingrese la nueva contraseña (Recibe el token por URL).
Route::get('/reset-password/{token}', function ($token) {
    return view('auth.password.reset', ['token' => $token]);
})->name('password.reset');

// Procesar el cambio de contraseña real.
Route::post('/reset-password', [LoginController::class, 'resetPassword'])->name('password.update');


// --- RUTAS PROTEGIDAS (SOLO USUARIOS LOGUEADOS) ---
Route::middleware(['auth'])->group(function () {

    // ==========================================
    //      RUTAS COMPARTIDAS (Multirrol)
    // ==========================================

    // Vista Global de la Agenda / Calendario.
    Route::get('/agenda', [AgendaController::class, 'index'])->name('agenda');

    // El Catálogo Zen dinámico con lógica de botones por rol.
    // Cambie el Route::view por Route::get para que pase por el controlador primero.
    Route::get('/catalogo', [ServicioController::class, 'index'])->name('catalogo');

    // RECUPERADA: Vista para mostrar y gestionar terapeutas desde el módulo de administración, pero accesible para todos los roles (con botones de acción visibles solo para admin).
    Route::get('/terapeutas', [TerapeutaController::class, 'index'])->name('terapeutas.index');

    // Ruta para actualizar el catalogo.    
    Route::put('/catalogo/actualizar/{id}', [ServicioController::class, 'update'])->name('catalogo.update');


    // ==========================================
    //            RUTAS DEL CLIENTE
    // ==========================================

    // Vista para que los clientes puedan ver el catálogo de servicios y reservar, ahora pasando por el controlador para lógica adicional.
    // Cambié el Route::view por Route::get para que pase por el controlador y pueda cargar los servicios dinámicamente.
    Route::get('/reservas', [ReservaController::class, 'index'])->name('clientes.reserva');

    // Ruta para procesar la reserva de un servicio, apuntando al método store del controlador de reservas.
    Route::post('/reservas', [ReservaController::class, 'store'])->name('clientes.reserva.store');

    // Ruta para conectar los especialistas con los servicios.
    Route::get('/especialistas-por-servicio/{id}', [\App\Http\Controllers\ReservaController::class, 'getEspecialistas']);


    // ==========================================
    //   MÓDULO ADMINISTRADOR (Prefijo 'admin')
    // ==========================================

    // Rutas para la gestión de usuarios, servicios y reportes desde el módulo de administración, apuntando a los métodos correspondientes en el AdminController.
    Route::prefix('admin')->group(function () {

        // Ruta para actualizar un trabajador específico, apuntando al método updateTrabajador del AdminController.
        Route::put('/trabajador/{id}', [AdminController::class, 'updateTrabajador'])->name('admin.update-trabajador');

        // Ruta para actualizar una recepcionista específica, apuntando al método updateRecepcionista del AdminController.
        Route::put('/recepcionista/{id}/actualizar', [AdminController::class, 'updateRecepcionista'])->name('admin.update-recepcionista');



        // Ruta para eliminar un usuario específico, apuntando al método destroyUsuario del AdminController.
        Route::delete('/usuario/{id}', [AdminController::class, 'destroyUsuario'])->name('admin.destroy-usuario');

        // Cambio de Route::view a Route::get apuntando al controlador.
        Route::get('/dashboard', [AdminController::class, 'dashboard'])->name('admin.dashboard');

        // Rutas para las vistas de administración, ahora pasando por el controlador para lógica adicional.
        Route::view('/reportes', 'admin.reportes')->name('admin.reportes');

        // Rutas para la gestión de servicios, ahora pasando por el controlador para lógica adicional.
        Route::get('/servicios', [AdminController::class, 'servicios'])->name('admin.servicios');

        // Endpoint para crear un trabajador, apuntando al método CreateTrabajador del AdminController.
        Route::post('/crear-trabajador', [AdminController::class, 'CreateTrabajador'])->name('admin.create-trabajador');

        // Endpoint para crear una recepcionista, apuntando al método CreateRecepcionista del AdminController.
        Route::post('/crear-recepcionista', [AdminController::class, 'CreateRecepcionista'])->name('admin.create-recepcionista');

        // CRUD de Servicios de Spa.
        Route::prefix('servicios')->group(function () {

            // Ruta para mostrar el formulario de creación de un nuevo servicio, apuntando al método create del ServicioController.
            Route::get('/crear', [ServicioController::class, 'create'])->name('servicios.crear');

            // Ruta para procesar el formulario de creación de un nuevo servicio, apuntando al método store del ServicioController.
            Route::post('/guardar', [ServicioController::class, 'store'])->name('servicios.guardar');

            // Ruta para actualizar un servicio existente.
            Route::put('/{id}/actualizar', [ServicioController::class, 'update'])->name('servicios.actualizar');

            // Ruta para eliminar un servicio existente.
            Route::delete('/{id}/eliminar', [ServicioController::class, 'destroy'])->name('servicios.eliminar');
        });
    });

    // ==========================================
    //   MÓDULO RECEPCIÓN (Prefijo 'recepcion')
    // ==========================================

    // Rutas para la gestión de la agenda y citas desde la recepción, apuntando a los métodos correspondientes en el RecepcionController y AgendaController.
    Route::prefix('recepcion')->group(function () {

        // Ruta para mostrar la agenda de citas en la recepción, apuntando al método agenda del RecepcionController.
        Route::get('/agenda', [RecepcionController::class, 'agenda'])->name('recepcion.agenda');

        // Ruta para actualizar el estado de una cita específica, apuntando al método updateStatus del AgendaController.
        Route::post('/cita/{id}/actualizar', [AgendaController::class, 'updateStatus'])->name('cita.update');

        // Ruta para mostrar la vista de verificación de pagos, apuntando al método index del PagoController.
        Route::get('/verificar-pagos', [\App\Http\Controllers\PagoController::class, 'index'])->name('pago.verificar');
    });

    // ==========================================
    //   MÓDULO ESPECIALISTAS / TRABAJADORES
    // ==========================================

    //  Rutas para el tablero operativo interno del especialista (masajista/esteticista), apuntando a los métodos correspondientes en el EspecialistaController y TerapeutaController.
    Route::prefix('especialista')->group(function () {

        // Rutas para el tablero operativo interno del especialista, ahora con URL única y apuntando al método tablero del EspecialistaController.
        Route::get('/tablero', [EspecialistaController::class, 'tablero'])->name('especialista.tablero');

        // REUBICADA: La ruta de Wladimir para el tablero operativo interno del terapeuta con URL única
        Route::get('/gestion-interna', [TerapeutaController::class, 'tablero'])->name('terapeuta.gestion-interna');
    });

    // ==========================================
    //        OTROS MÓDULOS DE CONTROL
    // ==========================================

    // Rutas para la sala de espera, si se implementa esa funcionalidad, apuntando al método index del SalaEsperaController.
    Route::get('/sala-espera', [SalaEsperaController::class, 'index'])->name('sala.espera');
});
