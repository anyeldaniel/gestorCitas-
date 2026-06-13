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

  /*  public function registro()
    {
        return view('auth.Registro');
    }   */

     public function RegistroController(RegisterUserRequest $request){

          //$model = new User();

        $validatedData = $request->validated();

        $usuario = User::create([
            'nombre' => $validatedData['username'],
            'correo' => $validatedData['email'],
            'telefono' => $validatedData['telefono'],
            'contraseña' =>$validatedData['password'],//Te quité el hash::make anyel, para que se haga la encriptación de la contraseña directamente desde el modelo, como buena práctica de MVC
            'rol' => 'cliente',
        ]);

       return redirect()->route('login')->with('success', 'Registro exitoso. Ahora puedes iniciar sesión.');
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
    
        $user = User::query()->where('correo', $request->input('email'))->first();


         if (!$user || !Hash::check($request->input('password'), $user->getAuthPassword())) 
            {
                return back()->withErrors([
                    'email' => 'Credenciales invalidas.',
                ])->withInput($request->only('email'));
            }

        // 5. Autenticación exitosa
        Auth::login($user);
        $request->session()->regenerate();

       // 6. Filtro para identificar el rol y asignar la ruta
    
    $rutaDestino = match ($user->rol) {
        'admin'         => 'admin.dashboard',    // Nombre de la ruta para el admin
        'recepcionista' => 'recepcion.agenda',    // Nombre de la ruta para el recepcionista
        'trabajador'    => 'especialista.tablero',   // Nombre de la ruta para el trabajador
        'cliente'       => 'catalogo',           // El cliente va al catálogo
        default         => 'login',              // Por si hay un usuario sin rol o con rol erróneo
    };

    // 7. Medida de seguridad: Si el rol no existe, cerramos la sesión y lo devolvemos
    if ($rutaDestino === 'login') {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        return redirect()->route('login')->withErrors([
            'email' => 'Usuario sin permisos asignados.',
        ]);
    }

    // 8. Redirección final a la ruta que le corresponde
    return redirect()->route($rutaDestino);

    }

// función para cerrar la sesión del usuario

    public function logout(Request $request)
    {
        // El Controlador le ordena al Modelo/Auth limpiar la sesión activa
        Auth::logout();

        // Destruye los datos de la sesión en el servidor
        $request->session()->invalidate();

        // Regenera el token CSRF para evitar vulnerabilidades en formularios futuros
        $request->session()->regenerateToken();

        // Redirige a la ruta de inicio o login usando el nombre asignado
        return redirect()->route('login');
    }

    }