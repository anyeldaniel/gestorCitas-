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

    {{-- ✅ MENSAJE DE ÉXITO OCULTO (El JS lo leerá para mostrar el SweetAlert) --}}
    @if(session('success'))
    <div id="dashboard-mensaje-exito" data-mensaje="{{ session('success') }}" style="display: none;"></div>
    @endif

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
         <img src="{{ asset('storage/' . $terapeuta->foto) }}" 
                   alt="Foto de {{ $terapeuta->nombre }}"
                 style="cursor: zoom-in;"
                 onclick="abrirVisorFoto('{{ asset('storage/' . $terapeuta->foto) }}', '{{ addslashes($terapeuta->nombre) }}')">
            @else
        <div class="foto-avatar-simulado" aria-label="Sin imagen disponible">
            <!-- ... tu svg y texto ... -->
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

                <!-- BOTÓN DE RESEÑAS -->
                @php
                    // Preparamos los datos en un array de PHP y lo convertimos a JSON
                    $resenasData = $terapeuta->resenasRecibidas->map(function($r) {
                        return [
                            "puntuacion" => $r->puntuacion,
                            "comentario" => $r->comentario ?? "Sin comentario.",
                            "cliente" => $r->cliente->nombre ?? "Cliente Anónimo",
                            "fecha" => $r->created_at->format("d M, Y")
                        ];
                    })->toJson(); 
                @endphp

                <button type="button" class="btn-zen" style="background: #f6f1ea; color: #3d2c1e; border: 1px solid #e8e2dd; margin-top: 0.5rem;" 
                        data-id="{{ $terapeuta->id }}"
                        data-nombre="{{ addslashes($terapeuta->nombre) }}"
                        data-promedio="{{ $terapeuta->promedio }}"
                        data-total="{{ $terapeuta->total_resenas }}"
                        data-resenas="{{ $resenasData }}"
                        onclick="verResenasTerapeuta(this)">
                    Ver Reseñas
                    <span class="flecha" style="color: #8b5e3c;">★</span>
                </button>
                <!-- FIN BOTÓN DE RESEÑAS -->

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
 
    <!-- VISOR DE FOTO (LIGHTBOX) -->
<dialog id="visor-foto-terapeuta" style="border: none; background: transparent; padding: 0; outline: none; max-width: 100vw; max-height: 100vh; overflow: visible;">
    <div style="position: fixed; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; background: rgba(0,0,0,0.85); backdrop-filter: blur(5px);" onclick="document.getElementById('visor-foto-terapeuta').close()">
        
        <button type="button" style="position: absolute; top: 20px; right: 30px; background: none; border: none; color: white; font-size: 2.5rem; cursor: pointer;" onclick="document.getElementById('visor-foto-terapeuta').close()">&times;</button>
        
        <img id="visor-foto-imagen" src="" alt="Vista ampliada" style="max-width: 90vw; max-height: 85vh; object-fit: contain; border-radius: 12px; box-shadow: 0 10px 30px rgba(0,0,0,0.5);">
        
        <h3 id="visor-foto-nombre" style="color: white; margin-top: 1rem; font-family: var(--font-display); font-size: 1.5rem;"></h3>
    </div>
</dialog>

<!-- ========================================== -->
<!-- MODAL DE RESEÑAS (ESTILO BLINDADO E INLINE)-->
<!-- ========================================== -->
<div id="modal-resenas" style="display: none; position: fixed; inset: 0; width: 100vw; height: 100vh; background-color: rgba(45, 42, 38, 0.75); backdrop-filter: blur(5px); -webkit-backdrop-filter: blur(5px); z-index: 2147483647; align-items: center; justify-content: center; padding: 1rem; box-sizing: border-box; margin: 0; border: none;">
    
    <!-- Caja contenedora blanca centrada -->
    <div style="background: #fbf8f5; width: 100%; max-width: 580px; max-height: 85vh; border-radius: 14px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5); display: flex; flex-direction: column; overflow: hidden; position: relative; border: 1px solid #efe9e1;">
        
        <!-- Cabecera marrón -->
        <div style="background-color: #8b5e3c; padding: 1.5rem 2rem; color: #ffffff; display: flex; justify-content: space-between; align-items: flex-start; gap: 1rem; flex-shrink: 0;">
            <div style="display: flex; flex-direction: column; flex: 1; min-width: 0;">
                <span style="font-family: 'Inter', sans-serif; font-size: 0.68rem; font-weight: 700; letter-spacing: 0.15em; text-transform: uppercase; margin-bottom: 0.35rem; color: rgba(255, 255, 255, 0.8);">OPINIONES VERIFICADAS</span>
                <h2 id="resenas-titulo-terapeuta" style="font-family: 'Cormorant Garamond', serif; font-size: 1.8rem; font-weight: 600; margin: 0; color: #ffffff; line-height: 1.2;">Reseñas</h2>
            </div>
            <button type="button" onclick="cerrarModalResenas()" style="background: rgba(255, 255, 255, 0.15); border: none; color: #ffffff; width: 34px; height: 34px; border-radius: 50%; display: flex; align-items: center; justify-content: center; cursor: pointer; flex-shrink: 0;" aria-label="Cerrar modal">
                ✕
            </button>
        </div>
        
        <!-- Cuerpo con scroll para las reseñas -->
        <div id="lista-resenas" style="padding: 1.75rem 2rem; overflow-y: auto; display: flex; flex-direction: column; gap: 1.25rem; flex: 1; box-sizing: border-box; background: #fbf8f5;">
            <!-- Contenido dinámico -->
        </div>

    </div>
</div>
</section>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/terapeutas.js'])
@endpush