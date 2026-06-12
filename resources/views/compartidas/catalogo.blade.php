@extends('layouts.app')

@section('title', 'Catálogo de Bienestar Premium')

@section('content')
<section class="modulo-vista">
    <header class="encabezado-modulo mb-8">
        <h1>Nuestras Terapias y Experiencias</h1>
        <p>Selecciona el tratamiento ideal para restaurar tu equilibrio corporal y estético.</p>
    </header>

    @if(session('success'))
        <div class="alert-success-spa">
            <span class="icono-zen">✨</span> {{ session('success') }}
        </div>
    @endif

    <section class="rejilla-catalogo" id="catalogo-tratamientos">
        
        @php
            $directorio = public_path('asset/img/parts');
            
            $imagenes = \Illuminate\Support\Facades\File::exists($directorio) 
                ? \Illuminate\Support\Facades\File::files($directorio) 
                : [];
        @endphp

        @forelse($imagenes as $imagen)
            <article class="tarjeta-componente">
                <img src="{{ asset('asset/img/parts/' . $imagen->getFilename()) }}" 
                     alt="{{ pathinfo($imagen->getFilename(), PATHINFO_FILENAME) }}" 
                     class="img-portada">
                
                <header>
                    <h3 class="text-lg font-semibold capitalize">
                        {{ str_replace('-', ' ', pathinfo($imagen->getFilename(), PATHINFO_FILENAME)) }}
                    </h3>
                    <p class="text-sm text-gray-500">Tratamiento exclusivo de nuestro spa.</p>
                </header>
                
                <footer class="flex justify-between items-center mt-2">
                    <span class="precio-tag">Consultar</span>
                    <a href="{{ route('clientes.reserva') }}" class="btn-zen">Reservar</a>
                </footer>
            </article>
        @empty
            <p>No se encontraron imágenes en la carpeta de servicios. Por favor, verifica la ruta en tu proyecto local.</p>
        @endforelse

    </section>
</section>
@endsection