<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - The Beauty Room</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    
    @vite(['resources/css/auth.css'])
    
    <!-- Mantenemos el script de reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    <style>
        body {
            background: linear-gradient(rgba(30, 35, 32, 0.75), rgba(30, 35, 32, 0.75)), 
                        url("{{ asset('asset/img/parts/fondodepantalla.jpg') }}") center/cover no-repeat fixed !important;
        }
        .tarjeta-unica {
            background-color: #FFFFFF;
            border-radius: 20px;
            padding: 50px;
            width: 100%;
            max-width: 450px;
            box-shadow: 0 25px 50px rgba(0, 0, 0, 0.3);
            display: flex;
            flex-direction: column;
            align-items: center;
        }
        .tarjeta-unica form { width: 100%; }
        .tarjeta-unica h2 { font-size: 28px; margin-bottom: 10px; }
        .tarjeta-unica p { text-align: center; font-size: 13px; color: #727A74; margin-bottom: 25px; line-height: 1.6; }
        .mensaje-exito { color: #588157; font-weight: 600; font-size: 13px; margin-bottom: 15px; text-align: center; }
        .error-texto { color: #d9534f; font-size: 11px; margin-top: 5px; font-weight: 600; display: block; }
    </style>
</head>
<body>

    <div class="tarjeta-unica">
        <div class="logo-header">
            <span class="estrellitas">✨</span>
            <small>RECUPERACIÓN</small>
        </div>
        <h2>¿Olvidaste tu contraseña?</h2>
        <p>Ingresa tu correo electrónico y te enviaremos los pasos para crear una nueva.</p>

        <!-- Mensaje de éxito de sesión -->
        @if (session('status'))
            <div class="mensaje-exito">{{ session('status') }}</div>
        @endif

        <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <label for="email">Correo electrónico</label>
            <!-- Usamos name="email" o "correo" dependiendo de lo que espere tu PasswordController -->
            <input type="email" id="email" name="email" placeholder="tu@correo.com" required style="margin-bottom: 15px;">
            @error('email') <span class="error-texto">{{ $message }}</span> @enderror

            <!-- Contenedor del reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" style="margin-bottom: 15px; display: flex; justify-content: center;"></div>
            @error('captcha') <span class="error-texto" style="text-align: center;">{{ $message }}</span> @enderror

            <button type="submit" class="button-aceptar" style="margin-top: 10px;">ENVIAR ENLACE</button>
        </form>

        <div style="margin-top: 25px;">
            <a href="{{ route('login.view') }}" class="link-olvido" style="font-size: 13px;">← Volver al inicio de sesión</a>
        </div>
    </div>

</body>
</html>
