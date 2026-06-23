@extends('layouts.app')
@section('title', 'Agenda de Bienestar')

@push('styles')
    @vite(['resources/css/agenda.css'])
@endpush

@section('content')
<main class="modulo-agenda">
    <header class="agenda-header">
        <section>
            <p class="subtitulo">PANEL DE GESTIÓN</p>
            <h1 class="titulo-agenda">Agenda de Bienestar</h1>
            <p class="descripcion-agenda">Gestión y monitoreo en tiempo real de las citas programadas.</p>
        </section>
        
        @if(auth()->check() && auth()->user()->rol === 'recepcionista')
            <a href="{{ route('citas.create') }}" class="btn-nueva-cita">
                <i data-lucide="plus"></i> Nueva cita
            </a>
        @endif
    </header>

    <section class="stats-container">
        <article class="stat-card"><span>{{ $citas->where('estado', 'confirmada')->count() }}</span><small>Confirmadas</small></article>
        <article class="stat-card"><span>{{ $citas->where('estado', 'pendiente')->count() }}</span><small>Pendientes</small></article>
        <article class="stat-card"><span>{{ $citas->where('estado', 'en curso')->count() }}</span><small>En curso</small></article>
        <article class="stat-card"><span>{{ $citas->where('estado', 'completada')->count() }}</span><small>Completadas</small></article>
        <article class="stat-card"><span>{{ $citas->where('estado', 'cancelada')->count() }}</span><small>Canceladas</small></article>
    </section>

    <form class="agenda-filtros" method="GET" action="{{ route('agenda') }}">
        <label class="filter-group">
            <span class="flex items-center gap-2"><i data-lucide="search"></i> BUSCAR</span>
            <input type="text" name="search" placeholder="Paciente o servicio..." value="{{ request('search') }}">
        </label>
        
        <label class="filter-group">
            <span class="flex items-center gap-2"><i data-lucide="calendar"></i> FECHA</span>
            <input type="date" name="fecha" value="{{ request('fecha', date('Y-m-d')) }}">
        </label>
        
        <label class="filter-group">
            <span class="flex items-center gap-2"><i data-lucide="user"></i> TERAPEUTA</span>
            <select name="terapeuta_id" onchange="this.form.submit()">
                <option value="">Todos los especialistas</option>
                @foreach($terapeutas as $t)
                    <option value="{{ $t->id }}" {{ request('terapeuta_id') == $t->id ? 'selected' : '' }}>{{ $t->nombre }}</option>
                @endforeach
            </select>
        </label>

        <label class="filter-group">
            <span class="flex items-center gap-2"><i data-lucide="filter"></i> ESTADO</span>
            <select name="estado" onchange="this.form.submit()">
                <option value="">Todos los estados</option>
                <option value="confirmada" {{ request('estado') == 'confirmada' ? 'selected' : '' }}>Confirmada</option>
                <option value="pendiente" {{ request('estado') == 'pendiente' ? 'selected' : '' }}>Pendiente</option>
                <option value="completada" {{ request('estado') == 'completada' ? 'selected' : '' }}>Completada</option>
                <option value="en curso" {{ request('estado') == 'en curso' ? 'selected' : '' }}>En curso</option>
                <option value="cancelada" {{ request('estado') == 'cancelada' ? 'selected' : '' }}>Cancelada</option>
            </select>
        </label>
    </form>

    <section class="tabla-responsive-wrapper">
        <table class="tabla-agenda">
            <thead>
                <tr>
                    <th>HORARIO</th><th>PACIENTE</th><th>TERAPEUTA</th><th>SERVICIO</th><th>ESTADO</th><th>ACCIONES</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citas as $cita)
                <tr>
                    <td><strong>{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') }}</strong></td>
                    <td>{{ $cita->paciente->nombre ?? 'N/A' }}</td>
                    <td>{{ $cita->terapeuta->nombre ?? 'N/A' }}</td>
                    <td>{{ $cita->servicio->nombre_servicio ?? 'N/A' }}</td>
                    <td><span class="status status-{{ $cita->estado }}">{{ ucfirst($cita->estado) }}</span></td>
                    <td>
                        <button class="btn-editar" onclick="abrirModalEditar({{ $cita->id }})">
                            <i data-lucide="edit-3"></i> Editar
                        </button>
                    </td>
                </tr>
                @empty
                <tr><td colspan="6" class="text-center p-4">No hay citas programadas.</td></tr>
                @endforelse
            </tbody>
        </table>
    </section>
</main>
@endsection
@push('scripts') 
@vite(['resources/js/agenda.js']) 
@endpush