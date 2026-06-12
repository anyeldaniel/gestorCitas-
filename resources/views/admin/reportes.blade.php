@extends('layouts.app')

@section('title', 'Reportes y Analíticas de Rendimiento')

@section('content')
<main class="modulo-vista reportes-admin">
    <header class="encabezado-modulo">
        <h1>Reportes y Estadísticas de Ocupación</h1>
        <p>Analice la demanda de tratamientos en el Spa y evalúe los volúmenes de productividad de su personal especializado.</p>
    </header>

    <section class="distribucion-recepcion">
        <!-- Filtros de búsqueda -->
        <aside class="bloque-registro">
            <header class="encabezado-registro">
                <h2>Rango de Análisis</h2>
                <small>Seleccione el período de auditoría</small>
            </header>

            <form id="form-filtros-reporte" autocomplete="off" class="formulario-express">
                <fieldset class="campo-formulario">
                    <label for="fecha_inicio">Fecha de Inicio</label>
                    <input type="date" id="fecha_inicio" required value="2026-05-01">
                </fieldset>

                <fieldset class="campo-formulario">
                    <label for="fecha_fin">Fecha de Cierre</label>
                    <input type="date" id="fecha_fin" required value="2026-05-31">
                </fieldset>

                <button type="submit" class="btn-zen btn-primario">Actualizar Gráficas</button>
            </form>
        </aside>

        <!-- Contenedores de Gráficos -->
        <section class="lista-admisiones panel-graficas">
            <article class="tarjeta-admision contenedor-grafica">
                <header class="info-cliente">
                    <h3>Flujo Mensual de Pacientes Atendidos</h3>
                    <p>Monitoreo de asistencia por semanas para detectar picos de alta demanda laboral.</p>
                </header>
                <canvas id="graficaFlujo"></canvas>
            </article>

            <article class="tarjeta-admision contenedor-grafica">
                <header class="info-cliente">
                    <h3>Servicios Más Requeridos</h3>
                    <p>Distribución porcentual de los tratamientos aplicados en las cabinas.</p>
                </header>
                <canvas id="graficaServicios"></canvas>
            </article>
        </section>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/reportes.js')
@endsection