@extends('layouts.app')

@section('title', 'Reportes y Analíticas de Rendimiento')

@push('styles')
    @vite(['resources/css/reportes.css'])
@endpush

@section('content')
<main class="modulo-vista reportes-admin">
    
    {{-- Encabezado del Módulo --}}
    <header class="encabezado-modulo">
        <h1>Reportes y Estadísticas de Ocupación</h1>
        <p>Analice la demanda de tratamientos en el Spa y evalúe los volúmenes de productividad de su personal especializado.</p>
    </header>

    <section class="distribucion-recepcion">
        
        {{-- PANEL DE FILTROS (IZQUIERDA) --}}
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

                <button type="submit" class="btn-zen btn-primario">
                    <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                    </svg>
                    Actualizar Gráficas
                </button>
            </form>
        </aside>

        {{-- PANEL DE GRÁFICAS (DERECHA) - DOS TARJETAS LADO A LADO --}}
        <section class="panel-graficas">
            
            {{-- Gráfica 1: Flujo Mensual --}}
            <article class="contenedor-grafica">
                <header class="info-cliente">
                    <h3>Flujo Mensual de Pacientes</h3>
                    <p>Monitoreo de asistencia por semanas para detectar picos de demanda.</p>
                </header>
                <div class="canvas-wrapper">
                    <canvas id="graficaFlujo"></canvas>
                </div>
            </article>

            {{-- Gráfica 2: Servicios Más Requeridos --}}
            <article class="contenedor-grafica">
                <header class="info-cliente">
                    <h3>Servicios Más Requeridos</h3>
                    <p>Distribución porcentual de los tratamientos aplicados en cabinas.</p>
                </header>
                <div class="canvas-wrapper">
                    <canvas id="graficaServicios"></canvas>
                </div>
            </article>

        </section>
    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
@vite('resources/js/reportes.js')
@endsection