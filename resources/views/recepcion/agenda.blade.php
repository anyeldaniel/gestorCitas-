@extends('layouts.app')

@section('title', 'Agenda Física de Cabinas')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo">
        <h1>Agenda Física del Spa</h1>
        <p>Control de horarios y asignación de cabinas. Arrastra las citas para reprogramar las horas de atención.</p>
    </header>

    <header class="barra-disponibilidad">
    <span class="titulo-disponibilidad">Estaciones Activas:</span>
    <span class="status-badge disponible">Dra. Alana (Cabina 1 — Libre)</span>
    <span class="status-badge ocupado">Tpta. Marcos (Cabina 2 — En Masaje)</span>
    <span class="status-badge disponible">Esteticista Clara (Cabina 3 — Libre)</span>
</header>

<section class="grilla-horaria-spa" style="background: white; border: 1px solid var(--color-borde-suave); border-radius: 8px; overflow: hidden;">
        
        @forelse($citasPorHora as $hora => $citas)
            <article class="bloque-hora" style="display: grid; grid-template-columns: 100px 1fr; border-bottom: 1px solid var(--color-borde-suave);">
                
                <time style="padding: 1.5rem; font-weight: bold; background: #fafafa; border-right: 1px solid var(--color-borde-suave); display: flex; align-items: center; justify-content: center; text-align: center;">
                    {{ $hora }}
                </time>
                
                <section class="zona-caida-agenda" data-hora="{{ $hora }}" style="padding: 0.75rem; display: flex; gap: 1rem; min-height: 80px; align-items: center; flex-wrap: wrap;">
                    
                    @foreach($citas as $cita)
                        <article class="tarjeta-cita-operativa" id="turno-{{ $cita->id }}" draggable="true" style="padding: 0.75rem 1rem; background: #fdfefe; border: 1px solid var(--color-borde-suave); border-left: 4px solid var(--color-verde-zen); border-radius: 6px; width: 100%; max-width: 450px;">
                            <h3 style="font-size: 0.95rem; font-weight: 600; margin: 0;">{{ $cita->cliente_nombre }}</h3>
                            <p style="font-size: 0.8rem; color: var(--color-texto-claro); margin: 2px 0 0 0;">
                                {{ $cita->nombre_servicio }} | <strong>Especialista: {{ $cita->especialista_nombre }}</strong><br>
                                <small style="color: var(--color-primario);">📅 Fecha: {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</small>
                            </p>
                        </article>
                    @endforeach

                </section>
            </article>
        @empty
            <div style="padding: 3rem; text-align: center; color: var(--color-texto-claro);">
                <p>No hay citas agendadas por el momento. ¡La agenda está libre!</p>
            </div>
        @endforelse

    </section>
</section>
@endsection