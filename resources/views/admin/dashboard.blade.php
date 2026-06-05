@extends('layouts.app')

@section('title', 'Dashboard Administrativo - Syncrostyle')

@section('content')
<main class="modulo-vista dashboard-admin">
    <header class="encabezado-modulo">
        <h1>Panel de Control Administrativo</h1>
        <p>Gestione el personal de "The Beauty Room", supervise el estado del negocio y controle los accesos de la plataforma.</p>
    </header>

    <!-- Indicadores de rendimiento clave (KPIs) -->
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

    <!-- Distribución de trabajo en dos bloques -->
    <section class="distribucion-recepcion">
        <!-- Formulario de Registro -->
        <aside class="bloque-registro">
            <header class="encabezado-registro">
                <h2>Registrar Personal</h2>
                <small>Cree las credenciales para nuevos especialistas o recepcionistas</small>
            </header>

            <form id="form-registro-trabajador" autocomplete="off" class="formulario-express">
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

        <!-- Listado de Personal Activo -->
        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h2>Equipo de Trabajo Registrado</h2>
            </header>
            
            <section id="contenedor-trabajadores" class="grid-personal">
                <article class="tarjeta-admision">
                    <header class="info-cliente">
                        <h3>Dra. Alana Ramos</h3>
                        <p>Rol: <span class="tag-rol especialista">Especialista</span></p>
                    </header>
                    <button class="btn-zen btn-baja btn-eliminar-trabajador" data-id="1">Baja</button>
                </article>

                <article class="tarjeta-admision">
                    <header class="info-cliente">
                        <h3>Carlos Mendoza</h3>
                        <p>Rol: <span class="tag-rol recepcionista">Recepcionista</span></p>
                    </header>
                    <button class="btn-zen btn-baja btn-eliminar-trabajador" data-id="2">Baja</button>
                </article>
            </section>
        </section>
    </section>
</main>

@vite('resources/js/dashboard.js')
@endsection