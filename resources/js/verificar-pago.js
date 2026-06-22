/* =========================================================================
   CONTROLADOR INTERACTIVO DE LA INTERFAZ DE PAGOS (FRONTEND - EILYN)
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    // Capturamos los elementos del modal semántico
    const modal = document.getElementById('modal-confirmacion');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalMensaje = document.getElementById('modal-mensaje');
    const motivoRechazo = document.getElementById('motivo-rechazo');
    const btnConfirmar = document.getElementById('btn-modal-confirmar');
    const btnCancelar = document.getElementById('btn-modal-cancelar');

    // Variables de control local para la interfaz
    let accionActual = '';
    let filaActual = null; 

    // 1. Escuchar clics en los botones "Autorizar" de la tabla
    document.querySelectorAll('.btn-autorizar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            accionActual = 'autorizar';
            filaActual = e.target.closest('tr'); // Guardamos la fila afectada

            // Configuramos los textos visuales
            modalTitulo.textContent = 'Confirmar Autorización';
            modalMensaje.textContent = '¿Estás seguro de que deseas autorizar este pago móvil? La reserva se marcará como confirmada automáticamente.';
            
            // Ocultamos el campo de texto (no se necesita para autorizar)
            motivoRechazo.classList.add('hidden');
            motivoRechazo.value = ''; 

            // Estilo de éxito para el botón de confirmación
            btnConfirmar.style.backgroundColor = 'var(--success-color)';
            
            modal.showModal(); // Abre el modal de forma nativa
        });
    });

    // 2. Escuchar clics en los botones "Rechazar" de la tabla
    document.querySelectorAll('.btn-rechazar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            accionActual = 'rechazar';
            filaActual = e.target.closest('tr'); // Guardamos la fila afectada

            // Configuramos los textos visuales
            modalTitulo.textContent = 'Rechazar Transacción';
            modalMensaje.textContent = 'Indica el motivo por el cual estás rechazando este pago móvil. Este mensaje será visible para el cliente:';
            
            // Mostramos el campo de texto para el motivo
            motivoRechazo.classList.remove('hidden');
            
            // Estilo de peligro para el botón de confirmación
            btnConfirmar.style.backgroundColor = 'var(--danger-color)';
            
            modal.showModal();
            motivoRechazo.focus();
        });
    });

    // 3. Botón Cancelar (Cierra la ventana sin alterar nada)
    btnCancelar.addEventListener('click', () => {
        modal.close();
    });

    // 4. Botón Confirmar (Simula el éxito del proceso directamente en la UI)
    btnConfirmar.addEventListener('click', () => {
        // Validación visual en el frontend: si es rechazo, el motivo es obligatorio
        if (accionActual === 'rechazar' && motivoRechazo.value.trim() === '') {
            motivoRechazo.style.borderColor = 'var(--danger-color)';
            motivoRechazo.focus();
            return;
        }

        if (filaActual) {
            // Buscamos la píldora (span) de estado dentro de la fila seleccionada
            const badgeEstado = filaActual.querySelector('span');
            
            if (badgeEstado) {
                if (accionActual === 'autorizar') {
                    badgeEstado.textContent = 'Autorizado';
                    badgeEstado.className = 'estado-autorizado'; // Aplica tus estilos CSS creados
                } else {
                    badgeEstado.textContent = 'Rechazado';
                    badgeEstado.className = 'estado-rechazado'; // Aplica tus estilos CSS creados
                }
            }

            // Deshabilitamos los botones de esa fila para indicar que ya se tomó una decisión
            filaActual.querySelectorAll('button').forEach(b => b.disabled = true);
        }

        // Cerramos el modal limpiamente
        modal.close();
    });
});