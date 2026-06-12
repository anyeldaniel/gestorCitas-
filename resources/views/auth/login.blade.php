<!DOCTYPE html>
<html lang="es">
<head>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<title>Iniciar sesion</title>
	@vite(['resources/css/app.css'])
</head>
<body>

	<main class="login-page">
		<section class="login-wrapper">
			<div class="form-login">
				<header>
					<h1>Iniciar sesion</h1>
					<p>Accede con tu correo y contrasena.</p>
				</header>

                <form id="login-form" method="POST" action="{{ route('login.post') }}">
					@csrf

                    @if ($errors->any())
                        <div class="error-message">
                            <ul>
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <div class="form-row">
                        <label for="email">Correo electronico</label>
                        <input id="email" name="email" type="email" placeholder="usuario@ejemplo.com" value="{{ old('email') }}" required autocomplete="email" autofocus>
                        @error('email')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
                    </div>

                    <div class="form-row">
                        <label for="password">Contrasena</label>
                        <input id="password" name="password" type="password" placeholder="********" required autocomplete="current-password">
                        @error('password')
                            <span class="error-message">{{ $message }}</span>
                        @enderror
					</div>

					<footer>
						<button type="submit" class="btn-zen">Entrar</button>

						<div class="form-footer-link">
							<a href="{{ route('registro.view') }}">Registrarme</a>
						</div>
					</footer>
				</form>
			</div>
		</section>
	</main>

    <script>
        // Este script recarga la página cuando el usuario vuelve atrás
        // para evitar un token CSRF antiguo y el error 419 Page Expired.
        window.addEventListener('pageshow', function(event) {
            if (event.persisted || window.performance.getEntriesByType('navigation')[0]?.type === 'back_forward') {
                window.location.reload();
            }
        });
    </script>

