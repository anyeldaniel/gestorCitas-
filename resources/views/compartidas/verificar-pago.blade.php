@extends('layouts.app')

@section('title', 'Verificación de Pagos - The Beauty Room')

@push('styles')
    @vite(['resources/css/verificar-pago.css'])
@endpush

@section('content')
<main class="modulo-vista">

    {{-- ============================================================
         HEADER
         ============================================================ --}}
    <header class="encabezado-modulo">
        <div class="encabezado-modulo__left">
            <p class="subtitulo-seccion">Finanzas</p>
            <h1>Verificación de pagos</h1>
            <p>Confirma las transferencias reportadas por tus clientes antes de validar sus citas.</p>
        </div>

        <aside class="badge-seguridad">
            <div class="badge-seguridad__icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                </svg>
            </div>
            <div class="badge-seguridad__texto">
                <small>Proceso automático</small>
                <strong>Confirmación inteligente</strong>
            </div>
        </aside>
    </header>

    {{-- ============================================================
         KPI CARDS
         ============================================================ --}}
    <section class="tarjetas-resumen-pagos">

        <article class="tarjeta-kpi-pago destacada">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <circle cx="12" cy="12" r="9"/>
                        <path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/>
                    </svg>
                </div>
                <p class="kpi-label">Pagos por verificar</p>
            </div>
            <div class="kpi-valor">
                <strong class="kpi-pendientes-count">{{ count($transacciones ?? []) ?: 3 }}</strong>
                <span>Requieren tu atención</span>
            </div>
        </article>

        <article class="tarjeta-kpi-pago">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <rect x="3" y="6" width="18" height="13" rx="2"/>
                        <path stroke-linecap="round" d="M3 10h18"/>
                    </svg>
                </div>
                <p class="kpi-label">Monto pendiente</p>
            </div>
            <div class="kpi-valor">
                <strong>$85.50</strong>
                <span>En 3 transacciones</span>
            </div>
        </article>

        <article class="tarjeta-kpi-pago">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" />
                    </svg>
                </div>
                <p class="kpi-label">Autorizados hoy</p>
            </div>
            <div class="kpi-valor">
                <strong>1</strong>
                <span>Pago confirmado</span>
            </div>
        </article>

    </section>

    {{-- ============================================================
         PANEL PRINCIPAL
         ============================================================ --}}
    <section class="panel-pagos">

        {{-- Header del panel --}}
        <div class="panel-pagos__header">
            <div class="panel-pagos__titulo">
                <span class="kicker">Transacciones reportadas</span>
                <h2>Pagos recientes</h2>
            </div>
            <div class="estado-actualizacion">
                <span class="dot"></span>
                <span>Actualizado ahora</span>
            </div>
        </div>

        {{-- Buscador + filtro fecha --}}
        <div class="panel-pagos__filtros">
            <div class="campo-busqueda">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <circle cx="11" cy="11" r="7"/>
                    <path stroke-linecap="round" d="m20 20-3.5-3.5"/>
                </svg>
                <input type="text" placeholder="Buscar cliente o referencia">
            </div>

            <div class="campo-fecha">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <rect x="3" y="5" width="18" height="16" rx="2"/>
                    <path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/>
                </svg>
                <input type="date" value="{{ request('fecha_filtro', date('Y-m-d')) }}">
            </div>
        </div>

        {{-- Tabs de estado --}}
        <nav class="panel-pagos__tabs">
            <button type="button" class="tab-estado activo" data-filtro="pendiente">
                Pendiente <span class="tab-contador">3</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="autorizado">
                Autorizado <span class="tab-contador">1</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="rechazado">
                Rechazado <span class="tab-contador">1</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="todos">
                Todos <span class="tab-contador">5</span>
            </button>
        </nav>

        {{-- Tabla --}}
        <div class="tabla-responsive-wrapper">
            <table class="tabla-transacciones">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Cliente</th>
                        <th>Método de pago</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>

                    {{-- Transacción 1 --}}
                    <tr>
                        <td class="celda-fecha">
                            <strong>22 jun 2026</strong>
                            <span>3:45 PM</span>
                        </td>
                        <td>
                            <div class="celda-cliente">
                                <div class="cliente-avatar">EM</div>
                                <div class="cliente-info">
                                    <strong>Eilyn Martinez</strong>
                                    <small>V-25.666.777</small>
                                </div>
                            </div>
                        </td>
                        <td class="celda-banco">
                            <strong>Pago Móvil</strong>
                            <small>0414 123 4567</small>
                        </td>
                        <td class="celda-referencia">
                            <code>#894125</code>
                        </td>
                        <td class="celda-monto">$15.00</td>
                        <td><span class="estado-pendiente">Pendiente</span></td>
                        <td>
                            <div class="celda-acciones">
                                <button type="button" class="btn-accion-icono btn-rechazar" title="Rechazar transacción">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn-autorizar" title="Autorizar transacción">
                                    Autorizar
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Transacción 2 --}}
                    <tr>
                        <td class="celda-fecha">
                            <strong>22 jun 2026</strong>
                            <span>2:18 PM</span>
                        </td>
                        <td>
                            <div class="celda-cliente">
                                <div class="cliente-avatar">AS</div>
                                <div class="cliente-info">
                                    <strong>Andrea Salazar</strong>
                                    <small>V-19.482.311</small>
                                </div>
                            </div>
                        </td>
                        <td class="celda-banco">
                            <strong>Transferencia</strong>
                            <small>0424 510 8932</small>
                        </td>
                        <td class="celda-referencia">
                            <code>#750284</code>
                        </td>
                        <td class="celda-monto">$42.00</td>
                        <td><span class="estado-pendiente">Pendiente</span></td>
                        <td>
                            <div class="celda-acciones">
                                <button type="button" class="btn-accion-icono btn-rechazar" title="Rechazar transacción">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn-autorizar" title="Autorizar transacción">
                                    Autorizar
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                    {{-- Transacción 3 --}}
                    <tr>
                        <td class="celda-fecha">
                            <strong>22 jun 2026</strong>
                            <span>11:08 AM</span>
                        </td>
                        <td>
                            <div class="celda-cliente">
                                <div class="cliente-avatar">DR</div>
                                <div class="cliente-info">
                                    <strong>Daniela Rojas</strong>
                                    <small>V-27.105.642</small>
                                </div>
                            </div>
                        </td>
                        <td class="celda-banco">
                            <strong>Pago Móvil</strong>
                            <small>0412 822 1024</small>
                        </td>
                        <td class="celda-referencia">
                            <code>#319067</code>
                        </td>
                        <td class="celda-monto">$28.50</td>
                        <td><span class="estado-pendiente">Pendiente</span></td>
                        <td>
                            <div class="celda-acciones">
                                <button type="button" class="btn-accion-icono btn-rechazar" title="Rechazar transacción">
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                                <button type="button" class="btn-autorizar" title="Autorizar transacción">
                                    Autorizar
                                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                                    </svg>
                                </button>
                            </div>
                        </td>
                    </tr>

                </tbody>
            </table>
        </div>

        {{-- Pie del panel --}}
        <footer class="panel-pagos__footer">
            <p>Mostrando <strong>3</strong> de <strong>5</strong> transacciones</p>
            <p class="nota">Los datos se muestran solo para fines de verificación interna.</p>
        </footer>

    </section>

</main>

{{-- ============================================================
     MODAL CONFIRMACIÓN - ESTILO CATÁLOGO
     ============================================================ --}}
<dialog id="modal-confirmacion" class="modal-contenedor-alerta-critica">
    <article class="cuerpo-alerta-centrado">
        <figure class="icono-alerta-advertencia">⚠️</figure>
        <h3 id="modal-titulo" class="titulo-alerta-critica">Confirmar Acción</h3>
        <p id="modal-mensaje" class="mensaje-alerta-descripcion">¿Estás seguro de que deseas procesar esta transacción?</p>

        <textarea id="motivo-rechazo"
                  placeholder="Escribe el motivo del rechazo aquí..."
                  rows="3"
                  class="textarea-motivo hidden"></textarea>

        <footer class="pie-alerta-botones">
            <button type="button" id="btn-modal-cancelar" class="btn-cancelar-alerta">Cancelar</button>
            <button type="button" id="btn-modal-confirmar" class="btn-confirmar-accion">Confirmar</button>
        </footer>
    </article>
</dialog>
@endsection

@push('scripts')
    @vite(['resources/js/verificar-pago.js'])
@endpush