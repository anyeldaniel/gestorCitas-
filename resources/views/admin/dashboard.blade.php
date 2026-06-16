@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Syncrostyle')

@section('content')
<main class="modulo-vista dashboard-admin">
    <header class="encabezado-modulo">
        <h1>Panel de Control Administrativo</h1>
        <p>Gestione el personal de "The Beauty Room", supervise el estado del negocio y controle los accesos de la plataforma.</p>
    </header>

    <section class="tarjetas-resumen-admin">
        <article class="tarjeta-kpi">
            <small>Citas Totales</small>
            <h2>1,240</h2>
        </article>

        <article class="tarjeta-kpi">
            <small>Especialistas Activos</small>
            <h2>8</h2>
        </article>

        <article class="tarjeta-kpi kpi-ingresos">
            <small>Ingresos Estimados</small>
            <h2>$3,450</h2>
        </article>
    </section>

    <section class="distribucion-recepcion">
        <aside class="bloque-registro">
            <header class="encabezado-registro">
                <h2>Registrar Personal</h2>
                <small>Cree las credenciales para nuevos especialistas o recepcionistas</small>
            </header>

            <form id="form-registro-trabajador" method="POST" action="{{ route('admin.create-trabajador') }}" autocomplete="off" class="formulario-express">
                @csrf
                <fieldset class="campo-formulario">
                    <label for="worker_name">Nombre Completo</label>
                    <input type="text" id="worker_name" name="name" required placeholder="Ej. Clara Mendoza">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="worker_email">Correo Electrónico</label>
                    <input type="email" id="worker_email" name="email" required placeholder="clara@thebeautyroom.com">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="worker_role">Rol Asignado</label>
                    <select id="worker_role" name="role" required>
                        <option value="especialista">Especialista (Terapeuta)</option>
                        <option value="recepcionista">Recepcionista / Admisión</option>
                    </select>
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="worker_password">Contraseña Provisional</label>
                    <input type="password" id="worker_password" name="password" required placeholder="Mínimo 8 caracteres">
                </fieldset>

                <button type="submit" class="btn-zen btn-primario">Dar de Alta Trabajador</button>
            </form>
        </aside>

        <section class="lista-admisiones">
            <header class="encabezado-registro" style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap; gap: 0.5rem;">
                <h2>Equipo de Trabajo Registrado</h2>
                 <!-- Este botón reutiliza la función global para registrar con el formulario completo -->
                <button type="button" class="btn-terapeuta-agregar" onclick="abrirModalAgregar()" style="padding: 0.5rem 1rem; font-size: 0.85rem;">
                    + Agregar especialista
                </button>
            </header>
            
            <section id="contenedor-trabajadores" class="grid-personal">
                
                <!-- Añadí la clase 'tarjeta-terapeuta' y el data-id para que el JS capture los datos al editar -->
                <article class="tarjeta-admision tarjeta-terapeuta" data-id="1">
                    <header class="info-cliente">
                        <h3>Dra. Alana Ramos</h3>
                        <p>Rol: <span class="tag-rol especialista">Especialista</span></p>
                        <p class="terapeuta-especialidad" style="display:none;">Dermatología Cosmética</p>
                        <p class="terapeuta-descripcion" style="display:none;">Especialista en rejuvenecimiento facial, peeling químico y masajes relajantes premium.</p>
                    </header>
                    <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto;">
                        <button type="button" class="btn-zen" onclick="editarTerapeuta(1)" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                        <button type="button" class="btn-zen btn-baja" onclick="eliminarTerapeuta(1)">Baja</button>
                    </footer>
                </article>

                <article class="tarjeta-admision" data-id="2">
                    <header class="info-cliente">
                        <h3>Carlos Mendoza</h3>
                        <p>Rol: <span class="tag-rol recepcionista">Recepcionista</span></p>
                    </header>
                    <button class="btn-zen btn-baja btn-eliminar-trabajador" data-id="2">Baja</button>
                </article>
            </section>
        </section>
    </section>

    <!-- aquí inyecto el componente del modal compartido en la parte baja de la vista administrativa para que el JS pueda manipularlo sin problemas -->
    @include('compartidas.modal-terapeuta')
</main>
@endsection