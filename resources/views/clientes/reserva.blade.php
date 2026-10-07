@extends('layouts.app')

@section('title', 'Reservar Cita - The Beauty Room')

@vite(['resources/css/reserva.css'])

@section('content')
<main class="modulo-reserva">

    {{-- HEADER --}}
    <header class="reserva-header">
        <div class="reserva-header__left">
            <p class="subtitulo-seccion">Agenda tu visita</p>
            <h1>Reserva un momento<br>para cuidar de ti</h1>
            <p class="descripcion">Elige el servicio, la fecha y la hora que mejor se adapten a tu día.</p>
        </div>

        <aside class="badge-info">
            <div class="badge-info__icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="badge-info__texto">
                <small>Proceso simple</small>
                <strong>Confirmación inmediata</strong>
            </div>
        </aside>
    </header>

    {{-- PASOS --}}
    <section class="pasos-container">
        <article class="paso activo" data-paso="1">
            <div class="paso-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
            </div>
            <div class="paso-texto">
                <small>Paso 1</small>
                <strong>Elige tu cita</strong>
            </div>
            <span class="paso-numero">01</span>
        </article>

        <article class="paso" data-paso="2">
            <div class="paso-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="12" cy="8" r="4"/>
                    <path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/>
                </svg>
            </div>
            <div class="paso-texto">
                <small>Paso 2</small>
                <strong>Completa tus datos</strong>
            </div>
            <span class="paso-numero">02</span>
        </article>

        <article class="paso" data-paso="3">
            <div class="paso-icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <div class="paso-texto">
                <small>Paso 3</small>
                <strong>Recibe confirmación</strong>
            </div>
            <span class="paso-numero">03</span>
        </article>
    </section>

    {{-- FORMULARIO --}}
    <form class="reserva-panel" action="{{ route('clientes.reserva.store') }}" method="POST" id="form-reserva">
        @csrf

        <input type="hidden" name="servicio_id" id="input-servicio" value="{{ $servicioData['id'] }}">
        <input type="hidden" name="fecha" id="input-fecha" value="{{ old('fecha') }}">
        <input type="hidden" name="hora" id="input-hora" value="{{ old('hora') }}">

        <div class="panel-header">
            <div>
                <span class="kicker">Nueva reserva</span>
                <h2>Encuentra tu horario ideal</h2>
            </div>
            <div class="estado-disponibilidad">
                <span class="dot"></span>
                <span>Disponibilidad actualizada</span>
            </div>
        </div>

        <div class="reserva-grid">

            {{-- COLUMNA 1: SERVICIOS --}}
            <section class="columna-servicios">
                <header class="columna-header">
                    <h3>Selecciona un servicio</h3>
                    <span class="columna-numero">01</span>
                </header>

                <div class="lista-servicios">
                    @php
                    $serviciosDemo = [
                        ['id' => 1, 'nombre' => 'Consulta general',         'duracion' => 45, 'precio' => 45],
                        ['id' => 2, 'nombre' => 'Consulta de seguimiento',  'duracion' => 30, 'precio' => 30],
                        ['id' => 3, 'nombre' => 'Consulta virtual',         'duracion' => 40, 'precio' => 38],
                        ['id' => 4, 'nombre' => 'Masaje relajante',         'duracion' => 60, 'precio' => 55],
                    ];
                    @endphp

                    @foreach($serviciosDemo as $s)
                    <label class="servicio-item {{ $servicioData['id'] == $s['id'] ? 'activo' : '' }}"
                           data-servicio-id="{{ $s['id'] }}"
                           data-precio="{{ $s['precio'] }}"
                           data-duracion="{{ $s['duracion'] }}">
                        <input type="radio" name="servicio_radio" value="{{ $s['id'] }}" {{ $servicioData['id'] == $s['id'] ? 'checked' : '' }}>
                        <div class="servicio-info">
                            <strong>{{ $s['nombre'] }}</strong>
                            <small>{{ $s['duracion'] }} min</small>
                        </div>
                        <span class="servicio-precio">${{ $s['precio'] }}</span>
                    </label>
                    @endforeach
                </div>
            </section>

            {{-- COLUMNA 2: CALENDARIO --}}
            <section class="columna-calendario">
                <header class="columna-header">
                    <h3>Escoge una fecha</h3>
                    <span class="columna-numero">02</span>
                </header>

                <div class="calendario-wrapper">
                    <div class="calendario-nav">
                        <button type="button" class="btn-nav" id="prev-mes">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/>
                            </svg>
                        </button>
                        <div class="calendario-titulo" id="calendario-mes">Cargando...</div>
                        <button type="button" class="btn-nav" id="next-mes">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7"/>
                            </svg>
                        </button>
                    </div>

                    <div class="calendario-dias-semana">
                        <span>L</span><span>M</span><span>M</span><span>J</span><span>V</span><span>S</span><span>D</span>
                    </div>

                    <div class="calendario-dias" id="calendario-dias"></div>
                </div>
            </section>

            {{-- COLUMNA 3: HORAS --}}
            <section class="columna-horas">
                <header class="columna-header">
                    <h3>Elige una hora</h3>
                    <span class="columna-numero">03</span>
                </header>

                <div class="horas-wrapper">
                    <p class="fecha-seleccionada">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <rect x="3" y="5" width="18" height="16" rx="2"/>
                            <path d="M16 3v4M8 3v4M3 10h18"/>
                        </svg>
                        <span id="fecha-texto">Selecciona una fecha</span>
                    </p>

                    <div class="lista-horas" id="lista-horas"></div>

                    <p class="zona-horaria">
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                            <circle cx="12" cy="12" r="9"/>
                            <path d="M12 7v5l3 2"/>
                        </svg>
                        Hora local · GMT-4
                    </p>
                </div>
            </section>
        </div>

        {{-- FOOTER --}}
        <footer class="panel-footer">
            <div class="paso-footer">
                <span class="numero-footer">04</span>
                <div>
                    <strong>Tus datos</strong>
                    <small>Para enviarte la confirmación</small>
                </div>
            </div>

            <div class="campos-cliente">
                <label>
                    <small>Nombre completo</small>
                    <input type="text" name="nombre" placeholder="Ej. María González" required>
                </label>
                <label>
                    <small>Correo electrónico</small>
                    <input type="email" name="email" placeholder="nombre@correo.com" required>
                </label>
                <label class="label-especialista">
                    <small>Especialista</small>
                    <select name="trabajador_id" required>
                        <option value="">Selecciona un especialista</option>
                        @foreach($terapeutas ?? [] as $t)
                            <option value="{{ $t->id }}" {{ old('trabajador_id') == $t->id ? 'selected' : '' }}>
                                {{ $t->nombre }}
                            </option>
                        @endforeach
                    </select>
                </label>
                </label>
            </div>

            <div class="resumen-precio">
                <div class="resumen-info">
                    <strong id="resumen-servicio">Consulta general</strong>
                    <small id="resumen-detalle">Selecciona fecha y hora</small>
                </div>
                <div class="resumen-monto">
                    <span id="resumen-precio">$45</span>
                </div>
            </div>

            <button type="submit" class="btn-confirmar">
                <span>Confirmar cita</span>
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                </svg>
            </button>
        </footer>
    </form>
</main>
@endsection


@vite(['resources/js/reserva.js'])
