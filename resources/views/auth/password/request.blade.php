<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar Contraseña - The Beauty Room</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    
    <!-- Tu CSS compilado por Vite -->
    @vite(['resources/css/auth.css'])
    
    <!-- Script de reCAPTCHA (se mantiene porque es un CDN externo) -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <!-- Forzar la imagen de fondo -->
    <style>
        body {
            background: linear-gradient(rgba(30, 25, 22, 0.85), rgba(30, 25, 22, 0.85)), 
                        url("{{ asset('asset/img/parts/fdp.jpg') }}") center/cover no-repeat fixed !important;
        }
    </style>
</head>
<body>

    <!-- Aquí pasamos el mensaje de sesión y el error al HTML usando atributos data-* -->
    <div class="tarjeta-unica" 
         data-session-status="{{ session('status') }}" 
         data-error-message="{{ $errors->first() }}">
        
        <div class="logo-header">
            <span class="estrellitas" style="font-size: 16px; line-height: 1;">✦</span>
            <small>RECUPERACIÓN</small>
        </div>
        
        <h2>¿Olvidaste tu contraseña?</h2>
        <p>Ingresa tu correo electrónico y te enviaremos los pasos para crear una nueva.</p>

        <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
            @csrf
            
            <label for="email">Correo electrónico</label>
            <input type="email" id="email" name="email" placeholder="tu@correo.com" required>
            @error('email') <span class="error-texto">{{ $message }}</span> @enderror

            <!-- Contenedor del reCAPTCHA -->
            <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}" style="margin-top: 5px; display: flex; justify-content: center;"></div>
            @error('captcha') <span class="error-texto" style="text-align: center;">{{ $message }}</span> @enderror

            <button type="submit" class="button-aceptar">ENVIAR ENLACE</button>
        </form>

        <div style="margin-top: 25px;">
            <a href="{{ route('login.view') }}" class="link-olvido" style="font-size: 13px;">← Volver al inicio de sesión</a>
        </div>
    </div>

    <!-- CDN de SweetAlert2 (necesario para que auth.js pueda usarlo) -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Llamada a tu archivo JS compilado por Vite -->
    @vite(['resources/js/auth.js'])
</body>
</html>