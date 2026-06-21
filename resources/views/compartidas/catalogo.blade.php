@extends('layouts.app')

@section('title', 'Catálogo de Bienestar Premium')

@push('styles')
    @vite(['resources/css/modal.css'])
@endpush

@section('content')
{{-- Contenedor principal de la vista del módulo --}}
<section class="modulo-vista">
    <header class="encabezado-modulo mb-8">
        <h1>Nuestras Terapias y Experiencias</h1>
        <p>Selecciona el tratamiento ideal para restaurar tu equilibrio corporal y estético.</p>
        
        {{-- Acción exclusiva del Administrador para añadir nuevos servicios --}}
        @if(auth()->user()->rol === 'admin')
            <div style="margin-top: 1.5rem; display: flex; justify-content: flex-end;">
                <button type="button" class="btn-zen" onclick="abrirModalAgregarServicio()" style="background-color: #00a6fb; display: flex; align-items: center; gap: 0.5rem; font-weight: 600;">
                    <svg xmlns="http://www.w3.org/2000/svg" style="width:1.25rem; height:1.25rem;" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Agregar Servicio
                </button>
            </div>
        @endif
    </header>

    @if(session('success'))
        <div class="alert-success-spa">
            <span class="icono-zen">✨</span> {{ session('success') }}
        </div>
    @endif

    {{-- Rejilla con inline styles definitivos para forzar el tamaño compacto de la imagen --}}
    <section class="rejilla-catalogo" id="contenedor-servicios-catalogo" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(260px, 310px)); gap: 1.5rem; justify-content: center; width: 100%; max-width: 1200px; margin: 0 auto; padding: 1rem 0;">
        
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
                
                $idSimulado = $loop->iteration;
                $precioSimulado = 35 + ($idSimulado * 5);
                $porcentajeAgendadoSimulado = 10; 
                $tiempoEstimadoSimulado = "60 min - 90 min";
                $descripcionSimulada = "Tratamiento exclusivo diseñado para liberar tensiones corporales, restaurar la vitalidad de la piel y proveer un estado absoluto de relajación zen.";
                $especialistasSimulados = ["Ana Gómez", "Carlos Ruiz"];
                $especialistasIdsSimulados = [1, 2];
            @endphp

            {{-- Tarjeta Estructural con Atributos de Datos Necesarios para JS --}}
            <article class="tarjeta-componente tarjeta-servicio-item" 
                     data-id="{{ $idSimulado }}"
                     data-nombre="{{ $nombreServicio }}"
                     data-precio="{{ $precioSimulado }}"
                     data-porcentaje="{{ $porcentajeAgendadoSimulado }}"
                     data-tiempo="{{ $tiempoEstimadoSimulado }}"
                     data-descripcion="{{ $descripcionSimulada }}"
                     data-especialistas-nombres='{{ json_encode($especialistasSimulados) }}'
                     data-especialistas-ids='{{ json_encode($especialistasIdsSimulados) }}'
                     style="display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; max-width: 310px; width: 100%; box-sizing: border-box; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                <div>
                    <img src="{{ asset('asset/img/parts/' . $imagen->getFilename()) }}" 
                         alt="{{ $nombreArchivo }}" 
                         class="img-portada foto-servicio-portada"
                         style="width: 100%; height: 170px; object-fit: cover; border-radius: 8px; margin-bottom: 0.75rem; display: block;">
                    
                    <header>
                        <h3 class="text-lg font-semibold capitalize" style="margin: 0 0 0.25rem 0; font-size: 1.15rem; color: #1e293b; line-height: 1.3;">
                            {{ $nombreServicio }}
                        </h3>
                        <p class="text-sm text-gray-500" style="margin: 0 0 0.4rem 0; font-size: 0.85rem; color: #64748b;">Tratamiento exclusivo de nuestro spa.</p>
                        <small class="texto-atenuado" style="display: block; margin-bottom: 0.75rem; font-size: 0.8rem; color: #94a3b8;">
                            ⏱️ Rango: <span class="tag-rol especialista">{{ $tiempoEstimadoSimulado }}</span>
                        </small>
                    </header>
                </div>
                
                <footer class="flex justify-between items-center mt-2" style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 0.4rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem; margin-top: auto;">
                    <span class="precio-tag price-tag" style="font-weight: 700; font-size: 1.1rem; color: #1e293b;">${{ $precioSimulado }}</span>
                    
                    <div class="acciones-catalogo-wrapper" style="display: flex; gap: 0.35rem; align-items: center;">
                        <button type="button" class="btn-secundario" style="padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; cursor: pointer;" 
                                onclick="verServicioDetalle('{{ $idSimulado }}')">
                            Consultar
                        </button>

                        @if(auth()->user()->rol === 'cliente')
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-zen" style="padding: 0.4rem 0.85rem; font-size: 0.8rem; border-radius: 6px;">
                                Reservar
                            </a>
                            
                        @elseif(auth()->user()->rol === 'admin')
                            <button type="button" class="btn-secundario" style="background-color: #e2e8f0; color: #334155; padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; border: none; cursor: pointer;" 
                                    onclick="editarServicio('{{ $idSimulado }}')">
                                Editar
                            </button>
                            
                            <button type="button" class="btn-baja" style="background: rgba(255, 90, 125, 0.08); color: #ff5a7d; border: 1px solid rgba(255, 90, 125, 0.3); padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; cursor: pointer;" 
                                    onclick="eliminarServicio('{{ $idSimulado }}')">
                                Eliminar
                            </button>
                            
                        @elseif(auth()->user()->rol === 'recepcionista')
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-zen" style="background-color: #4f8eff; padding: 0.4rem 0.85rem; font-size: 0.8rem; border-radius: 6px;">
                                Agendar Cita
                            </a>
                            
                        @elseif(auth()->user()->rol === 'trabajador')
                            <span class="tag-especialidad" style="padding: 0.3rem 0.5rem; font-size: 0.75rem; background-color: #f1f5f9; color: #475569; border-radius: 4px; font-weight: 600;">
                                Premium
                            </span>
                        @endif
                    </div>
                </footer>
            </article>
        @empty
            <p class="texto-atenuado">No se encontraron imágenes en la carpeta de servicios. Por favor, verifica la ruta en tu proyecto local.</p>
        @endforelse
    </section>
</section>

{{-- Inclusión del Modal Único Desacoplado --}}
@include('compartidas.modal-catalogo')
@endsection

@push('scripts')
    @vite(['resources/js/catalogo.js'])
@endpush