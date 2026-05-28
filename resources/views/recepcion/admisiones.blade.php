@extends('layouts.app')

@section('title', 'Control de Admisiones y Registro Rápido')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo">
        <h1>Control de Admisiones y Sala de Espera</h1>
        <p>Registra clientes nuevos al instante y gestiona los flujos de entrada y salida de las cabinas de tratamiento.</p>
    </header>

    <section class="distribucion-recepcion">
        
        <aside class="bloque-registro">
            <header class="encabezado-registro">
                <h2>Registro Express</h2>
                <small>Alta de cliente sin cita previa</small>
            </header>

            <form id="form-registro-rapido" autocomplete="off" class="formulario-express">
                <fieldset class="campo-formulario">
                    <label for="nombre">Nombre Completo *</label>
                    <input type="text" id="nombre" required>
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="telefono">Teléfono *</label>
                    <input type="tel" id="telefono" required>
                </fieldset>

                <button type="submit" class="btn-zen">Asignar Turno Inmediato</button>
            </form>
        </aside>

        <section class="lista-admisiones">
            
            <article class="tarjeta-admision">
                <header class="info-cliente">
                    <h3>María Delgado</h3>
                    <p>Limpieza Facial Profunda — <strong>Sala de Espera</strong></p>
                </header>
                <footer>
                    <button type="button" class="btn-check check-in">Confirmar Check-In</button>
                </footer>
            </article>

            <article class="tarjeta-admision estado-en-cabina">
                <header class="info-cliente">
                    <h3>Carlos Mendoza</h3>
                    <p class="texto-activo-cabina">Masaje con Piedras — <strong>En Cabina 2</strong></p>
                </header>
                <footer>
                    <button type="button" class="btn-check check-out-activo">Procesar Check-Out / Cobro</button>
                </footer>
            </article>

        </section>
    </section>
</section>
@endsection