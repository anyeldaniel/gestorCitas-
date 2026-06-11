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
            <article class="tarjeta-terapeuta" data-id="{{ $terapeuta['id'] }}">
                
                <figure class="terapeuta-foto-contenedor">
                    <div class="foto-avatar-simulado">{{ substr($terapeuta['nombre'], 5, 2) }}</div>
                    <span class="tag-disponibilidad">{{ $terapeuta['disponibilidad'] }}</span>
                </figure>

                <div class="terapeuta-info">
                    <h2>{{ $terapeuta['nombre'] }}</h2>
                    <p class="terapeuta-especialidad">{{ $terapeuta['especialidad'] }}</p>
                    <small class="terapeuta-experiencia">{{ $terapeuta['experiencia'] }}</small>
                    <p class="terapeuta-descripcion">{{ $terapeuta['descripcion'] }}</p>
                </div>
<!--Lo mismo que arriba, condicional para mostrar acciones de gestión solo al admin-->
                @if($userRole === 'admin')
                    <footer class="terapeuta-acciones">
                        <button type="button" class="btn-terapeuta-editar" onclick="editarTerapeuta({{ $terapeuta['id'] }})">Editar Info</button>
                        <button type="button" class="btn-terapeuta-eliminar" onclick="eliminarTerapeuta({{ $terapeuta['id'] }})">Remover</button>
                    </footer>
                @endif

            </article>
        @empty
            <p class="grid-vacio">No hay terapeutas registrados en el sistema actualmente.</p>
        @endforelse
    </main>

</section>
@endsection

@push('scripts')
    @vite(['resources/js/terapeutas.js'])
@endpush