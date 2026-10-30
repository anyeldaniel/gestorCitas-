@extends('layouts.app')

@section('title', 'Mis Reservas - The Beauty Room')

@push('styles')
    @vite(['resources/css/mis-reservas.css'])
@endpush

@push('scripts')
    @vite(['resources/js/mis-reservas.js'])
@endpush

@section('content')
@php
    $proximaCita = $citas->whereIn('estado', ['pendiente', 'confirmada'])->sortBy('fecha')->first();
    $historialCitas = $citas->where('estado', 'completada')->sortByDesc('fecha');
    
    $totalPendiente = $citas->whereIn('estado', ['pendiente', 'confirmada'])->sum('monto_total'); 
    $visitasRealizadas = $historialCitas->count();

    // ==========================================
    // LÓGICA DINÁMICA DE TIPS Y DESCRIPCIÓN
    // ==========================================
    $nombreServicio = strtolower($proximaCita->servicio->nombre_servicio ?? '');
    $descripcionServicio = $proximaCita->servicio->descripcion ?? 'Un ritual exclusivo diseñado para renovar tu energía y devolverte el equilibrio.';
    
    $tipBienestar = '“Respira profundo. Antes de tu sesión, llega unos minutos antes para disfrutar de nuestra infusión de hierbas.”';
    $tipsPreparacion = [
        'Llega 10 minutos antes para recibir con calma.',
        'Evita usar perfumes fuertes el día de tu sesión.',
        'Hidrátate bien antes de tu ritual.'
    ];

    if (str_contains($nombreServicio, 'manicura') || str_contains($nombreServicio, 'uñas')) {
        $tipBienestar = '“El detalle está en tus manos. Nos encanta recibirte con las uñas limpias y sin crema para que tu esmalte dure mucho más.”';
        $tipsPreparacion = [
            'Ven sin crema en las manos para mejor adherencia.',
            'Avísanos si tienes algún corte o uña encarnada.',
            'Trae tu color favorito si lo tienes en casa.'
        ];
    } elseif (str_contains($nombreServicio, 'peeling') || str_contains($nombreServicio, 'quimico')) {
        $tipBienestar = '“Tu piel respira mejor cuando está libre de impurezas. Evita exfoliarte o usar ácidos 24 horas antes de tu cita.”';
        $tipsPreparacion = [
            'Evita exfoliarte el rostro 24 horas antes.',
            'Ven sin maquillaje para optimizar el tiempo.',
            'Informa sobre cualquier alergia o sensibilidad.'
        ];
    } elseif (str_contains($nombreServicio, 'masaje') || str_contains($nombreServicio, 'descontracturante')) {
        $tipBienestar = '“El silencio es parte del ritual. Te invitamos a desconectar tu teléfono al ingresar a la cabina.”';
        $tipsPreparacion = [
            'No ingieras comidas pesadas 1 hora antes.',
            'Llega con tiempo para ducharte si lo deseas.',
            'Comunica al especialista tu nivel de presión preferido.'
        ];
    } elseif (str_contains($nombreServicio, 'anticelulitico') || str_contains($nombreServicio, 'anticelulítica')) {
        $tipBienestar = '“La constancia es clave. Hidratarse abundantemente después del tratamiento ayuda a eliminar toxinas.”';
        $tipsPreparacion = [
            'Ven con ropa cómoda que permita el acceso a la zona.',
            'Evita cremas corporales el día de la sesión.',
            'Bebe agua antes y después del tratamiento.'
        ];
    }
@endphp

<main class="dashboard-wrapper">
    
    <!-- HERO & STATS -->
    <header class="hero-section">
        <section class="hero-text">
            <span class="hero-subtitle">Tu espacio personal</span>
            <h1>Tus momentos de bienestar,<br>en un solo lugar.</h1>
            <p>Consulta tus próximas citas, completa pagos pendientes y vuelve a reservar tus rituales favoritos.</p>
        </section>
        
        <aside class="stats-box">
            <article class="stat-item">
                <h2>{{ $proximaCita ? '01' : '00' }}</h2>
                <span>Próxima cita</span>
            </article>
            <hr class="stat-divider">
            <article class="stat-item">
                <h2>{{ str_pad($visitasRealizadas, 2, '0', STR_PAD_LEFT) }}</h2>
                <span>Visitas realizadas</span>
            </article>
            <hr class="stat-divider">
            <article class="stat-item">
                <h2>${{ number_format($totalPendiente, 0) }}</h2>
                <span>Pendiente</span>
            </article>
        </aside>
    </header>

    <!-- PRÓXIMA CITA -->
    <section class="proxima-cita-section">
        <header class="section-header">
            <div class="section-title">
                <span>Próxima cita</span>
                <h2>Todo listo para tu próxima visita</h2>
            </div>
            <a href="{{ route('catalogo') }}" class="btn-nueva-reserva">Nueva reserva <span>+</span></a>
        </header>

        @if($proximaCita)
            @php
                $fechaCarbon = \Carbon\Carbon::parse($proximaCita->fecha);
                $totalPagado = $proximaCita->pagos->where('estado_pago', 'aprobado')->sum('monto_anticipado');
                $enRevision = $proximaCita->pagos->where('estado_pago', 'revision')->sum('monto_anticipado');
                $montoRestante = $proximaCita->monto_total - $totalPagado;
                $estaPagado = $totalPagado >= $proximaCita->monto_total;

                // NUEVAS VARIABLES PARA MULTI-SERVICIO
                $nombresServicios = $proximaCita->servicios->pluck('nombre_servicio')->join(' + ');
                $duracionTotal = $proximaCita->servicios->sum('duracion_minutos');
            @endphp

            <div class="cita-layout-grid">
                
                <!-- COLUMNA IZQUIERDA: TIPS DINÁMICOS -->
                <aside class="tips-column">
                    <article class="card-tip-bienestar">
                        <span class="tip-label">PARA TI:</span>
                        <h3 class="tip-title">Tip de bienestar</h3>
                        <p class="tip-text">{{ $tipBienestar }}</p>
                        <span class="tip-author">— Equipo The Beauty Room</span>
                    </article>

                    <article class="card-prepara-visita">
                        <header class="prepara-header">
                            <h4>Antes de venir</h4>
                            <span>PREPARA TU VISITA</span>
                        </header>
                        <p class="prepara-desc">Pequeños detalles para que tu experiencia sea perfecta.</p>
                        <div class="checklist">
                            @foreach($tipsPreparacion as $index => $tip)
                                <div class="check-item">
                                    <input type="checkbox" id="prep-{{ $index }}" class="prep-checkbox">
                                    <label for="prep-{{ $index }}">{{ $tip }}</label>
                                </div>
                            @endforeach
                        </div>
                    </article>
                </aside>

                <!-- COLUMNA CENTRO: TARJETA TICKET -->
                <article class="cita-ticket">
                    <div class="ticket-fecha">
                        <span class="mes">{{ $fechaCarbon->translatedFormat('M') }}</span>
                        <span class="dia">{{ $fechaCarbon->format('d') }}</span>
                        <span class="dia-semana">{{ $fechaCarbon->translatedFormat('l') }}</span>
                        <div class="ticket-anio">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                            {{ $fechaCarbon->format('Y') }}
                        </div>
                    </div>

                    <div class="ticket-detalles">
                        @if($proximaCita->servicio->imagen)
                            <div class="ticket-imagen-fondo">
                                <img src="{{ asset('storage/' . $proximaCita->servicio->imagen) }}" alt="{{ $proximaCita->servicio->nombre_servicio }}">
                            </div>
                        @endif

                        <div class="ticket-top">
                            <span class="ticket-ref">RESERVA #BR-{{ $fechaCarbon->format('Ymd') }}{{ $proximaCita->id }}</span>
                            <span class="badge {{ $estaPagado ? 'badge-pagado' : 'badge-pendiente' }}">
                                {{ $estaPagado ? '✓ PAGADO' : '● PENDIENTE DE PAGO' }}
                            </span>
                        </div>

                        <h3 class="ticket-titulo">{{ $nombresServicios ?: ($proximaCita->servicio->nombre_servicio ?? 'Tratamiento') }}</h3>
                        <p class="ticket-desc">{{ $descripcionServicio }}</p>

                        <div class="ticket-meta">
                            <div class="meta-item">
                                <span class="meta-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                                </span>
                                <div>
                                    <small>HORA</small>
                                    <strong>{{ \Carbon\Carbon::parse($proximaCita->hora)->format('h:i A') }}</strong>
                                </div>
                            </div>
                            
                            <div class="meta-item" style="grid-column: span 2; align-items: flex-start; margin-top: 5px; margin-bottom: 5px;">
                                <span class="meta-icon" style="margin-top: 3px;">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/></svg>
                                </span>
                                <div style="width: 100%;">
                                    <small>ESPECIALISTAS ASIGNADOS</small>
                                    <div style="display: flex; flex-direction: column; gap: 6px; margin-top: 5px;">
                                        @foreach($proximaCita->servicios as $serv)
                                            @php
                                                $espId = $serv->pivot->trabajador_id;
                                                $especialista = \App\Models\User::find($espId);
                                            @endphp
                                            <strong style="font-size: 0.85rem; color: var(--text-main); line-height: 1.3;">
                                                {{ $especialista->nombre ?? 'Por asignar' }} <br>
                                                <span style="font-weight: 500; font-size: 0.75rem; color: var(--text-muted);">({{ $serv->nombre_servicio }})</span>
                                            </strong>
                                        @endforeach
                                    </div>
                                </div>
                            </div>

                            <div class="meta-item">
                                <span class="meta-icon">
                                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </span>
                                <div>
                                    <small>DURACIÓN TOTAL</small>
                                    <strong>{{ $duracionTotal > 0 ? $duracionTotal : ($proximaCita->servicio->duracion_minutos ?? 60) }} minutos</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </article>

                <!-- COLUMNA DERECHA: PAGO -->
                <aside class="cita-pago-sidebar">
                    <div>
                        <span class="pago-total-label">{{ $estaPagado ? 'TOTAL PAGADO' : 'TOTAL PENDIENTE' }}</span>
                        <p class="pago-total-monto">${{ number_format($estaPagado ? $totalPagado : $montoRestante, 2) }}</p>
                        
                        @if(!$estaPagado && $enRevision == 0)
                            <div class="pago-alerta">
                                <span class="pago-alerta-icon">
                                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 8v4l3 3"/></svg>
                                </span>
                                <div class="pago-alerta-text">
                                    <strong>Completa el pago para confirmar</strong>
                                    <span>Pago seguro y confirmación inmediata.</span>
                                </div>
                            </div>
                        @endif
                    </div>

                    @if(!$estaPagado && $enRevision == 0)
                        <a href="{{ route('checkout.view', ['cita_id' => $proximaCita->id]) }}" class="btn-completar-pago">
                            COMPLETAR PAGO <span>→</span>
                        </a>
                    @endif
                </aside>

            </div>
        @else
            <p class="empty-state">No tienes citas próximas agendadas.</p>
        @endif
    </section>

    <!-- HISTORIAL -->
    <section class="historial-section">
        <header class="section-header">
            <div class="section-title">
                <span>Tu historial</span>
                <h2>Rituales que ya disfrutaste</h2>
            </div>
            <span class="historial-count">{{ $visitasRealizadas }} visitas completadas</span>
        </header>

        <div class="historial-grid">
            @forelse($historialCitas as $cita)
                @php 
                    $fechaHist = \Carbon\Carbon::parse($cita->fecha); 
                    // Verificamos si ya calificó esta cita
                    $yaCalifico = \App\Models\Resena::where('user_id', auth()->id())->where('cita_id', $cita->id)->exists();
                @endphp
                
                <article class="historial-card">
                    <time class="historial-fecha" datetime="{{ $fechaHist->format('Y-m-d') }}">
                        <span class="dia">{{ $fechaHist->format('d') }}</span>
                        <span class="mes">{{ $fechaHist->translatedFormat('M') }}</span>
                    </time>
                    
                    <div class="historial-info">
                        <span class="badge-completado">Completada</span>
                        <h4>{{ $cita->servicio->nombre_servicio ?? 'Tratamiento' }}</h4>
                        <p>{{ $cita->trabajador->nombre ?? 'Especialista' }} - {{ $cita->servicio->duracion_minutos ?? 60 }} min</p>
                        
                        <!-- 🆕 BOTÓN PARA CALIFICAR (Solo si no lo ha hecho) -->
                        @if(!$yaCalifico)
                            <button type="button" class="btn-calificar" 
                                    onclick="abrirModalResena({{ $cita->id }}, '{{ $cita->trabajador->nombre ?? 'Especialista' }}')">
                                ⭐ Calificar atención
                            </button>
                        @else
                            <span class="badge-calificado">✓ Ya calificaste esta cita</span>
                        @endif
                    </div>

                    <div class="historial-precio">
                        <span>Total</span>
                        <strong>${{ number_format($cita->monto_total, 0) }}</strong>
                    </div>
                </article>
            @empty
                <p class="empty-state">Aún no tienes visitas registradas.</p>
            @endforelse
        </div>
    </section>

</main>

<!-- ============================================
     MODAL DE RESEÑA 
     ============================================ -->

<div id="modal-resena-overlay" style="display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.6); backdrop-filter: blur(4px); z-index: 99999; align-items: center; justify-content: center;">
   
    <!-- CORRECCIÓN AQUÍ: Se usa 0 como ID temporal y se reemplaza dinámicamente al enviar -->
    <form id="form-resena" method="POST" action="{{ route('resenas.store', 0) }}" 
          onsubmit="this.action = this.action.replace('/0', '/' + document.getElementById('resena-cita-id').value);" 
          style="background: #fff; padding: 2rem; border-radius: 16px; width: 90%; max-width: 400px; position: relative; box-shadow: 0 10px 25px rgba(0,0,0,0.1);">
        @csrf
        <input type="hidden" name="cita_id" id="resena-cita-id">
        <input type="hidden" name="puntuacion" id="resena-puntuacion" value="0">

        <!-- Botón cerrar -->
        <button type="button" onclick="cerrarModalResena()" style="position: absolute; top: 1rem; right: 1rem; background: none; border: none; font-size: 1.5rem; cursor: pointer; color: #888; line-height: 1;">×</button>

        <div style="text-align: center; margin-bottom: 1.5rem;">
            <h2 style="font-family: 'Cormorant Garamond', serif; font-size: 1.6rem; margin: 0 0 0.5rem 0; color: #1f1d1a;">Califica tu experiencia</h2>
            <p style="font-size: 0.85rem; color: #888; margin: 0;">¿Cómo fue tu atención con <strong id="resena-nombre-terapeuta" style="color: #8b5e3c; font-size: 1rem;"></strong>?</p>
        </div>

        <!-- Estrellas -->
        <div style="display: flex; justify-content: center; gap: 0.5rem; margin-bottom: 0.5rem;">
            <span class="estrella-clic" data-valor="1" style="font-size: 2.5rem; color: #d1c7bd; cursor: pointer; user-select: none;">★</span>
            <span class="estrella-clic" data-valor="2" style="font-size: 2.5rem; color: #d1c7bd; cursor: pointer; user-select: none;">★</span>
            <span class="estrella-clic" data-valor="3" style="font-size: 2.5rem; color: #d1c7bd; cursor: pointer; user-select: none;">★</span>
            <span class="estrella-clic" data-valor="4" style="font-size: 2.5rem; color: #d1c7bd; cursor: pointer; user-select: none;">★</span>
            <span class="estrella-clic" data-valor="5" style="font-size: 2.5rem; color: #d1c7bd; cursor: pointer; user-select: none;">★</span>
        </div>
        
        <p id="texto-puntuacion" style="text-align: center; font-size: 0.85rem; color: #888; margin-bottom: 1.5rem; min-height: 1.2rem;">Selecciona una puntuación</p>

        <!-- Comentario -->
        <label for="comentario-resena" style="display: block; font-size: 0.7rem; font-weight: 700; color: #888; text-transform: uppercase; margin-bottom: 0.5rem;">Comentario (opcional)</label>
        <textarea id="comentario-resena" name="comentario" placeholder="Cuéntanos tu experiencia..." rows="3" style="width: 100%; box-sizing: border-box; padding: 0.8rem; border: 1px solid #e8e2dd; border-radius: 8px; font-family: inherit; resize: vertical; margin-bottom: 1.5rem;"></textarea>

        <!-- Botones -->
        <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
            <button type="button" onclick="cerrarModalResena()" style="padding: 0.6rem 1.5rem; border-radius: 24px; border: 1px solid #e8e2dd; background: transparent; color: #4a4640; font-weight: 600; font-size: 0.75rem; cursor: pointer;">Cancelar</button>
            <button type="submit" id="btn-enviar-resena" disabled style="padding: 0.6rem 1.5rem; border-radius: 24px; border: none; background: #1f1d1a; color: #fff; font-weight: 600; font-size: 0.75rem; cursor: pointer; opacity: 0.5;">Enviar Calificación</button>
        </div>
    </form>
</div>

@push('scripts')
    @vite(['resources/js/mis-reservas.js'])
@endpush
@endsection