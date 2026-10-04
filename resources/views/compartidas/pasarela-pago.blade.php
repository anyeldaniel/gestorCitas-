@extends('layouts.app')

@push('styles')
    @vite(['resources/css/pasarela-pago.css'])
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
@endpush

@push('scripts')
    @vite(['resources/js/pasarela-pago.js'])
@endpush

@section('content')
<main class="pasarela-container">
    <div class="pasarela-wrapper">
        <!-- Header del Proceso -->
        <div class="proceso-header">
            <h1 class="titulo-spa">SERENITAS SPA</h1>
            <h2 class="subtitulo-proceso">Finalizar reserva</h2>
            
            <!-- Pasos -->
            <div class="pasos-progreso">
                <div class="paso-item activo">
                    <span class="paso-numero">1</span>
                    <span class="paso-label">TUS DATOS</span>
                </div>
                <div class="paso-linea"></div>
                <div class="paso-item activo">
                    <span class="paso-numero">2</span>
                    <span class="paso-label">PAGO</span>
                </div>
                <div class="paso-linea"></div>
                <div class="paso-item">
                    <span class="paso-numero">3</span>
                    <span class="paso-label">CONFIRMADO</span>
                </div>
            </div>
        </div>

        <div class="contenido-pasarela">
            <!-- Columna Izquierda - Formulario -->
            <div class="columna-formulario">
                <!-- Información de Contacto -->
                <div class="seccion-contacto">
                    <h3 class="seccion-titulo">Información de contacto</h3>
                    
                    <div class="campo-grupo">
                        <label class="campo-label">NOMBRE COMPLETO</label>
                        <input type="text" class="campo-input" id="nombre-titular" 
                               value="{{ auth()->user()->name ?? 'Ana García López' }}" 
                               placeholder="Nombre completo">
                    </div>
                    
                    <div class="campo-grupo">
                        <label class="campo-label">CORREO ELECTRÓNICO</label>
                        <input type="email" class="campo-input" id="email-confirmacion" 
                               value="{{ auth()->user()->email ?? 'ana@ejemplo.com' }}" 
                               placeholder="correo@ejemplo.com">
                    </div>
                    
                    <div class="campo-grupo">
                        <label class="campo-label">TELÉFONO (opcional)</label>
                        <input type="tel" class="campo-input" id="telefono" 
                               placeholder="+34 600 000 000">
                    </div>
                </div>

                <!-- Método de Pago -->
                <div class="seccion-pago">
                    <h3 class="seccion-titulo">Método de pago</h3>
                    
                    <div class="metodos-grid">
                        <div class="metodo-item">
                            <input type="radio" name="metodo_pago" value="visa" id="visa" checked>
                            <label for="visa">
                                <i class="fab fa-cc-visa"></i>
                                <span>Visa</span>
                            </label>
                        </div>
                        <div class="metodo-item">
                            <input type="radio" name="metodo_pago" value="mastercard" id="mastercard">
                            <label for="mastercard">
                                <i class="fab fa-cc-mastercard"></i>
                                <span>MasterCard</span>
                            </label>
                        </div>
                        <div class="metodo-item">
                            <input type="radio" name="metodo_pago" value="paypal" id="paypal">
                            <label for="paypal">
                                <i class="fab fa-paypal"></i>
                                <span>PayPal</span>
                            </label>
                        </div>
                    </div>

                    <!-- Datos de Tarjeta -->
                    <div class="datos-tarjeta">
                        <div class="campo-grupo">
                            <label class="campo-label">NÚMERO DE TARJETA</label>
                            <div class="input-con-icono">
                                <i class="fas fa-credit-card"></i>
                                <input type="text" class="campo-input" id="numero-tarjeta" 
                                       placeholder="1234 5678 9012 3456" maxlength="19">
                            </div>
                        </div>

                        <div class="campo-grupo">
                            <label class="campo-label">FECHA DE EXPIRACIÓN</label>
                            <input type="text" class="campo-input" id="fecha-expiracion" 
                                   placeholder="MM/AA" maxlength="5">
                        </div>

                        <div class="campo-grupo">
                            <label class="campo-label">CVV</label>
                            <input type="password" class="campo-input" id="cvv" 
                                   placeholder="123" maxlength="4">
                        </div>
                    </div>
                </div>

                <!-- Botón Continuar -->
                <button type="submit" class="btn-continuar" id="btn-pagar">
                    <span>CONTINUAR AL PAGO</span>
                    <i class="fas fa-arrow-right"></i>
                </button>
                
                <div class="seguridad-badge">
                    <i class="fas fa-lock"></i>
                    <span>Pago 100% seguro</span>
                </div>
            </div>

            <!-- Columna Derecha - Resumen -->
            <div class="columna-resumen">
                <div class="resumen-card">
                    <h3 class="resumen-titulo">RESUMEN DEL PEDIDO</h3>
                    
                    <div class="resumen-items">
                        <div class="resumen-item">
                            <span class="item-nombre">Ritual de Piedras Calientes</span>
                            <span class="item-precio">€185</span>
                        </div>
                        <div class="resumen-item">
                            <span class="item-nombre">Facial Hidratante Premium</span>
                            <span class="item-precio">€165</span>
                        </div>
                    </div>

                    <div class="resumen-totales">
                        <div class="total-linea">
                            <span>Subtotal</span>
                            <span>€350</span>
                        </div>
                        <div class="total-linea descuento">
                            <span>Descuento</span>
                            <span>-€25</span>
                        </div>
                        <div class="total-linea">
                            <span>IVA (21%)</span>
                            <span>€68.25</span>
                        </div>
                    </div>

                    <div class="total-final">
                        <span class="total-label">Total</span>
                        <span class="total-monto" id="monto-total">€393.25</span>
                    </div>

                    <!-- Badge de Seguridad -->
                    <div class="resumen-seguridad">
                        <i class="fas fa-shield-alt"></i>
                        <span>Transacción segura</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal de Confirmación -->
    <dialog id="modal-confirmacion" class="modal-pasarela">
        <div class="modal-contenido">
            <div class="modal-icono">
                <i class="fas fa-check-circle"></i>
            </div>
            <h2>¡Pago Exitoso!</h2>
            <p>Tu transacción ha sido procesada correctamente.</p>
            <div class="modal-detalles" id="modal-detalles"></div>
            <div class="modal-acciones">
                <button class="btn-modal" id="btn-modal-cerrar">
                    <i class="fas fa-print"></i>
                    Imprimir Comprobante
                </button>
                <button class="btn-modal-secundario" id="btn-modal-continuar">
                    Continuar
                </button>
            </div>
        </div>
    </dialog>

    <!-- Loader -->
    <div id="loader-pago" class="loader-overlay hidden">
        <div class="loader-spinner">
            <div class="spinner"></div>
            <p>Procesando tu pago...</p>
            <p class="loader-subtext">Por favor, no cierres esta ventana</p>
        </div>
    </div>
</main>
@endsection