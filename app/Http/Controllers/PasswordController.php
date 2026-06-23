<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Str;

class PasswordController extends Controller
{
    public function VerifiCorreo(Request $request)
    {


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
            'password' => 'required|confirmed|min:8',
            'password_confirmation' => 'required',
        ], [
            
            
            'email.required' => 'El correo es obligatorio.',
            'email.email' => 'El correo electrónico debe ser una dirección de correo válida.',
            'password.required' => 'La contraseña es obligatoria.',
            'password.confirmed' => 'Las contraseñas no coinciden.',
            'password.min' => 'La contraseña debe tener al menos 8 caracteres.',
            'password_confirmation.required' => 'La confirmación de la contraseña es obligatoria.',
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
        // ¡Éxito! El token era válido y la clave se cambió.
        return redirect('login')->with('status', '¡Tu contraseña ha sido actualizada con éxito!');
    }

    // Si falló (el token expiró, no coincide, etc.), lo devolvemos con error
    return back()->withErrors(['email' => trans($status)])->withInput();

       
}
}