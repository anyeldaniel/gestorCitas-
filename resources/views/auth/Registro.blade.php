<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>The Beauty Room - Registro</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])

    <!-- Fondo de pantalla completa: Foto de la manicura -->
    <style>
        body {
            background: linear-gradient(rgba(30, 25, 22, 0.75), rgba(30, 25, 22, 0.75)), 
                        url("{{ asset('asset/img/parts/manicura-tradicional.jpg') }}") center/cover no-repeat fixed !important;
        }
    </style>
</head>

<body class="login-container">
    <main class="contenedor-formularios" id="contenedor-principal">

        <!-- ============================================== -->
        <!-- FORMULARIO DE REGISTRO (Visible por defecto)   -->
        <!-- ============================================== -->
        <section class="tarjeta-registro" style="opacity: 1; visibility: visible; transform: translateX(100%); z-index: 5;" aria-labelledby="titulo-registro">
            <header class="logo-header"></header>
            <h2 id="titulo-registro">Crea tu cuenta</h2>

            <form method="POST" action="{{ route('registro.create') }}" id="form-registro">
                @csrf
                
                <!-- Bloque de errores generales -->
                @if ($errors->any())
                    <div style="background-color: #f8d7da; color: #721c24; padding: 10px; border-radius: 8px; font-size: 12px;">
                        <ul style="margin: 0; padding-left: 15px;">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <fieldset class="grupo-campos">
                    <label for="username">Nombre completo</label>
                    <input type="text" id="username" name="username" placeholder="Ej: Anyel Daniel" value="{{ old('username') }}" required autocomplete="name">
                    
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej: 04121234567" value="{{ old('telefono') }}" required autocomplete="tel">

                    <label for="email_reg">Correo electrónico</label>
                    <input type="email" id="email_reg" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required autocomplete="email">

                    <div class="grid-2-columnas">
                        <div class="caja-input-clave">
                            <label for="password">Contraseña</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="btn-ver-clave" aria-label="Mostrar contraseña">👁️</button>
                        </div>
                        <div class="caja-input-clave">
                            <label for="confirm-password">Confirmar</label>
                            <input type="password" id="confirm-password" name="confirmPassword" placeholder="••••••••" required autocomplete="new-password">
                            <button type="button" class="btn-ver-clave" aria-label="Mostrar contraseña">👁️</button>
                        </div>
                    </div>

                    <div class="caja-recaptcha">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                    </div>
                </fieldset>

                <button type="submit" class="button-aceptar">REGISTRARME</button>

                <!-- Botón para ir al Login (siempre visible aquí) -->
                <a href="{{ route('login.view') }}" class="btn-cambio-movil" style="display: block; text-align: center; text-decoration: none; line-height: 2;">
                    ¿Ya tienes cuenta? Inicia Sesión
                </a>
            </form>
        </section>

        <!-- ============================================== -->
        <!-- PANEL DERECHO CON LA FOTO DE LA MANICURA       -->
        <!-- ============================================== -->
        <aside class="panel-superpuesto" id="mi-panel" style="transform: translateX(-100%); border-radius: 0 150px 150px 0;">
            
            <!-- Inyectamos la foto de la manicura en el panel -->
            <div class="fondo-panel fondo-registro" style="background-image: url('{{ asset('asset/img/parts/manicura-tradicional.jpg') }}'); opacity: 1;" aria-hidden="true"></div>
            
            <div class="capa-verde-filtro" aria-hidden="true"></div>

            <div class="panel-texto-login" style="opacity: 1; visibility: visible; transform: translateX(0);">
                <h2>The Beauty Room</h2>
                <p>Accede a tu perfil para gestionar tus reservas y descubrir nuevas experiencias de relajación.</p>
                <a href="{{ route('login.view') }}" class="btn-outline" style="text-decoration: none; display: inline-block; text-align: center;">Iniciar Sesión</a>
            </div>
        </aside>

    </main>

    <!-- Scripts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        document.getElementById('form-registro').addEventListener('submit', function() {
            Swal.fire({
                title: 'Creando tu cuenta...',
                text: 'Estamos registrando tu perfil en The Beauty Room.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
        });
    </script>
</body>
</html>