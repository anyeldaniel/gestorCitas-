@extends('layouts.app')

@section('title', 'Gestión de Servicios - Syncrostyle')

@section('content')
<main class="modulo-vista catalogos-admin">
    <header class="encabezado-modulo">
        <h1>Gestión de Catálogo de Servicios</h1>
        <p>Añada nuevos tratamientos, modifique precios vigentes u oculte temporalmente servicios de la interfaz del cliente.</p>
    </header>

    <section class="distribucion-recepcion">
        <!-- Formulario para Añadir Servicio -->
        <aside class="bloque-registro">
            <header class="encabezado-registro">
                <h2>Nuevo Tratamiento</h2>
                <small>Ingrese las especificaciones del servicio</small>
            </header>

            <form id="form-servicios-spa" autocomplete="off" class="formulario-express">
                @csrf
                <fieldset class="campo-formulario">
                    <label for="service_name">Nombre del Tratamiento</label>
                    <input type="text" id="service_name" name="name" required placeholder="Ej. Masaje Piedras Volcánicas">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="service_price">Precio ($ USD)</label>
                    <input type="number" step="0.01" id="service_price" name="price" required placeholder="0.00">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="service_duration">Duración (Minutos)</label>
                    <input type="number" id="service_duration" name="duration_minutes" required placeholder="60">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="service_desc">Descripción Corta</label>
                    <textarea id="service_desc" name="description" rows="3" placeholder="Detalle los beneficios del tratamiento..."></textarea>
                </fieldset>

                <button type="submit" class="btn-zen btn-exito">Publicar en Catálogo</button>
            </form>
        </aside>

        <!-- Lista de Servicios -->
        <section class="lista-admisiones">
            <header class="encabezado-registro">
                <h2>Servicios Ofrecidos Activos</h2>
            </header>

            <section id="contenedor-servicios" class="grid-servicios">
                <article class="tarjeta-admision">
                    <header class="info-cliente">
                        <h3>Limpieza Facial Premium</h3>
                        <p>Duración: 45 min | <strong class="precio-texto">$45.00</strong></p>
                    </header>
                    <button class="btn-zen btn-baja btn-eliminar-servicio" data-id="101">Eliminar</button>
                </article>

                <article class="tarjeta-admision">
                    <header class="info-cliente">
                        <h3>Masaje Relajante de Espalda</h3>
                        <p>Duración: 60 min | <strong class="precio-texto">$60.00</strong></p>
                    </header>
                    <button class="btn-zen btn-baja btn-eliminar-servicio" data-id="102">Eliminar</button>
                </article>
            </section>
        </section>
    </section>
</main>

@vite('resources/js/servicios.js')
@endsection