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

        // Verificamos si el usuario olvidó marcar la casilla del recaptcha.
        if (!$request->input('g-recaptcha-response')) {
            return back()
                ->withErrors(['captcha' => 'Por favor, marca la casilla de "No soy un robot".'])
                ->withInput();
        }

        // Se envía el código secreto a Google para verificar que sea un humano real.
        $response = Http::asForm()->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => env('RECAPTCHA_SECRET_KEY'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        // Si Google rechaza el código (es un bot o el tiempo expiró).
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
            'contraseña' => $validatedData['password'], // Se encripta en el modelo por el cast.
            'rol' => 'cliente',
        ]);

        try {
            Mail::to($usuario->correo)->send(new CuentaCreada($usuario));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error('Error enviando correo de bienvenida: ' . $e->getMessage());
        }

        return redirect()->route('login.view')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
    }

    public function login(Request $request)
    {
        // Validaciones de entrada.
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // Credenciales mapeadas para Laravel
        $credentials = [
            'correo' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        // autenticación nativa.
        if (Auth::attempt($credentials, $request->filled('remember'))) {

            // Si es correcto, regeneramos la sesión de manera segura.
            $request->session()->regenerate();
            $user = Auth::user();

            // Mapeo de roles según las rutas.
            $rutaDestino = match ($user->rol) {
                'admin'         => 'admin.dashboard',
                'recepcion'     => 'agenda', // Apunta al name('agenda') global tenemos en web.php
                'trabajador'    => 'agenda',
                'cliente'       => 'catalogo',
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

        // si el intento falla (clave o correo incorrectos).
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