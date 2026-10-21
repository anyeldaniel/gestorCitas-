@extends('layouts.app')

@section('title', 'Finalizar Reserva - The Beauty Room')

@push('styles')
    @vite(['resources/css/pago-checkout.css'])
@endpush

@section('content')
<main class="checkout-wrapper">

    <!-- INDICADOR DE PASOS -->
    <nav class="checkout-steps" aria-label="Progreso de la reserva">
        <div class="step completed">
            <span class="step-icon">✓</span>
            <span class="step-label">Reserva</span>
        </div>
        <div class="step-line"></div>
        <div class="step active">
            <span class="step-icon">2</span>
            <span class="step-label">Pago</span>
        </div>
        <div class="step-line"></div>
        <div class="step">
            <span class="step-icon">3</span>
            <span class="step-label">Confirmación</span>
        </div>
    </nav>

    <!-- CABECERA -->
    <header class="checkout-header">
        <span class="hero-subtitle">Último paso</span>
        <h1>Finaliza tu reserva</h1>
        <p>Asegura tu momento de bienestar. Elige cómo prefieres realizar el pago.</p>
    </header>

    <form action="{{ route('checkout.procesar') }}" method="POST" id="form-pago">
        @csrf
        <input type="hidden" name="cita_id" value="{{ $cita->id }}">
        <input type="hidden" name="monto" id="input-monto" value="{{ $montoAbono }}">
        <input type="hidden" id="monto-abono-data" value="{{ $montoAbono }}">
        <input type="hidden" id="monto-total-data" value="{{ $montoTotal }}">

        <section class="checkout-card">
            
            <!-- COLUMNA IZQUIERDA: DETALLES Y FORMULARIO -->
            <section class="checkout-main">
                
                <header class="cita-info-header">
                    <span class="kicker">Tu cita</span>
                    <div class="cita-duracion-badge">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        60 min
                    </div>
                </header>
                <h2 class="service-title">{{ $nombreServicio }}</h2>
                
                <div class="service-meta-grid">
                    <article class="meta-item">
                        <div class="meta-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="5" width="18" height="16" rx="2"/><path d="M16 3v4M8 3v4M3 10h18"/></svg>
                        </div>
                        <div class="meta-text">
                            <small>Fecha y hora</small>
                            <strong>{{ $fecha->translatedFormat('d \d\e F') }} - {{ $hora }}</strong>
                        </div>
                    </article>
                    <article class="meta-item">
                        <div class="meta-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 21v-2a6 6 0 0 1 6-6h4a6 6 0 0 1 6 6v2"/></svg>
                        </div>
                        <div class="meta-text">
                            <small>Especialista</small>
                            <strong>{{ $cita->trabajador->nombre ?? 'Por asignar' }}</strong>
                        </div>
                    </article>
                </div>

                <!-- SELECCIÓN DE MÉTODO DE PAGO -->
                <section class="payment-methods-section">
                    <header class="section-header-inline">
                        <h3><span class="icon-circle">$</span> ¿Cómo deseas pagar?</h3>
                        <span class="helper-text">Elige una opción</span>
                    </header>
                    
                    <div class="payment-methods-grid">
                        <label class="payment-card active-card" id="card-c2p">
                            <input type="radio" name="metodo_pago" value="c2p" required checked>
                            <div class="card-content">
                                <div class="card-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                                </div>
                                <div class="card-text">
                                    <strong>Pago móvil</strong>
                                    <small>Confirmación manual</small>
                                </div>
                            </div>
                            <div class="card-radio"></div>
                        </label>

                        <label class="payment-card" id="card-transferencia">
                            <input type="radio" name="metodo_pago" value="transferencia" required>
                            <div class="card-content">
                                <div class="card-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M3 21h18M3 10h18M5 6l7-3 7 3M4 10v11M20 10v11"/></svg>
                                </div>
                                <div class="card-text">
                                    <strong>Transferencia</strong>
                                    <small>Cuenta bancaria</small>
                                </div>
                            </div>
                            <div class="card-radio"></div>
                        </label>

                        <label class="payment-card" id="card-efectivo">
                            <input type="radio" name="metodo_pago" value="divisas_efectivo" required>
                            <div class="card-content">
                                <div class="card-icon">
                                    <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 2v20M17 5H9.5a3.5 3.5 0 0 0 0 7h5a3.5 3.5 0 0 1 0 7H6"/></svg>
                                </div>
                                <div class="card-text">
                                    <strong>Efectivo</strong>
                                    <small>Pagar en el local</small>
                                </div>
                            </div>
                            <div class="card-radio"></div>
                        </label>
                    </div>

                    <!-- ALERTA PAGO MÓVIL -->
                    <div id="alerta-c2p" class="alert-box">
                        <div class="alert-icon">
                            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="5" y="2" width="14" height="20" rx="2"/><path d="M12 18h.01"/></svg>
                        </div>
                        <div class="alert-text">
                            <strong>Reporta tu pago móvil</strong>
                            <p>Realiza el pago a través del <strong>0416-000-0000</strong> y el <strong>V-12.345.678</strong> y completa los datos.</p>
                        </div>
                    </div>

                    <!-- CAMPOS PAGO MÓVIL -->
                    <div id="campos_c2p" class="form-fields-grid">
                        <div class="form-group">
                            <label for="banco">Banco emisor</label>
                            <select name="banco" id="banco" class="form-control">
                                <option value="0134">Banesco</option>
                                <option value="0102">Banco de Venezuela</option>
                                <option value="0105">Banco Mercantil</option>
                                <option value="0108">Banco Provincial</option>
                                <option value="0191">Banco Nacional de Crédito (BNC)</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono</label>
                            <input type="text" name="telefono" id="telefono" class="form-control" placeholder="0414-000-0000">
                        </div>
                        <div class="form-group">
                            <label for="token">Últimos 6 dígitos</label>
                            <input type="text" name="token" id="token" class="form-control" placeholder="000000" maxlength="6">
                        </div>
                    </div>

                    <!-- CAMPOS TRANSFERENCIA (OCULTOS) -->
                    <div id="campos_transferencia" class="form-fields-grid hidden">
                        <div class="form-group" style="grid-column: 1 / -1;">
                            <label for="referencia">Número de referencia</label>
                            <input type="text" name="referencia" id="referencia" class="form-control" placeholder="Últimos 6 dígitos de la transacción">
                        </div>
                    </div>
                </section>

                <footer class="checkout-security-footer">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg>
                    Tu información está protegida. Conexión segura para procesar tus datos.
                </footer>
            </section>

            <!-- COLUMNA DERECHA: RESUMEN -->
            <aside class="checkout-sidebar">
                
                <header class="sidebar-header">
                    <span class="sidebar-kicker">Resumen de pago</span>
                    <div class="sidebar-sello">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M12 6v6l4 2"/></svg>
                        USD
                    </div>
                </header>

                <div class="sidebar-resumen-servicio">
                    <h3>{{ $nombreServicio }}</h3>
                    <p>1 sesión · 60 minutos</p>
                    <div class="sidebar-precio-total">${{ number_format($montoTotal, 2) }}</div>
                </div>

                <section class="sidebar-section">
                    <span class="sidebar-label">Selecciona tu modalidad</span>
                    <div class="modalidad-toggle">
                        <button type="button" class="modalidad-btn seleccionado" id="btn-modalidad-abono">
                            <div class="modalidad-radio"></div>
                            <div class="modalidad-text">
                                <strong>Abono del {{ $porcentaje }}%</strong>
                                <small>Restante al dar</small>
                            </div>
                            <span class="modalidad-precio">${{ number_format($montoAbono, 2) }}</span>
                        </button>
                        
                        <button type="button" class="modalidad-btn" id="btn-modalidad-total">
                            <div class="modalidad-radio"></div>
                            <div class="modalidad-text">
                                <strong>Pago total</strong>
                                <small>Sin saldo pendiente</small>
                            </div>
                            <span class="modalidad-precio">${{ number_format($montoTotal, 2) }}</span>
                        </button>
                    </div>
                </section>

                <div class="a-pagar-box">
                    <span class="sidebar-label">A pagar ahora</span>
                    <div class="monto-display">
                        <span class="currency-symbol">$</span>
                        <h3 id="display-monto">{{ number_format($montoAbono, 2) }}</h3>
                    </div>
                    <p class="restante-text" id="restante-text">Restante en el local: ${{ number_format($montoRestante, 2) }}</p>
                </div>

                <label class="checkout-terms">
                    <input type="checkbox" required checked>
                    <span class="checkmark"></span>
                    <span class="terms-text">Acepto las <a href="#">políticas de reserva</a> y cancelación.</span>
                </label>

                <button type="submit" class="btn-pagar" id="btn-submit">
                    <span id="btn-text">Confirmar y pagar ${{ number_format($montoAbono, 2) }}</span>
                    <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </button>

                <footer class="sidebar-footer">
                    <span><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/></svg> Pago protegido</span>
                    <span><svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg> Datos cifrados</span>
                </footer>

            </aside>
        </section>
    </form>
</main>

<script>
    window.LaravelErrors = @json($errors->all());
</script>
@endsection

@push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    @vite(['resources/js/pago-checkout.js'])
@endpush