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
    
    <div class="caja-registro-maestra">
        
        <!-- LADO IZQUIERDO: FOTO -->
        <div class="registro-foto">
            <div class="capa-verde-filtro"></div>
            <div class="registro-texto">
                <h2>The Beauty Room</h2>
                <p>Accede a tu perfil para gestionar tus reservas y descubrir nuevas experiencias de relajación.</p>
                <a href="{{ route('login.view') }}" class="btn-outline">Iniciar Sesión</a>
            </div>
        </div>

        <!-- LADO DERECHO: FORMULARIO -->
        <div class="registro-formulario">
            <div class="registro-scroll">
                <h2 class="registro-titulo">Crea tu cuenta</h2>

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

                    <label for="username">Nombre completo</label>
                    <input type="text" id="username" name="username" placeholder="Ej: Anyel Daniel" value="{{ old('username') }}" required autocomplete="name">
                    @error('username') <span class="error-texto">{{ $message }}</span> @enderror
                    
                    <label for="telefono">Teléfono</label>
                    <input type="tel" id="telefono" name="telefono" placeholder="Ej: 04121234567" value="{{ old('telefono') }}" required autocomplete="tel">
                    @error('telefono') <span class="error-texto">{{ $message }}</span> @enderror

                    <label for="email_reg">Correo electrónico</label>
                    <input type="email" id="email_reg" name="email" placeholder="correo@ejemplo.com" value="{{ old('email') }}" required autocomplete="email">
                    @error('email') <span class="error-texto">{{ $message }}</span> @enderror

                    <div class="grid-2-columnas">
                        <div>
                            <label for="password_reg">Contraseña</label>
                            <div class="caja-input-clave">
                                <input type="password" id="password_reg" name="password" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" class="btn-ver-clave" onclick="togglePassword('password_reg', this)">👁️</button>
                            </div>
                        </div>
                        <div>
                            <label for="confirm-password">Confirmar</label>
                            <div class="caja-input-clave">
                                <input type="password" id="confirm-password" name="confirmPassword" placeholder="••••••••" required autocomplete="new-password">
                                <button type="button" class="btn-ver-clave" onclick="togglePassword('confirm-password', this)">👁️</button>
                            </div>
                        </div>
                    </div>
                    @error('password') <span class="error-texto">{{ $message }}</span> @enderror

                    <div id="password-feedback" class="feedback-total">
                        <span id="length-req"></span><br>
                        <span id="alpha-req"></span><br>
                        <span id="special-req"></span><br>
                        <span id="strength-text"></span>
                    </div>

                    <div class="caja-recaptcha">
                        <div class="g-recaptcha" data-sitekey="{{ env('RECAPTCHA_SITE_KEY') }}"></div>
                    </div>
                    @error('captcha') <span class="error-texto">{{ $message }}</span> @enderror

                    <button type="submit" class="button-aceptar" style="margin-top: 15px;">REGISTRARME</button>
                </form>
            </div>
        </div>

    </div>

    <div id="auth-config" data-success="{{ session('success') ? '1' : '0' }}" data-force-register="0" style="display:none;"></div>
</body>
</html>