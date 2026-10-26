<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - The Beauty Room</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])

    <!-- Fondo de pantalla completa (Foto 1) -->
    <style>
        body {
            background: linear-gradient(rgba(30, 25, 22, 0.75), rgba(30, 25, 22, 0.75)), 
                        url("{{ asset('asset/img/parts/fdp.jpg') }}") center/cover no-repeat fixed !important;
        }
    </style>
</head>

<body>
    <main class="contenedor-formularios" id="contenedor-principal">

        <!-- ============================================== -->
        <!-- FORMULARIO DE INICIO DE SESIÓN                 -->
        <!-- ============================================== -->
        <section class="tarjeta-login" aria-labelledby="titulo-login">
            <header class="logo-header"></header>
            <h2 id="titulo-login">Bienvenida de vuelta</h2>

            <form method="POST" action="{{ route('login.post') }}" id="form-login">
                @csrf
                
                <fieldset class="grupo-campos">
                    <label for="correo-electronico">Correo electrónico</label>
                    <input type="email" id="correo-electronico" name="email" placeholder="tu@correo.com" value="{{ old('email') }}" required autocomplete="email">
                    @error('email') <span class="error-texto" role="alert">{{ $message }}</span> @enderror

                    <div class="password-header">
                        <label for="clave">Contraseña</label>
                        <a href="{{ route('passwordRequest.view') }}" class="link-olvido">¿Olvidaste tu contraseña?</a>
                    </div>
                    
                    <div class="caja-input-clave">
                        <input type="password" id="clave" name="password" placeholder="••••••••" required autocomplete="current-password">
                        <button type="button" class="btn-ver-clave" aria-label="Mostrar contraseña">👁️</button>
                    </div>
                    @error('password') <span class="error-texto" role="alert">{{ $message }}</span> @enderror
                </fieldset>

                <button type="submit" class="button-aceptar">INICIAR SESIÓN</button>
                
                <!-- Botón móvil para cambiar a registro -->
                <button type="button" class="btn-cambio-movil" id="btn-ir-registro">
                    ¿No tienes cuenta? Regístrate
                </button>

                <div class="separador"><span>O ingresa con</span></div>
                
                <button type="button" class="btn-google">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Logo de Google" width="16" height="16"> Google
                </button>
            </form>
        </section>

        <!-- ============================================== -->
        <!-- FORMULARIO DE REGISTRO                         -->
        <!-- ============================================== -->
        <section class="tarjeta-registro" aria-labelledby="titulo-registro">
            <header class="logo-header"></header>
            <h2 id="titulo-registro">Crea tu cuenta</h2>

            <form method="POST" action="{{ route('registro.create') }}" id="form-registro">
                @csrf
                
                <fieldset class="grupo-campos">
                    <label for="username">Nombre completo</label>
                    <input type="text" id="username" name="username" placeholder="Ej: Anyel Daniel" value="{{ old('username') }}" required autocomplete="name">
                    @error('username') <span class="error-texto" role="alert">{{ $message }}</span> @enderror

                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej: 04121234567" value="{{ old('telefono') }}" required autocomplete="tel">
                    @error('telefono') <span class="error-texto" role="alert">{{ $message }}</span> @enderror

                    <label for="email_reg">Correo electrónico</label>
                    <input type="email" id="email_reg" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required autocomplete="email">
                    @error('email') <span class="error-texto" role="alert">{{ $message }}</span> @enderror

                    <div class="grid-2-columnas">
                        <div class="caja-input-clave">
                            <label for="password_reg">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="btn-ver-clave" aria-label="Mostrar contraseña">👁️</button>
                        </div>
                        <div class="caja-input-clave">
                            <label for="confirm-password">Confirmar</label>
                            <input type="password" id="confirm-password" name="confirmPassword" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="btn-ver-clave" aria-label="Mostrar contraseña">👁️</button>
                        </div>
                    </div>
                    @error('password') <span class="error-texto" role="alert">{{ $message }}</span> @enderror

                    <div class="caja-recaptcha">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                    </div>
                    @error('captcha') <span class="error-texto" role="alert">{{ $message }}</span> @enderror
                </fieldset>

                <button type="submit" class="button-aceptar">REGISTRARME</button>

                <!-- Botón móvil para volver al login -->
                <button type="button" class="btn-cambio-movil" id="btn-ir-login">
                    ¿Ya tienes cuenta? Inicia Sesión
                </button>
            </form>
        </section>

        <!-- ============================================== -->
        <!-- PANEL DESLIZANTE CON IMÁGENES (Foto 2)         -->
        <!-- ============================================== -->
        <aside class="panel-superpuesto" id="mi-panel">
            
            <!-- AQUÍ INYECTAMOS LAS IMÁGENES DENTRO DEL PANEL VERDE -->
            <div class="fondo-panel fondo-login" style="background-image: url('{{ asset('asset/img/parts/imagen-login.jpg') }}');" aria-hidden="true"></div>
            <div class="fondo-panel fondo-registro" style="background-image: url('{{ asset('asset/img/parts/manicura-tradicional.jpg') }}');" aria-hidden="true"></div>
            
            <div class="capa-verde-filtro" aria-hidden="true"></div>

            <div class="panel-texto-registro">
                <h2>Un momento para ti.</h2>
                <p>Únete a nuestra comunidad y reserva tu cita en nuestro santuario de bienestar y estética avanzada.</p>
                <button type="button" class="btn-outline" id="btn-panel-registro">Crear Cuenta</button>
            </div>

            <div class="panel-texto-login">
                <h2>The Beauty Room</h2>
                <p>Accede a tu perfil para gestionar tus reservas y descubrir nuevas experiencias de relajación.</p>
                <button type="button" class="btn-outline" id="btn-panel-login">Iniciar Sesión</button>
            </div>
        </aside>

    </main>

    <!-- Alerta de éxito de Laravel -->
    @if(session('success'))
        <div id="session-success" data-message="{{ session('success') }}" style="display: none;"></div>
    @endif
</body>
</html>