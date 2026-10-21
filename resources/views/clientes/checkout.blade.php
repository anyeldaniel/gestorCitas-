<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Procesar Pago - The Beauty Room</title>
    <link href="https://fonts.googleapis.com/css2?family=Montserrat:wght@300;400;500;600&family=Playfair+Display:ital,wght@0,500;0,600;1,500&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body { font-family: 'Montserrat', sans-serif; background-color: #F4F1EB; margin: 0; min-height: 100vh; display: flex; justify-content: center; align-items: center; padding: 20px; }
        .checkout-container { background-color: #FFFFFF; width: 100%; max-width: 520px; border-radius: 16px; box-shadow: 0 15px 35px rgba(44, 53, 45, 0.08); padding: 40px; }
        h2 { font-family: 'Playfair Display', serif; color: #2C352D; text-align: center; margin-top: 0; font-size: 28px; }
        .resumen { background-color: #FAFAFA; border: 1px solid #E5E2DC; padding: 20px; border-radius: 12px; margin-bottom: 25px; text-align: center; }
        .resumen p { margin: 5px 0; color: #5C665E; font-size: 14px; }
        .monto-total { font-size: 24px !important; font-weight: 600; color: #2C352D !important; margin-top: 10px !important; }
        
        .opciones-pago { display: flex; gap: 10px; margin-bottom: 20px; }
        .opcion-card { flex: 1; border: 1px solid #DCD7CE; padding: 12px; border-radius: 8px; text-align: center; cursor: pointer; font-size: 13px; background: #FCFBF9; transition: all 0.2s; }
        .opcion-card input { display: none; }
        .opcion-card.seleccionado { border-color: #5A6B5D; background: #F0F4F1; font-weight: 600; color: #2C352D; }

        label { display: block; font-size: 12px; font-weight: 600; color: #5C665E; margin-bottom: 8px; text-transform: uppercase; letter-spacing: 1px; }
        select, input { width: 100%; padding: 12px 15px; margin-bottom: 20px; border: 1px solid #DCD7CE; border-radius: 8px; font-family: 'Montserrat', sans-serif; font-size: 14px; background-color: #FCFBF9; box-sizing: border-box; }
        select:focus, input:focus { outline: none; border-color: #839788; background-color: #FFFFFF; }
        
        .btn-pagar { width: 100%; padding: 15px; background: linear-gradient(135deg, #5A6B5D, #2C352D); color: #FFFFFF; border: none; border-radius: 8px; font-weight: 600; font-size: 14px; cursor: pointer; transition: all 0.3s ease; text-transform: uppercase; letter-spacing: 1px; }
        .btn-pagar:hover { background: linear-gradient(135deg, #6D8071, #3A463B); transform: translateY(-2px); box-shadow: 0 8px 20px rgba(44, 53, 45, 0.2); }
    </style>
</head>
<body>

    @if($errors->any())
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            Swal.fire({
                icon: 'error',
                title: 'Transacción rechazada',
                text: @json($errors->first()),
                confirmButtonColor: '#2C352D'
            });
        });
    </script>
    @endif

    <div class="checkout-container">
        <h2>Finaliza tu Reserva</h2>
        
        @php
            // Calculamos el porcentaje de abono requerido (ej. 50% por defecto si el servicio no especifica otro)
            $porcentaje = $cita->servicio->porcentaje_abono ?? 50;
            $montoTotal = $cita->monto_total;
            $montoAbono = round(($montoTotal * $porcentaje) / 100, 2);
        @endphp

        <div class="resumen">
            <p><strong>Cita #{{ $cita->id }} — {{ $cita->servicio->nombre_servicio ?? 'Servicio Spa' }}</strong></p>
            <p>Fecha: {{ \Carbon\Carbon::parse($cita->fecha)->format('d/m/Y') }} a las {{ \Carbon\Carbon::parse($cita->hora)->format('h:i A') }}</p>
            <p>Costo Total del Servicio: ${{ number_format($montoTotal, 2) }}</p>
            <p class="monto-total" id="etiqueta-monto">A pagar ahora: ${{ number_format($montoAbono, 2) }}</p>
        </div>

        <form action="{{ route('checkout.procesar') }}" method="POST" id="form-pago">
            @csrf
            <input type="hidden" name="cita_id" value="{{ $cita->id }}">
            <!-- Input oculto que enviará el monto real que el cliente decidió pagar -->
            <input type="hidden" name="monto" id="input-monto" value="{{ $montoAbono }}">

            <!-- SELECCIÓN DE TIPO DE PAGO (ABONO VS TOTAL) -->
            <label>Modalidad de Pago</label>
            <div class="opciones-pago">
                <label class="opcion-card seleccionado" id="card-abono" onclick="seleccionarTipoPago('abono')">
                    <input type="radio" name="tipo_pago" value="abono" checked>
                    <strong>Abono del {{ $porcentaje }}%</strong><br>
                    <span>${{ number_format($montoAbono, 2) }}</span>
                </label>
                <label class="opcion-card" id="card-total" onclick="seleccionarTipoPago('total')">
                    <input type="radio" name="tipo_pago" value="total">
                    <strong>Pago Completo</strong><br>
                    <span>${{ number_format($montoTotal, 2) }}</span>
                </label>
            </div>

            <label for="metodo_pago">Método de Pago</label>
            <select name="metodo_pago" id="metodo_pago" onchange="toggleCampos()" required>
                <option value="">Seleccione una opción...</option>
                <option value="c2p">Pago Móvil (C2P - Automático)</option>
                <option value="transferencia">Transferencia / Zelle (Manual)</option>
                <option value="divisas_efectivo">Efectivo en el Local</option>
            </select>

            <!-- CAMPOS PARA PAGO MÓVIL (CON TODOS LOS BANCOS DE VENEZUELA) -->
            <div id="campos_c2p" style="display: none;">
                <label for="banco">Banco de Origen</label>
                <select name="banco" id="banco">
                    <option value="0102">0102 - Banco de Venezuela</option>
                    <option value="0134">0134 - Banesco</option>
                    <option value="0105">0105 - Banco Mercantil</option>
                    <option value="0108">0108 - Banco Provincial</option>
                    <option value="0191">0191 - Banco Nacional de Crédito (BNC)</option>
                    <option value="0114">0114 - Bancaribe</option>
                    <option value="0151">0151 - Banco Fondo Común (BFC)</option>
                    <option value="0163">0163 - Banco del Tesoro</option>
                    <option value="0175">0175 - Banco Bicentenario</option>
                    <option value="0128">0128 - Banco Caroní</option>
                    <option value="0138">0138 - Banco Plaza</option>
                    <option value="0104">0104 - Venezolano de Crédito</option>
                    <option value="0157">0157 - Del Sur Banco Universal</option>
                    <option value="0168">0168 - Bancrecer</option>
                    <option value="0177">0177 - Banco de la Fuerza Armada Nacional (Banfanb)</option>
                    <option value="0174">0174 - Banplus</option>
                </select>

                <label for="telefono">Teléfono Afiliado</label>
                <input type="text" name="telefono" id="telefono" placeholder="Ej: 04141234567">

                <label for="token">Clave de Pago (Token OTP)</label>
                <input type="text" name="token" id="token" placeholder="Ej: 123456 (Simulador aprueba con 123456)" maxlength="6">
            </div>

            <!-- CAMPOS PARA TRANSFERENCIA -->
            <div id="campos_transferencia" style="display: none;">
                <label for="referencia">Número de Referencia</label>
                <input type="text" name="referencia" id="referencia" placeholder="Últimos 6 dígitos de la transacción">
            </div>

            <button type="submit" class="btn-pagar" id="btn-submit">Procesar Pago</button>
        </form>
    </div>

    <script>
        const montoAbonoVal = {{ $montoAbono }};
        const montoTotalVal = {{ $montoTotal }};

        function seleccionarTipoPago(tipo) {
            const cardAbono = document.getElementById('card-abono');
            const cardTotal = document.getElementById('card-total');
            const inputMonto = document.getElementById('input-monto');
            const etiquetaMonto = document.getElementById('etiqueta-monto');

            if (tipo === 'abono') {
                cardAbono.classList.add('seleccionado');
                cardTotal.classList.remove('seleccionado');
                cardAbono.querySelector('input').checked = true;
                inputMonto.value = montoAbonoVal;
                etiquetaMonto.innerText = 'A pagar ahora: $' + montoAbonoVal.toFixed(2);
            } else {
                cardTotal.classList.add('seleccionado');
                cardAbono.classList.remove('seleccionado');
                cardTotal.querySelector('input').checked = true;
                inputMonto.value = montoTotalVal;
                etiquetaMonto.innerText = 'A pagar ahora: $' + montoTotalVal.toFixed(2);
            }
        }

        function toggleCampos() {
            const metodo = document.getElementById('metodo_pago').value;
            const camposC2P = document.getElementById('campos_c2p');
            const camposTransferencia = document.getElementById('campos_transferencia');
            const btnSubmit = document.getElementById('btn-submit');

            camposC2P.style.display = 'none';
            camposTransferencia.style.display = 'none';

            if (metodo === 'c2p') {
                camposC2P.style.display = 'block';
                btnSubmit.innerText = 'Pagar y Confirmar Cita';
            } else if (metodo === 'transferencia') {
                camposTransferencia.style.display = 'block';
                btnSubmit.innerText = 'Enviar Reporte de Pago';
            } else if (metodo === 'divisas_efectivo') {
                btnSubmit.innerText = 'Agendar y Pagar en Local';
            } else {
                btnSubmit.innerText = 'Procesar Pago';
            }
        }

        document.getElementById('form-pago').addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Procesando transacción',
                html: 'Conectando con la red bancaria nacional...<br><br><span style="color: #727A74; font-size: 13px;">Por favor, espere un momento.</span>',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
            setTimeout(() => { this.submit(); }, 2500);
        });
    </script>
</body>
</html>