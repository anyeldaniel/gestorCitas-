@extends('layouts.app')

@section('title', 'Nuestros Terapeutas')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<section class="modulo-terapeutas">

    <header class="terapeutas-header">
        <div>
            <h1>Especialistas en Bienestar</h1>
            <p>Conoce al equipo profesional detrás de las experiencias exclusivas de The Beauty Room.</p>
        </div>
        <!-- Añado condicional para que, dependieno del rol, se muestren las opcionesde gestion de personal-->
        @if(auth()->check() && auth()->user()->rol === 'admin')
        <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
            <span class="icono-mas">+</span> Registrar Especialista
        </button>
        @endif
    </header>

    <main class="grid-terapeutas">
        @forelse($terapeutas as $terapeuta)
        <article class="tarjeta-terapeuta" data-id="{{ $terapeuta->id }}" data-especialidades="{{ $terapeuta->especialidades ?? '' }}">

            <figure class="terapeuta-foto-contenedor">
                <!-- Condicional para mostrar la foto real o el avatar simulado con el icono y las iniciales -->
                @if($terapeuta->foto && file_exists(public_path('storage/' . $terapeuta->foto)))
                <img src="{{ asset('storage/' . $terapeuta->foto) }}" alt="Foto de {{ $terapeuta->nombre }}" style="width:100%; height:100%; object-fit:cover; border-radius:inherit;">
                @else
                <div class="foto-avatar-simulado" style="display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 0.2rem; width: 100%; height: 100%;">
                    <!-- Ícono SVG Zen de silueta de perfil -->
                    <svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" fill="currentColor" style="width: 28px; height: 28px; opacity: 0.7; color: #64748b;">
                        <path fill-rule="evenodd" d="M7.5 6a4.5 4.5 0 1 1 9 0 4.5 4.5 0 0 1-9 0ZM3.751 20.105a8.25 8.25 0 0 1 16.498 0 .75.75 0 0 1-.437.695A18.683 18.683 0 0 1 12 22.5c-2.786 0-5.433-.608-7.812-1.7a.75.75 0 0 1-.437-.695Z" clip-rule="evenodd" />
                    </svg>
                    <span style="font-size: 0.9rem; font-weight: 700; text-transform: uppercase; color: #475569; letter-spacing: 0.5px;">
                        {{ substr($terapeuta->nombre, 0, 2) }}
                    </span>
                </div>
                @endif
            </figure>

            <div class="terapeuta-info">
                <h2>{{ $terapeuta->nombre }}</h2>
                <p class="terapeuta-telefono">Teléfono: {{ $terapeuta->telefono ?? 'S/N' }}</p>
                <p class="terapeuta-email" style="display:none;">{{ $terapeuta->correo ?? '' }}</p>

                <!-- Carga dinámica de especialidades guardadas en la base de datos -->
                <div class="contenedor-tags-especialidades">
                    @if(!empty($terapeuta->especialidades))
                    @foreach(explode(',', $terapeuta->especialidades) as $esp)
                    <span class="tag-especialidad">{{ trim($esp) }}</span>
                    @endforeach
                    @else
                    <span style="color:#94a3b8; font-size:0.8rem; font-style:italic;">Sin especialidades</span>
                    @endif
                </div>

                <!-- Descripción dinámica desde la Base de Datos -->
                <p class="terapeuta-descripcion">{{ $terapeuta->descripcion ?? 'Especialista profesional en tratamientos de bienestar.' }}</p>
            </div>

            <footer class="terapeuta-acciones" style="display:flex; flex-direction:column; gap:0.4rem; padding:0.5rem;">
                <button type="button" class="btn-zen" style="width:100%; background-color:#e2e8f0; color:#1e293b;" onclick="verTerapeutaDetalle({{ $terapeuta->id }})">Ver Terapeuta</button>

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
            <p class="grid-vacio">No hay terapeutas registrados en el sistema actualmente.</p>
        </div>
        @endforelse
    </main>
    <!--Inyecto el archivo parcial reutilizable del modal para crear y editar terapeutas, evitando código duplicado y manteniendo la estructura ordenada -->
    @include('compartidas.modal-terapeuta')

</section>
@endsection

@push('scripts')
@vite(['resources/js/terapeutas.js'])
@endpush