@extends('layouts.app')

@section('title', 'Verificación de Pagos')

@push('styles')
    @vite(['resources/css/verificar-pago.css'])
@endpush

@section('content')
<main class="modulo-vista">

    <!-- Encabezado del Módulo -->
    <header class="encabezado-modulo">
        <div class="encabezado-modulo__left">
            <p class="subtitulo-seccion">Finanzas</p>
            <h1>Verificación de pagos</h1>
            <p>Confirma las transferencias reportadas por tus clientes antes de validar sus citas.</p>
        </div>
        <aside class="badge-seguridad">
            <div class="badge-seguridad__icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
            </div>
            <div class="badge-seguridad__texto">
                <small>Proceso automático</small>
                <strong>Confirmación inteligente</strong>
            </div>
        </aside>
    </header>

    @php
        // Cálculo de métricas basadas en la colección de pagos
        $pendientes = $pagos->where('estado_pago', 'revision')->count();
        $montoPendiente = $pagos->where('estado_pago', 'revision')->sum('monto_anticipado');
        $aprobadosHoy = $pagos->where('estado_pago', 'aprobado')->where('created_at', '>=', \Carbon\Carbon::today())->count();
    @endphp

    <!-- Tarjetas KPI -->
    <section class="tarjetas-resumen-pagos">
        <article class="tarjeta-kpi-pago destacada">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="9"/><path stroke-linecap="round" stroke-linejoin="round" d="M12 7v5l3 2"/></svg>
                </div>
                <p class="kpi-label">Pagos por verificar</p>
            </div>
            <div class="kpi-valor">
                <strong>{{ $pendientes }}</strong>
                <span>Requieren tu atención</span>
            </div>
        </article>

        <article class="tarjeta-kpi-pago">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="6" width="18" height="13" rx="2"/><path stroke-linecap="round" d="M3 10h18"/></svg>
                </div>
                <p class="kpi-label">Monto pendiente</p>
            </div>
            <div class="kpi-valor">
                <strong>${{ number_format($montoPendiente, 2) }}</strong>
                <span>En {{ $pendientes }} transacciones</span>
            </div>
        </article>

        <article class="tarjeta-kpi-pago">
            <div class="kpi-header">
                <div class="kpi-icono">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M9 12.75 11.25 15 15 9.75M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Z" /></svg>
                </div>
                <p class="kpi-label">Autorizados hoy</p>
            </div>
            <div class="kpi-valor">
                <strong>{{ $aprobadosHoy }}</strong>
                <span>Pagos confirmados</span>
            </div>
        </article>
    </section>

    <!-- Panel Principal -->
    <section class="panel-pagos">

        <!-- Header del Panel -->
        <header class="panel-pagos__header">
            <div class="panel-pagos__titulo">
                <span class="kicker">Transacciones reportadas</span>
                <h2>Pagos recientes</h2>
            </div>
            <div class="estado-actualizacion">
                <span class="dot"></span>
                <span>Actualizado ahora</span>
            </div>
        </header>

        <!-- Filtros -->
        <div class="panel-pagos__filtros">
            <div class="campo-busqueda">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><circle cx="11" cy="11" r="7"/><path stroke-linecap="round" d="m20 20-3.5-3.5"/></svg>
                <input type="text" id="input-busqueda-pagos" placeholder="Buscar cliente o referencia">
            </div>
            <div class="campo-fecha">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><rect x="3" y="5" width="18" height="16" rx="2"/><path stroke-linecap="round" d="M16 3v4M8 3v4M3 10h18"/></svg>
                <input type="date" id="input-fecha-pagos">
            </div>
        </div>

        <!-- Tabs de Estado -->
        <nav class="panel-pagos__tabs">
            <button type="button" class="tab-estado" data-filtro="todos">
                Todos <span class="tab-contador">{{ $pagos->count() }}</span>
            </button>
            <button type="button" class="tab-estado activo" data-filtro="revision">
                Pendiente <span class="tab-contador">{{ $pendientes }}</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="aprobado">
                Autorizado <span class="tab-contador">{{ $pagos->where('estado_pago', 'aprobado')->count() }}</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="rechazado">
                Rechazado <span class="tab-contador">{{ $pagos->where('estado_pago', 'rechazado')->count() }}</span>
            </button>
            <button type="button" class="tab-estado" data-filtro="reembolso">
                Reembolso <span class="tab-contador">{{ $pagos->whereNotNull('estado_reembolso')->count() }}</span>
            </button>
        </nav>

        <!-- Tabla de Transacciones -->
        <div class="tabla-responsive-wrapper">
            <table class="tabla-transacciones">
                <thead>
                    <tr>
                        <th>Fecha y hora</th>
                        <th>Cliente</th>
                        <th>Método / Banco</th>
                        <th>Referencia</th>
                        <th>Monto</th>
                        <th>Estado</th>
                        <th style="text-align:right;">Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($pagos as $pago)
                        @php
                            $cliente = $pago->cita->cliente ?? null;
                            $nombreCliente = $cliente ? $cliente->nombre : 'Cliente Eliminado';
                            
                            // Lógica para obtener "YP" (Inicial del Nombre + Inicial del Apellido)
                            $partesNombre = explode(' ', trim($nombreCliente));
                            $iniciales = strtoupper(substr($partesNombre[0], 0, 1) . (isset($partesNombre[1]) ? substr($partesNombre[1], 0, 1) : ''));
                            
                            $estadoClass = 'estado-pendiente';
                            $estadoTexto = 'Pendiente';
                            if($pago->estado_pago === 'aprobado') { $estadoClass = 'estado-autorizado'; $estadoTexto = 'Autorizado'; }
                            if($pago->estado_pago === 'rechazado') { $estadoClass = 'estado-rechazado'; $estadoTexto = 'Rechazado'; }
                            if($pago->estado_reembolso === 'por_definir') { $estadoClass = 'estado-pendiente'; $estadoTexto = 'Reembolso por definir'; }
                            if($pago->estado_reembolso === 'pendiente') { $estadoClass = 'estado-pendiente'; $estadoTexto = 'Reembolso pendiente'; }
                            $pagosAprobados = $pago->cita->pagos
                                ->where('estado_pago', 'aprobado')
                                ->where('estado_reembolso', '!=', 'pendiente')
                                ->map(fn ($pagoAprobado) => ['id' => $pagoAprobado->id, 'monto' => $pagoAprobado->monto_anticipado])
                                ->values();
                        @endphp
                        
                        <tr data-estado="{{ $pago->estado_reembolso ? 'reembolso' : $pago->estado_pago }}"
                            data-fecha="{{ $pago->created_at->format('Y-m-d') }}"
                            data-pagos-aprobados='@json($pagosAprobados)'>
                            <td class="celda-fecha">
                                <strong>{{ $pago->created_at->format('d M Y') }}</strong>
                                <span>{{ $pago->created_at->format('h:i A') }}</span>
                            </td>
                            
                            <td>
                                <div class="celda-cliente">
                                    <div class="cliente-avatar">{{ $iniciales }}</div>
                                    <div class="cliente-info">
                                        <strong>{{ $nombreCliente }}</strong>
                                        <small>{{ $cliente->correo ?? 'Sin correo' }}</small>
                                    </div>
                                </div>
                            </td>
                            
                            <td class="celda-banco">
                                <strong>{{ $pago->metodoBancoLabel() }}</strong>
                                @if($pago->banco_origen && $pago->metodo_pago !== 'c2p')
                                    <small>{{ $pago->banco_origen }}</small>
                                @endif
                            </td>
                            
                            <td class="celda-referencia">
                                <code>#{{ $pago->referencia ?? 'N/A' }}</code>
                            </td>
                            
                            <td class="celda-monto">
                                ${{ number_format($pago->monto_anticipado, 2) }}
                            </td>
                            
                            <td>
                                <span class="{{ $estadoClass }}">{{ $estadoTexto }}</span>
                                @if($pago->estado_reembolso === 'pendiente')
                                    <small>${{ number_format($pago->monto_reembolso, 2) }}</small>
                                @endif
                                @if($pago->motivo_reembolso)
                                    <small>{{ $pago->motivo_reembolso }}</small>
                                @endif
                                @if($pago->estado_pago === 'rechazado')
                                    <small>{{ $pago->motivo_rechazo }}</small>
                                @endif
                            </td>
                            
                            <td>
                                <div class="celda-acciones">
                                    @if($pago->estado_pago === 'revision')
                                        <button type="button" class="btn-accion-icono btn-rechazar" title="Rechazar" data-url="{{ route('recepcion.rechazar.pago', $pago->id) }}">
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18 18 6M6 6l12 12"/></svg>
                                        </button>
                                        <button type="button" class="btn-autorizar" data-url="{{ route('recepcion.aprobar.pago', $pago->id) }}">
                                            Autorizar
                                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5"><path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/></svg>
                                        </button>
                                    @elseif($pago->estado_reembolso === 'por_definir')
                                        <button type="button" class="btn-autorizar btn-definir-reembolso"
                                                data-url="{{ route('recepcion.definir.reembolso', $pago->id) }}"
                                                data-max="{{ $pago->monto_anticipado }}">
                                            Definir reembolso
                                        </button>
                                    @else
                                        <span class="texto-procesado">Procesado</span>
                                    @endif
                                    
                                    <!-- Botón para abrir el modal de detalle -->
                                    <button type="button" class="btn-ver-detalle" 
                                            data-cliente="{{ $nombreCliente }}"
                                            data-correo="{{ $cliente->correo ?? 'Sin correo' }}"
                                            data-iniciales="{{ $iniciales }}"
                                            data-metodo="{{ $pago->metodoBancoLabel() }}"
                                            data-banco="{{ $pago->banco_origen ?? '' }}"
                                            data-referencia="{{ $pago->referencia ?? 'N/A' }}"
                                            data-monto="${{ number_format($pago->monto_anticipado, 2) }}"
                                            data-fecha="{{ $pago->created_at->format('d M Y · h:i A') }}"
                                            data-estado="{{ $estadoTexto }}"
                                            
                                            {{-- Ajustado a las propiedades de tu base de datos --}}
                                            data-servicio="{{ $pago->cita->servicio->nombre_servicio ?? 'No asignado' }}"
                                            data-duracion="{{ $pago->cita->servicio->duracion_minutos ?? '0' }} minutos"
                                            data-fecha-cita="{{ $pago->cita->fecha ? \Carbon\Carbon::parse($pago->cita->fecha)->translatedFormat('l, d \d\e F \d\e Y') : 'No definida' }}"
                                            data-horario="{{ $pago->cita->hora ?? 'No definida' }}"
                                            data-especialista="{{ $pago->cita->trabajador->nombre ?? 'Por asignar' }}"
                                            data-contacto="{{ $cliente->telefono ?? 'Sin teléfono' }}">
                                        Ver detalle
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr class="fila-vacia">
                            <td colspan="7">
                                <span>No hay transacciones registradas.</span>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pie del Panel -->
        <footer class="panel-pagos__footer">
            <p>Mostrando <strong id="footer-contador">{{ $pagos->count() }}</strong> transacciones</p>
            <p class="nota">Los datos se muestran solo para fines de verificación interna.</p>
        </footer>

    </section>
</main>

<!-- ========================================== -->
<!-- MODAL 1: CONFIRMACIÓN DE PAGO              -->
<!-- ========================================== -->
<dialog id="modal-confirmacion" class="modal-contenedor-alerta-critica">
    <div class="modal-pago-card">
        <header class="modal-pago-header">
            <div class="modal-pago-header__texto">
                <div class="modal-icono-escudo" id="modal-icono-escudo">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"></path><path d="M9 12l2 2 4-4"></path></svg>
                </div>
                <span id="modal-kicker" class="modal-pago-kicker">VERIFICACIÓN DE PAGO</span>
                <h3 id="modal-titulo" class="titulo-alerta-critica">¿Quieres autorizar este pago?</h3>
                <p class="modal-pago-subtitulo" id="modal-subtitulo">Revisa los datos antes de confirmar. Esta acción dejará la reserva aprobada.</p>
            </div>
            <button type="button" id="btn-modal-cerrar-x" class="modal-pago-cerrar" aria-label="Cerrar modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="modal-pago-body">
            <div id="modal-resumen-pago" class="modal-pago-resumen">
                <div class="cliente-avatar-modal" id="modal-avatar-iniciales">YP</div>
                <div class="modal-pago-resumen__info">
                    <strong id="modal-pago-cliente">Cliente</strong>
                    <span id="modal-pago-detalle">Método · Referencia</span>
                </div>
                <div class="modal-pago-resumen__meta">
                    <strong id="modal-pago-monto">$0.00</strong>
                    <span id="modal-pago-fecha">Fecha</span>
                </div>
            </div>

            <div class="modal-nota-info" id="modal-nota-info">
                <div class="nota-icono">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><circle cx="12" cy="12" r="10"></circle><line x1="12" y1="16" x2="12" y2="12"></line><line x1="12" y1="8" x2="12.01" y2="8"></line></svg>
                </div>
                <p id="modal-mensaje-nota">La reserva de <strong>Cliente</strong> quedará confirmada al aprobar el pago.</p>
            </div>

            <p id="modal-mensaje" class="mensaje-alerta-descripcion hidden"></p>
            <textarea id="motivo-rechazo" class="textarea-motivo hidden" rows="3" minlength="5" maxlength="500" placeholder="Escribe el motivo del rechazo aquí..."></textarea>
            
            <div id="campos-reembolso-pago" class="textarea-motivo hidden">
                <p>Indica cuánto se reembolsará por cada pago aprobado:</p>
                <div id="lista-reembolsos-pago"></div>
            </div>
            
            <label id="campo-monto-reembolso" class="textarea-motivo hidden">
                Importe del reembolso
                <input id="monto-reembolso" type="number" min="0.01" step="0.01" required>
            </label>
        </div>

        <footer class="pie-alerta-botones">
            <button type="button" id="btn-modal-cancelar" class="btn-cancelar-alerta">Cancelar</button>
            <button type="button" id="btn-modal-confirmar" class="btn-confirmar-accion btn-accion-autorizar">Autorizar pago</button>
        </footer>
    </div>
</dialog>

<!-- ========================================== -->
<!-- MODAL 2: DETALLE DE LA TRANSACCIÓN         -->
<!-- ========================================== -->
<dialog id="modal-detalle-pago" class="modal-contenedor-alerta-critica modal-detalle">
    <div class="modal-pago-card">
        
        <header class="modal-detalle-header">
            <div class="modal-pago-header__texto">
                <span class="modal-pago-kicker">DETALLE DE LA TRANSACCIÓN</span>
                <h3 id="detalle-titulo" class="titulo-alerta-critica">Cita de Cliente</h3>
                <p class="detalle-estado-fecha">
                    <span id="detalle-badge-estado" class="estado-autorizado">Autorizado</span>
                    <span id="detalle-fecha" class="detalle-fecha-texto">27 nov 2026 · 01:13</span>
                </p>
            </div>
            <button type="button" id="btn-detalle-cerrar-x" class="modal-pago-cerrar" aria-label="Cerrar modal">
                <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6L6 18M6 6l12 12"/></svg>
            </button>
        </header>

        <div class="modal-detalle-body">
            
            <div class="detalle-seccion-pago">
                <span class="detalle-label">INFORMACIÓN DEL PAGO</span>
                <span class="detalle-monto-grande" id="detalle-monto">$0,00</span>
            </div>

            <div class="detalle-tarjeta-datos">
                <span class="detalle-dato-col">
                    <span class="detalle-dato-label">MÉTODO / BANCO</span>
                    <strong id="detalle-metodo">Pago móvil · Banesco</strong>
                </span>
                <span class="detalle-dato-col">
                    <span class="detalle-dato-label">REFERENCIA</span>
                    <strong id="detalle-referencia">#000000</strong>
                </span>
                <span class="detalle-dato-col">
                    <span class="detalle-dato-label">RESULTADO</span>
                    <strong id="detalle-resultado">Autorizado</strong>
                </span>
            </div>

            <div class="detalle-seccion-cita-header">
                <span class="detalle-label">DETALLES DE LA CITA</span>
                <span class="detalle-badge-reserva" id="detalle-reserva-estado">Reserva confirmada</span>
            </div>

            <div class="detalle-tarjeta-cita">
                <div class="detalle-cita-grid">
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">SERVICIO</span>
                        <strong id="detalle-servicio">Masaje Anti-estrés</strong>
                    </span>
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">DURACIÓN</span>
                        <strong id="detalle-duracion">75 minutos</strong>
                    </span>
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">FECHA</span>
                        <strong id="detalle-fecha-cita">Jueves, 26 de noviembre de 2026</strong>
                    </span>
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">HORARIO</span>
                        <strong id="detalle-horario">14:30 - 15:45</strong>
                    </span>
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">ESPECIALISTA</span>
                        <strong id="detalle-especialista">Por asignar</strong>
                    </span>
                    <span class="detalle-cita-item">
                        <span class="detalle-dato-label">CONTACTO</span>
                        <strong id="detalle-contacto">04123691316</strong>
                    </span>
                </div>
            </div>

            <div class="detalle-cliente-card">
                <div class="cliente-avatar-modal" id="detalle-cliente-iniciales" style="width: 40px; height: 40px; font-size: 0.75rem;">YP</div>
                <div class="modal-pago-resumen__info">
                    <strong id="detalle-cliente-nombre">Yenni Perez</strong>
                    <span id="detalle-cliente-correo">yennifervueda19@gmail.com</span>
                </div>
            </div>

        </div>

        <footer class="pie-alerta-botones">
            <button type="button" id="btn-detalle-cerrar" class="btn-confirmar-accion btn-accion-autorizar">Cerrar detalle</button>
        </footer>
    </div>
</dialog>
@endsection

@push('scripts')
    @vite(['resources/js/verificar-pago.js'])
@endpush