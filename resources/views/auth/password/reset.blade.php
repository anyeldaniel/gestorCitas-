<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer nueva contraseña - The Beauty Room</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <script src="https://unpkg.com/lucide@latest"></script>
    
    <!-- CDN de SweetAlert2 -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    @vite(['resources/css/auth.css', 'resources/js/auth.js'])

    <!-- Fondo inyectado para que Vite no lo rompa -->
    <style>
        .body-reset {
            background: linear-gradient(rgba(30, 35, 32, 0.75), rgba(30, 35, 32, 0.75)),
            url("{{ asset('asset/img/parts/fdp.jpg') }}") center/cover no-repeat fixed !important;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
    </style>
</head>
<body class="body-reset">

    <!-- EL DIV OCULTO AHORA ESTÁ AL PRINCIPIO -->
    <div id="reset-config" 
         data-success="{{ session('status') ? '1' : '0' }}" 
         data-message="{{ session('status') }}" 
         style="display: none;">
    </div>

    <div class="tarjeta-unica">
        
        <!-- Logo Header -->
        <div class="logo-header" style="justify-content: center; margin-bottom: 20px;">
            <i data-lucide="sparkles" style="width: 16px; height: 16px; color: #B59B74;"></i>
            <span>THE BEAUTY ROOM</span>
        </div>

        <h2>Crea tu nueva clave</h2>
        
        <!-- ============================================= -->
        <!-- BLOQUE DE DEBUG: Muestra los errores en pantalla -->
        <!-- ============================================= -->
        @if ($errors->any())
            <div style="background-color: #f8d7da; color: #721c24; padding: 10px; margin-bottom: 15px; border-radius: 5px; font-size: 12px; border: 1px solid #f5c6cb; text-align: left;">
                <strong>Ocurrió un error:</strong>
                <ul style="margin: 5px 0 0 0; padding-left: 20px;">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
        <!-- ============================================= -->

        <p>Un nuevo comienzo para seguir cuidándote.<br>Elige una clave segura para tu cuenta.</p>

        <form id="reset-password-form" method="POST" action="{{ route('password.update') }}">
            @csrf
            
            <input type="hidden" name="token" value="{{ $token }}">
            
            <!-- Correo (Lo dejamos readonly para que no lo cambien por error) -->
            <div class="form-row" style="margin-bottom: 15px;">
                <label for="email">CORREO ELECTRÓNICO</label>
                <input id="email" name="email" type="email" value="{{ request()->email ?? old('email') }}" readonly style="background-color: #f5f5f5; cursor: not-allowed;">
            </div>

            <!-- Nueva Clave -->
            <div class="form-row" style="margin-bottom: 15px;">
                <label for="password">NUEVA CLAVE</label>
                <div class="caja-input-clave">
                    <input id="password" name="password" type="password" placeholder="Ingresa tu nueva clave" required>
                    <button type="button" class="btn-ver-clave" onclick="togglePassword('password', this)">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <!-- Confirmar Clave -->
            <div class="form-row" style="margin-bottom: 15px;">
                <label for="password_confirmation">CONFIRMAR NUEVA CLAVE</label>
                <div class="caja-input-clave">
                    <input id="password_confirmation" name="password_confirmation" type="password" placeholder="Vuelve a escribir tu nueva clave" required>
                    <button type="button" class="btn-ver-clave" onclick="togglePassword('password_confirmation', this)">
                        <i data-lucide="eye"></i>
                    </button>
                </div>
            </div>

            <!-- Validaciones -->
            <div id="password-feedback" class="feedback-total" style="display: none;">
                <span id="length-req"></span><br>
                <span id="alpha-req"></span><br>
                <span id="special-req"></span>
            </div>
            <div id="strength-text"></div>

            <!-- Botón Guardar -->
            <button type="submit" class="button-aceptar" style="margin-top: 20px;">
                GUARDAR NUEVA CLAVE &nbsp; <i data-lucide="arrow-right" style="width: 14px; height: 14px; vertical-align: middle;"></i>
            </button>
        </form>

        <a href="{{ route('login.view') }}" class="link-volver">
            <i data-lucide="arrow-left" style="width: 14px; height: 14px;"></i> Volver al inicio de sesión
        </a>

        <!-- Footer de seguridad -->
        <div class="texto-seguridad" style="display: flex; align-items: center; justify-content: center; gap: 5px; margin-top: 25px; text-align: center;">
            <i data-lucide="lock" style="width: 12px; height: 12px; flex-shrink: 0;"></i>
            <span style="font-size: 11px; color: #AFAFAF;">Tu bienestar empieza con una cuenta segura.</span>
        </div>

    </div>

</body>
</html>