@extends('layouts.app')

@section('title', 'Reservar Cita - The Beauty Room')

@vite(['resources/css/reserva.css'])

@section('content')
<main class="modulo-reserva" id="modulo-reserva" 
      data-especialistas="{{ json_encode($serviciosAgrupados->flatten()->map(function($s) {
          return [
              'id' => $s->id,
              'nombre' => $s->nombre_servicio,
              'especialistas' => $s->especialistas->map(function($e) {
                  return ['id' => $e->id, 'nombre' => $e->nombre];
              })
          ];
      })->keyBy('id')) }}"
      data-success="{{ session('success') }}">

    <header class="reserva-header">
        <div class="reserva-header__left">
            <p class="subtitulo-seccion">Agenda tu visita</p>
            <h1>Reserva un momento<br>para cuidar de ti</h1>
            <p class="descripcion">Elige los servicios, la fecha y la hora que mejor se adapten a tu día.</p>
        </div>
        <aside class="badge-info">
            <div class="badge-info__icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div class="badge-info__texto">
                <small>Proceso simple</small>
                <strong>Confirmación inmediata</strong>
            </div>
        </aside>
    </header>

    <section class="pasos-container">
        <article class="paso activo"><div class="paso-icono"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg></div><div class="paso-texto"><small>Paso 1</small><strong>Elige tu cita</strong></div><span class="paso-numero">01</span></article>
        <article class="paso"><div class="paso-icono"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/></svg></div><div class="paso-texto"><small>Paso 2</small><strong>Completa tus datos</strong></div><span class="paso-numero">02</span></article>
        <article class="paso"><div class="paso-icono"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg></div><div class="paso-texto"><small>Paso 3</small><strong>Recibe confirmación</strong></div><span class="paso-numero">03</span></article>
    </section>

    <form class="reserva-panel" action="{{ route('clientes.reserva.store') }}" method="POST" id="form-reserva">
        @csrf

        <input type="hidden" name="fecha" id="input-fecha" value="{{ old('fecha') }}">
        <input type="hidden" name="hora" id="input-hora" value="{{ old('hora') }}">

        <div class="panel-header">
            <div><span class="kicker">Nueva reserva</span><h2>Encuentra tu horario ideal</h2></div>
            <div class="estado-disponibilidad"><span class="dot"></span><span>Disponibilidad actualizada</span></div>
        </div>

        <div class="reserva-grid">
            {{-- COLUMNA 1: SERVICIOS MÚLTIPLES --}}
            <section class="columna-servicios">
                <header class="columna-header"><h3>Selecciona servicios</h3><span class="columna-numero">01</span></header>
                <div class="lista-servicios">
                    @foreach($serviciosAgrupados as $categoria => $servicios)
                        <div class="categoria-servicios">
                            <h4 class="categoria-titulo">{{ $categoria }}</h4>
                            @foreach($servicios as $s)
                            <label class="servicio-item {{ in_array($s->id, old('servicios', [])) ? 'activo' : '' }}"
                                   data-servicio-id="{{ $s->id }}" data-precio="{{ $s->precio }}" data-duracion="{{ $s->duracion_minutos }}">
                                <input type="checkbox" name="servicios[]" value="{{ $s->id }}" {{ in_array($s->id, old('servicios', [])) ? 'checked' : '' }}>
                                <div class="servicio-info">
                                    <strong>{{ $s->nombre_servicio }}</strong>
                                    <small>{{ $s->duracion_minutos }} min</small>
                                </div>
                                <span class="servicio-precio">${{ number_format($s->precio, 0) }}</span>
                            </label>
                            @endforeach
                        </div>
                    @endforeach
                </div>
            </section>

            {{-- COLUMNA 2: CALENDARIO --}}
            <section class="columna-calendario">
                <header class="columna-header"><h3>Escoge una fecha</h3><span class="columna-numero">02</span></header>
                <div class="calendario-wrapper">
                    <div class="calendario-nav">
                        <button type="button" class="btn-nav" id="prev-mes"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg></button>
                        <div class="calendario-titulo" id="calendario-mes">Cargando...</div>
                        <button type="button" class="btn-nav" id="next-mes"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/></svg></button>
                    </div>
                    <div class="calendario-dias-semana"><span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span></div>
                    <div class="calendario-dias" id="calendario-dias"></div>
                </div>
            </section>

            {{-- COLUMNA 3: HORAS --}}
            <section class="columna-horas">
                <header class="columna-header"><h3>Elige una hora</h3><span class="columna-numero">03</span></header>
                <div class="horas-wrapper">
                    <p class="fecha-seleccionada">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                        <span id="fecha-texto">Selecciona una fecha</span>
                    </p>
                    <div class="lista-horas" id="lista-horas"></div>
                    <p class="zona-horaria"><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path d="M12 7v5l3 2"/></svg>Hora local · GMT-4</p>
                </div>
            </section>
        </div>

        <footer class="panel-footer">
            <div class="paso-footer"><span class="numero-footer">04</span><div><strong>Tus datos</strong><small>Para enviarte la confirmación</small></div></div>
            
            <div class="campos-cliente">
                <label><small>Nombre completo</small><input type="text" name="nombre" placeholder="Ej.María González" required></label>
                <label><small>Correo electrónico</small><input type="email" name="email" placeholder="nombre@correo.com" required></label>
                
                {{-- CONTENEDOR DINÁMICO PARA ESPECIALISTAS --}}
                <div class="especialistas-container" id="contenedor-especialistas" style="grid-column: span 2; display: flex; flex-direction: column; gap: 10px;">
                    <small style="font-family: var(--font-ui); font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted);">Especialistas</small>
                    <p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;" id="msg-especialistas">Selecciona un servicio para asignar especialistas.</p>
                </div>
                
            </div>
            
            <div class="resumen-precio">
                <div class="resumen-info">
                    <strong id="resumen-servicio">Ningún servicio</strong>
                    <small id="resumen-detalle">Selecciona fecha y hora</small>
                </div>
                <div class="resumen-monto"><span id="resumen-precio">$0</span></div>
            </div>
            <button type="submit" class="btn-confirmar"><span>Confirmar cita</span><svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg></button>
        </footer>
    </form>
</main>
@endsection
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/reserva.js'])