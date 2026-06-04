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
</head>
<body>

    <header class="header-spa">
        <picture class="logo-contenedor">
            <img src="{{ asset('img/logo-the-beauty-room.png') }}" alt="Logotipo Oficial de The Beauty Room Spa" width="180" height="45">
        </picture>

        <nav class="nav-roles" aria-label="Navegación de Roles Operativos">
            <a href="{{ url('/recepcion/agenda') }}" class="{{ Request::is('recepcion/agenda') ? 'active' : '' }}">Agenda</a>
            <a href="{{ url('/recepcion/admisiones') }}" class="{{ Request::is('recepcion/admisiones') ? 'active' : '' }}">Sala de Espera</a>
            <a href="{{ url('/especialistas/kanban') }}" class="{{ Request::is('especialistas/kanban') ? 'active' : '' }}">Terapeutas</a>
            <a href="{{ url('/admin/dashboard') }}" class="{{ Request::is('admin/dashboard') ? 'active' : '' }}">Administración</a>
            <a href="{{ url('/admin/reportes') }}" class="{{ Request::is('admin/reportes') ? 'active' : '' }}">Reportes</a>
            <a href="{{ url('/clientes/catalogo') }}" class="{{ Request::is('clientes/catalogo') ? 'active' : '' }}">Catálogo Zen</a>
            <a href="{{ route('clientes.reserva') }}" class="{{ Request::is('clientes/reserva') ? 'active' : '' }}">Reservas</a>

        </nav>
    </header>

    <main class="contenedor-principal">
        @yield('content')
    </main>

    <footer class="footer-spa">
        <p>&copy; {{ date('Y') }} <strong>The Beauty Room Spa</strong>. Todos los derechos reservados.</p>
        <small>Potenciado por Sistema de Gestión Operativa SyncroStyle</small>
    </footer>

</body>
</html>