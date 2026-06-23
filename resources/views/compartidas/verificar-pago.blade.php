@extends('layouts.app')

@push('styles')
    @vite(['resources/css/verificar-pago.css', 'resources/js/verificar-pago.js'])
@endpush

@section('content')
<main class="modulo-vista">
    
    <header class="encabezado-modulo mb-8">
        <h1>Verificar Pagos de Reservas</h1>
        <p>Listado de transacciones pendientes por confirmar en el banco externo.</p>
    </header>

    <form class="formulario-filtro" action="" method="GET">
        <label for="fecha_filtro">Consultar por Fecha:</label>
        <input type="date" id="fecha_filtro" name="fecha_filtro">
        <button type="submit" class="btn-buscar">Buscar</button>
    </form>

    <table class="tabla-transacciones">
        <thead>
            <tr>
                <th>Fecha / Hora</th>
                <th>Cliente</th>
                <th>Banco Origen</th>
                <th>Teléfono Emisor</th>
                <th>Cédula</th>
                <th>Referencia</th>
                <th>Monto</th>
                <th>Estado</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td>22/06/2026 03:45 PM</td>
                <td>Eilyn Martinez</td>
                <td>Banesco</td>
                <td>04141234567</td>
                <td>V-25666777</td>
                <td>894125</td>
                <td>$15.00</td>
                <td><span class="estado-pendiente">Pendiente</span></td>
                <td>
                    <button type="button" class="btn-autorizar">Autorizar</button>
                    <button type="button" class="btn-rechazar">Rechazar</button>
                </td>
            </tr>
        </tbody>
    </table>

</main>
<dialog id="modal-confirmacion" class="modal-pago">
        <form method="dialog" class="contenido-modal">
            <header class="modal-header">
                <h2 id="modal-titulo">Confirmar Acción</h2>
            </header>
            
            <section class="modal-body">
                <p id="modal-mensaje">¿Estás seguro de que deseas procesar esta transacción?</p>
                <textarea id="motivo-rechazo" placeholder="Escribe el motivo del rechazo aquí..." rows="3" class="hidden"></textarea>
            </section>
            
            <footer class="modal-footer">
                <button type="button" id="btn-modal-cancelar" class="btn-cancelar">Cancelar</button>
                <button type="button" id="btn-modal-confirmar" class="btn-confirmar">Confirmar</button>
            </footer>
        </form>
    </dialog>
@endsection