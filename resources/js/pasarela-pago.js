/* ==========================================================================
   PASARELA DE PAGO - Lógica del formulario
   ========================================================================== */

document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('form-pago');
    if (!form) return;

    const inputComprobante = document.getElementById('comprobante');
    const zonaArchivo = document.querySelector('.zona-archivo');
    const nombreArchivo = document.getElementById('archivo-nombre');
    const btnEnviar = document.getElementById('btn-enviar-pago');
    const modal = document.getElementById('modal-confirmar-pago');

    /* ============================================================
       1. Manejo del archivo (nombre + estado visual)
       ============================================================ */
    if (inputComprobante) {
        inputComprobante.addEventListener('change', (e) => {
            const file = e.target.files[0];
            if (!file) {
                zonaArchivo?.classList.remove('archivo-cargado');
                if (nombreArchivo) nombreArchivo.textContent = 'Arrastra tu imagen o haz clic para subir';
                return;
            }

            // Validar tamaño (5MB)
            const maxSize = 5 * 1024 * 1024;
            if (file.size > maxSize) {
                alert('El archivo supera los 5MB. Intenta con uno más pequeño.');
                inputComprobante.value = '';
                return;
            }

            // Validar tipo
            const tiposPermitidos = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            if (!tiposPermitidos.includes(file.type)) {
                alert('Solo se permiten imágenes JPG, PNG, WEBP o PDF.');
                inputComprobante.value = '';
                return;
            }

            zonaArchivo?.classList.add('archivo-cargado');
            if (nombreArchivo) {
                nombreArchivo.textContent = `✓ ${file.name}`;
            }
        });
    }

    /* ============================================================
       2. Validación personalizada del formulario
       ============================================================ */
    form.addEventListener('submit', (e) => {
        e.preventDefault();

        // Validar campos requeridos HTML5
        if (!form.checkValidity()) {
            form.reportValidity();
            return;
        }

        // Validar referencia (solo números y letras)
        const referencia = form.querySelector('#referencia')?.value.trim();
        if (referencia && referencia.length < 4) {
            alert('La referencia debe tener al menos 4 caracteres.');
            return;
        }

        // Validar monto
        const monto = parseFloat(form.querySelector('#monto')?.value || '0');
        if (!monto || monto <= 0) {
            alert('Ingresa un monto válido mayor a 0.');
            return;
        }

        // Estado de carga
        if (btnEnviar) {
            btnEnviar.classList.add('cargando');
            btnEnviar.disabled = true;
            const span = btnEnviar.querySelector('span');
            if (span) span.textContent = 'Enviando...';
        }

        // Simulación de envío (aquí conectas con tu backend real)
        // Reemplaza esto por un fetch a tu ruta 'pasarela.store'
        setTimeout(() => {
            if (btnEnviar) {
                btnEnviar.classList.remove('cargando');
                btnEnviar.disabled = false;
                const span = btnEnviar.querySelector('span');
                if (span) span.textContent = 'Reportar pago';
            }

            // Mostrar modal de confirmación
            if (modal) {
                modal.showModal();
            }

            // Reset del formulario
            form.reset();
            zonaArchivo?.classList.remove('archivo-cargado');
            if (nombreArchivo) nombreArchivo.textContent = 'Arrastra tu imagen o haz clic para subir';

            /* ------------------------------------------------------
               PARA CONECTAR CON TU BACKEND REAL, reemplaza el
               setTimeout anterior por algo como:

               const formData = new FormData(form);
               fetch(form.action, {
                   method: 'POST',
                   body: formData,
                   headers: {
                       'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                       'Accept': 'application/json',
                   },
               })
               .then(res => res.json())
               .then(data => {
                   if (data.success) {
                       modal.showModal();
                       form.reset();
                   } else {
                       alert(data.message || 'Error al enviar el pago.');
                   }
               })
               .catch(() => alert('Error de conexión. Intenta de nuevo.'))
               .finally(() => {
                   btnEnviar.classList.remove('cargando');
                   btnEnviar.disabled = false;
                   btnEnviar.querySelector('span').textContent = 'Reportar pago';
               });
               ------------------------------------------------------ */
        }, 900);
    });

    /* ============================================================
       3. Formateo de cédula y teléfono en tiempo real
       ============================================================ */
    const inputCedula = document.getElementById('cedula');
    if (inputCedula) {
        inputCedula.addEventListener('input', (e) => {
            let v = e.target.value.replace(/[^0-9VvEeJj-]/g, '').toUpperCase();
            e.target.value = v;
        });
    }

    const inputTelefono = document.getElementById('telefono');
    if (inputTelefono) {
        inputTelefono.addEventListener('input', (e) => {
            let v = e.target.value.replace(/[^0-9-]/g, '');
            e.target.value = v;
        });
    }

    const inputReferencia = document.getElementById('referencia');
    if (inputReferencia) {
        inputReferencia.addEventListener('input', (e) => {
            e.target.value = e.target.value.replace(/[^0-9A-Za-z]/g, '');
        });
    }

    /* ============================================================
       4. Cerrar modal al hacer clic fuera
       ============================================================ */
    if (modal) {
        modal.addEventListener('click', (e) => {
            const rect = modal.querySelector('.modal-pago__cuerpo')?.getBoundingClientRect();
            if (!rect) return;
            const fuera =
                e.clientX < rect.left ||
                e.clientX > rect.right ||
                e.clientY < rect.top ||
                e.clientY > rect.bottom;
            if (fuera) modal.close();
        });
    }
});