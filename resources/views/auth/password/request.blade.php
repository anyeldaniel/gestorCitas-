<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Recuperar contraseña - The Beauty Room</title>
    @vite(['resources/css/auth.css'])
</head>
<script src="https://unpkg.com/lucide@latest"></script>
<body class="login-container">

<script src="https://www.google.com/recaptcha/api.js" async defer></script>

    <main class="split-layout">
        <section class="image-side">
            <div class="overlay">
                <h1>Recupera tu acceso.</h1>
                <p>THE BEAUTY ROOM</p>
            </div>
        </section>

        <section class="form-side">
            <div class="form-inner">
                <header>
                    <i data-lucide="key-round" style="width: 16px; height: 16px; color: #8b5e3c; margin-right: 5px; display: inline-block; vertical-align: middle;"></i>
                    <small>RECUPERACIÓN</small>
                    <h1>¿Olvidaste tu contraseña?</h1>
                    <p style="margin-bottom: 20px;">Ingresa tu correo electrónico y te enviaremos los pasos para crear una nueva.</p>
                </header>

                <form id="forgot-password-form" method="POST" action="{{ route('password.email') }}">
                    @csrf
                    
                    <div class="form-row">
                        <label for="email">CORREO ELECTRÓNICO</label>
                        <input id="email" name="email" type="email" placeholder="tu@correo.com" required>
                          @error('email')
                            <div style="color: red; font-size: 0.9em; margin-top: 5px;">
                             {{ $message }}
                                </div>
                               @enderror
                               
                               @if (session('status'))
                           <div style="color: green; font-weight: bold; margin-bottom: 15px;">
                             {{ session('status') }}
                            </div>
                            @endif
                    </div>

                    <div class="g-recaptcha mb-3" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>

                     @error('captcha')
                       <div class="text-danger mt-1">{{ $message }}</div>
                     @enderror



                    <button type="submit" class="btn-primary">ENVIAR ENLACE</button>
                    
                    <p class="register-link">
                        <a href="{{ route('login.view') }}">Volver al inicio de sesión</a>
                    </p>
                </form>
            </div>
        </section>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>