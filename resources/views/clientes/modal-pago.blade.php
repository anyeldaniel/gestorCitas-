<style>
    /* Ocultamos el modal por defecto y lo hacemos flotante */
    .modal-overlay {
        display: none; 
        position: fixed;
        z-index: 9999;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background-color: rgba(0, 0, 0, 0.6); /* Fondo oscuro semitransparente */
        display: flex; /* Usamos flex para centrar, pero lo ocultaremos con JS/inline si es necesario */
        align-items: center;
        justify-content: center;
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.3s ease;
    }

    /* Clase para cuando el modal esté activo (visible) */
    .modal-overlay.activo {
        opacity: 1;
        pointer-events: auto;
    }

    /* Diseño de la cajita blanca */
    .modal-content {
        background: #fff;
        padding: 2rem;
        border-radius: 8px;
        width: 90%;
        max-width: 500px;
        box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        position: relative;
    }

    .modal-close {
        position: absolute;
        top: 10px;
        right: 15px;
        font-size: 1.5rem;
        background: none;
        border: none;
        cursor: pointer;
    }
</style>

<aside id="modalPago" class="modal-overlay" aria-labelledby="titulo-pago" role="dialog" aria-modal="true">
    
    <div class="modal-content"> 
        
        <button type="button" class="modal-close" id="closePago" aria-label="Cerrar modal">&times;</button>
        
        <header>
            <h3 id="titulo-pago">Paso Final: Registrar Pago Móvil</h3>
            <p class="modal-subtitle">Efectúa el pago móvil a los datos del Spa y reporta el comprobante aquí:</p>
        </header>

        <article class="datos-bancarios-spa">
            <h4>Datos de Destino:</h4>
            <ul>
                <li><strong>Banco:</strong> Banco Nacional de Crédito (BNC)</li>
                <li><strong>Teléfono:</strong> 0412-1234567</li>
                <li><strong>RIF:</strong> J-12345678-9</li>
            </ul>
            <hr>
            <p class="monto-total"><strong>Monto Total a abonar:</strong> <span id="montoCalculado">$0.00</span></p>
        </article>

        <form id="formVerificarPago">
            <fieldset class="form-grupo">
                <label for="banco_origen">Banco desde el que realizó el pago:</label>
                <select name="banco_origen" id="banco_origen" class="form-control" required>
                    <option value="">Seleccione su banco...</option>
                    <option value="Banesco">Banesco</option>
                    <option value="Mercantil">Mercantil</option>
                    <option value="Provincial">Provincial</option>
                    <option value="Venezuela">Banco de Venezuela</option>
                    <option value="BNC">BNC</option>
                </select>
            </fieldset>

            <fieldset class="form-grupo">
                <label for="telefono_emisor">Número de teléfono emisor:</label>
                <input type="tel" id="telefono_emisor" name="telefono_emisor" placeholder="04121234567" class="form-control" required>
            </fieldset>

            <fieldset class="form-grupo">
                <label for="cedula_emisora">Número de Cédula del titular:</label>
                <input type="text" id="cedula_emisora" name="cedula_emisora" placeholder="V-12345678" class="form-control" required>
            </fieldset>

            <fieldset class="form-grupo">
                <label for="referencia">Número de Referencia (Últimos 6 dígitos):</label>
                <input type="text" id="referencia" name="referencia" maxlength="6" placeholder="123456" class="form-control" required>
            </fieldset>

            <button type="button" id="btnEnviarPago" class="btn-verificar">Confirmar Pago</button>
        </form>
    </div>
</aside>

<aside id="modalAnuncio" class="modal-overlay" aria-labelledby="titulo-anuncio" role="dialog" aria-modal="true">
    <article class="modal-content modal-anuncio-content">
        <header>
            <div class="icono-espera" aria-hidden="true"></div>
            <h2 id="titulo-anuncio">¡Solicitud Recibida!</h2>
            <p class="mensaje-alerta"><strong>Su cita está pendiente por verificar.</strong></p>
        </header>
        <main>
            <p>Nuestro equipo administrativo validará los datos de su pago móvil en los próximos minutos. Una vez aprobado, su cita quedará agendada formalmente.</p>
        </main>
        <footer>
            <button type="button" id="btnEntendido" class="btn-principal">Entendido</button>
        </footer>
    </article>
</aside>