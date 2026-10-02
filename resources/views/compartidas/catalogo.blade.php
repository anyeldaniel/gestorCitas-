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
        
        @php
            $directorio = public_path('asset/img/parts');
            $imagenes = \Illuminate\Support\Facades\File::exists($directorio) 
                ? \Illuminate\Support\Facades\File::files($directorio) 
                : [];
        @endphp

        @forelse($imagenes as $imagen)
            @php
                $nombreArchivo = pathinfo($imagen->getFilename(), PATHINFO_FILENAME);
                $nombreServicio = str_replace('-', ' ', $nombreArchivo);
                
                // Datos simulados (en producción esto vendría de la BD)
                $idSimulado = $loop->iteration;
                $precioSimulado = 35 + ($idSimulado * 5);
                $porcentajeAgendadoSimulado = 10; 
                $tiempoEstimadoSimulado = "60 min - 90 min";
                $descripcionSimulada = "Tratamiento exclusivo diseñado para liberar tensiones corporales, restaurar la vitalidad de la piel y proveer un estado absoluto de relajación zen.";
                $especialistasSimulados = ["Ana Gómez", "Carlos Ruiz"];
                $especialistasIdsSimulados = [1, 2];
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
                    <img src="{{ asset('asset/img/parts/' . $imagen->getFilename()) }}" 
                         alt="{{ $nombreArchivo }}" 
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

{{-- Inclusión del Modal (Asegúrate de que esté en la carpeta correcta) --}}
@include('compartidas.modal-catalogo')
@endsection

@push('scripts')
    @vite(['resources/js/catalogo.js'])
@endpush