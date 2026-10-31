<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Syncrostyle SPA — The Beauty Room')</title>

    {{-- Google Fonts (de josebackend) --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Instrument+Sans:ital,wght@0,400..700;1,400..700&display=swap" rel="stylesheet">

    {{-- Estilos globales --}}
    @vite(['resources/css/app.css', 'resources/js/app.js', 'resources/js/layout.js'])

    {{-- Stack para que tus vistas hijas inyecten sus estilos --}}
    @stack('styles')
</head>
<body>
    @auth
        @php
            $user = auth()->user();
            $isAdmin         = $user->rol === 'admin';
            $isRecepcionista = $user->rol === 'recepcion';
            $isTrabajador    = $user->rol === 'trabajador';
            $isCliente       = $user->rol === 'cliente';
        @endphp
    @endauth

    <header id="main-header">
        <div class="max-w-7xl mx-auto flex justify-between items-center px-8">
            <a href="{{ route('agenda') }}" class="flex items-center gap-2 text-white mr-auto">
                <i data-lucide="sparkles" class="w-8 h-8"></i>
                <span class="brand-title">THE BEAUTY ROOM</span>
            </a>

            {{-- NAV DESKTOP --}}
            <nav class="hidden md:flex items-center space-x-6">
                @auth
                    @if($isAdmin || $isRecepcionista || $isTrabajador)
                        <a href="{{ route('agenda') }}" class="nav-item">Agenda</a>
                    @endif

                    @if($isAdmin || $isRecepcionista || $isCliente)
                        <a href="{{ url('/terapeutas') }}" class="nav-item">Terapeutas</a>
                    @endif

                    @if($isAdmin)
                        <a href="{{ url('/admin/dashboard') }}" class="nav-item">Administración</a>
                        <a href="{{ route('admin.reportes') }}" class="nav-item">Reportes</a>
                    @endif

                    @if($isAdmin || $isRecepcionista)
                        <a href="{{ route('pago.verificar') }}" class="nav-item">Verificar Pagos</a>
                    @endif

                    @if($isCliente)
                        <a href="{{ url('/reservas') }}" class="nav-item">Reservar</a>
                        <a href="{{ route('clientes.mis.reservas') }}" class="nav-item">Mis Reservas</a>
                    @endif

                    <a href="{{ route('catalogo') }}" class="nav-item">Catálogo Zen</a>

                    {{-- 🔔 CAMPANA DESKTOP --}}
                    <div class="notif-widget" id="notif-widget">
                        <button type="button" class="notif-campana" id="notif-btn" aria-label="Notificaciones">
                            <i data-lucide="bell"></i>
                            <span class="notif-badge" id="notif-badge" hidden>0</span>
                        </button>

                        <div class="notif-panel" id="notif-panel" hidden>
                            <header class="notif-panel-header">
                                <strong>Notificaciones</strong>
                                <button type="button" id="notif-marcar-todas" class="notif-btn-mini">Marcar todas</button>
                            </header>
                            <ul class="notif-lista" id="notif-lista">
                                <li class="notif-vacio">Cargando…</li>
                            </ul>
                        </div>
                    </div>

                    <button onclick="document.getElementById('logout-form').submit();" class="btn-logout">Cerrar Sesión</button>
                    <form id="logout-form" action="{{ route('logout') }}" method="POST" class="hidden">@csrf</form>
                @endauth
            </nav>
            <button id="hamburger-btn" class="md:hidden text-white"><i data-lucide="menu"></i></button>
        </div>

        {{-- NAV MÓVIL --}}
        <nav id="mobile-menu">
            <button onclick="document.getElementById('mobile-menu').classList.remove('active')" class="text-white self-end mb-4"><i data-lucide="x"></i></button>
            
            <div class="flex flex-col gap-6 text-left w-full">
                @auth
                    {{-- 🔔 CAMPANA MÓVIL --}}
                    <div class="notif-widget notif-widget-mobile" id="notif-widget-mobile" style="margin-bottom: 1rem; display: flex; justify-content: flex-end;">
                        <button type="button" class="notif-campana" id="notif-btn-mobile" aria-label="Notificaciones">
                            <i data-lucide="bell"></i>
                            <span class="notif-badge" id="notif-badge-mobile" hidden>0</span>
                        </button>

                        <div class="notif-panel" id="notif-panel-mobile" hidden style="right: 0; left: auto; width: 100%;">
                            <header class="notif-panel-header">
                                <strong>Notificaciones</strong>
                                <button type="button" id="notif-marcar-todas-mobile" class="notif-btn-mini">Marcar todas</button>
                            </header>
                            <ul class="notif-lista" id="notif-lista-mobile">
                                <li class="notif-vacio">Cargando…</li>
                            </ul>
                        </div>
                    </div>

                    @if($isAdmin || $isRecepcionista || $isTrabajador)
                        <a href="{{ route('agenda') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Agenda</a>
                    @endif
                    @if($isAdmin || $isRecepcionista || $isCliente)
                        <a href="{{ url('/terapeutas') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Terapeutas</a>
                    @endif
                    @if($isAdmin)
                        <a href="{{ url('/admin/dashboard') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Administración</a>
                        <a href="{{ route('admin.reportes') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Reportes</a>
                    @endif
                    @if($isAdmin || $isRecepcionista)
                        <a href="{{ route('pago.verificar') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Verificar Pagos</a>
                    @endif
                    @if($isCliente)
                        <a href="{{ url('/reservas') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Reservar</a>
                        <a href="{{ route('clientes.mis.reservas') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Mis Reservas</a>
                    @endif
                    <a href="{{ route('catalogo') }}" class="text-white text-lg font-medium border-b border-white/20 pb-2">Catálogo Zen</a>
                    <button onclick="document.getElementById('logout-form').submit();" class="mt-4 bg-white text-[#8b5e3c] py-3 rounded font-bold uppercase tracking-wider text-sm">Cerrar Sesión</button>
                @endauth
            </div>
        </nav>
    </header>

    <main>@yield('content')</main>

    <footer>
        <div class="max-w-7xl mx-auto grid grid-cols-1 md:grid-cols-3 gap-12">
            <section>
                <h3 class="footer-section-title">THE BEAUTY ROOM</h3>
                <p class="text-sm opacity-70 leading-relaxed">
                    Un espacio sagrado donde la belleza y el bienestar se encuentran. Tu equilibrio, nuestra misión.
                </p>
            </section>
            <section>
                <h3 class="footer-section-title">SERVICIOS</h3>
                <ul class="footer-link-list">
                    <li>Masajes Terapéuticos</li>
                    <li>Tratamientos Faciales</li>
                    <li>Manicura y Pedicura</li>
                    <li>Aromaterapia</li>
                    <li>Piedras Volcánicas</li>
                </ul>
            </section>
            <section>
                <h3 class="footer-section-title">CONTACTO</h3>
                <ul class="footer-link-list space-y-3">
                    <li class="flex items-center gap-2"><i data-lucide="map-pin" class="w-4 h-4"></i> La Guaira - Catia La Mar</li>
                    <li class="flex items-center gap-2"><i data-lucide="phone" class="w-4 h-4"></i> +58 414-2578005</li>
                    <li class="flex items-center gap-2"><i data-lucide="mail" class="w-4 h-4"></i> hola@thebeautyroom.mx</li>
                </ul>
            </section>
        </div>
        <div class="footer-bottom-bar">
            &copy; {{ date('Y') }} The Beauty Room Spa. Potenciado por Sistema de Gestión Operativa SyncroStyle.
        </div>
    </footer>

    {{-- Inicialización de Iconos --}}
    <script src="https://unpkg.com/lucide@latest"></script>
    <script>
        lucide.createIcons();
        document.getElementById('hamburger-btn')?.addEventListener('click', () => {
            document.getElementById('mobile-menu').classList.add('active');
        });
        window.NOTIF_URL_LISTA = "{{ route('notificaciones.lista') }}";
        window.NOTIF_URL_LEER = "{{ route('notificaciones.leer', ['notificacion' => ':id']) }}";
        window.NOTIF_URL_LEER_TODAS = "{{ route('notificaciones.leerTodas') }}";
    </script>

    @vite(['resources/js/notificaciones.js'])
    @stack('scripts')
</body>
</html>