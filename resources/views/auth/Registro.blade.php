<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Beauty Room - Registro</title>
    @vite(['resources/css/auth.css'])
</head>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>

<script src="https://unpkg.com/lucide@latest"></script>
<body class="login-container">

    <main class="split-layout">
        <section class="image-side">
            <div class="overlay">
                <h1>Crea tu espacio.</h1>
                <p>THE BEAUTY ROOM</p>
            </div>
        </section>

        <section class="form-side">
            <div class="form-inner">
                <header>
                    <i data-lucide="sparkles" style="width: 16px; height: 16px; color: #8b5e3c; margin-right: 5px; display: inline-block; vertical-align: middle;"></i>
                    <small> BIENVENIDO</small>
                    <h1>Crea tu cuenta</h1>
                    <p>Únete a nuestra comunidad y reserva tu cita.</p>
                </header>

                <form id="registerForm" method="POST" action="{{ route('registro.create') }}">
                    @csrf

                    <div class="form-row">
                        <label for="username">NOMBRE COMPLETO</label>
                        <input type="text" id="username" name="username" value="{{ old('username') }}" required placeholder="Ej: Anyel Daniel">
                    </div>
                    
                    <div class="form-row">
                        <label for="email">CORREO ELECTRÓNICO</label>
                        <input type="email" id="email" name="email" value="{{ old('email') }}" required placeholder="correo@ejemplo.com">
                    </div>
                    
                    <div class="form-row">
                        <label for="telefono">TELÉFONO</label>
                        <input type="tel" id="telefono" name="telefono" value="{{ old('telefono') }}" placeholder="Ej: 600000000">
                    </div>
                    
                    <div class="form-row">
                        <label for="password">CONTRASEÑA</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>
                    
                    <div class="form-row">
                        <label for="confirmPassword">CONFIRMAR CONTRASEÑA</label>
                        <input type="password" id="confirmPassword" name="confirmPassword" required placeholder="••••••••">
                    </div>

                    <div class="g-recaptcha mb-3" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

                     @error('captcha')
                       <div class="text-danger mt-1">{{ $message }}</div>
                     @enderror
                    
                    <button type="submit" class="btn-primary">REGISTRARME</button>
                    
                    <p class="register-link">¿Ya tienes cuenta? <a href="{{ route('login.view') }}">Inicia sesión aquí</a></p>
                </form>
            </div>
        </section>
    </main>
     <script>
  lucide.createIcons();
</script>
</body>
</html>