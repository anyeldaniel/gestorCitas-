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
              action="/api/cliente/reservas" 
              enctype="multipart/form-data" 
              autocomplete="off">
            @csrf
            
            <div class="grupo-campo">
                <label for="servicio_id">Tratamiento o Masaje Seleccionado *</label>
                <select name="servicio_id" id="servicio_id" required>
                    <option value="" disabled selected>Elija una opción...</option>
                    
                    <optgroup label="1. Masajes Corporales">
                        <option value="1">Masaje Relajante Anti-estrés — $35 (90min)</option>
                        <option value="2">Masaje Descontracturante Profundo — $45 (60min)</option>
                        <option value="3">Masaje con Piedras Volcánicas — $55 (90min)</option>
                        <option value="4">Masaje Circulatorio / Drenaje Linfático — $40 (60min)</option>
                        <option value="5">Masaje Aromaterapéutico — $40 (60min)</option>
                    </optgroup>
                    
                    <optgroup label="2. Cuidado Facial">
                        <option value="6">Limpieza Facial Profunda — $30 (90min)</option>
                        <option value="7">Hidratación y Nutrición Intensiva — $35 (50min)</option>
                        <option value="8">Facial Anti-age con Colágeno — $45 (90min)</option>
                        <option value="9">Peeling Químico / Renovación — $50 (100min)</option>
                    </optgroup>
                    
                    <optgroup label="3. Terapia con Plasma">
                        <option value="10">Plasma Rico en Plaquetas (PRP) Facial — $70 (60min)</option>
                        <option value="11">PRP Capilar Anticaída — $80 (60min)</option>
                    </optgroup>
                    
                    <optgroup label="4. Estética de Manos y Pies">
                        <option value="12">Manicura Tradicional Spa — $12 (40min)</option>
                        <option value="13">Manicura Semi-permanente — $18 (50min)</option>
                        <option value="14">Pedicura Spa Relajante — $22 (75min)</option>
                        <option value="15">Uñas Esculpidas (Gel / Acrílico) — $35 (150min)</option>
                        <option value="16">Tratamiento de Parafina Profunda — $10 (30min)</option>
                    </optgroup>
                    
                    <optgroup label="5. Experiencias Premium">
                        <option value="17">Ritual Supremo 'Beauty & Luxury' — $140 (180min)</option>
                    </optgroup>
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

            <footer class="pie-formulario">
                <button type="submit" class="btn-zen w-full">Confirmar y Agendar mi Cita</button>
            </footer>
        </form>
    </div>
</section>
@endsection