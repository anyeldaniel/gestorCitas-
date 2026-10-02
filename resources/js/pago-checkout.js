/* =========================================================================
   PAGO CHECKOUT - JS del cliente
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Pago checkout cargado');

    // =====================================================================
    // 1. VALIDACIÓN DEL FORMULARIO
    // =====================================================================
    const form = document.querySelector('.checkout-form');
    if (form) {
        form.addEventListener('submit', (e) => {
            const metodo = form.querySelector('input[name="metodo"]:checked');
            const referencia = form.querySelector('input[name="referencia"]');

            if (!metodo) {
                e.preventDefault();
                alert('Por favor selecciona un método de pago.');
                return false;
            }

            if (!referencia || referencia.value.trim().length < 4) {
                e.preventDefault();
                alert('La referencia debe tener al menos 4 caracteres.');
                if (referencia) referencia.focus();
                return false;
            }
        });
    }

    // =====================================================================
    // 2. PREVISUALIZACIÓN DEL COMPROBANTE
    // =====================================================================
    const inputComprobante = document.querySelector('input[name="comprobante"]');
    const textoComprobante = document.querySelector('.input-archivo-texto');

    if (inputComprobante && textoComprobante) {
        const textoOriginal = textoComprobante.innerHTML;

        inputComprobante.addEventListener('change', (e) => {
            const archivo = e.target.files[0];

            if (archivo) {
                if (archivo.size > 2 * 1024 * 1024) {
                    alert('El archivo es demasiado grande. Máximo 2MB.');
                    inputComprobante.value = '';
                    return;
                }

                textoComprobante.innerHTML = `
                    <strong style="color: var(--primary-brown); font-weight: 600;">
                        ✓ ${archivo.name}
                    </strong>
                    <small>${(archivo.size / 1024).toFixed(1)} KB</small>
                `;
            } else {
                textoComprobante.innerHTML = textoOriginal;
            }
        });
    }

    // =====================================================================
    // 3. ESTILO ACTIVO EN MÉTODOS DE PAGO
    // =====================================================================
    const metodos = document.querySelectorAll('.metodo-opcion');

    metodos.forEach(metodo => {
        const radio = metodo.querySelector('input[type="radio"]');

        if (radio && radio.checked) {
            metodo.classList.add('activo');
        }

        if (radio) {
            radio.addEventListener('change', () => {
                metodos.forEach(m => m.classList.remove('activo'));
                metodo.classList.add('activo');
            });
        }
    });

    console.log('✅ Checkout listo');
});