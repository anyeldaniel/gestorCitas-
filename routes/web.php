<?php
use App\Http\Controllers\LoginController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/inicio', [LoginController::class, 'index']);
Route::get('/registro', [LoginController::class, 'registro']);
//Route::post('/login', [LoginController::class, 'login']);



