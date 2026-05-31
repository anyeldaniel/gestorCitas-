<?php
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () { return view('welcome');});


// Rutas de autenticación (auth) --------------------------------------------------------------------------

Route::get('/inicio', [LoginController::class, 'index']) ->name('inicio');
Route::get('/registro', [LoginController::class, 'registro']) ->name('registro.view');
Route::post('/login', [LoginController::class, 'login']) ->name('login');
Route::post('/registrado', [LoginController::class, 'RegistroController']) ->name('registro.create');
Route::get('/catalogo', function () { return view('clientes.catalogo');})->name('catalogo');
