<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso - The Beauty Room</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">

    <!-- Script de seguridad de Google reCAPTCHA -->
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>

    @vite(['resources/css/auth.css'])

    <style>
        body {
            background: linear-gradient(rgba(30, 35, 32, 0.75), rgba(30, 35, 32, 0.75)),
            url("{{ asset('asset/img/parts/fdp.jpg') }}") center/cover no-repeat fixed !important;
        }

        .fondo-panel {
            position: absolute;
            top: -10%;
            left: -10%;
            width: 120%;
            height: 120%;
            background-position: center;
            background-size: cover;
            transition: opacity 0.8s ease-in-out;
            z-index: 1;
        }

        .fondo-login {
            background-image: url("{{ asset('asset/img/parts/imagen-login.jpg') }}");
            opacity: 1;
        }

        .fondo-registro {
            background-image: url("{{ asset('asset/img/parts/manicura-tradicional.jpg') }}");
            opacity: 0;
        }

        .contenedor-formularios.activo .fondo-login {
            opacity: 0;
        }

        .contenedor-formularios.activo .fondo-registro {
            opacity: 1;
        }

        .capa-verde-filtro {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, rgba(163, 184, 153, 0.5), rgba(44, 53, 45, 0.65));
            z-index: 2;
        }

        .panel-texto-registro,
        .panel-texto-login {
            z-index: 3;
        }

        .error-texto {
            color: #d9534f;
            font-size: 11px;
            margin-top: -10px;
            font-weight: 600;
            display: block;
        }
    </style>
</head>

<body>

    <div id="alerta-personalizada" class="alerta-oculta"></div>

    <div class="contenedor-formularios" id="contenedor-principal">

        <!-- ============================================== -->
        <!-- LADO IZQUIERDO: FORMULARIO DE INICIO DE SESIÓN -->
        <!-- ============================================== -->
        <div class="tarjeta-login">
            <div class="logo-header"> </div>
            <h2>Bienvenida de vuelta</h2>

            <form method="POST" action="{{ route('login.post') }}" id="form-login">
                @csrf

                <label for="correo-electronico">Correo electrónico</label>
                <input type="email" id="correo-electronico" name="email" placeholder="tu@correo.com" value="{{ old('email') }}" required>
                @error('email') <span class="error-texto">{{ $message }}</span> @enderror

                <div class="password-header">
                    <label for="clave">Contraseña</label>
                    <a href="{{ route('passwordRequest.view') }}" class="link-olvido">¿Olvidaste tu contraseña?</a>
                </div>
                <div class="caja-input-clave">
                    <input type="password" id="clave" name="password" placeholder="••••••••" required>
                    <span class="btn-ver-clave" onclick="togglePassword('clave', this)">👁️</span>
                </div>
                @error('password') <span class="error-texto">{{ $message }}</span> @enderror

                <button type="submit" class="button-aceptar">INICIAR SESIÓN</button>

                <div class="separador"><span>O ingresa con</span></div>
                <button type="button" class="btn-google">
                    <img src="https://fonts.gstatic.com/s/i/productlogos/googleg/v6/24px.svg" alt="Google" width="16"> Google
                </button>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- LADO DERECHO: FORMULARIO DE REGISTRO           -->
        <!-- ============================================== -->
        <div class="tarjeta-registro">
            <div class="logo-header"> </div>
            <h2>Crea tu cuenta</h2>

            <form method="POST" action="{{ route('registro.create') }}" id="form-registro">
                @csrf

                <label for="username">Nombre completo</label>
                <input type="text" id="username" name="username" placeholder="Ej: Anyel Daniel" value="{{ old('username') }}" required>
                @error('username') <span class="error-texto">{{ $message }}</span> @enderror

                <label for="telefono">Teléfono</label>
                <input type="tel" id="telefono" name="telefono" placeholder="Ej: 04121234567" value="{{ old('telefono') }}" required>
                @error('telefono') <span class="error-texto">{{ $message }}</span> @enderror

                <label for="email_reg">Correo electrónico</label>
                <input type="email" id="email_reg" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required>
                @error('email') <span class="error-texto">{{ $message }}</span> @enderror

                <div class="grid-2-columnas">
                    <div>
                        <label for="password_reg">Contraseña</label>
                        <div class="caja-input-clave">
                            <input type="password" id="password_reg" name="password" placeholder="••••••••" required>
                            <span class="btn-ver-clave" onclick="togglePassword('password_reg', this)">👁️</span>
                        </div>
                    </div>
                    <div>
                        <label for="confirm-password">Confirmar</label>
                        <div class="caja-input-clave">
                            <input type="password" id="confirm-password" name="confirmPassword" placeholder="••••••••" required>
                            <span class="btn-ver-clave" onclick="togglePassword('confirm-password', this)">👁️</span>
                        </div>
                    </div>
                </div>
                @error('password') <span class="error-texto">{{ $message }}</span> @enderror

                <div class="caja-recaptcha">
                    <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                </div>
                @error('captcha') <span class="error-texto">{{ $message }}</span> @enderror

                <button type="submit" class="button-aceptar" style="margin-top: 5px;">REGISTRARME</button>
            </form>
        </div>

        <!-- ============================================== -->
        <!-- PANEL SUPERPUESTO DESLIZANTE                   -->
        <!-- ============================================== -->
        <div class="panel-superpuesto" id="mi-panel">

            <div class="fondo-panel fondo-login"></div>
            <div class="fondo-panel fondo-registro"></div>
            <div class="capa-verde-filtro"></div>

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
        </div>

    </div>

    <script src="{{ asset('js/login.js') }}"></script>

    @if($errors->has('username') || $errors->has('telefono') || $errors->has('captcha') || old('username'))
    <script>
        document.getElementById('contenedor-principal').classList.add('activo');
    </script>
    @endif

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        document.getElementById('form-registro').addEventListener('submit', function() {
            Swal.fire({
                title: 'Creando tu cuenta...',
                text: 'Estamos registrando tu perfil en The Beauty Room.',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
        });
    </script>

    @if(session('success'))
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'success',
                title: '¡Cuenta creada con éxito!',
                text: 'Ya eres parte de The Beauty Room. Por favor, inicia sesión con tus datos para comenzar a agendar tus citas.',
                confirmButtonText: 'Iniciar Sesión',
                confirmButtonColor: '#5A6B5D',
                background: '#FFFFFF',
                color: '#2C352D'
            });
        });
    </script>
    @endif

</body>
</html>