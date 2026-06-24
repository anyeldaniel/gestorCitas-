<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;
use App\Mail\CuentaCreada;
use Illuminate\Support\Facades\Http;

class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function RegistroController(RegisterUserRequest $request)
    {

            // 1. Verificamos si el usuario olvidó marcar la casilla
         if (!$request->input('g-recaptcha-response')) {
         return back()
        ->withErrors(['captcha' => 'Por favor, marca la casilla de "No soy un robot".'])
        ->withInput();
                }

// 2. Enviamos el código secreto a Google para verificar que sea un humano real
     $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
     'secret' => env('RECAPTCHA_SECRET_KEY'),
     'response' => $request->input('g-recaptcha-response'),
     'remoteip' => $request->ip(),
                 ]);

// 3. Si Google rechaza el código (es un bot o el tiempo expiró)
          if (!$response->json('success')) {
         return back()
        ->withErrors(['captcha' => 'La validación del CAPTCHA falló. Intenta de nuevo.'])
        ->withInput();
                      }


        $validatedData = $request->validated();

        $usuario = User::create([
            'nombre' => $validatedData['username'],
            'correo' => $validatedData['email'],
            'telefono' => $validatedData['telefono'],
            'contraseña' => $validatedData['password'], // Se encripta en el modelo por el cast
            'rol' => 'cliente',
        ]);

      Mail::to($usuario->correo)->send(new CuentaCreada($usuario));

        return redirect()->route('login.view')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }

    public function login(Request $request)
    {
        // 1. Validaciones de entrada
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 2. Credenciales mapeadas para Laravel
        // Importante: 'password' DEBE llamarse así aquí para que el framework lo procese internamente,
        // aunque en tu modelo apunte a 'contraseña'
        $credentials = [
            'correo' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        // 3. Intento de autenticación nativa
        // Al pasarle $credentials, Laravel usa los métodos personalizados que añadimos al modelo User
        if (Auth::attempt($credentials, $request->filled('remember'))) {
            
            // Si es correcto, regeneramos la sesión de manera segura
            $request->session()->regenerate();
            $user = Auth::user();

            // 4. Mapeo de roles según tus rutas actuales
            $rutaDestino = match ($user->rol) {
                'admin'         => 'admin.dashboard',
                'recepcion'     => 'agenda', // Apunta al name('agenda') global que tienes en web.php
                'trabajador'    => 'agenda',
                'cliente'       => 'catalogo', // Tu ruta de catálogo corregida
                default         => 'login',
            };

            if ($rutaDestino === 'login') {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login.view')->withErrors([
                    'email' => 'Usuario sin permisos asignados.',
                ]);
            }

            return redirect()->route($rutaDestino);
        }

        // 4. Si el intento falla (clave o correo incorrectos)
        return back()->withErrors([
            'email' => 'Credenciales inválidas.',
        ])->withInput($request->only('email'));
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken(); 
        return redirect()->route('login.view');
    }
}