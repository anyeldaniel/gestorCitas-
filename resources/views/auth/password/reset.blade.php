<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Establecer nueva contraseña - The Beauty Room</title>
    @vite(['resources/css/auth.css'])
</head>
<script src="https://unpkg.com/lucide@latest"></script>
<body class="login-container">

    <main class="split-layout">
        <section class="image-side">
            <div class="overlay">
                <h1>Seguridad ante todo.</h1>
                <p>THE BEAUTY ROOM</p>
            </div>
        </section>

        <section class="form-side">
            <div class="form-inner">
                <header>
                    <i data-lucide="shield-check" style="width: 16px; height: 16px; color: #8b5e3c; margin-right: 5px; display: inline-block; vertical-align: middle;"></i>
                    <small>NUEVA CONTRASEÑA</small>
                    <h1>Crea tu nueva clave</h1>
                    <p style="margin-bottom: 20px;">Por seguridad, elige una contraseña que no hayas usado antes.</p>
                </header>

                <form id="reset-password-form" method="POST" action="{{ route('password.update') }}">
                    @csrf
                    
                    <input type="hidden" name="token" value="{{ $token }}">
                    
                    <div class="form-row">
                        <label for="email">CORREO ELECTRÓNICO</label>
                        <input id="email" name="email" type="email" placeholder="tu@correo.com" required>
                    </div>

                    <div class="form-row">
                        <label for="password">NUEVA CONTRASEÑA</label>
                        <input id="password" name="password" type="password" placeholder="••••••••" required>
                    </div>

                    <div class="form-row">
                        <label for="password_confirmation">CONFIRMAR NUEVA CONTRASEÑA</label>
                        <input id="password_confirmation" name="password_confirmation" type="password" placeholder="••••••••" required>
                    </div>

                    <button type="submit" class="btn-primary">ACTUALIZAR CONTRASEÑA</button>
                </form>
            </div>
        </section>
    </main>

    <script>
        lucide.createIcons();
    </script>
</body>
</html>