@extends('layouts.app')

@section('title', 'Catálogo de Bienestar Premium')

@push('styles')
    {{-- Asegúrate de que este archivo CSS esté compilado por Vite --}}
    @vite(['resources/css/modal.css'])
@endpush

@section('content')
<section class="modulo-vista">

    {{-- Encabezado del Módulo --}}
    <header class="encabezado-modulo">
        <div>
            <h1>Nuestras Terapias y Experiencias</h1>
            <p>Selecciona el tratamiento ideal para restaurar tu equilibrio corporal y estético.</p>
        </div>

        {{-- Acción exclusiva del Administrador --}}
        @if(auth()->user()->rol === 'admin')
            <button type="button" class="btn-agregar-servicio" onclick="abrirModalAgregarServicio()">
                <svg xmlns="http://www.w3.org/2000/svg" width="18" height="18" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                Agregar Servicio
            </button>
        @endif
    </header>

    {{-- Mensaje de Éxito --}}
    @if(session('success'))
        <div class="alert-success-spa" style="background: #d4edda; color: #155724; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; border: 1px solid #c3e6cb;">
            <span>✨</span> {{ session('success') }}
        </div>
    @endif

    {{-- Rejilla del Catálogo --}}
    <section class="rejilla-catalogo" id="contenedor-servicios-catalogo">

        @forelse($servicios as $servicio)
            @php
                $nombreServicio = $servicio->nombre_servicio;
                $idSimulado = $servicio->id;
                $precioSimulado = $servicio->precio;
                $porcentajeAgendadoSimulado = 10;
                $tiempoEstimadoSimulado = $servicio->duracion_minutos . " min";
                $descripcionSimulada = $servicio->descripcion ?? '';
                $especialistasSimulados = [];
                $especialistasIdsSimulados = [];
            @endphp

            {{-- Tarjeta de Servicio --}}
            <article class="tarjeta-servicio-item"
                     data-id="{{ $idSimulado }}"
                     data-nombre="{{ $nombreServicio }}"
                     data-precio="{{ $precioSimulado }}"
                     data-porcentaje="{{ $porcentajeAgendadoSimulado }}"
                     data-tiempo="{{ $tiempoEstimadoSimulado }}"
                     data-descripcion="{{ $descripcionSimulada }}"
                     data-especialistas-nombres='{{ json_encode($especialistasSimulados) }}'
                     data-especialistas-ids='{{ json_encode($especialistasIdsSimulados) }}'>

                {{-- Imagen --}}
                <div class="contenedor-imagen-spa">
                    <img src="{{ $servicio->imagen ? asset('storage/' . $servicio->imagen) : asset('asset/img/parts/antiage.jpg') }}"
                         alt="{{ $nombreServicio }}"
                         class="foto-servicio-portada">
                    <span class="badge-categoria-flotante">Premium</span>
                </div>

                {{-- Información --}}
                <div class="info-servicio-body">
                    <h3 class="titulo-servicio-spa">{{ $nombreServicio }}</h3>
                    <p class="descripcion-servicio-spa">{{ $descripcionSimulada }}</p>

                    <div class="detalles-servicio">
                        <span class="tiempo-servicio">
                            <svg xmlns="http://www.w3.org/2000/svg" width="14" height="14" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            {{ $tiempoEstimadoSimulado }}
                        </span>
                        <span class="precio-tag">${{ $precioSimulado }}</span>
                    </div>
                </div>

                {{-- Botones de Acción --}}
                <footer class="footer-tarjeta">
                    <div class="acciones-catalogo-wrapper">
                        <button type="button" class="btn-secundario" onclick="verServicioDetalle('{{ $idSimulado }}')">
                            Consultar
                        </button>

                        @if(auth()->user()->rol === 'cliente')
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-primario">
                                Reservar
                            </a>

                        @elseif(auth()->user()->rol === 'admin')
                            {{-- Botón Editar con Ícono SVG --}}
                            <button type="button" class="btn-icono-accion btn-editar" onclick="editarServicio('{{ $idSimulado }}')" title="Editar Servicio">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                            </button>

                            {{-- Botón Eliminar con Ícono SVG --}}
                            <button type="button" class="btn-icono-accion btn-eliminar" onclick="eliminarServicio('{{ $idSimulado }}')" title="Eliminar Servicio">
                                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                            </button>

                        @elseif(auth()->user()->rol === 'recepcionista')
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-primario" style="background-color: #4f8eff;">
                                Agendar Cita
                            </a>
                        @endif
                    </div>
                </footer>
            </article>
        @empty
            <p style="text-align: center; color: var(--spa-text-muted); grid-column: 1 / -1; padding: 3rem;">
                No se encontraron servicios disponibles en este momento.
            </p>
        @endforelse
    </section>
</section>

{{-- ============================================
     ONBOARDING DE BIENVENIDA — 3 PASOS
     Solo se muestra a clientes (no admin, no recepcionista, no trabajador)
     ============================================ --}}
@if(auth()->user()->rol === 'cliente')
<dialog id="onboardingBienvenida" class="onboarding-bienvenida">

    {{-- Marca superior --}}
    <div class="onboarding-marca">
        <div class="logo-circulo">BR</div>
        <span class="nombre-marca">The Beauty Room</span>
    </div>

    {{-- Tarjeta principal --}}
    <div class="onboarding-tarjeta">

        {{-- Botón cerrar --}}
        <button type="button" class="onboarding-cerrar" id="onboardingCerrar" aria-label="Cerrar">×</button>

        {{-- LADO IZQUIERDO (oscuro) --}}
        <aside class="onboarding-lado-oscuro">
            <span class="onboarding-numero" id="onboardingNumero">01</span>

            <div class="onboarding-icono-circulo" id="onboardingIcono">
                {{-- Icono por defecto: chispa (paso 1) --}}
                <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                    <path d="M12 3v3m0 12v3m9-9h-3M6 12H3m15.364-6.364l-2.121 2.121M8.757 15.243l-2.121 2.121m12.728 0l-2.121-2.121M8.757 8.757L6.636 6.636"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
            </div>

            <div class="onboarding-frase">
                Your Beauty,<br>Your Moment
            </div>
        </aside>

        {{-- LADO DERECHO (claro) --}}
        <div class="onboarding-lado-claro">

            <div class="onboarding-contenido">

                {{-- Barra de progreso --}}
                <div class="onboarding-progreso" id="onboardingProgreso">
                    <span class="linea activa"></span>
                    <span class="linea"></span>
                    <span class="linea"></span>
                </div>

                {{-- PASO 1 --}}
                <div class="onboarding-paso activo" data-paso="1">
                    <span class="onboarding-etiqueta">Descubre</span>
                    <p class="onboarding-subtitulo">Bienvenida a The Beauty Room</p>
                    <h2 class="onboarding-titulo">Explora nuestros rituales</h2>
                    <p class="onboarding-descripcion">
                        Conoce todos nuestros servicios, sus beneficios, duración y precio
                        antes de elegir.
                    </p>
                </div>

                {{-- PASO 2 --}}
                <div class="onboarding-paso" data-paso="2">
                    <span class="onboarding-etiqueta">Conecta</span>
                    <p class="onboarding-subtitulo">Conoce a nuestras expertas</p>
                    <h2 class="onboarding-titulo">Encuentra tu match ideal</h2>
                    <p class="onboarding-descripcion">
                        Revisa el perfil y las especialidades de cada profesional para
                        encontrar a la terapeuta perfecta para ti.
                    </p>
                </div>

                {{-- PASO 3 --}}
                <div class="onboarding-paso" data-paso="3">
                    <span class="onboarding-etiqueta">Reserva</span>
                    <p class="onboarding-subtitulo">Agenda en pocos pasos</p>
                    <h2 class="onboarding-titulo">Tu momento te espera</h2>
                    <p class="onboarding-descripcion">
                        Elige servicio, fecha y hora. Recibirás tu confirmación
                        inmediatamente por correo.
                    </p>
                </div>

            </div>

            {{-- Pie: paginación y botón --}}
            <footer class="onboarding-pie">
                <span class="onboarding-paso-texto" id="onboardingPasoTexto">Paso 1 de 3</span>

                <button type="button" class="onboarding-btn-continuar" id="onboardingContinuar">
                    <span id="onboardingBtnTexto">Continuar</span>
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M5 12h14M13 5l7 7-7 7"/>
                    </svg>
                </button>
            </footer>

        </div>

    </div>

    {{-- Nota inferior --}}
    <p class="onboarding-nota">Este recorrido solo aparecerá esta vez.</p>

</dialog>
@endif

@include('compartidas.modal-catalogo')
@endsection

@push('scripts')
@vite(['resources/js/catalogo.js'])
@endpush