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
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    @vite(['resources/css/auth.css', 'resources/js/auth.js'])
</head>

<body class="body-registro">
    
    <!-- Contenedor maestro de dos columnas con altura fluida -->
    <div class="caja-registro-maestra">
        
        <!-- LADO IZQUIERDO: FOTO (Estiramiento fluido junto al formulario) -->
        <div class="registro-foto">
            <div class="capa-verde-filtro"></div>
            <div class="registro-texto">
                <h2>The Beauty Room</h2>
                <p>Accede a tu perfil para gestionar tus reservas y descubrir nuevas experiencias de relajación.</p>
                <a href="{{ route('login.view') }}" class="btn-outline">INICIAR SESIÓN</a>
            </div>
        </div>

        <!-- LADO DERECHO: FORMULARIO (Sin scroll interno, crece de forma natural) -->
        <div class="registro-formulario">
            <div class="registro-scroll">
                <div class="cabecera-formulario">
                    <span class="subtitulo-experiencia">comienza tu experiencia</span>
                    <h2 class="registro-titulo">Crea tu cuenta</h2>
                </div>

                <form method="POST" action="{{ route('registro.create') }}" id="form-registro" class="registro-form">
                    @csrf
                    
                    @if ($errors->any())
                        <div class="alerta-error-total">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="campo-completo">
                        <label for="username">NOMBRE COMPLETO</label>
                        <input type="text" id="username" name="username" placeholder="Ej: Elena Martínez" value="{{ old('username') }}" required>
                        @error('username') <span class="error-texto">{{ $message }}</span> @enderror
                    </div>
                    
                    <div class="grid-2-columnas">
                        <div class="campo-completo">
                            <label for="telefono">TELÉFONO</label>
                            <input type="tel" id="telefono" name="telefono" placeholder="(+57) 300 123 4567" value="{{ old('telefono') }}" required>
                            @error('telefono') <span class="error-texto">{{ $message }}</span> @enderror
                        </div>
                        <div class="campo-completo">
                            <label for="email_reg">CORREO ELECTRÓNICO</label>
                            <input type="email" id="email_reg" name="email" placeholder="valentina@gmail.com" value="{{ old('email') }}" required>
                            @error('email') <span class="error-texto">{{ $message }}</span> @enderror
                        </div>
                    </div>

                    <div class="grid-2-columnas">
                        <div class="campo-completo">
                            <label for="password_reg">CONTRASEÑA</label>
                            <div class="caja-input-clave">
                                <input type="password" id="password_reg" name="password" placeholder="........" required>
                                <button type="button" class="btn-ver-clave" onclick="togglePassword('password_reg', this)">
                                    <i data-lucide="eye"></i>
                                </button>
                            </div>
                        </div>
                        <div class="campo-completo">
                            <label for="confirm-password">CONFIRMAR</label>
                            <div class="caja-input-clave">
                                <input type="password" id="confirm-password" name="confirmPassword" placeholder="........" required>
                                <button type="button" class="btn-ver-clave" onclick="togglePassword('confirm-password', this)">
                                    <i data-lucide="eye"></i>
                                </button>
                            </div>
                        </div>
                    </div>

                    <div id="password-feedback" class="feedback-total" style="display: none;">
                        <span id="length-req"></span><br>
                        <span id="alpha-req"></span><br>
                        <span id="special-req"></span>
                    </div>
                    <div id="strength-text"></div>

                    <div class="caja-recaptcha">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                    </div>
                    @error('captcha') <span class="error-texto">{{ $message }}</span> @enderror

                    <button type="submit" class="button-aceptar">REGISTRARME</button>
                </form>
            </div>
        </div>
    </div>

    <!-- FOOTER SYNCROSTYLE -->
    <footer class="footer-syncrostyle">
        Sistema de Gestión Operativo <strong>SYNCROSTYLE</strong>
    </footer>

</body>
</html>