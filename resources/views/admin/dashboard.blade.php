@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Syncrostyle')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<main class="dashboard-admin">
    <!-- HEADER -->
    <header class="encabezado-modulo">
        <h1>Panel de Control Administrativo</h1>
        <p>Gestione el personal de "The Beauty Room", supervise el estado del negocio y controle los accesos.</p>
    </header>

    <!-- ALERTA DE ÉXITO -->
    @if(session('success'))
    <div class="alert-success-spa">
        <strong>¡Éxito!</strong> {{ session('success') }}
    </div>
    @endif

    <!-- KPI CARDS -->
    <section class="tarjetas-resumen-admin">
        <article class="tarjeta-kpi">
            <h3>Citas Totales</h3>
            <div class="valor">{{ $totalCitas ?? 0 }}</div>
        </article>
        <article class="tarjeta-kpi">
            <h3>Especialistas Activos</h3>
            <div class="valor">{{ isset($trabajadores) ? $trabajadores->count() : 0 }}</div>
        </article>
        <article class="tarjeta-kpi">
            <h3>Ingresos Estimados</h3>
            <div class="valor">${{ isset($ingresosEstimados) ? number_format($ingresosEstimados, 2) : '0.00' }}</div>
        </article>
    </section>

    <!-- DOBLE COLUMNA -->
    <div class="contenedor-doble-columnas">

        <!-- ==========================================
             COLUMNA 1 - RECEPCIONISTAS
             ========================================== -->
        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h3>Equipo de Recepción</h3>
                <button type="button" class="btn-agregar-general" id="btn-add-recep">+ Agregar recepcionista</button>
            </header>

            <div id="lista-recepcionistas">
                @forelse($recepcionistas as $recep)
                <article class="tarjeta-fila" data-id="{{ $recep->id }}">
                    <div class="avatar-iniciales">{{ substr($recep->nombre, 0, 2) }}</div>
                    <div class="info-usuario">
                        <h4>{{ $recep->nombre }}</h4>
                        <p>{{ $recep->correo }}</p>
                    </div>
                    <div class="acciones-fila">
                        <button class="btn-accion" onclick="Syncro.editRecep({{ $recep->id }}, '{{ addslashes($recep->nombre) }}', '{{ $recep->email }}', '{{ $recep->telefono ?? '' }}')">Editar</button>
                        <button class="btn-accion btn-baja" onclick="Syncro.delRecep({{ $recep->id }})">Eliminar</button>
                    </div>
                </article>
                @empty
                <p style="padding: 1rem; text-align: center; color: var(--gris-texto);">No hay recepcionistas registrados.</p>
                @endforelse
            </div>
        </section>

        <!-- ==========================================
             COLUMNA 2 - ESPECIALISTAS
             ========================================== -->
        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h3>Especialistas Registrados</h3>
                <button type="button" class="btn-agregar-general" id="btn-add-esp">+ Agregar especialista</button>
            </header>

            <div id="lista-especialistas">
                @forelse($trabajadores as $trab)
                <article class="tarjeta-fila" data-id="{{ $trab->id }}">
                    <div class="avatar-iniciales">{{ substr($trab->nombre, 0, 2) }}</div>
                    <div class="info-usuario">
                        <h4>{{ $trab->nombre }}</h4>
                        @if(!empty($trab->especialidades))
                        <div class="contenedor-badges">
                            @foreach(explode(',', $trab->especialidades) as $esp)
                                <span class="badge">{{ trim($esp) }}</span>
                            @endforeach
                        </div>
                        @endif
                    </div>
                    <div class="acciones-fila">
                        <button class="btn-accion" onclick="Syncro.editEsp({{ $trab->id }})">Editar</button>
                        <button class="btn-accion btn-baja" onclick="Syncro.delEsp({{ $trab->id }})">Eliminar</button>
                    </div>
                </article>
                @empty
                <p style="padding: 1rem; text-align: center; color: var(--gris-texto);">No hay especialistas registrados.</p>
                @endforelse
            </div>
        </section>
    </div>

    <!-- ==========================================
         INCLUIR MODALES DE TERAPEUTAS (con variable)
         ========================================== -->
    @include('compartidas.modal-terapeuta', ['userRole' => $userRole ?? null])

    <!-- ==========================================
         MODAL RECEPCIONISTA
         ========================================== -->
    <dialog id="modal-recepcionista" class="modal-zen">
        <header class="modal-header">
            <h2 id="modal-recepcionista-titulo">Agregar Nuevo Recepcionista</h2>
            <button type="button" class="btn-cerrar-modal" onclick="document.getElementById('modal-recepcionista').close()">&times;</button>
        </header>

        <form id="form-recepcionista" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf
            <!-- ✅ CAMPO OCULTO PARA EL MÉTODO (POST o PUT) -->
            <input type="hidden" name="_method" id="recep_method" value="POST">

            <!-- NOMBRE -->
            <fieldset class="campo-formulario">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" id="recep_name" required minlength="3" maxlength="255" placeholder="Ej. Carlos Mendoza">
                <small style="color: #64748b; font-size: 0.8rem;">Mínimo 3 caracteres, solo letras.</small>
            </fieldset>

            <!-- TELÉFONO Y EMAIL -->
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

            <!-- CONTRASEÑA -->
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

            <!-- BOTONES -->
            <footer class="modal-acciones">
                <button type="button" class="btn-zen btn-secundario" onclick="document.getElementById('modal-recepcionista').close()">Cancelar</button>
                <button type="submit" class="btn-zen btn-primario">Guardar Recepcionista</button>
            </footer>
        </form>
    </dialog>

    <!-- ==========================================
         MODAL CONFIRMACIÓN (REUTILIZABLE) - ESTILO CATÁLOGO
         ========================================== -->
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
@vite(['resources/js/dashboard.js'])
@vite(['resources/js/terapeutas.js'])
@endpush