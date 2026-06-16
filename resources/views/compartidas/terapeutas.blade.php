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
        @if($userRole === 'admin')
            <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
                <span class="icono-mas">+</span> Registrar Especialista
            </button>
        @endif
    </header>

    <main class="grid-terapeutas">
        @forelse($terapeutas as $terapeuta)
            <article class="tarjeta-terapeuta" data-id="{{ $terapeuta['id'] }}" data-especialidades="{{ $terapeuta['especialidades'] ?? '' }}">
                
                <figure class="terapeuta-foto-contenedor">
                    <div class="foto-avatar-simulado">
                        {{ substr($terapeuta['nombre'], 0, 2) }}
                    </div>
                    <span class="tag-disponibilidad">{{ $terapeuta['disponibilidad'] ?? 'Disponible' }}</span>
                </figure>

                <div class="terapeuta-info">
                    <h2>{{ $terapeuta['nombre'] }}</h2>
                    <p class="terapeuta-telefono">Teléfono: {{ $terapeuta['telefono'] ?? 'S/N' }}</p>
                    <p class="terapeuta-email" style="display:none;">{{ $terapeuta['correo'] ?? '' }}</p>
                    <small class="terapeuta-experiencia">Experiencia: {{ $terapeuta['experiencia'] ?? '3 años' }}</small>
                    
                    <div class="contenedor-tags-especialidades">
                        @if(!empty($terapeuta['especialidades']))
                            @foreach(explode(',', $terapeuta['especialidades']) as $esp)
                                <span class="tag-especialidad">{{ trim($esp) }}</span>
                            @endforeach
                        @endif
                    </div>

                    <p class="terapeuta-descripcion">{{ $terapeuta['descripcion'] }}</p>
                </div>

                <footer class="terapeuta-acciones" style="display:flex; flex-direction:column; gap:0.4rem; padding:0.5rem;">
                    <button type="button" class="btn-zen" style="width:100%; background-color:#e2e8f0; color:#1e293b;" onclick="verTerapeutaDetalle({{ $terapeuta['id'] }})">Ver Terapeuta</button>
                    
                    @if($userRole === 'admin')
                        <div style="display:flex; gap:0.25rem; width:100%;">
                            <button type="button" class="btn-terapeuta-editar" style="flex:1;" onclick="editarTerapeuta({{ $terapeuta['id'] }})">Editar Info</button>
                            <button type="button" class="btn-terapeuta-eliminar" style="flex:1;" onclick="eliminarTerapeuta({{ $terapeuta['id'] }})">Dar de Baja</button>
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