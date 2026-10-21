@extends('layouts.app')

@section('title', 'Mis Reservas - The Beauty Room')

@push('styles')
    @vite(['resources/css/mis-reservas.css'])
@endpush

@section('content')
@php
    $proximaCita = $citas->whereIn('estado', ['pendiente', 'confirmada'])->sortBy('fecha')->first();
    $historialCitas = $citas->where('estado', 'completada')->sortByDesc('fecha');
    
    $totalPendiente = $citas->whereIn('estado', ['pendiente', 'confirmada'])->sum('monto_total'); 
    $visitasRealizadas = $historialCitas->count();
@endphp

<main class="dashboard-wrapper">
    
    <!-- HERO & STATS -->
    <header class="hero-section">
        <section class="hero-text">
            <span class="hero-subtitle">Tu espacio personal</span>
            <h1>Tus momentos de bienestar,<br>en un solo lugar</h1>
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
            @endphp

            <article class="proxima-cita-card">
                <!-- Fecha -->
                <time class="cita-fecha-sidebar" datetime="{{ $fechaCarbon->format('Y-m-d') }}">
                    <span class="mes">{{ $fechaCarbon->translatedFormat('M') }}</span>
                    <span class="dia">{{ $fechaCarbon->format('d') }}</span>
                    <span class="dia-semana">{{ $fechaCarbon->translatedFormat('l') }}</span>
                </time>

                <!-- Detalles -->
                <div class="cita-detalles-main">
                    <header class="cita-header-info">
                        <div>
                            <span class="cita-ref">Reserva #BR-{{ $proximaCita->id }}-{{ $fechaCarbon->format('Ymd') }}</span>
                            <h3 class="cita-titulo">{{ $proximaCita->servicio->nombre_servicio ?? 'Tratamiento Spa' }}</h3>
                        </div>
                        <span class="badge {{ $estaPagado ? 'badge-pagado' : 'badge-pendiente' }}">
                            {{ $estaPagado ? '✓ Pagado' : '● Pendiente de pago' }}
                        </span>
                    </header>

                    <div class="cita-meta-grid">
                        <div class="meta-info-item">
                            <span class="meta-icon-circle">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                            </span>
                            <div>
                                <small>Hora</small>
                                <strong>{{ \Carbon\Carbon::parse($proximaCita->hora)->format('h:i A') }}</strong>
                            </div>
                        </div>
                        
                        <div class="meta-info-item">
                            <span class="meta-icon-circle">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/></svg>
                            </span>
                            <div>
                                <small>Especialista</small>
                                <strong>{{ $proximaCita->trabajador->nombre ?? 'Por asignar' }}</strong>
                            </div>
                        </div>

                        <div class="meta-info-item">
                            <span class="meta-icon-circle">
                                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                            </span>
                            <div>
                                <small>Duración</small>
                                <strong>60 minutos</strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Pago -->
                <aside class="cita-pago-sidebar">
                    <div>
                        <span class="pago-total-label">{{ $estaPagado ? 'Total Pagado' : 'Total Pendiente' }}</span>
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
                            Completar pago <span>→</span>
                        </a>
                    @endif
                </aside>
            </article>
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
                @php $fechaHist = \Carbon\Carbon::parse($cita->fecha); @endphp
                <article class="historial-card">
                    <time class="historial-fecha" datetime="{{ $fechaHist->format('Y-m-d') }}">
                        <span class="dia">{{ $fechaHist->format('d') }}</span>
                        <span class="mes">{{ $fechaHist->translatedFormat('M') }}</span>
                    </time>
                    
                    <div class="historial-info">
                        <span class="badge-completado">Completada</span>
                        <h4>{{ $cita->servicio->nombre_servicio ?? 'Tratamiento' }}</h4>
                        <p>{{ $cita->trabajador->nombre ?? 'Especialista' }} - 60 min</p>
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
@endsection