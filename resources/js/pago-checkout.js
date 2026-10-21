document.addEventListener('DOMContentLoaded', () => {

    // Errores de Laravel
    if (window.LaravelErrors && window.LaravelErrors.length > 0) {
        Swal.fire({
            icon: 'error',
            title: 'Transacción rechazada',
            text: window.LaravelErrors[0],
            confirmButtonColor: '#3b2a20'
        });
    }

    const montoAbonoVal = parseFloat(document.getElementById('monto-abono-data').value);
    const montoTotalVal = parseFloat(document.getElementById('monto-total-data').value);

    const btnAbono = document.getElementById('btn-modalidad-abono');
    const btnTotal = document.getElementById('btn-modalidad-total');
    const inputMonto = document.getElementById('input-monto');
    const displayMonto = document.getElementById('display-monto');
    const restanteText = document.getElementById('restante-text');
    const btnText = document.getElementById('btn-text');
    
    const cards = document.querySelectorAll('.payment-card');
    const alertaC2P = document.getElementById('alerta-c2p');
    const camposC2P = document.getElementById('campos_c2p');
    const camposTransferencia = document.getElementById('campos_transferencia');

    const formatMoney = (amount) => amount.toLocaleString('en-US', { minimumFractionDigits: 2, maximumFractionDigits: 2 });

    const actualizarModalidad = (esAbono) => {
        if (esAbono) {
            btnAbono.classList.add('seleccionado');
            btnTotal.classList.remove('seleccionado');
            inputMonto.value = montoAbonoVal;
            displayMonto.innerText = formatMoney(montoAbonoVal);
            btnText.innerText = `Confirmar y pagar $${formatMoney(montoAbonoVal)}`;
            restanteText.innerText = `Restante en el local: $${formatMoney(montoTotalVal - montoAbonoVal)}`;
            restanteText.style.display = 'block';
        } else {
            btnTotal.classList.add('seleccionado');
            btnAbono.classList.remove('seleccionado');
            inputMonto.value = montoTotalVal;
            displayMonto.innerText = formatMoney(montoTotalVal);
            btnText.innerText = `Confirmar y pagar $${formatMoney(montoTotalVal)}`;
            restanteText.style.display = 'none';
        }
    };

    if (btnAbono && btnTotal) {
        btnAbono.addEventListener('click', (e) => { e.preventDefault(); actualizarModalidad(true); });
        btnTotal.addEventListener('click', (e) => { e.preventDefault(); actualizarModalidad(false); });
    }

    cards.forEach(card => {
        const radio = card.querySelector('input[type="radio"]');
        
        card.addEventListener('click', () => {
            cards.forEach(c => c.classList.remove('active-card'));
            card.classList.add('active-card');
            radio.checked = true;
            radio.dispatchEvent(new Event('change'));
        });

        radio.addEventListener('change', (e) => {
            const metodo = e.target.value;
            alertaC2P.classList.add('hidden');
            camposC2P.classList.add('hidden');
            camposTransferencia.classList.add('hidden');

            if (metodo === 'c2p') {
                alertaC2P.classList.remove('hidden');
                camposC2P.classList.remove('hidden');
            } else if (metodo === 'transferencia') {
                camposTransferencia.classList.remove('hidden');
            }
        });
    });

    const formPago = document.getElementById('form-pago');
    if (formPago) {
        formPago.addEventListener('submit', function(e) {
            e.preventDefault();
            Swal.fire({
                title: 'Procesando transacción',
                html: 'Conectando con la red bancaria...<br><br><span style="color: #727A74; font-size: 13px;">Por favor, espere un momento.</span>',
                allowOutsideClick: false,
                showConfirmButton: false,
                didOpen: () => { Swal.showLoading(); }
            });
            setTimeout(() => { this.submit(); }, 1500);
        });
    }
});