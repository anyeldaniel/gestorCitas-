@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Syncrostyle')

@push('styles')
@vite(['resources/css/terapeutas.css'])
@endpush

@section('content')
<main class="dashboard-admin">
    <header class="encabezado-modulo">
        <h1>Panel de Control Administrativo</h1>
        <p>Gestione el personal de "The Beauty Room", supervise el estado del negocio y controle los accesos.</p>
    </header>

    @if(session('success'))
    <div class="alert-success-spa" style="margin-bottom: 2rem; background-color: #d1fae5; color: #065f46; padding: 1rem; border-radius: 8px;">
        <strong>¡Éxito!</strong> {{ session('success') }}
    </div>
    @endif

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

    <div class="contenedor-doble-columnas">
        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h3>Equipo de Recepción</h3>
                <button type="button" class="btn-agregar-general" id="btn-add-recep">+ Agregar recepcionista</button>
            </header>
            @forelse($recepcionistas as $recep)
            <article class="tarjeta-fila">
                <div class="avatar-iniciales">{{ substr($recep->nombre, 0, 2) }}</div>
                <div class="info-usuario">
                    <h4>{{ $recep->nombre }}</h4>
                    <p>{{ $recep->correo }}</p>
                </div>
                <div class="acciones-fila" style="display: flex; gap: 0.5rem;">
                    <button class="btn-accion" onclick="Syncro.editRecep({{ $recep->id }}, '{{ $recep->nombre }}', '{{ $recep->email }}', '{{ $recep->telefono }}')">Editar</button>
                    <button class="btn-accion btn-baja" onclick="Syncro.delRecep({{ $recep->id }})">Baja</button>
                </div>
            </article>
            @empty
            <p style="padding: 1rem; text-align: center; color: var(--gris-texto);">No hay recepcionistas.</p>
            @endforelse
        </section>

        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h3>Especialistas Registrados</h3>
                <button type="button" class="btn-agregar-general" id="btn-add-esp">+ Agregar especialista</button>
            </header>
            @forelse($trabajadores as $trab)
            <article class="tarjeta-fila">
                <div class="avatar-iniciales">{{ substr($trab->nombre, 0, 2) }}</div>
                <div class="info-usuario">
                    <h4>{{ $trab->nombre }}</h4>
                </div>
                <div class="acciones-fila" style="display: flex; gap: 0.5rem;">
                    <button class="btn-accion" onclick="Syncro.editEsp({{ $trab->id }})">Editar</button>
                    <button class="btn-accion btn-baja" onclick="Syncro.delEsp({{ $trab->id }})">Baja</button>
                </div>
            </article>
            @empty
            <p style="padding: 1rem; text-align: center; color: var(--gris-texto);">No hay especialistas.</p>
            @endforelse
        </section>
    </div>

    @include('compartidas.modal-terapeuta')

    <dialog id="modal-recepcionista" class="modal-zen">
        <header class="modal-header">
            <h2 id="modal-recepcionista-titulo">Nuevo Recepcionista</h2>
            <button type="button" onclick="document.getElementById('modal-recepcionista').close()" style="border:none; background:none; cursor:pointer;">&times;</button>
        </header>
        <form id="form-recepcionista" method="POST" class="modal-form" enctype="multipart/form-data">
            @csrf
            <fieldset class="campo-formulario">
                <label>Nombre Completo</label>
                <input type="text" name="nombre" id="recep_name" required>
            </fieldset>
            <div class="fila-doble">
                <fieldset class="campo-formulario">
                    <label>Teléfono</label>
                    <input type="tel" name="telefono" id="recep_phone" required>
                </fieldset>
                <fieldset class="campo-formulario">
                    <label>Correo</label>
                    <input type="email" name="email" id="recep_email" required>
                </fieldset>
            </div>
            <div class="fila-doble">
                <fieldset class="campo-formulario">
                    <label>Contraseña</label>
                    <input type="password" name="password" id="recep_password">
                </fieldset>
                <fieldset class="campo-formulario">
                    <label>Confirmar</label>
                    <input type="password" name="password_confirmation" id="recep_password_confirmation">
                </fieldset>
            </div>
            <button type="submit" class="btn-guardar" style="width: 100%; cursor: pointer;">Guardar cambios</button>
        </form>
    </dialog>
</main>
@endsection

@push('scripts')
<script>
    window.Syncro = {
        init: function() {
            document.getElementById('btn-add-recep').addEventListener('click', this.openRecep);
            document.getElementById('btn-add-esp').addEventListener('click', this.openEsp);
        },
        
        openRecep: function() {
            const m = document.getElementById('modal-recepcionista');
            const f = document.getElementById('form-recepcionista');
            document.getElementById('modal-recepcionista-titulo').innerText = "Agregar Recepcionista";
            f.reset();
            f.action = "{{ url('/admin/crear-recepcionista') }}";
            m.showModal();
        },

        editRecep: function(id, n, c, t) {
            const m = document.getElementById('modal-recepcionista');
            const f = document.getElementById('form-recepcionista');
            document.getElementById('modal-recepcionista-titulo').innerText = "Editar Recepcionista";
            document.getElementById('recep_name').value = n;
            document.getElementById('recep_email').value = c;
            document.getElementById('recep_phone').value = t;
            // Quitamos required en edición para que no obligue a cambiar la contraseña
            document.getElementById('recep_password').removeAttribute('required');
            f.action = "{{ url('/admin/recepcionista') }}/" + id;
            m.showModal();
        },

        delRecep: function(id) {
            if(confirm('¿Eliminar recepcionista?')) window.location.href = "{{ url('/admin/eliminar-recepcionista') }}/" + id;
        },

        openEsp: function() {
            if(typeof window.abrirModalAgregar === 'function') window.abrirModalAgregar();
        },

        editEsp: function(id) {
            if(typeof window.editarTerapeuta === 'function') window.editarTerapeuta(id);
        },

        delEsp: function(id) {
            if(typeof window.eliminarTerapeuta === 'function') window.eliminarTerapeuta(id);
        }
    };

    document.addEventListener('DOMContentLoaded', () => Syncro.init());
</script>
@endpush