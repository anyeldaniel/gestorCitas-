@extends('layouts.app')

@section('title', 'Completar Pago - The Beauty Room')

@vite(['resources/css/pago-checkout.css'])

@section('content')
<main class="modulo-checkout">

    {{-- HEADER --}}
    <header class="checkout-header">
        <p class="subtitulo-seccion">Paso final</p>
        <h1>Completa tu pago</h1>
        <p class="descripcion">Reporta tu pago para asegurar tu cita. Nuestro equipo verificará la transacción y te confirmaremos por correo.</p>
    </header>

    <div class="checkout-grid">

        {{-- COLUMNA IZQUIERDA: RESUMEN + DATOS BANCARIOS --}}
        <aside class="checkout-resumen">
            <h2>Resumen de tu reserva</h2>

            <div class="resumen-lista">
                <div class="resumen-item">
                    <small>Servicio</small>
                    <strong>{{ $cita->servicio->nombre_servicio ?? 'Servicio Spa' }}</strong>
                </div>
                <div class="resumen-item">
                    <small>Fecha</small>
                    <strong>{{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }}</strong>
                </div>
                <div class="resumen-item">
                    <small>Hora</small>
                    <strong>{{ \Carbon\Carbon::parse($cita->hora)->format('g:i A') }}</strong>
                </div>
                <div class="resumen-item">
                    <small>Especialista</small>
                    <strong>{{ $cita->trabajador->nombre ?? 'Por asignar' }}</strong>
                </div>
            </div>

            <div class="resumen-total">
                <div class="total-linea">
                    <span>Precio del servicio</span>
                    <strong>${{ number_format($montoTotal, 2) }}</strong>
                </div>
                <div class="total-linea destacado">
                    <span>Abono a pagar ({{ $porcentaje }}%)</span>
                    <strong>${{ number_format($montoAbono, 2) }}</strong>
                </div>
            </div>

            {{-- DATOS BANCARIOS DEL SPA --}}
            <div class="datos-spa">
                <h3>Datos para tu pago</h3>
                <ul>
                    <li>
                        <span>Banco</span>
                        <strong>Banco Nacional de Crédito (BNC)</strong>
                    </li>
                    <li>
                        <span>Teléfono</span>
                        <strong>0412-1234567</strong>
                    </li>
                    <li>
                        <span>RIF</span>
                        <strong>J-12345678-9</strong>
                    </li>
                </ul>
                <p class="nota-bancaria">
                    ⓘ Realiza el pago móvil o transferencia a estos datos antes de continuar.
                </p>
            </div>
        </aside>

        {{-- COLUMNA DERECHA: FORMULARIO DE REPORTE --}}
        <section class="checkout-form-wrapper">
            <form action="{{ route('pago.procesar', ['cita' => $cita->id]) }}" method="POST" enctype="multipart/form-data" class="checkout-form">
                @csrf

                {{-- Método de pago --}}
                <fieldset class="campo-formulario">
                    <legend>1. Método de pago</legend>
                    <div class="metodos-pago">
                        <label class="metodo-opcion">
                            <input type="radio" name="metodo" value="pago_movil" required checked>
                            <span class="metodo-icono">📱</span>
                            <span class="metodo-texto">Pago Móvil</span>
                        </label>
                        <label class="metodo-opcion">
                            <input type="radio" name="metodo" value="transferencia" required>
                            <span class="metodo-icono">🏦</span>
                            <span class="metodo-texto">Transferencia</span>
                        </label>
                    </div>
                </fieldset>

                {{-- Datos de la transacción --}}
                <fieldset class="campo-formulario">
                    <legend>2. Datos de la transacción</legend>

                    <label>
                        <small>Referencia / Nº de operación *</small>
                        <input type="text" name="referencia" required maxlength="50"
                               placeholder="Ej. 894125"
                               value="{{ old('referencia') }}">
                    </label>

                    <div class="fila-doble">
                        <label>
                            <small>Banco emisor</small>
                            <input type="text" name="banco"
                                   placeholder="Ej. Banesco"
                                   value="{{ old('banco') }}">
                        </label>
                        <label>
                            <small>Teléfono emisor</small>
                            <input type="tel" name="telefono"
                                   placeholder="04141234567"
                                   value="{{ old('telefono') }}">
                        </label>
                    </div>

                    <label>
                        <small>Cédula del titular</small>
                        <input type="text" name="cedula"
                               placeholder="V-12345678"
                               value="{{ old('cedula') }}">
                    </label>
                </fieldset>

                {{-- Comprobante --}}
                <fieldset class="campo-formulario">
                    <legend>3. Comprobante (opcional)</legend>

                    <label class="input-archivo">
                        <input type="file" name="comprobante" accept="image/*,.pdf">
                        <span class="input-archivo-icono">📎</span>
                        <span class="input-archivo-texto">
                            Sube una foto o captura del comprobante<br>
                            <small>JPG, PNG, PDF — máx. 2MB</small>
                        </span>
                    </label>
                </fieldset>

                {{-- Errores --}}
                @if($errors->any())
                    <div class="alert-error">
                        <ul>
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                {{-- Acciones --}}
                <footer class="checkout-acciones">
                    <a href="{{ route('catalogo') }}" class="btn-secundario">Cancelar</a>
                    <button type="submit" class="btn-primario">
                        <span>Confirmar pago</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5" width="14" height="14">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6"/>
                        </svg>
                    </button>
                </footer>
            </form>
        </section>
    </div>
</main>
@endsection

@vite(['resources/js/pago-checkout.js'])