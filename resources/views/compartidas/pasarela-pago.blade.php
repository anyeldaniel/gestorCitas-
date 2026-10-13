@extends('layouts.app')

@section('title', 'Pasarela de Pago - The Beauty Room')

@push('styles')
    @vite(['resources/css/pasarela-pago.css'])
@endpush

@section('content')
<main class="modulo-pasarela">

    {{-- ============================================================
         HEADER DEL MÓDULO
         ============================================================ --}}
    <header class="pasarela-header">
        <div class="pasarela-header__left">
            <p class="subtitulo-seccion">Pago seguro</p>
            <h1>Completa tu reserva</h1>
            <p>Realiza tu pago por Pago Móvil o Transferencia y reporta los datos para confirmar tu cita.</p>
        </div>

        <aside class="badge-seguridad">
            <div class="badge-seguridad__icono">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M16.5 10.5V6.75a4.5 4.5 0 1 0-9 0v3.75m-.75 11.25h10.5a2.25 2.25 0 0 0 2.25-2.25v-6.75a2.25 2.25 0 0 0-2.25-2.25H6.75a2.25 2.25 0 0 0-2.25 2.25v6.75a2.25 2.25 0 0 0 2.25 2.25Z" />
                </svg>
            </div>
            <div class="badge-seguridad__texto">
                <small>Proceso verificado</small>
                <strong>Datos protegidos</strong>
            </div>
        </aside>
    </header>

    {{-- ============================================================
         LAYOUT PRINCIPAL: RESUMEN + FORMULARIO
         ============================================================ --}}
    <section class="pasarela-layout">

        {{-- ---------- COLUMNA IZQUIERDA: RESUMEN DE LA CITA ---------- --}}
        <aside class="pasarela-resumen">

            <div class="resumen-card">
                <span class="kicker">Resumen de tu cita</span>
                <h2>Servicio reservado</h2>

                <ul class="resumen-lista">
                    <li>
                        <span>Servicio</span>
                        <strong>{{ $cita->servicio ?? 'Masaje Relajante' }}</strong>
                    </li>
                    <li>
                        <span>Especialista</span>
                        <strong>{{ $cita->terapeuta ?? 'Por asignar' }}</strong>
                    </li>
                    <li>
                        <span>Fecha</span>
                        <strong>{{ $cita->fecha ?? now()->format('d/m/Y') }}</strong>
                    </li>
                    <li>
                        <span>Hora</span>
                        <strong>{{ $cita->hora ?? '3:45 PM' }}</strong>
                    </li>
                    <li>
                        <span>Duración</span>
                        <strong>{{ $cita->duracion ?? '60 min' }}</strong>
                    </li>
                </ul>

                <div class="resumen-total">
                    <span>Total a pagar</span>
                    <strong>${{ number_format($cita->monto ?? 45.00, 2) }}</strong>
                </div>

                <p class="resumen-nota">
                    Una vez reportado el pago, tu cita quedará <strong>pendiente de verificación</strong>.
                </p>
            </div>

            <div class="info-bancaria">
                <span class="kicker">Datos para el pago</span>
                <h3>Realiza tu pago aquí</h3>

                <div class="metodo-pago">
                    <div class="metodo-pago__icono">📱</div>
                    <div class="metodo-pago__info">
                        <strong>Pago Móvil</strong>
                        <span>Banco: Banesco (0134)</span>
                        <span>Teléfono: <code>0414-2578005</code></span>
                        <span>Cédula: <code>V-12.345.678</code></span>
                    </div>
                </div>

                <div class="metodo-pago">
                    <div class="metodo-pago__icono">🏦</div>
                    <div class="metodo-pago__info">
                        <strong>Transferencia</strong>
                        <span>Banco: Mercantil (0105)</span>
                        <span>Cuenta: <code>0105-0000-00-0000000000</code></span>
                        <span>Titular: The Beauty Room</span>
                    </div>
                </div>

                <div class="metodo-pago">
                    <div class="metodo-pago__icono">💵</div>
                    <div class="metodo-pago__info">
                        <strong>Zelle</strong>
                        <span>Correo: <code>pagos@thebeautyroom.mx</code></span>
                        <span>Titular: The Beauty Room</span>
                    </div>
                </div>

                <div class="info-bancaria__aviso">
                    <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M11.25 11.25l.041-.02a.75.75 0 0 1 1.063.852l-.708 2.836a.75.75 0 0 0 1.063.853l.041-.021M21 12a9 9 0 1 1-18 0 9 9 0 0 1 18 0Zm-9-3.75h.008v.008H12V8.25Z" />
                    </svg>
                    <p>Guarda tu número de referencia. Lo necesitarás para reportar el pago.</p>
                </div>
            </div>

        </aside>

        {{-- ---------- COLUMNA DERECHA: FORMULARIO ---------- --}}
        <section class="pasarela-formulario">
            <div class="form-card">
                <span class="kicker">Reportar pago</span>
                <h2>Completa los datos de tu transacción</h2>
                <p class="form-descripcion">
                    Llena el formulario con los datos exactos del pago que realizaste. Verificaremos
                    y confirmaremos tu cita en menos de 24 horas.
                </p>

                <form id="form-pago" method="POST" action="{{ route('pasarela.store') }}" enctype="multipart/form-data" novalidate>
                    @csrf

                    {{-- Método de pago --}}
                    <div class="campo-grupo">
                        <label class="campo-label">Método de pago <span class="req">*</span></label>
                        <div class="metodos-radio">
                            <label class="metodo-opcion">
                                <input type="radio" name="metodo" value="pago_movil" required>
                                <span class="metodo-opcion__box">
                                    <span class="metodo-opcion__emoji">📱</span>
                                    <span class="metodo-opcion__texto">Pago Móvil</span>
                                </span>
                            </label>
                            <label class="metodo-opcion">
                                <input type="radio" name="metodo" value="transferencia" required>
                                <span class="metodo-opcion__box">
                                    <span class="metodo-opcion__emoji">🏦</span>
                                    <span class="metodo-opcion__texto">Transferencia</span>
                                </span>
                            </label>
                            <label class="metodo-opcion">
                                <input type="radio" name="metodo" value="zelle" required>
                                <span class="metodo-opcion__box">
                                    <span class="metodo-opcion__emoji">💵</span>
                                    <span class="metodo-opcion__texto">Zelle</span>
                                </span>
                            </label>
                        </div>
                    </div>

                    {{-- Fila: Banco emisor + Cédula --}}
                    <div class="fila-campos">
                        <div class="campo-grupo">
                            <label class="campo-label" for="banco">Banco emisor <span class="req">*</span></label>
                            <select id="banco" name="banco" class="campo-input" required>
                                <option value="">Selecciona tu banco</option>
                                <option value="banesco">Banesco</option>
                                <option value="mercantil">Mercantil</option>
                                <option value="venezuela">Banco de Venezuela</option>
                                <option value="provincial">BBVA Provincial</option>
                                <option value="bnc">BNC</option>
                                <option value="bicentenario">Bicentenario</option>
                                <option value="otros">Otro</option>
                            </select>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label" for="cedula">Cédula del titular <span class="req">*</span></label>
                            <input type="text" id="cedula" name="cedula" class="campo-input" placeholder="V-12.345.678" required>
                        </div>
                    </div>

                    {{-- Fila: Teléfono + Referencia --}}
                    <div class="fila-campos">
                        <div class="campo-grupo">
                            <label class="campo-label" for="telefono">Teléfono emisor <span class="req">*</span></label>
                            <input type="tel" id="telefono" name="telefono" class="campo-input" placeholder="0414-1234567" required>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label" for="referencia">Número de referencia <span class="req">*</span></label>
                            <input type="text" id="referencia" name="referencia" class="campo-input" placeholder="Ej: 894125" maxlength="20" required>
                        </div>
                    </div>

                    {{-- Fila: Monto + Fecha --}}
                    <div class="fila-campos">
                        <div class="campo-grupo">
                            <label class="campo-label" for="monto">Monto pagado <span class="req">*</span></label>
                            <input type="number" id="monto" name="monto" class="campo-input" placeholder="45.00" step="0.01" min="0.01" required>
                        </div>
                        <div class="campo-grupo">
                            <label class="campo-label" for="fecha_pago">Fecha del pago <span class="req">*</span></label>
                            <input type="date" id="fecha_pago" name="fecha_pago" class="campo-input" value="{{ date('Y-m-d') }}" required>
                        </div>
                    </div>

                    {{-- Notas --}}
                    <div class="campo-grupo">
                        <label class="campo-label" for="notas">Notas adicionales (opcional)</label>
                        <textarea id="notas" name="notas" class="campo-input" rows="3" placeholder="Ej: Pagué desde la cuenta de mi madre, María Pérez."></textarea>
                    </div>

                    {{-- Comprobante --}}
                    <div class="campo-grupo">
                        <label class="campo-label">Comprobante (opcional)</label>
                        <label class="zona-archivo" for="comprobante">
                            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M12 16.5V9.75m0 0 3 3m-3-3-3 3M6.75 19.5a4.5 4.5 0 0 1-1.41-8.775 5.25 5.25 0 0 1 10.233-2.33 3 3 0 0 1 3.758 3.848A3.752 3.752 0 0 1 18 19.5H6.75Z" />
                            </svg>
                            <span id="archivo-nombre">Arrastra tu imagen o haz clic para subir</span>
                            <input type="file" id="comprobante" name="comprobante" accept="image/*,.pdf" hidden>
                        </label>
                        <small class="campo-ayuda">Formatos aceptados: JPG, PNG, PDF. Máx 5MB.</small>
                    </div>

                    {{-- Checkbox confirmación --}}
                    <label class="check-confirmacion">
                        <input type="checkbox" name="confirmacion" required>
                        <span>Confirmo que los datos ingresados son verídicos y corresponden al pago realizado.</span>
                    </label>

                    {{-- Botón de envío --}}
                    <button type="submit" class="btn-enviar-pago" id="btn-enviar-pago">
                        <span>Reportar pago</span>
                        <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2.5">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M5 12h14M13 6l6 6-6 6" />
                        </svg>
                    </button>
                </form>
            </div>
        </section>

    </section>

</main>

{{-- ============================================================
     MODAL DE CONFIRMACIÓN DE ENVÍO
     ============================================================ --}}
<dialog id="modal-confirmar-pago" class="modal-pago">
    <article class="modal-pago__cuerpo">
        <div class="modal-pago__icono">✅</div>
        <h3 class="modal-pago__titulo">¡Pago reportado!</h3>
        <p class="modal-pago__mensaje">
            Hemos recibido los datos de tu pago. Nuestro equipo lo verificará en las próximas horas
            y recibirás una confirmación por correo.
        </p>
        <div class="modal-pago__pie">
            <a href="{{ route('agenda') }}" class="btn-modal-pago">Volver a la agenda</a>
        </div>
    </article>
</dialog>

@endsection

@push('scripts')
    @vite(['resources/js/pasarela-pago.js'])
@endpush