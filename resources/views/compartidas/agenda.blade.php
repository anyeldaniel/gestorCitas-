@extends('layouts.app')

@section('title', 'Agenda de Citas')

@push('styles')
    @vite(['resources/css/agenda.css'])
@endpush

@section('content')
<section class="modulo-agenda">
    
    <header class="agenda-header">
        <h1>Agenda de Bienestar</h1>
        <p>Gestión y monitoreo en tiempo real de las citas programadas.</p>
    </header>

    {{-- Barra de Filtros: Se oculta el selector de terapeuta si el usuario es un trabajador --}}
    <form class="agenda-filtros" id="form-filtros-agenda">
        
        @if(auth()->user()->rol !== 'trabajador')
            <fieldset class="grupo-filtro">
                <label for="filtro-terapeuta">Terapeuta</label>
                <select id="filtro-terapeuta" name="terapeuta_id">
                    <option value="">Todos los especialistas</option>
                    @foreach($terapeutas as $terapeuta)
                        <option value="{{ $terapeuta->id }}" {{ request('terapeuta_id') == $terapeuta->id ? 'selected' : '' }}>
                            {{ $terapeuta->nombre }}
                        </option>
                    @endforeach
                </select>
            </fieldset>
        @endif

        <fieldset class="grupo-filtro">
            <label for="filtro-fecha">Fecha</label>
            <input type="date" id="filtro-fecha" name="fecha" value="{{ request('fecha', date('Y-m-d')) }}">
        </fieldset>
    </form>

    {{-- Tabla Maestra --}}
    <main class="tabla-responsiva">
        <table class="tabla-agenda">
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Paciente</th>
                    {{-- Ocultamos la columna Terapeuta si el usuario logueado ya es el trabajador --}}
                    @if(auth()->user()->rol !== 'trabajador')
                        <th>Terapeuta</th>
                    @endif
                    <th>Servicio</th>
                    <th>Estado</th>
                    <th class="texto-centrado">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citas as $cita)
                    <tr class="fila-cita">
                        <td class="columna-hora">
                            <time>{{ \Carbon\Carbon::parse($cita->fecha_hora_inicio)->format('H:i') }}</time>
                        </td>
                        <td>{{ $cita->paciente->nombre ?? 'Sin asignar' }}</td>
                        
                        @if(auth()->user()->rol !== 'trabajador')
                            <td>
                                <span class="badge-terapeuta">{{ $cita->terapeuta->nombre ?? 'No asignado' }}</span>
                            </td>
                        @endif

                        <td class="texto-atenuado">{{ $cita->servicio->nombre_servicio ?? 'Servicio' }}</td>
                        <td>
                            <span class="status-tag status-{{ strtolower($cita->estado) }}">
                                {{ ucfirst($cita->estado) }}
                            </span>
                        </td>
                        <td class="acciones-celda">
                            <button type="button" class="btn-agenda-modificar" onclick="abrirModalEditar({{ $cita->id }})">Editar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        {{-- Ajustamos el colspan dinámicamente según el rol --}}
                        <td colspan="{{ auth()->user()->rol === 'trabajador' ? 5 : 6 }}" class="tabla-vacia">
                            No hay citas registradas para este día.
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>
</section>
@endsection

@push('scripts')
    @vite(['resources/js/agenda.js'])
@endpush