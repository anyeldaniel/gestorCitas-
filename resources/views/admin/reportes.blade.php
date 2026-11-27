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

        {{-- PANEL DE GRÁFICAS (CENTRO) --}}
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

        {{-- NUEVO PANEL LATERAL DERECHO (Sugerencia) --}}
        <aside class="panel-sugerencia">
            <header class="sugerencia-cabecera">
                <span class="icono-sugerencia" aria-hidden="true">
                    <svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M3 13.125C3 12.504 3.504 12 4.125 12h2.25c.621 0 1.125.504 1.125 1.125v6.75C7.5 20.496 6.996 21 6.375 21h-2.25A1.125 1.125 0 013 19.875v-6.75zM9.75 8.625c0-.621.504-1.125 1.125-1.125h2.25c.621 0 1.125.504 1.125 1.125v11.25c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V8.625zM16.5 4.125c0-.621.504-1.125 1.125-1.125h2.25C20.496 3 21 3.504 21 4.125v15.75c0 .621-.504 1.125-1.125 1.125h-2.25a1.125 1.125 0 01-1.125-1.125V4.125z" />
                    </svg>
                </span>
                <small>Sugerencia</small>
                <h2>Explora tus reportes</h2>
            </header>
            
            <section class="sugerencia-cuerpo">
                <p>Identifica los días con mayor demanda y compara el desempeño del equipo.</p>
                
                {{-- Mini gráfica decorativa (SVG estático) --}}
                <figure class="mini-grafica" aria-hidden="true">
                    <svg viewBox="0 0 200 80" preserveAspectRatio="none">
                        <path d="M0,60 L40,55 L80,45 L120,20 L160,35 L200,10" fill="none" stroke="var(--spa-brown)" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round"/>
                        <path d="M0,60 L40,55 L80,45 L120,20 L160,35 L200,10 L200,80 L0,80 Z" fill="url(#gradiente)" opacity="0.1"/>
                        <defs>
                            <linearGradient id="gradiente" x1="0" y1="0" x2="0" y2="1">
                                <stop offset="0%" stop-color="var(--spa-brown)"/>
                                <stop offset="100%" stop-color="transparent"/>
                            </linearGradient>
                        </defs>
                    </svg>
                </figure>

                <ul class="lista-indicadores">
                    <li><span class="punto" aria-hidden="true"></span> Demanda mensual</li>
                    <li><span class="punto" aria-hidden="true"></span> Productividad</li>
                    <li><span class="punto" aria-hidden="true"></span> Ingresos</li>
                </ul>

                <footer class="sugerencia-pie">
                    <span>Disponible en</span>
                    <strong>Reportes</strong>
                </footer>
            </section>
        </aside>

    </section>
</main>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<meta name="reportes-url" content="{{ route('admin.reportes.datos') }}">
@vite('resources/js/reportes.js')
@endsection