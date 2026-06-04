<?php
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('welcome');});


// Rutas de autenticación (auth) --------------------------------------------------------------------------

Route::get('/inicio', [LoginController::class, 'index']) ->name('inicio');
Route::view('/Registro', 'auth.Registro')->name('registro.view'); // Esta estructura de ruta es para redirigir a vistas sin llevar info
Route::post('/login', [LoginController::class, 'login']) ->name('login');
Route::post('/registrado', [LoginController::class, 'RegistroController']) ->name('registro.create');
Route::view('/catalogo', 'clientes.catalogo')->name('catalogo');
Route::view('/admin', 'admin.dashboard')->name('admin.dashboard');
// faltarian las dos rutas del recepcionista y especialista pero
//hay que esperar a que el admin los pueda registrar



// RUTAS DEL CLIENTE (si se necesita segmentar tambien estas rutas se hacen)--------------------------------------------------------------------------


//-------- rutas del head (cabezal)-----------------------------------------------------------------------------------

Route::view('/reserva', 'clientes.reserva')->name('clientes.reserva');


















