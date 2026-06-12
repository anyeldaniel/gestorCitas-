@extends('layouts.app')

@section('title', 'Agenda Global')

@section('content')
<section class="modulo-agenda">
    
    <header class="agenda-header">
        <h1>Agenda Global de Terapeutas</h1>
        <p>Monitoreo y asignación en tiempo real para las experiencias de The Beauty Room.</p>
    </header>

    <!-- Barra de Filtros con Elemento Form Semántico -->
    <form class="agenda-filtros" id="form-filtros-agenda" onsubmit="event.preventDefault();">
        <fieldset class="grupo-filtro">
            <label for="filtro-terapeuta">Terapeuta Especialista</label>
            <select id="filtro-terapeuta" name="terapeuta">
                <option value="todos">Todos los terapeutas</option>
                <option value="alana">Dra. Alana Ramos</option>
                <option value="andres">Lic. Andrés García</option>
            </select>
        </fieldset>

        <fieldset class="grupo-filtro">
            <label for="filtro-fecha">Fecha de Visualización</label>
            <input type="date" id="filtro-fecha" name="fecha" value="{{ date('Y-m-d') }}">
        </fieldset>
    </form>

    <!-- Contenedor Responsivo de la Tabla Maestra -->
    <main class="tabla-responsiva">
        <table class="tabla-agenda">
            <thead>
                <tr>
                    <th>Horario</th>
                    <th>Paciente / Cliente</th>
                    <th>Terapeuta</th>
                    <th>Servicio / Tratamiento</th>
                    <th>Estado</th>
                    <th class="texto-centrado">Acciones</th>
                </tr>
            </thead>
            <tbody>
                @forelse($citas as $cita)
                    <tr class="fila-cita" data-terapeuta="{{ $cita['terapeuta'] }}">
                        <td class="columna-hora"><time>{{ $cita['hora'] }}</time></td>
                        <td>{{ $cita['cliente'] }}</td>
                        <td><span class="badge-terapeuta">{{ $cita['terapeuta'] }}</span></td>
                        <td class="texto-atenuado">{{ $cita['servicio'] }}</td>
                        <td>
                            <span class="status-tag {{ strtolower($cita['estado']) }}">
                                {{ $cita['estado'] }}
                            </span>
                        </td>
                        <td class="acciones-celda">
                            <button type="button" class="btn-agenda-modificar" onclick="modificarCita({{ $cita['id'] }})">Reasignar</button>
                            <button type="button" class="btn-agenda-cancelar" onclick="cancelarCita({{ $cita['id'] }})">Cancelar</button>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="tabla-vacia">No hay citas registradas para este día.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </main>

</section>

@endsection

@push('scripts')
    @vite(['resources/js/agenda.js']) <!-- Aquí esstoy añadiendo el script para que se inyecte la lógica de js en el módulo de agenda -->@endpush