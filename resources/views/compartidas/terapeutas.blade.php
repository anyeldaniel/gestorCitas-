@extends('layouts.app')

@section('title', 'Nuestros Terapeutas')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<section class="modulo-terapeutas">

    <!-- HEADER -->
    <header class="terapeutas-header">
        <div class="terapeutas-header-texto">
            <h1>Nuestros Terapeutas</h1>
            <p>Conoce al equipo profesional detrás de las experiencias exclusivas de The Beauty Room.</p>
        </div>

        <div class="terapeutas-header-acciones">
            <!-- Buscador -->
            <div class="terapeutas-buscador">
                <svg class="buscador-icono" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
                </svg>
                <input
                    type="search"
                    id="buscador-terapeutas"
                    class="buscador-input"
                    placeholder="Buscar por nombre o especialidad..."
                    aria-label="Buscar terapeutas"
                    autocomplete="off"
                >
                <button type="button" id="buscador-limpiar" class="buscador-limpiar" aria-label="Limpiar búsqueda" hidden>
                    &times;
                </button>
            </div>

            <!-- Botón Registrar (solo admin) -->
            @if(auth()->check() && auth()->user()->rol === 'admin')
            <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
                <span class="icono-mas">+</span> Registrar Especialista
            </button>
            @endif
        </div>
    </header>

    {{-- Contador de resultados --}}
    <p id="buscador-resultados" class="buscador-resultados" aria-live="polite" hidden></p>

    {{-- Mensaje vacío --}}
    <div id="buscador-sin-resultados" class="buscador-sin-resultados" hidden>
        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
            <path stroke-linecap="round" stroke-linejoin="round" d="m21 21-4.35-4.35M17 11a6 6 0 1 1-12 0 6 6 0 0 1 12 0Z" />
        </svg>
        <p>No se encontraron terapeutas que coincidan con tu búsqueda.</p>
        <small>Prueba con otro nombre o especialidad.</small>
    </div>

    <!-- GRID DE TERAPEUTAS -->
    <main class="grid-terapeutas">
        @forelse($terapeutas as $terapeuta)
        <article class="tarjeta-terapeuta"
                 data-id="{{ $terapeuta->id }}"
                 data-especialidades="{{ $terapeuta->especialidades ?? '' }}">

            <!-- FOTO / PLACEHOLDER -->
            <figure class="terapeuta-foto-contenedor">
                @if($terapeuta->foto && file_exists(public_path('storage/' . $terapeuta->foto)))
                    <img src="{{ asset('storage/' . $terapeuta->foto) }}" alt="Foto de {{ $terapeuta->nombre }}">
                @else
                    <div class="foto-avatar-simulado" aria-label="Sin imagen disponible">
                        <svg class="icono-placeholder" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.4" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="3" width="18" height="18" rx="3"/>
                            <circle cx="9" cy="9" r="2"/>
                            <path d="M21 15l-5-5L5 21"/>
                        </svg>
                        <span class="texto-placeholder">Coloque imagen</span>
                    </div>
                @endif
                <span class="indicador-estado disponible"></span>
            </figure>

            <!-- INFORMACIÓN -->
            <div class="terapeuta-info">
                <span class="tag-especialidad">
                    @if(!empty($terapeuta->especialidades))
                        {{ explode(',', $terapeuta->especialidades)[0] }}
                    @else
                        Especialista en Bienestar
                    @endif
                </span>

                <h2 class="terapeuta-nombre">{{ $terapeuta->nombre }}</h2>

                @php
                    $promedio = $terapeuta->promedio ?? 0;
                    $totalResenas = $terapeuta->total_resenas ?? 0;
                    $estrellasLlenas = floor($promedio);
                    $tieneMedia = ($promedio - $estrellasLlenas) >= 0.5;
                @endphp

                <div class="contenedor-estrellas" aria-label="{{ number_format($promedio, 1) }} de 5 estrellas basado en {{ $totalResenas }} reseñas">
                    <span class="estrellas">
                        @for($i = 1; $i <= 5; $i++)
                            @if($i <= $estrellasLlenas)
                                ★
                            @elseif($i == $estrellasLlenas + 1 && $tieneMedia)
                                <span style="color: var(--spa-gold); opacity: 0.55;">★</span>
                            @else
                                <span style="color: #d1c7bd;">★</span>
                            @endif
                        @endfor
                    </span>
                    <span class="rating-valor">{{ number_format($promedio, 1) }}</span>
                    <span class="rating-cantidad">({{ $totalResenas }})</span>
                </div>

                <!-- DATOS OCULTOS PARA JS -->
                <p class="terapeuta-telefono" style="display:none;">{{ $terapeuta->telefono ?? 'S/N' }}</p>
                <p class="terapeuta-email" style="display:none;">{{ $terapeuta->correo ?? '' }}</p>
                <p class="terapeuta-descripcion" style="display:none;">{{ $terapeuta->descripcion ?? '' }}</p>
            </div>

            <!-- ACCIONES -->
            <footer class="terapeuta-acciones">
                <button type="button" class="btn-zen" onclick="verTerapeutaDetalle({{ $terapeuta->id }})">
                    Ver Perfil
                    <span class="flecha">→</span>
                </button>

                @if(auth()->check() && auth()->user()->rol === 'admin')
                <div style="display:flex; gap:0.25rem; width:100%;">
                    <button type="button" class="btn-terapeuta-editar" style="flex:1;" onclick="editarTerapeuta({{ $terapeuta->id }})">Editar Info</button>
                    <button type="button" class="btn-terapeuta-eliminar" style="flex:1;" onclick="eliminarTerapeuta({{ $terapeuta->id }})">Dar de Baja</button>
                </div>
                @endif
            </footer>

        </article>
        @empty
        <div class="contenedor-vacio">
            <p>No hay terapeutas registrados actualmente.</p>
        </div>
        @endforelse
    </main>

    <!-- MODAL COMPARTIDO -->
    @include('compartidas.modal-terapeuta', ['userRole' => $userRole ?? null])

</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/terapeutas.js'])
@endpush