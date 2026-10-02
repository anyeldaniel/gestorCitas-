@extends('layouts.app')

@section('title', 'Nuestros Terapeutas')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<section class="modulo-terapeutas">

    <!-- HEADER -->
    <header class="terapeutas-header">
        <div>
            <h1>Nuestros Terapeutas</h1>
            <p>Conoce al equipo profesional detrás de las experiencias exclusivas de The Beauty Room.</p>
        </div>
        @if(isset($userRole) && $userRole === 'admin')
        <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
            <span class="icono-mas">+</span> Registrar Especialista
        </button>
        @endif
    </header>

    <!-- GRID DE TERAPEUTAS -->
    <main class="grid-terapeutas">
        @forelse($terapeutas as $terapeuta)
        <article class="tarjeta-terapeuta"
                 data-id="{{ $terapeuta->id }}"
                 data-especialidades="{{ $terapeuta->especialidades ?? '' }}">

            <!-- FOTO / PLACEHOLDER -->
            <figure class="terapeuta-foto-contenedor">
                @if($terapeuta->foto && file_exists(public_path('storage/' . $terapeuta->foto)))
                    <img src="{{ asset('storage/' . $terapeuta->foto) }}"
                         alt="Foto de {{ $terapeuta->nombre }}">
                @else
                    <div class="foto-avatar-simulado" aria-label="Sin imagen disponible">
                        <svg class="icono-placeholder"
                             xmlns="http://www.w3.org/2000/svg"
                             viewBox="0 0 24 24"
                             fill="none"
                             stroke="currentColor"
                             stroke-width="1.4"
                             stroke-linecap="round"
                             stroke-linejoin="round">
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

                <div class="contenedor-estrellas">
                    <span class="estrellas">★★★★★</span>
                    <span class="rating-valor">5.0</span>
                    <span class="rating-cantidad">(0)</span>
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

                @if(isset($userRole) && $userRole === 'admin')
                <div class="admin-controls">
                    <button type="button" class="btn-terapeuta-editar" onclick="editarTerapeuta({{ $terapeuta->id }})">Editar</button>
                    <button type="button" class="btn-terapeuta-eliminar" onclick="eliminarTerapeuta({{ $terapeuta->id }})">Eliminar</button>
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
@vite(['resources/js/terapeutas.js'])
@endpush