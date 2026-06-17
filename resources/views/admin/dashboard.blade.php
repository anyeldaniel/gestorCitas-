@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Syncrostyle')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<main class="modulo-vista dashboard-admin">
    <header class="encabezado-modulo">
        <h1>Panel de Control Administrativo</h1>
        <p>Gestione el personal de "The Beauty Room", supervise el estado del negocio y controle los accesos de la plataforma.</p>
    </header>

    @if(session('success'))
    <div class="alert-success-spa" style="margin-bottom: 2rem; background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px; border-left: 5px solid #10b981;">
        <strong>¡Éxito!</strong> {{ session('success') }}
    </div>
    @endif

    @if($errors->any())
    <div class="alert-errores-spa" style="margin-bottom: 2rem; background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; border-left: 5px solid #ef4444;">
        <strong>¡Atención! No se pudo guardar:</strong>
        <ul style="margin-top: 0.5rem; margin-bottom: 0;">
            @foreach($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif

    <section class="tarjetas-resumen-admin">
        <article class="tarjeta-kpi">
            <small>Citas Totales</small>
            <h2>{{ $totalCitas ?? 0 }}</h2>
        </article>

        <article class="tarjeta-kpi">
            <small>Especialistas Activos</small>
            <h2>{{ isset($trabajadores) ? $trabajadores->count() : 0 }}</h2>
        </article>

        <article class="tarjeta-kpi kpi-ingresos">
            <small>Ingresos Estimados</small>
            <h2>${{ isset($ingresosEstimados) ? number_format($ingresosEstimados, 2) : '0.00' }}</h2>
        </article>
    </section>

    <div class="contenedor-doble-columnas">

        <section class="lista-admisiones">
            <header class="encabezado-registro" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <h2>Equipo de Recepción</h2>
                <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalRecepcionista()">
                    + Agregar recepcionista
                </button>
            </header>

            <section id="contenedor-recepcionistas" class="grid-personal" style="display:flex; flex-direction:column; gap:1rem; min-height:150px;">
                @forelse($recepcionistas as $recep)
                <article class="tarjeta-admision tarjeta-recepcionista-item"
                    data-id="{{ $recep->id }}"
                    data-correo="{{ $recep->correo }}"
                    data-telefono="{{ $recep->telefono }}">

                    <figure class="terapeuta-foto-contenedor" style="margin-bottom:0.5rem; display:flex; justify-content:center;">
                        <div class="foto-avatar-simulado" style="width:50px; height:50px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; overflow:hidden;">
                            {{ substr($recep->nombre, 0, 2) }}
                        </div>
                    </figure>

                    <header class="info-cliente" style="text-align:center;">
                        <h3>{{ $recep->nombre }}</h3>
                        <p>Rol: <span class="tag-rol recepcionista">Recepcionista</span></p>
                    </header>

                    <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto; justify-content:center;">
                        <button type="button" class="btn-zen" onclick="editarRecepcionista({{ $recep->id }})" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                        <button type="button" class="btn-zen btn-baja" onclick="eliminarRecepcionista({{ $recep->id }})">Baja</button>
                    </footer>
                </article>
                @empty
                <p style="text-align: center; color: #64748b; margin: auto;">No hay recepcionistas registrados.</p>
                @endforelse
            </section>

            <div class="paginador-ui">
                <button type="button" id="prev-recepcionistas" class="btn-paginacion">&laquo; Ant</button>
                <span id="info-recepcionistas" class="info-paginacion">Pág. 1 de 1</span>
                <button type="button" id="next-recepcionistas" class="btn-paginacion">Sig &raquo;</button>
            </div>
        </section>

        <section class="lista-admisiones">
            <header class="encabezado-registro" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <h2>Especialistas Registrados</h2>
                <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()">
                    + Agregar especialista
                </button>
            </header>

            <section id="contenedor-trabajadores" class="grid-personal" style="display:flex; flex-direction:column; gap:1rem;">
                @forelse($trabajadores as $trab)
                <article class="tarjeta-admision tarjeta-terapeuta" data-id="{{ $trab->id }}" data-especialidades="{{ $trab->especialidades ?? 'Bienestar General' }}">
                    <figure class="terapeuta-foto-contenedor" style="margin-bottom:0.5rem; display:flex; justify-content:center;">
                        <div class="foto-avatar-simulado" style="width:50px; height:50px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; overflow:hidden;">
                            {{ substr($trab->nombre, 0, 2) }}
                        </div>
                    </figure>

                    <header class="info-cliente" style="text-align:center;">
                        <h3>{{ $trab->nombre }}</h3>
                        <p>Rol: <span class="tag-rol especialista">Especialista</span></p>

                        <div class="contenedor-tags-especialidades" style="justify-content:center;">
                            @if(!empty($trab->especialidades))
                            @foreach(explode(',', $trab->especialidades) as $esp)
                            <span class="tag-especialidad">{{ trim($esp) }}</span>
                            @endforeach
                            @else
                            <span class="tag-especialidad">Terapeuta</span>
                            @endif
                        </div>

                        <p class="terapeuta-telefono" style="display:none;">{{ $trab->telefono ?? 'S/N' }}</p>
                        <p class="terapeuta-email" style="display:none;">{{ $trab->correo }}</p>
                        <p class="terapeuta-descripcion" style="display:none;">{{ $trab->descripcion ?? 'Miembro del equipo profesional de The Beauty Room.' }}</p>
                    </header>

                    <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto; justify-content:center;">
                        <button type="button" class="btn-zen btn-ver-especialista" onclick="verTerapeutaDetalle({{ $trab->id }})">Ver Terapeuta</button>
                        <button type="button" class="btn-zen" onclick="editarTerapeuta({{ $trab->id }})" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                        <button type="button" class="btn-zen btn-baja" onclick="eliminarTerapeuta({{ $trab->id }})">Baja</button>
                    </footer>
                </article>
                @empty
                <p style="text-align: center; color: #64748b; margin: auto;">No hay especialistas registrados.</p>
                @endforelse
            </section>

            <div class="paginador-ui">
                <button type="button" id="prev-terapeutas" class="btn-paginacion">&laquo; Ant</button>
                <span id="info-terapeutas" class="info-paginacion">Pág. 1 de 1</span>
                <button type="button" id="next-terapeutas" class="btn-paginacion">Sig &raquo;</button>
            </div>
        </section>

    </div>

    <dialog id="modal-recepcionista" class="modal-zen">
        <header class="modal-header">
            <h2 id="modal-recepcionista-titulo">Agregar Nuevo Recepcionista</h2>
            <button type="button" class="btn-cerrar-modal" onclick="document.getElementById('modal-recepcionista').close()">&times;</button>
        </header>
        <form id="form-recepcionista" method="POST" action="{{ route('admin.create-recepcionista') }}" autocomplete="off" class="modal-form">
            @csrf

            <fieldset class="campo-formulario">
                <label for="recep_foto">Foto de Perfil (Opcional)</label>
                <div class="campo-foto-previsualizacion">
                    <div id="previsualizacion-avatar-recepcionista" class="avatar-preview">RE</div>
                    <input type="file" name="foto" accept="image/*" onchange="previsualizarImagen(this, 'previsualizacion-avatar-recepcionista')">
                </div>
            </fieldset>

            <fieldset class="campo-formulario">
                <label for="recep_name">Nombre Completo</label>
                <input type="text" name="nombre" id="recep_name" required minlength="3" maxlength="255" value="{{ old('nombre') }}" placeholder="Ej. Carlos Mendoza">
                <small style="color: #64748b; font-size: 0.8rem;">Mínimo 3 caracteres, solo letras.</small>
            </fieldset>

            <fieldset class="campo-formulario">
                <label for="recep_phone">Teléfono de Contacto</label>
                <input type="tel" name="telefono" id="recep_phone" required pattern="[0-9]{7,20}" title="Debe contener entre 7 y 20 números, sin espacios ni guiones" value="{{ old('telefono') }}" placeholder="Ej. 04141112233">
                <small style="color: #64748b; font-size: 0.8rem;">Solo números seguidos, sin guiones ni espacios (Ej. 04141234567).</small>
            </fieldset>

            <fieldset class="campo-formulario">
                <label for="recep_email">Correo Electrónico</label>
                <input type="email" name="email" id="recep_email" required value="{{ old('email') }}" placeholder="carlos@thebeautyroom.com">
                <small style="color: #64748b; font-size: 0.8rem;">Debe ser un correo válido y no estar registrado previamente.</small>
            </fieldset>

            <div id="error-password-recep" class="aviso-error-password" style="display: none; color: var(--color-error); font-size: 0.85rem; font-weight: 600; margin-bottom: -0.5rem;">
                Las contraseñas no coinciden. Por favor, verifíquelas.
            </div>

            <section id="recep-campo-password" class="grupo-seguridad-modal grid-dos-columnas">
                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password">Contraseña de Acceso</label>
                    <div class="input-icono-wrapper">
                        <input type="password" name="password" id="recep_password" required minlength="8" placeholder="Mínimo 8 caracteres">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>

                <fieldset class="campo-formulario campo-password-contenedor">
                    <label for="recep_password_confirmation">Confirmar Contraseña</label>
                    <div class="input-icono-wrapper">
                        <input type="password" name="password_confirmation" id="recep_password_confirmation" required minlength="8" placeholder="Repita la contraseña">
                        <button type="button" class="btn-alternar-password" onclick="alternarVisibilidad('recep_password_confirmation', this)" aria-label="Mostrar contraseña">
                            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
                            </svg>
                        </button>
                    </div>
                </fieldset>
            </section>

            <footer class="modal-acciones">
                <button type="button" class="btn-zen btn-secundario" onclick="document.getElementById('modal-recepcionista').close()">Cancelar</button>
                <button type="button" onclick="this.form.action='{{ route('admin.create-recepcionista') }}'; this.form.submit();" class="btn-zen btn-primario">Guardar Recepcionista</button>
            </footer>
        </form>
    </dialog>

    @include('compartidas.modal-terapeuta')
</main>
@endsection

@push('scripts')
@vite(['resources/js/terapeutas.js', 'resources/js/dashboard.js'])
@endpush