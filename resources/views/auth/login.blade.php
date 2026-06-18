<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Beauty Room - Iniciar sesión</title>
    @vite(['resources/css/auth.css'])
</head>
<script src="https://unpkg.com/lucide@latest"></script>
<body class="login-container">

    <main class="split-layout">
        <section class="image-side">
            <div class="overlay">
                <h1>Un momento para ti.</h1>
                <p>THE BEAUTY ROOM</p>
            </div>
        </section>

        <section class="form-side">
            <div class="form-inner">
                <header>
                    <i data-lucide="sparkles" style="width: 16px; height: 16px; color: #8b5e3c; margin-right: 5px; display: inline-block; vertical-align: middle;"></i>
                    <small> THE BEAUTY ROOM</small>
                    <h1>Bienvenida de vuelta</h1>
                    <p>Accede a tu cuenta para gestionar tus reservas.</p>
                </header>

                <form id="login-form" method="POST" action="{{ route('login.post') }}">
                    @csrf
                    
                    <div class="form-row">
                        <label for="email">CORREO ELECTRÓNICO</label>
                        <input id="email" name="email" type="email" placeholder="tu@correo.com" required>
                    </div>

                    <div class="form-row">
                        <div class="password-header">
                            <label for="password">CONTRASEÑA</label>
                            <a href="#" class="forgot-pass">¿Olvidaste la contraseña?</a>
                        </div>
                        <input id="password" name="password" type="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-primary" style="margin-top: 10px;">INICIAR SESIÓN</button>
                    
                    <p class="register-link">¿Aún no tienes cuenta? <a href="{{ route('registro.view') }}">Regístrate aquí</a></p>
                </form>
            </div>
        </section>
    </main>
    
    <script>
        lucide.createIcons();
    </script>
</body>
</html>