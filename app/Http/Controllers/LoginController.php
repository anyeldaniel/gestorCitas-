<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\RegisterUserRequest;
use App\Models\User;



class LoginController extends Controller
{
    public function index()
    {
        return view('auth.login');
    }

    public function registro()
    {
        return view('auth.Registro');
    }   

     public function RegistroController(RegisterUserRequest $request){

          //$model = new User();

        $validatedData = $request->validated();

        $usuario = User::create([
            'nombre' => $validatedData['username'],
            'correo' => $validatedData['email'],
            'telefono' => $validatedData['telefono'],
            'contraseña' => Hash::make($validatedData['password']),
            'rol' => 'cliente',
        ]);

        return redirect()->route('inicio')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
     }


    

    public function login(Request $request)
    {
        // 1. Validaciones rápidas para el Login
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string|min:6',
        ]);

        // 2. Mapeamos las credenciales.
        // Ojo: La base de datos buscará "correo", pero para Laravel la clave de contraseña debe ser "password"
        $credentials = [
            'correo' => $request->input('email'),
            'password' => $request->input('password'),
        ];

        // 3. Buscar el usuario por correo primero
        // esta linea funciona pero se porque queda en rojo
    
        $user = User::query()->where('correo', $request->input('email'))->first();


         if (!$user || !Hash::check($request->input('password'), $user->getAuthPassword())) 
            {
                return back()->withErrors([
                    'email' => 'Credenciales invalidas.',
                ])->withInput($request->only('email'));
            }
        
       /*
       -----ES EL MISMO CODIGO DE ARRIBA PERO SEPARADO, PERO EL DE ARRIBA ES POR TEMAS DE SEGURIDAD-----
       if (! $user) {
            return back()->withErrors([
                'email' => 'La cuenta no existe.',
            ])->withInput($request->only('email'));
        }

        // 4. Verificar contraseña manualmente para dar un mensaje específico
        if (! Hash::check($request->input('password'), $user->getAuthPassword())) {
            return back()->withErrors([
                'password' => 'Contraseña equivocada.',
            ])->withInput($request->only('email'));
        }*/

        // 5. Autenticación exitosa
        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('catalogo');

    }
    }



