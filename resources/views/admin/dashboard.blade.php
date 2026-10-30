@extends('layouts.app')

@section('title', 'Panel de Control - The Beauty Room')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<main class="dashboard-admin">

    <!-- ==========================================
         HEADER ESTILO IMAGEN 1
         ========================================== -->
    <header class="dash-header">
        <div class="dash-header-info">
            <h1>Panel de control</h1>
            <p>Gestiona el personal, supervisa el pulso del negocio y mantén cada acceso bajo control.</p>
        </div>
        <div class="dash-fecha-resumen">
            <span>Resumen Actual</span>
            <strong>
    @php
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        echo $meses[(int)date('n')] . ' ' . date('Y');
    @endphp
</strong>
         </div>
    </header>

    {{-- Mensaje de Éxito Oculto (El JS lo leerá para mostrar el SweetAlert) --}}
    @if(session('success'))
    <div id="dashboard-mensaje-exito" data-mensaje="{{ session('success') }}" style="display: none;"></div>
    @endif

    <!-- ==========================================
         KPI CARDS ESTILO IMAGEN 1
         ========================================== -->
    <section class="dash-kpis-grid">
        <!-- Citas Activas (Tarjeta Oscura) -->
        <article class="dash-kpi-card dash-kpi-card--dark">
            <div class="dash-kpi-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M6.75 3v2.25M17.25 3v2.25M3 18.75V7.5a2.25 2.25 0 0 1 2.25-2.25h13.5A2.25 2.25 0 0 1 21 7.5v11.25m-18 0A2.25 2.25 0 0 0 5.25 21h13.5A2.25 2.25 0 0 0 21 18.75m-18 0v-7.5A2.25 2.25 0 0 1 5.25 9h13.5A2.25 2.25 0 0 1 21 11.25v7.5" />
                </svg>
            </div>
            <div class="dash-kpi-content">
                <h3>Citas Activas</h3>
                <div class="valor">{{ $totalCitas ?? 0 }}</div>
                <small>0 programadas para hoy</small>
            </div>
        </article>

        <!-- Equipo Activo -->
        <article class="dash-kpi-card">
            <div class="dash-kpi-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M15 19.128a9.38 9.38 0 0 0 2.625.372 9.337 9.337 0 0 0 4.121-.952 4.125 4.125 0 0 0-7.533-2.493M15 19.128v-.003c0-1.113-.285-2.16-.786-3.07M15 19.128v.106A12.318 12.318 0 0 1 8.624 21c-2.331 0-4.512-.645-6.374-1.766l-.001-.109a6.375 6.375 0 0 1 11.964-3.07M12 6.375a3.375 3.375 0 1 1-6.75 0 3.375 3.375 0 0 1 6.75 0Zm8.25 2.25a2.625 2.625 0 1 1-5.25 0 2.625 2.625 0 0 1 5.25 0Z" />
                </svg>
            </div>
            <div class="dash-kpi-content">
                <h3>Equipo Activo</h3>
                <div class="valor">{{ isset($trabajadores) ? $trabajadores->count() : 3 }}</div>
                <small>{{ isset($trabajadores) ? $trabajadores->count() : 1 }} especialista en agenda</small>
            </div>
        </article>

        <!-- Ingresos Estimados -->
        <article class="dash-kpi-card">
            <div class="dash-kpi-icon">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M2.25 18 9 11.25l4.306 4.306a11.95 11.95 0 0 1 5.814-5.518l2.74-1.22m0 0-5.94-2.281m5.94 2.28-2.28 5.941" />
                </svg>
            </div>
            <div class="dash-kpi-content">
                <h3>Ingresos Estimados</h3>
                <div class="valor">${{ isset($ingresosEstimados) ? number_format($ingresosEstimados, 2) : '423' }}</div>
                <small>Proyección de citas activas</small>
            </div>
        </article>
    </section>

    <!-- ==========================================
         DOBLE COLUMNA ESTILO IMAGEN 1
         ========================================== -->
    <div class="dash-columnas-grid">

        <!-- COLUMNA 1: RECEPCIONISTAS -->
        <section class="dash-panel">
            <header class="dash-panel-header">
                <div class="dash-panel-header-text">
                    <h3>Equipo de recepción</h3>
                    <p>{{ $recepcionistas->count() ?? 0 }} perfiles con acceso</p>
                </div>
                <button type="button" class="dash-btn-add" id="btn-add-recep">
                    <span>+</span> Agregar
                </button>
            </header>

            <div class="dash-panel-body" id="lista-recepcionistas">
                @forelse($recepcionistas as $recep)
                <article class="dash-user-row" data-id="{{ $recep->id }}">
                    <div class="dash-avatar">
                        @if($recep->foto && file_exists(public_path('storage/' . $recep->foto)))
                            <img src="{{ asset('storage/' . $recep->foto) }}" alt="{{ $recep->nombre }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ substr($recep->nombre, 0, 2) }}
                        @endif
                    </div>
                    <div class="dash-user-info">
                        <div class="dash-user-name">
                            <h4>{{ $recep->nombre }}</h4>
                            <span class="dash-status-dot" title="Activo"></span>
                        </div>
                        <p class="dash-user-email">{{ $recep->correo }}</p>
                        <div class="dash-user-tags">
                            <span class="dash-tag">Recepción</span>
                        </div>
                    </div>
                    <div class="dash-actions">
                        <button class="dash-btn-action" onclick="Syncro.editRecep({{ $recep->id }}, '{{ addslashes($recep->nombre) }}', '{{ $recep->correo }}', '{{ $recep->telefono ?? '' }}')">Editar</button>
                        <button class="dash-btn-action dash-btn-action--danger" onclick="Syncro.delRecep({{ $recep->id }})">Eliminar</button>
                    </div>
                </article>
                @empty
                <p style="padding: 2rem; text-align: center; color: var(--dash-text-muted);">No hay recepcionistas registrados.</p>
                @endforelse
            </div>

            {{-- PAGINADOR RECEPCIONISTAS --}}
            <footer class="dash-paginador" id="paginador-recepcionistas" data-total="{{ $recepcionistas->count() ?? 0 }}">
                <button type="button" class="dash-paginador-btn" data-pagina="prev" aria-label="Página anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <span class="dash-paginador-info">Página 1 de 1</span>
                <button type="button" class="dash-paginador-btn" data-pagina="next" aria-label="Página siguiente">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </footer>
        </section>

        <!-- COLUMNA 2: ESPECIALISTAS -->
        <section class="dash-panel">
            <header class="dash-panel-header">
                <div class="dash-panel-header-text">
                    <h3>Especialistas</h3>
                    <p>{{ $trabajadores->count() ?? 0 }} perfiles atendiendo clientes</p>
                </div>
                <button type="button" class="dash-btn-add" id="btn-add-esp">
                    <span>+</span> Agregar
                </button>
            </header>

            <div class="dash-panel-body" id="lista-especialistas">
                @forelse($trabajadores as $trab)
                <article class="dash-user-row" data-id="{{ $trab->id }}">
                    <div class="dash-avatar">
                        @if($trab->foto && file_exists(public_path('storage/' . $trab->foto)))
                            <img src="{{ asset('storage/' . $trab->foto) }}" alt="{{ $trab->nombre }}" style="width: 100%; height: 100%; border-radius: 50%; object-fit: cover;">
                        @else
                            {{ substr($trab->nombre, 0, 2) }}
                        @endif
                    </div>
                    <div class="dash-user-info">
                        <div class="dash-user-name">
                            <h4>{{ $trab->nombre }}</h4>
                            <span class="dash-status-dot" title="Activo"></span>
                        </div>
                        <p class="dash-user-email">{{ $trab->correo }}</p>
                        <div class="dash-user-tags">
                            @if(!empty($trab->especialidades))
                                @foreach(explode(',', $trab->especialidades) as $esp)
                                    <span class="dash-tag">{{ trim($esp) }}</span>
                                @endforeach
                            @endif

                            <!-- ETIQUETA DINÁMICA DE CITAS -->
                            <span class="dash-tag dash-tag--role">
                                {{ $trab->citas_mes }} {{ $trab->citas_mes == 1 ? 'cita este mes' : 'citas este mes' }}
                            </span>

                        </div>
                    </div>
                    <div class="dash-actions">
                        <button class="dash-btn-action" onclick="Syncro.editEsp({{ $trab->id }}, '{{ addslashes($trab->nombre) }}', '{{ $trab->correo }}', '{{ $trab->telefono ?? '' }}', '{{ addslashes($trab->especialidades ?? '') }}', '{{ addslashes($trab->descripcion ?? '') }}')">Editar</button>
                        <button class="dash-btn-action dash-btn-action--danger" onclick="Syncro.delEsp({{ $trab->id }})">Eliminar</button>
                    </div>
                </article>
                @empty
                <p style="padding: 2rem; text-align: center; color: var(--dash-text-muted);">No hay especialistas registrados.</p>
                @endforelse
            </div>

            {{-- PAGINADOR ESPECIALISTAS --}}
            <footer class="dash-paginador" id="paginador-especialistas" data-total="{{ $trabajadores->count() ?? 0 }}">
                <button type="button" class="dash-paginador-btn" data-pagina="prev" aria-label="Página anterior">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5 8.25 12l7.5-7.5" />
                    </svg>
                </button>
                <span class="dash-paginador-info">Página 1 de 1</span>
                <button type="button" class="dash-paginador-btn" data-pagina="next" aria-label="Página siguiente">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" />
                    </svg>
                </button>
            </footer>
        </section>
    </div>

    <!-- ==========================================
         MODALES (SIN CAMBIOS)
         ========================================== -->
    @include('compartidas.modal-terapeuta', ['userRole' => $userRole ?? null])

    <!-- MODAL RECEPCIONISTA -->
    <dialog id="modal-recepcionista" class="modal-zen">
        <header class="modal-header">
            <h2 id="modal-recepcionista-titulo">Agregar Nuevo Recepcionista</h2>
            <button type="button" class="btn-cerrar-modal" onclick="document.getElementById('modal-recepcionista').close()">&times;</button>
        </header>

        <form id="form-recepcionista" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="_method" id="recep_method" value="POST">

            <fieldset class="campo-formulario">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" id="recep_name" required minlength="3" maxlength="255" placeholder="Ej. Carlos Mendoza">
                <small style="color: #64748b; font-size: 0.8rem;">Mínimo 3 caracteres, solo letras.</small>
            </fieldset>

            <div class="fila-doble">
                <fieldset class="campo-formulario">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" id="recep_phone" required pattern="[0-9]{7,20}" placeholder="Ej. 04141112233">
                    <small style="color: #64748b; font-size: 0.8rem;">Solo números, sin guiones.</small>
                </fieldset>
                <fieldset class="campo-formulario">
                    <label>Correo Electrónico</label>
                    <input type="email" name="email" id="recep_email" required placeholder="ejemplo@thebeautyroom.com">
                    <small style="color: #64748b; font-size: 0.8rem;">Debe ser un correo válido.</small>
                </fieldset>
            </div>

            <div id="recep-campo-password" style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem;">
                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password">Contraseña</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="recep_password" name="password" minlength="8" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>
                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password_confirmation">Confirmar</label>
                    <div class="input-icono-wrapper">
                        <input type="password" id="recep_password_confirmation" name="password_confirmation" minlength="8" placeholder="Repita la contraseña">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password_confirmation', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" width="20" height="20">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>
            </div>

            <footer class="modal-acciones">
                <button type="button" class="btn-zen btn-secundario" onclick="document.getElementById('modal-recepcionista').close()">Cancelar</button>
                <button type="submit" class="btn-zen btn-primario">Guardar Recepcionista</button>
            </footer>
        </form>
    </dialog>

    <!-- MODAL CONFIRMACIÓN -->
    <dialog id="modal-confirmacion-custom" class="modal-contenedor-alerta-critica">
        <article class="cuerpo-alerta-centrado">
            <figure class="icono-alerta-advertencia">⚠️</figure>
            <h3 id="confirm-alerta-titulo" class="titulo-alerta-critica">¿Confirmar acción?</h3>
            <p id="confirm-alerta-mensaje" class="mensaje-alerta-descripcion"></p>

            <footer class="pie-alerta-botones">
                <button type="button" id="btn-confirm-cancelar" class="btn-cancelar-alerta">Cancelar</button>
                <button type="button" id="btn-confirm-aceptar" class="btn-aceptar-eliminar">Eliminar permanentemente</button>
            </footer>
        </article>
    </dialog>

</main>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
@vite(['resources/js/dashboard.js'])
@vite(['resources/js/terapeutas.js'])
@endpush