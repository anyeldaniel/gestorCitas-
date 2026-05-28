@extends('layouts.app')

@section('title', 'Panel del Especialista — Kanban')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo">
        <h1>Tablero de Gestión de Pacientes</h1>
        <p>Organiza el flujo de atención del Spa en tiempo real. Arrastra las tarjetas de citas entre las distintas columnas de estado.</p>
    </header>

    <section class="tablero-kanban">
        
        <article class="columna-kanban" data-estado="pendiente">
            <header class="columna-titulo pendiente">
                <h2>Por Atender</h2>
                <span class="contador-citas" id="cant-pendiente">2</span>
            </header>
            
            <section class="contenedor-tarjetas">
                
                <article class="tarjeta-cita" id="cita-201" draggable="true">
                    <h3>Carlos Mendoza</h3>
                    <p><strong>Servicio:</strong> Masaje con Piedras Volcánicas</p>
                    <time> 09:00 AM</time>
                </article>

                <article class="tarjeta-cita" id="cita-202" draggable="true">
                    <h3>María Delgado</h3>
                    <p><strong>Servicio:</strong> Limpieza Facial Profunda</p>
                    <time> 10:30 AM</time>
                </article>
                
            </section>
        </article>

        <article class="columna-kanban" data-estado="en-proceso">
            <header class="columna-titulo en-proceso">
                <h2>En Cabina</h2>
                <span class="contador-citas" id="cant-proceso">0</span>
            </header>
            <section class="contenedor-tarjetas">
                </section>
        </article>

        <article class="columna-kanban" data-estado="finalizado">
            <header class="columna-titulo finalizado">
                <h2>Culminados</h2>
                <span class="contador-citas" id="cant-finalizado">0</span>
            </header>
            <section class="contenedor-tarjetas">
                </section>
        </article>

    </section>
</section>
@endsection