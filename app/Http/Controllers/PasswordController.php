<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PasswordController extends Controller
{
    public function VerifiCorreo(Request $request)
    {
        // 1. Verificamos si el usuario olvidó marcar la casilla
        if (!$request->input('g-recaptcha-response')) {
            return back()
                ->withErrors(['captcha' => 'Por favor, marca la casilla de "No soy un robot".'])
                ->withInput();
        }

        // 2. Enviamos el código secreto a Google para verificar que sea un humano real
        $response = Http::asForm()->timeout(10)->post('https://www.google.com/recaptcha/api/siteverify', [
            'secret' => config('services.recaptcha.secret_key'),
            'response' => $request->input('g-recaptcha-response'),
            'remoteip' => $request->ip(),
        ]);

        // 3. Si Google rechaza el código (es un bot o el tiempo expiró)
        if (!$response->successful() || $response->json('success') !== true) {
            Log::warning('Google rechazó la verificación reCAPTCHA durante la recuperación de contraseña.', [
                'error_codes' => $response->json('error-codes', []),
                'http_status' => $response->status(),
            ]);

            return back()
                ->withErrors(['captcha' => 'La validación del CAPTCHA falló. Intenta de nuevo.'])
                ->withInput();
        }

        $request->validate([ 
            'email' => 'required|email',
        ]);

        $email = $request->input('email');
        $user = User::where('correo', $email)->first();

        if (! $user) {
            return back()->withErrors(['email' => 'El correo no está registrado en el sistema.'])->withInput();
        }

        $status = Password::broker('users')->sendResetLink([
            'correo' => $email,
        ]);

        if ($status !== Password::RESET_LINK_SENT) {
            return back()->withErrors(['email' => trans($status)])->withInput();
        }

        return back()->with('status', 'Hemos enviado un enlace de restablecimiento a tu correo electrónico.');
    }

    public function resetPassword(Request $request)
    {
        $request->validate([
            'token' => 'required',
            'email' => 'required|email',
            'password' => [
                'required',
                'string',
                'min:8',
                'max:20',
                'regex:/[a-zA-Z]/',
                'regex:/[0-9]/',
                'regex:/[!@#$%^&*(),.?":{}|<>]/',
                'confirmed',
            ],
            'password_confirmation' => 'required|string|min:8|max:20|same:password',
        ], [
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password.max' => 'La contraseña no puede tener más de 20 caracteres.',
            'password.regex' => 'La contraseña debe incluir letras, números y un carácter especial.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
            'password_confirmation.same' => 'Las contraseñas no coinciden.',
        ]);

        $email = $request->input('email');
        $user = User::where('correo', $email)->first();
        
        if (! $user) {
            return back()->withErrors(['email' => 'El correo no está registrado en el sistema.'])->withInput();
        }

        $resetCredentials = $request->only('email', 'password', 'password_confirmation', 'token');
        $resetCredentials['correo'] = $resetCredentials['email'];
        unset($resetCredentials['email']);

        $status = Password::broker('users')->reset(
            $resetCredentials,
            function ($user, $password) {
                $user->contraseña = $password;
                $user->setRememberToken(Str::random(60)); // Opcional: Genera un nuevo token de "remember me" para invalidar sesiones anteriores
                $user->save();
            }
        );
        
        if ($status == Password::PASSWORD_RESET) {
            // ¡Éxito! Redirigimos al login con el mensaje de éxito.
            return redirect()->route('login.view')->with('status', '¡Tu nueva clave se ha creado con éxito!');
        }

        // Si falló (el token expiró, no coincide, etc.), lo devolvemos con error
        return back()->withErrors(['email' => trans($status)])->withInput();
    }
}