@extends('layouts.app')
@section('title', 'Agenda de Bienestar')

@push('styles')
    @vite(['resources/css/agenda.css'])
@endpush

@section('content')
@php
    $esAdmin        = $user->rol === 'admin';
    $esRecepcion    = $user->rol === 'recepcionista';
    $esTrabajador   = $user->rol === 'trabajador';
    $puedeGestionar = $esAdmin || $esRecepcion;
    $puedeCancelar  = $puedeGestionar || $esTrabajador;

    $fechaActual = \Carbon\Carbon::parse($fecha);
    $fechaBonita = ucfirst($fechaActual->locale('es')->translatedFormat('l, d \d\e F'));

    $horas   = intdiv($minutosReservados, 60);
    $minutos = $minutosReservados % 60;
    $tiempoReservado = trim(($horas ? "{$horas} h " : '') . ($minutos ? "{$minutos} min" : '')) ?: '0 min';

    $iniciales = function ($nombre) {
        $partes = preg_split('/\s+/', trim((string) $nombre));
        $ini = '';
        foreach (array_slice($partes, 0, 2) as $p) {
            $ini .= mb_strtoupper(mb_substr($p, 0, 1));
        }
        return $ini ?: '—';
    };

    $estadosDisponibles = [
        'pendiente'  => 'clock',
        'confirmada' => 'check-circle-2',
        'en curso'   => 'play-circle',
        'completada' => 'check-check',
    ];
@endphp

<main class="modulo-agenda" data-rol="{{ $user->rol }}">

    {{-- ============================================================
         CABECERA
         ============================================================ --}}
    <header class="agenda-header">
        <div class="agenda-header-texto">
            <h2 class="subtitulo">
                @if($puedeGestionar)
                    Panel de gestión · Hola, {{ $user->nombre }}
                @else
                    Mi jornada
                @endif
            </h2>
            <h1 class="titulo-agenda">
                @if($puedeGestionar)
                    Agenda de bienestar
                @else
                    Buenos días, {{ explode(' ', trim($user->nombre))[0] }}
                @endif
            </h1>
            <p class="descripcion-agenda">
                @if($puedeGestionar)
                    Gestiona tu horario y monitorea en tiempo real las citas programadas.
                @else
                    Aquí tienes todo lo que necesitas para organizar tu día.
                @endif
            </p>
        </div>

        <aside class="agenda-header-fecha" aria-label="Fecha actual">
            <span class="chip-hoy">Hoy</span>
            <time datetime="{{ $fecha }}" class="agenda-fecha-dia">{{ $fechaBonita }}</time>
            <span class="agenda-fecha-horario">09:00 — 18:00</span>
        </aside>
    </header>

    {{-- ============================================================
         STATS (solo admin / recepción)
         ============================================================ --}}
    @if($puedeGestionar)
        <section class="stats-container" aria-label="Resumen de estados del día">
            <h2 class="sr-only">Resumen de estados</h2>

            <article class="stat-card stat-confirmada">
                <span class="stat-icono" aria-hidden="true">
                    <i data-lucide="check-circle-2"></i>
                </span>
                <div class="stat-info">
                    <strong class="stat-numero">{{ $stats['confirmadas'] }}</strong>
                    <small class="stat-etiqueta">Confirmadas</small>
                </div>
            </article>

            <article class="stat-card stat-pendiente">
                <span class="stat-icono" aria-hidden="true">
                    <i data-lucide="clock"></i>
                </span>
                <div class="stat-info">
                    <strong class="stat-numero">{{ $stats['pendientes'] }}</strong>
                    <small class="stat-etiqueta">Pendientes</small>
                </div>
            </article>

            <article class="stat-card stat-en-curso">
                <span class="stat-icono" aria-hidden="true">
                    <i data-lucide="play-circle"></i>
                </span>
                <div class="stat-info">
                    <strong class="stat-numero">{{ $stats['en_curso'] }}</strong>
                    <small class="stat-etiqueta">En curso</small>
                </div>
            </article>

            <article class="stat-card stat-completada">
                <span class="stat-icono" aria-hidden="true">
                    <i data-lucide="check-check"></i>
                </span>
                <div class="stat-info">
                    <strong class="stat-numero">{{ $stats['completadas'] }}</strong>
                    <small class="stat-etiqueta">Completadas</small>
                </div>
            </article>

            <article class="stat-card stat-cancelada">
                <span class="stat-icono" aria-hidden="true">
                    <i data-lucide="x-circle"></i>
                </span>
                <div class="stat-info">
                    <strong class="stat-numero">{{ $stats['canceladas'] }}</strong>
                    <small class="stat-etiqueta">Canceladas</small>
                </div>
            </article>
        </section>
    @endif

    {{-- ============================================================
         MI HORARIO · selector de semana
         ============================================================ --}}
    <section class="bloque-semana" aria-label="Selector de semana">
        <header class="bloque-semana-cabecera">
            <div class="bloque-semana-titulo">
                <h2 class="subtitulo">
                    Semana del {{ $semanaDias[0]['numero'] }} — {{ $semanaDias[5]['numero'] }}
                </h2>
                <h3 class="titulo-seccion">Mi horario</h3>
            </div>

            <ul class="leyenda-semana" aria-label="Leyenda">
                <li><span class="punto punto-disponible" aria-hidden="true"></span> Disponible</li>
                <li><span class="punto punto-con-citas" aria-hidden="true"></span> Con citas</li>
            </ul>
        </header>

        <nav class="chips-semana" aria-label="Seleccionar día">
            @foreach($semanaDias as $dia)
                @php
                    $conteo = $conteoSemana[$dia['fecha']] ?? 0;
                    $clases = trim(
                        'chip-dia'
                        . ($dia['activo'] ? ' is-active' : '')
                        . ($dia['es_hoy'] ? ' is-today' : '')
                    );
                @endphp

                <a href="{{ route('agenda', ['fecha' => $dia['fecha']]) }}"
                   class="{{ $clases }}"
                   @if($dia['activo']) aria-current="date" @endif
                   aria-label="{{ $dia['dia'] }} {{ $dia['numero'] }}{{ $conteo > 0 ? " · {$conteo} citas" : ' · disponible' }}">
                    <span class="chip-dia-nombre">{{ $dia['dia'] }}</span>
                    <strong class="chip-dia-numero">{{ $dia['numero'] }}</strong>
                    @if($conteo > 0)
                        <small class="chip-dia-info">
                            {{ $conteo }} {{ $conteo === 1 ? 'cita' : 'citas' }}
                        </small>
                    @else
                        <small class="chip-dia-info chip-dia-disponible">Disponible</small>
                    @endif
                </a>
            @endforeach
        </nav>
    </section>

    {{-- ============================================================
         FILTROS (solo admin / recepción)
         ============================================================ --}}
    @if($puedeGestionar)
        <form class="agenda-filtros"
              method="GET"
              action="{{ route('agenda') }}"
              aria-label="Filtros de agenda">

            <label class="filter-group filter-search">
                <span class="filter-label">
                    <i data-lucide="search" aria-hidden="true"></i> Buscar
                </span>
                <input type="text"
                       name="search"
                       placeholder="Cliente o servicio..."
                       value="{{ request('search') }}">
            </label>

            <label class="filter-group">
                <span class="filter-label">
                    <i data-lucide="calendar" aria-hidden="true"></i> Fecha
                </span>
                <input type="date" name="fecha" value="{{ $fecha }}">
            </label>

            <label class="filter-group">
                <span class="filter-label">
                    <i data-lucide="user-round" aria-hidden="true"></i> Terapeuta
                </span>
                <select name="terapeuta_id">
                    <option value="">Todos los especialistas</option>
                    @foreach($terapeutas as $t)
                        <option value="{{ $t->id }}" @selected(request('terapeuta_id') == $t->id)>
                            {{ $t->nombre }}
                        </option>
                    @endforeach
                </select>
            </label>

            <label class="filter-group">
                <span class="filter-label">
                    <i data-lucide="filter" aria-hidden="true"></i> Estado
                </span>
                <select name="estado">
                    <option value="">Todos los estados</option>
                    @foreach(['pendiente', 'confirmada', 'en curso', 'completada', 'cancelada'] as $e)
                        <option value="{{ $e }}" @selected(request('estado') === $e)>
                            {{ ucfirst($e) }}
                        </option>
                    @endforeach
                </select>
            </label>

            <div class="filter-resumen" aria-label="Tiempo reservado del día">
                <span class="filter-label">Tiempo reservado</span>
                <strong class="filter-resumen-valor">{{ $tiempoReservado }}</strong>
            </div>
        </form>
    @endif

    {{-- ============================================================
         AGENDA DEL DÍA
         ============================================================ --}}
    <section class="bloque-dia" aria-label="Agenda del día">
        <header class="bloque-dia-cabecera">
            <div class="bloque-dia-titulo">
                <h2 class="subtitulo">Agenda del día</h2>
                <h3 class="titulo-seccion">{{ $fechaBonita }}</h3>
            </div>
            <small class="bloque-dia-meta">
                {{ $citas->count() }} {{ $citas->count() === 1 ? 'resultado' : 'resultados' }}
                · {{ $tiempoReservado }}
            </small>
        </header>

        @if($citas->isEmpty())
            <div class="estado-vacio" role="status">
                <i data-lucide="calendar-heart" aria-hidden="true"></i>
                <h3>No tienes citas agendadas</h3>
                <p>Este día está disponible en tu horario.</p>
            </div>
        @else
            <ul class="lista-citas">
                @foreach($citas as $cita)
                    <li class="tarjeta-cita {{ $cita->claseEstado() }}"
                        data-cita-id="{{ $cita->id }}">

                        {{-- Hora --}}
                        <div class="cita-hora">
                            <time datetime="{{ \Carbon\Carbon::parse($cita->fecha->toDateString() . ' ' . $cita->hora)->toIso8601String() }}">
                                <strong>{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}</strong>
                            </time>
                            @if($cita->horaFin())
                                <small class="cita-hora-fin">{{ $cita->horaFin() }}</small>
                            @endif
                        </div>

                        {{-- Cliente --}}
                        <div class="cita-cliente">
                            <span class="avatar-iniciales" aria-hidden="true">
                                {{ $iniciales($cita->cliente->nombre ?? '') }}
                            </span>
                            <div class="cita-cliente-info">
                                <span class="etiqueta-mini">Cliente</span>
                                <strong class="cita-nombre">{{ $cita->cliente->nombre ?? 'Cliente no disponible' }}</strong>
                                @if($cita->cliente?->descripcion)
                                    <small class="cita-nota">{{ $cita->cliente->descripcion }}</small>
                                @endif
                            </div>
                        </div>

                        {{-- Servicio --}}
                        <div class="cita-servicio">
                            <span class="etiqueta-mini">Servicio</span>
                            <strong class="cita-servicio-nombre">
                                {{ $cita->servicio->nombre_servicio ?? 'Servicio no disponible' }}
                            </strong>
                            <small class="cita-servicio-duracion">
                                <i data-lucide="clock" aria-hidden="true"></i>
                                {{ $cita->servicio->duracion_minutos ?? '—' }} min
                            </small>
                        </div>

                        {{-- Estado --}}
                        <div class="cita-estado">
                            <span class="badge-estado {{ $cita->claseEstado() }}" data-badge>
                                <span class="punto-estado" aria-hidden="true"></span>
                                <span data-badge-texto>{{ $cita->etiquetaEstado() }}</span>
                            </span>
                        </div>

                        {{-- Acciones --}}
                        <div class="cita-acciones">
                            @if($puedeGestionar && !$cita->estaCancelada())
                                <button type="button"
                                        class="btn-cambiar-estado"
                                        data-cambiar-estado
                                        data-cita="{{ $cita->id }}"
                                        data-estado="{{ $cita->estado }}">
                                    <i data-lucide="refresh-cw" aria-hidden="true"></i>
                                    <span>Estado</span>
                                </button>
                            @endif

                            @if($puedeCancelar && !$cita->estaCancelada())
                                <button type="button"
                                        class="btn-declinar"
                                        data-declinar
                                        data-cita="{{ $cita->id }}"
                                        data-cliente="{{ $cita->cliente->nombre ?? 'Cliente' }}"
                                        data-servicio="{{ $cita->servicio->nombre_servicio ?? 'Servicio' }}"
                                        data-fecha="{{ \Carbon\Carbon::parse($cita->fecha)->locale('es')->translatedFormat('d \d\e F') }}"
                                        data-hora="{{ \Carbon\Carbon::parse($cita->hora)->format('H:i') }}">
                                    <span>Declinar cita</span>
                                </button>
                            @endif

                            @if($cita->estaCancelada())
                                <span class="motivo-cancelacion"
                                      title="{{ $cita->motivo_cancelacion }}">
                                    <i data-lucide="info" aria-hidden="true"></i>
                                    <span>Cancelada</span>
                                </span>
                            @endif
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif
    </section>
</main>

{{-- ============================================================
     MODAL · CANCELAR CITA
     ============================================================ --}}
<dialog id="modal-cancelar-cita" class="modal-agenda" aria-labelledby="modal-cancelar-titulo">
    <article class="modal-tarjeta">
        <header class="modal-cabecera">
            <h2 class="subtitulo">Gestionar cita</h2>
            <h3 id="modal-cancelar-titulo" class="modal-titulo">
                ¿No puedes atender esta cita?
            </h3>
            <button type="button"
                    class="btn-cerrar-modal"
                    data-close-modal
                    aria-label="Cerrar">&times;</button>
        </header>

        <div class="modal-resumen-cita">
            <span class="avatar-iniciales avatar-modal" id="cancel-avatar" aria-hidden="true">—</span>
            <div class="modal-resumen-info">
                <strong id="cancel-cliente">Cliente</strong>
                <small id="cancel-servicio">Servicio</small>
            </div>
            <div class="modal-resumen-hora">
                <strong id="cancel-hora">--:--</strong>
                <small id="cancel-fecha">—</small>
            </div>
        </div>

        <form id="form-cancelar-cita" method="POST" action="">
            @csrf

            <label class="campo-modal">
                <span class="label-modal">Explica brevemente el motivo</span>
                <textarea name="motivo"
                          rows="4"
                          required
                          minlength="5"
                          maxlength="500"
                          placeholder="Ej. Tengo una emergencia personal y no podré llegar a tiempo."></textarea>
                <small class="ayuda-modal">
                    Administración recibirá tu explicación y se encargará de contactar a la clienta para reprogramar.
                </small>
            </label>

            <footer class="modal-pie">
                <button type="button" class="btn-secundario" data-close-modal>
                    Conservar cita
                </button>
                <button type="submit" class="btn-primario">
                    Confirmar declinación
                </button>
            </footer>
        </form>
    </article>
</dialog>

{{-- ============================================================
     MODAL · CAMBIAR ESTADO
     ============================================================ --}}
<dialog id="modal-cambiar-estado" class="modal-agenda" aria-labelledby="modal-estado-titulo">
    <article class="modal-tarjeta modal-tarjeta-compacta">
        <header class="modal-cabecera">
            <h2 class="subtitulo">Actualizar estado</h2>
            <h3 id="modal-estado-titulo" class="modal-titulo">
                ¿A qué estado pasa esta cita?
            </h3>
            <button type="button"
                    class="btn-cerrar-modal"
                    data-close-modal
                    aria-label="Cerrar">&times;</button>
        </header>

        <div class="lista-estados">
            @foreach($estadosDisponibles as $estado => $icono)
                <button type="button"
                        class="btn-estado-opcion"
                        data-estado="{{ $estado }}">
                    <i data-lucide="{{ $icono }}" aria-hidden="true"></i>
                    <span>{{ ucfirst($estado) }}</span>
                </button>
            @endforeach
        </div>
    </article>
</dialog>

{{-- ============================================================
     MODAL · ALERTA CENTRAL
     ============================================================ --}}
<dialog id="modal-alerta" class="modal-alerta-centro" aria-labelledby="alerta-titulo">
    <article class="alerta-contenido" role="alert">
        <div class="alerta-icono" id="alerta-icono" aria-hidden="true">
            <i data-lucide="check-circle-2"></i>
        </div>
        <h3 id="alerta-titulo">Listo</h3>
        <p id="alerta-mensaje">Operación realizada con éxito.</p>
        <button type="button" class="btn-primario" data-close-modal>
            Cerrar
        </button>
    </article>
</dialog>

@endsection

@push('scripts')
    @vite(['resources/js/agenda.js'])
@endpush