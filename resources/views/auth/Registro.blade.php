<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registro de Usuario</title>
    <style>  
    /* Reseteo básico */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
}

body {
    display: flex;
    justify-content: center;
    align-items: center;
    min-height: 100vh;
    background-color: #f0f2f5; /* Un gris muy claro, estilo red social */
}

.register-container {
    background-color: #ffffff;
    padding: 40px;
    border-radius: 12px;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
    width: 100%;
    max-width: 400px;
}

.register-container h2 {
    text-align: center;
    color: #333;
    margin-bottom: 25px;
    font-size: 24px;
}

.input-group {
    margin-bottom: 15px;
}

.input-group label {
    display: block;
    margin-bottom: 5px;
    color: #555;
    font-size: 14px;
    font-weight: 500;
}

.input-group input {
    width: 100%;
    padding: 12px;
    border: 1px solid #ccc;
    border-radius: 8px;
    font-size: 14px;
    transition: border-color 0.3s ease, box-shadow 0.3s ease;
}

/* Efecto al hacer clic en el input */
.input-group input:focus {
    outline: none;
    border-color: #000000;
    box-shadow: 0 0 0 2px rgba(0, 0, 0, 0.1);
}

.btn-register {
    width: 100%;
    padding: 12px;
    background-color: #111111;
    color: white;
    border: none;
    border-radius: 8px;
    font-size: 16px;
    font-weight: 600;
    cursor: pointer;
    transition: background-color 0.3s ease;
    margin-top: 10px;
}

.btn-register:hover {
    background-color: #333333;
}

.error-message {
    color: #dc3545;
    font-size: 13px;
    margin-bottom: 10px;
    text-align: center;
    min-height: 18px; /* Evita que el formulario "salte" si el texto está vacío */
}

.login-link {
    text-align: center;
    margin-top: 20px;
    font-size: 14px;
    color: #666;
}

.login-link a {
    color: #111111;
    text-decoration: none;
    font-weight: 600;
}

.login-link a:hover {
    text-decoration: underline;
}
    </style>










</head>
<body>
    <div class="register-container">
        <h2>Crear Cuenta</h2>
        
        <!-- Cuando lo conectes a tu backend, añade action="/tu-ruta" y method="POST" -->
        <form id="registerForm" method="POST" action="{{ route('registro.create') }}">
            @csrf

            @if ($errors->any())
                <div class="error-message">
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="input-group">
                <label for="username">Nombre Completo</label>
                <input type="text" id="username" name="username" value="{{ old('username') }}" required placeholder="Ej: Anyel Daniel">
                @error('username')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="input-group">
                <label for="email">Correo Electrónico</label>
                <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                @error('email')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="input-group">
                <label for="telefono">Número de teléfono</label>
                <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 600000000" inputmode="numeric" pattern="[0-9]*" oninput="this.value=this.value.replace(/\D/g,'')">
                @error('telefono')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="input-group">
                <label for="password">Contraseña</label>
                <input type="password" id="password" name="password" required placeholder="Mínimo 8 caracteres">
                @error('password')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>
            
            <div class="input-group">
                <label for="confirmPassword">Confirmar Contraseña</label>
                <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="Repite tu contraseña">
                @error('confirmPassword')
                    <span class="error-message">{{ $message }}</span>
                @enderror
            </div>

            <div class="error-message" id="errorMessage"></div>
            
            <button type="submit" class="btn-register">Registrarse</button>

            
            <p class="login-link">¿Ya tienes cuenta? <a href="inicio">Inicia sesión aquí</a></p>
        </form>
    </div>

    <script src="script.js"></script>
    <script>
        // Este script recarga la página cuando el usuario vuelve atrás
        // para evitar un token CSRF antiguo y el error 419 Page Expired.
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward') {
                window.location.reload();
            }
        });
    </script>
</body>
</html>