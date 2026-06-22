@extends('layouts.app')

@section('title', 'Agendar Cita de Bienestar')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo mb-8">
        <h1>Agendar una Sesión</h1>
        <p>Completa los datos requeridos para reservar tu espacio en nuestro santuario de bienestar.</p>
    </header>

    <div class="contenedor-formulario-spa">
        <form class="formulario-spa"
            id="form-reserva-spa"
            method="POST"
            action="{{ route('clientes.reserva.store') }}"
            enctype="multipart/form-data"
            autocomplete="off">
            @csrf

            <div class="grupo-campo">
                <label for="servicio_id">Tratamiento o Masaje Seleccionado *</label>
                <select name="servicio_id" id="servicio_id" required>
                    <option value="" disabled selected>Elija una opción...</option>
                    @foreach(\App\Models\Servicio::all() as $servicio)
                    <option value="{{ $servicio->id }}"
                        {{ (isset($servicioSeleccionado) && $servicioSeleccionado == $servicio->id) ? 'selected' : '' }}>
                        {{ $servicio->nombre_servicio }} — ${{ $servicio->precio }} ({{ $servicio->duracion_minutos }}min)
                    </option>
                    @endforeach
                </select>
            </div>

            <div class="grupo-campo">
                <label for="trabajador_id">¿Algún especialista en particular? (Opcional)</label>
                <select name="trabajador_id" id="trabajador_id">
                    <option value="aleatorio">Cualquiera disponible</option>
                    @foreach(\App\Models\User::where('rol', 'trabajador')->get() as $especialista)
                    <option value="{{ $especialista->id }}">{{ $especialista->nombre }}</option>
                    @endforeach
                </select>
            </div>

            <div class="grid-campos-dobles">
                <div class="grupo-campo">
                    <label for="fecha">Fecha de la Cita *</label>
                    <input type="date" name="fecha" id="fecha" required min="{{ date('Y-m-d') }}">
                </div>

                <div class="grupo-campo">
                    <label for="hora">Hora Estimada *</label>
                    <input type="time" name="hora" id="hora" required min="08:00" max="19:00">
                </div>
            </div>

            <div class="grupo-campo">
                <label for="adjunto_receta">
                    Receta Médica o Indicaciones
                    <span class="nota-opcional">(Opcional)</span>
                </label>
                <input type="file" name="adjunto_receta" id="adjunto_receta" accept="image/*,application/pdf">
            </div>

            <div id="contenedor-errores-js" class="alert-errores-spa" style="display: none;"></div>

            <footer class="pie-formulario">
                <button type="submit" class="btn-zen w-full">Confirmar y Agendar mi Cita</button>
            </footer>
        </form>
    </div>
</section>

<script>
    const baseUrl = "{{ url('/') }}";
</script>

<script src="{{ asset('js/reserva-spa.js') }}"></script>
@endsection