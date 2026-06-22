<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;

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
}
