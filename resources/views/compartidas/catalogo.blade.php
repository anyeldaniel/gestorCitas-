@extends('layouts.app')

@section('title', 'Catálogo de Bienestar Premium')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo mb-8">
        <h1>Nuestras Terapias y Experiencias</h1>
        <p>Selecciona el tratamiento ideal para restaurar tu equilibrio corporal y estético.</p>
        
        {{-- Acción exclusiva del Administrador para añadir nuevos servicios en un modal sin recargar página --}}
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

    <section class="rejilla-catalogo" id="catalogo-tratamientos">
        
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
                
                // Simulación de datos que vendrán de la base de datos para las interacciones dinámicas
                $idSimulado = $loop->iteration;
                $precioSimulado = 35 + ($idSimulado * 5);
                $porcentajeAgendadoSimulado = 10; // Ejemplo: 10% fijado por el admin
                $tiempoEstimadoSimulado = "60 min - 90 min";
                $descripcionSimulada = "Tratamiento exclusivo diseñado para liberar tensiones corporales, restaurar la vitalidad de la piel y proveer un estado absoluto de relajación zen.";
                $especialistasSimulados = ["Ana Gómez", "Carlos Ruiz"];
            @endphp

            <article class="tarjeta-componente" style="display: flex; flex-direction: column; justify-content: space-between;">
                <div>
                    <img src="{{ asset('asset/img/parts/' . $imagen->getFilename()) }}" 
                         alt="{{ $nombreArchivo }}" 
                         class="img-portada">
                    
                    <header>
                        <h3 class="text-lg font-semibold capitalize" style="margin-bottom: 0.25rem;">
                            {{ $nombreServicio }}
                        </h3>
                        <p class="text-sm text-gray-500" style="margin-bottom: 0.5rem;">Tratamiento exclusivo de nuestro spa.</p>
                        {{-- CORREGIDO: Eliminada la 'r' que provocaba el desplome del renderizado --}}
                        <small class="texto-atenuado" style="display: block; margin-bottom: 1rem;">
                            ⏱️ Rango: {{ $tiempoEstimadoSimulado ?? '60-90 min' }}
                        </small>
                    </header>
                </div>
                
                <footer class="flex justify-between items-center mt-2" style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 0.5rem; flex-wrap: wrap;">
                    <span class="precio-tag">${{ $precioSimulado }}</span>
                    
                    <div class="acciones-catalogo-wrapper" style="display: flex; gap: 0.35rem; align-items: center;">
                        {{-- Botón universal de Consultar Detalles disponible para todos los roles --}}
                        <button type="button" class="btn-secundario" style="padding: 0.5rem 0.75rem; font-size: 0.85rem; border-radius: 6px;" 
                                onclick="consultarServicio('{{ $nombreServicio }}', '${{ $precioSimulado }}', '{{ $porcentajeAgendadoSimulado }}%', '{{ $tiempoEstimadoSimulado }}', '{{ $descripcionSimulada }}', {{ json_encode($especialistasSimulados) }}, '{{ asset('asset/img/parts/' . $imagen->getFilename()) }}')">
                            Consultar
                        </button>

                        {{-- Condicional aplicada en la vista según el Rol Operativo --}}
                        @if(auth()->user()->rol === 'cliente')
                            {{-- El cliente puede reservar directamente e inyectar los datos en la vista de reservas --}}
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-zen" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                                Reservar
                            </a>
                            
                        @elseif(auth()->user()->rol === 'admin')
                            {{-- El administrador maneja los botones de Editar y Eliminar de forma directa en la tarjeta --}}
                            <button type="button" class="btn-secundario" style="background-color: #cbd5e1; padding: 0.5rem 0.75rem; font-size: 0.85rem;" 
                                    onclick="abrirModalEditarServicio({id: '{{ $idSimulado }}', nombre: '{{ $nombreServicio }}', precio: '{{ $precioSimulado }}', porcentaje: '{{ $porcentajeAgendadoSimulado }}', tiempo: '{{ $tiempoEstimadoSimulado }}', descripcion: '{{ $descripcionSimulada }}', foto: '{{ asset('asset/img/parts/' . $imagen->getFilename()) }}'})">
                                Editar
                            </button>
                            
                            <button type="button" class="btn-baja" style="background: rgba(255, 90, 125, 0.08); color: #ff5a7d; border: 1px solid rgba(255, 90, 125, 0.3); padding: 0.5rem 0.75rem; font-size: 0.85rem; border-radius: 6px;" 
                                    onclick="confirmarEliminarServicio('{{ $idSimulado }}', '{{ $nombreServicio }}')">
                                Eliminar
                            </button>
                            
                        @elseif(auth()->user()->rol === 'recepcionista')
                            {{-- El personal de recepción puede asistir agendando la cita directamente --}}
                            <a href="{{ route('clientes.reserva') }}?servicio_id={{ $idSimulado }}&nombre={{ urlencode($nombreServicio) }}&precio={{ $precioSimulado }}&porcentaje={{ $porcentajeAgendadoSimulado }}" class="btn-zen" style="background-color: #4f8eff; padding: 0.5rem 1rem; font-size: 0.85rem;">
                                Agendar Cita
                            </a>
                            
                        @elseif(auth()->user()->rol === 'trabajador')
                            {{-- Los especialistas o terapeutas solo visualizan el tratamiento sin acciones de agendamiento ajenas --}}
                            <span class="tag-especialidad" style="padding: 0.4rem 0.60rem; font-size: 0.8rem; background-color: #f1f5f9; color: #475569; border-radius: 4px; font-weight: 600;">
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

{{-- Modales Interactivos en la misma vista sin cambiar de enlace --}}

<dialog id="modalConsultarServicio" class="modal-zen">
    <div class="modal-header">
        <h2 id="consultarNombre">Detalles del Tratamiento</h2>
        <button type="button" class="btn-cerrar-modal" onclick="document.getElementById('modalConsultarServicio').close()">&times;</button>
    </div>
    <div style="padding: 1.5rem; display: flex; flex-direction: column; gap: 1rem;">
        <img id="consultarFoto" src="" alt="Ficha de servicio" style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
        <p style="font-size: 0.95rem; color: #475569;" id="consultarDescripcion"></p>
        <div style="background: #f8fafc; padding: 1rem; border-radius: 8px; display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; font-size: 0.9rem;">
            <div><strong>Precio Base:</strong> <span id="consultarPrecio"></span></div>
            <div><strong>Abono Agendado:</strong> <span id="consultarPorcentaje"></span></div>
            <div><strong>Tiempo Estimado:</strong> <span id="consultarTiempo"></span></div>
        </div>
        <div>
            <strong style="font-size: 0.9rem; color: #1e293b; display: block; margin-bottom: 0.25rem;">Especialistas Certificados:</strong>
            <div id="consultarEspecialistas" class="contenedor-tags-especialidades"></div>
        </div>
        <div style="display: flex; justify-content: flex-end; margin-top: 0.5rem;">
            <button type="button" class="btn-secundario" onclick="document.getElementById('modalConsultarServicio').close()">Regresar</button>
        </div>
    </div>
</dialog>

<dialog id="modalFormServicio" class="modal-zen">
    <div class="modal-header">
        <h2 id="formServicioTitle">Agregar Nuevo Servicio</h2>
        <button type="button" class="btn-cerrar-modal" onclick="cerrarFormServicio()">&times;</button>
    </div>
    <form id="formActionServicio" action="{{ route('servicios.guardar') }}" method="POST" class="modal-form" enctype="multipart/form-data">
        @csrf
        <input type="hidden" id="servicio_id" name="id">
        <input type="hidden" id="servicio_method" name="_method" value="POST">

        <div class="campo-formulario">
            <label for="inputNombre">Nombre del Servicio *</label>
            <input type="text" id="inputNombre" name="nombre" required placeholder="Ej. Masaje Relajante Premium">
        </div>

        <div class="grupo-seguridad-modal">
            <div class="campo-formulario">
                <label for="inputPrecio">Precio del Servicio ($) *</label>
                <input type="number" id="inputPrecio" name="precio" required step="0.01" placeholder="35.00">
            </div>
            <div class="campo-formulario">
                <label for="inputPorcentaje">Porcentaje de Agendado (%) *</label>
                <input type="number" id="inputPorcentaje" name="porcentaje_agendado" required min="0" max="100" placeholder="10">
            </div>
        </div>

        <div class="campo-formulario">
            <label for="inputTiempo">Tiempo Estimado (Rango de Horas) *</label>
            <select id="inputTiempo" name="tiempo_estimado" required style="padding:0.65rem 0.75rem; border:1px solid #cbd5e1; border-radius:0.375rem;">
                <option value="30 min - 50 min">30 min - 50 min</option>
                <option value="60 min - 90 min" selected>60 min - 90 min</option>
                <option value="90 min - 120 min">90 min - 120 min</option>
                <option value="120 min - 180 min">120 min - 180 min</option>
            </select>
        </div>

        <div class="campo-formulario">
            <label>Especialistas Asignados</label>
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.5rem; padding: 0.5rem 0;">
                <label style="font-weight: normal; display: flex; align-items: center; gap: 0.35rem;">
                    <input type="checkbox" name="especialistas[]" value="Ana Gómez" style="width: auto;"> Ana Gómez
                </label>
                <label style="font-weight: normal; display: flex; align-items: center; gap: 0.35rem;">
                    <input type="checkbox" name="especialistas[]" value="Carlos Ruiz" style="width: auto;"> Carlos Ruiz
                </label>
            </div>
        </div>

        <div class="campo-formulario">
            <label for="inputDescripcion">Descripción del Servicio *</label>
            <textarea id="inputDescripcion" name="descripcion" rows="3" required placeholder="Escribe los detalles exclusivos del tratamiento..."></textarea>
        </div>

        <div class="campo-formulario">
            <label for="inputFoto">Foto del Servicio</label>
            <div class="campo-foto-previsualizacion">
                <div id="fotoPreviewBox" class="avatar-preview" style="border-radius: 8px; width: 70px; height: 50px;">Zen</div>
                <input type="file" id="inputFoto" name="foto" accept="image/*" onchange="previsualizarFotoServicio(this)">
            </div>
        </div>

        <div style="display:flex; justify-content:flex-end; gap:0.75rem; margin-top:1rem;">
            <button type="button" class="btn-secundario" onclick="cerrarFormServicio()">Regresar</button>
            <button type="submit" class="btn-primario" style="background: #00a6fb;" id="btnGuardarServicio">Guardar Cambios</button>
        </div>
    </form>
</dialog>

<dialog id="modalEliminarServicio" class="modal-alerta-custom">
    <div class="alerta-contenido">
        <h3 style="margin-top: 0; color: #2c3e50;">¿Estás seguro de eliminar el servicio?</h3>
        <p>Esta acción removerá permanentemente el tratamiento <strong id="eliminarTargetNombre"></strong> del Catálogo Zen.</p>
        <div class="alerta-acciones">
            <button type="button" class="btn-secundario" onclick="document.getElementById('modalEliminarServicio').close()">Cancelar</button>
            <form id="formEliminarServicioAction" action="" method="POST" style="display: inline;">
                @csrf
                @method('DELETE')
                <button type="submit" class="btn-terapeuta-eliminar" style="background-color: #ff5a7d;">Confirmar Eliminación</button>
            </form>
        </div>
    </div>
</dialog>

<script>
    function consultarServicio(nombre, precio, porcentaje, tiempo, descripcion, especialistas, fotoUrl) {
        document.getElementById('consultarNombre').innerText = nombre;
        document.getElementById('consultarPrecio').innerText = precio;
        document.getElementById('consultarPorcentaje').innerText = porcentaje;
        document.getElementById('consultarTiempo').innerText = tiempo;
        document.getElementById('consultarDescripcion').innerText = descripcion;
        document.getElementById('consultarFoto').src = fotoUrl;

        const contenedorEsp = document.getElementById('consultarEspecialistas');
        contenedorEsp.innerHTML = '';
        especialistas.forEach(esp => {
            const span = document.createElement('span');
            span.className = 'tag-especialidad';
            span.innerText = esp;
            contenedorEsp.appendChild(span);
        });

        document.getElementById('modalConsultarServicio').showModal();
    }

    function abrirModalAgregarServicio() {
        document.getElementById('formServicioTitle').innerText = "Agregar Nuevo Servicio";
        document.getElementById('servicio_id').value = '';
        document.getElementById('servicio_method').value = "POST";
        document.getElementById('formActionServicio').action = "/admin/servicios/guardar";
        document.getElementById('fotoPreviewBox').innerText = "Zen";
        document.getElementById('modalFormServicio').showModal();
    }

    function abrirModalEditarServicio(data) {
        document.getElementById('formServicioTitle').innerText = "Editar Servicio";
        document.getElementById('servicio_id').value = data.id;
        document.getElementById('inputNombre').value = data.nombre;
        document.getElementById('inputPrecio').value = data.precio;
        document.getElementById('inputPorcentaje').value = data.porcentaje;
        document.getElementById('inputTiempo').value = data.tiempo;
        document.getElementById('inputDescripcion').value = data.descripcion;
        
        document.getElementById('servicio_method').value = "PUT";
        document.getElementById('formActionServicio').action = `/admin/servicios/${data.id}/actualizar`;
        
        document.getElementById('fotoPreviewBox').innerHTML = `<img src="${data.foto}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">`;
        document.getElementById('modalFormServicio').showModal();
    }

    function confirmarEliminarServicio(id, nombre) {
        document.getElementById('eliminarTargetNombre').innerText = name;
        document.getElementById('formEliminarServicioAction').action = `/admin/servicios/${id}/eliminar`;
        document.getElementById('modalEliminarServicio').showModal();
    }

    function previsualizarFotoServicio(input) {
        const box = document.getElementById('fotoPreviewBox');
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                box.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:4px;">`;
            }
            reader.readAsDataURL(input.files[0]);
        }
    }

    function cerrarFormServicio() {
        document.getElementById('formActionServicio').reset();
        document.getElementById('modalFormServicio').close();
    }
</script>
@endsection