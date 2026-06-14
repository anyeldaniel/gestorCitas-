<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Syncrostyle SPA') — The Beauty Room</title>
    
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    @stack('styles')<!--Acá añado estilos específicos de cada vista, att: Andrés -->
</head>
<body>

    <header class="header-spa">
        <picture class="logo-contenedor">
            <img src="{{ asset('logo/logo-the-beauty-room.PNG') }}" alt="Logotipo Oficial de The Beauty Room Spa" class="logo-spa">
        </picture>
<!--Aquì estoy añadiendo el nav para que se muestre en todas las vistas, y se adapte segun el rol del usuario logueado, mostrando solo las opciones correspondientes a su rol.-->
        <nav class="nav-roles" aria-label="Navegación de Roles Operativos">
            @auth
                @if(auth()->user()->rol === 'admin' || auth()->user()->rol === 'recepcionista' || auth()->user()->rol === 'trabajador')
                    <a href="{{ route('agenda') }}" class="{{ Request::is('agenda*') ? 'active' : '' }}">Agenda</a>
                @endif

                @if(auth()->user()->rol === 'admin' || auth()->user()->rol === 'recepcionista')
                    <a href="{{ url('/sala-espera') }}" class="{{ Request::is('sala-espera*') ? 'active' : '' }}">Sala de Espera</a>
                @endif

                @if(auth()->user()->rol === 'admin' || auth()->user()->rol === 'recepcionista' || auth()->user()->rol === 'cliente')
                    <a href="{{ url('/terapeutas') }}" class="{{ Request::is('terapeutas*') ? 'active' : '' }}">Terapeutas</a>
                @endif

                @if(auth()->user()->rol === 'admin')
                    <a href="{{ url('/admin/dashboard') }}" class="{{ Request::is('admin/dashboard*') ? 'active' : '' }}">Administración</a>
                @endif

                @if(auth()->user()->rol === 'admin')
                    <a href="{{ route('admin.reportes') }}" class="{{ Request::is('admin/reportes*') ? 'active' : '' }}">Reportes</a>
                @endif

                @if(auth()->user()->rol === 'recepcionista' || auth()->user()->rol === 'trabajador' || auth()->user()->rol === 'cliente')
                    <a href="{{ route('catalogo') }}" class="{{ Request::is('catalogo*') ? 'active' : '' }}">Catálogo Zen</a>
                @endif

                @if(auth()->user()->rol === 'recepcionista' || auth()->user()->rol === 'admin')
                    <a href="{{ url('/recepcion/pagos') }}" class="{{ Request::is('recepcion/pagos*') ? 'active' : '' }}">Verificar Pagos</a>
                @endif

                @if(auth()->user()->rol === 'cliente')
                    <a href="{{ url('/reservas') }}" class="{{ Request::is('reservas*') ? 'active' : '' }}">Reservas</a>
                @endif

                <a href="#" class="btn-logout" onclick="event.preventDefault(); document.getElementById('logout-form').submit();">
                    Cerrar Sesión
                </a>

                <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
                    @csrf
                </form>
            @endauth
        </nav>
    </header>

    <main class="contenedor-principal">
        @yield('content')
    </main>

    <footer class="footer-spa">
        <p>&copy; {{ date('Y') }} <strong>The Beauty Room Spa</strong>. Todos los derechos reservados.</p>
        <small>Potenciado por Sistema de Gestión Operativa SyncroStyle</small>
    </footer>
    @stack('scripts')
</body>
</html>