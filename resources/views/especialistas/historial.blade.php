@extends('layouts.app')

@section('title', 'Historial de Pacientes Atendidos')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo">
        <h1>Historial de Tratamientos</h1>
        <p>Consulte el registro histórico de las sesiones ejecutadas en cabina y las observaciones acumuladas.</p>
    </header>

    <section class="lista-admisiones" style="margin-top: 1.5rem;">
        
        <article class="tarjeta-admision">
            <header class="info-cliente">
                <h3>Carlos Mendoza</h3>
                <p>Masaje con Piedras Volcánicas — Finalizado el 25/05/2026</p>
                <small style="color: var(--color-texto-claro); display: block; margin-top: 0.5rem;">
                    <strong>Nota médica:</strong> Contractura muscular en zona escapular aliviada con calor localizado.
                </small>
            </header>
            <footer>
                <a href="{{ url('/especialistas/tarjeta/201') }}" class="btn-check check-in" style="text-decoration: none; display: inline-block;">Ver Ficha Completa</a>
            </footer>
        </article>

        <article class="tarjeta-admision">
            <header class="info-cliente">
                <h3>María Delgado</h3>
                <p>Limpieza Facial Profunda — Finalizado el 20/05/2026</p>
                <small style="color: var(--color-texto-claro); display: block; margin-top: 0.5rem;">
                    <strong>Nota médica:</strong> Piel con tendencia grasa, se aplicó mascarilla de arcilla zen e hidratación posterior.
                </small>
            </header>
            <footer>
                <a href="{{ url('/especialistas/tarjeta/202') }}" class="btn-check check-in" style="text-decoration: none; display: inline-block;">Ver Ficha Completa</a>
            </footer>
        </article>

    </section>
</section>
@endsection