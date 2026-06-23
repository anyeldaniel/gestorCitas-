@extends('layouts.app')

@section('title', 'Nuestros Terapeutas')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<section class="modulo-terapeutas">

    <header class="terapeutas-header">
        <div>
            <h1>Nuestros Terapeutas</h1>
            <p>Conoce al equipo profesional detrás de las experiencias exclusivas de The Beauty Room.</p>
        </div>
        @if($userRole === 'admin')
        <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
            <span class="icono-mas">+</span> Registrar Especialista
        </button>
        @endif
    </header>

    <main class="grid-terapeutas">
        @forelse($terapeutas as $terapeuta)
        <article class="tarjeta-terapeuta" data-id="{{ $terapeuta->id }}" data-especialidades="{{ $terapeuta->especialidades ?? '' }}">

            <figure class="terapeuta-foto-contenedor">
                @if($terapeuta->foto && file_exists(public_path('storage/' . $terapeuta->foto)))
                <img src="{{ asset('storage/' . $terapeuta->foto) }}" alt="Foto de {{ $terapeuta->nombre }}">
                @else
                <div class="foto-avatar-simulado">
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                    </svg>
                    <span>{{ substr($terapeuta->nombre, 0, 2) }}</span>
                </div>
                @endif
            </figure>

            <div class="terapeuta-info">
                <h2>{{ $terapeuta->nombre }}</h2>
                
                <div class="contenedor-tags-especialidades">
                    @if(!empty($terapeuta->especialidades))
                        <span class="tag-especialidad">{{ explode(',', $terapeuta->especialidades)[0] }}</span>
                    @else
                        <span class="tag-especialidad">Especialista en Bienestar</span>
                    @endif
                </div>

                <div class="contenedor-estrellas">
                    ★★★★★
                </div>

                <p class="terapeuta-telefono" style="display:none;">{{ $terapeuta->telefono ?? 'S/N' }}</p>
                <p class="terapeuta-email" style="display:none;">{{ $terapeuta->correo ?? '' }}</p>
                <p class="terapeuta-descripcion" style="display:none;">{{ $terapeuta->descripcion ?? '' }}</p>
            </div>

            <footer class="terapeuta-acciones">
                <button type="button" class="btn-zen" onclick="verTerapeutaDetalle({{ $terapeuta->id }})">Ver Perfil</button>

                @if($userRole === 'admin')
                <div class="admin-controls">
                    <button type="button" class="btn-terapeuta-editar" onclick="editarTerapeuta({{ $terapeuta->id }})">Editar</button>
                    <button type="button" class="btn-terapeuta-eliminar" onclick="eliminarTerapeuta({{ $terapeuta->id }})">Baja</button>
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

    @include('compartidas.modal-terapeuta')

</section>
@endsection

@push('scripts')
@vite(['resources/js/terapeutas.js'])
@endpush