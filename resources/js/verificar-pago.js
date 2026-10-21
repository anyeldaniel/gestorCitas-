/**
 * Módulo de Verificación de Pagos
 * Controla el filtrado de la tabla, la búsqueda, el filtro por fecha
 * y la interacción con el modal de confirmación.
 */

document.addEventListener('DOMContentLoaded', () => {
    // Referencias del DOM
    const tablaBody = document.querySelector('.tabla-transacciones tbody');
    const filas = document.querySelectorAll('.tabla-transacciones tbody tr');
    const tabs = document.querySelectorAll('.tab-estado');
    const inputBusqueda = document.getElementById('input-busqueda-pagos');
    const inputFecha = document.getElementById('input-fecha-pagos');
    const footerContador = document.getElementById('footer-contador');
    
    // Referencias del Modal
    const modal = document.getElementById('modal-confirmacion');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalMensaje = document.getElementById('modal-mensaje');
    const modalTextarea = document.getElementById('motivo-rechazo');
    const btnConfirmar = document.getElementById('btn-modal-confirmar');
    const btnCancelar = document.getElementById('btn-modal-cancelar');

    // Estado de la aplicación
    let accionActual = '';
    let filaActual = null;

    // =====================================================================
    // 1. Lógica de Filtrado (Tabs, Búsqueda y Fecha)
    // =====================================================================
    function aplicarFiltros() {
        const tabActivo = document.querySelector('.tab-estado.activo');
        const filtroEstado = tabActivo ? tabActivo.dataset.filtro : 'todos';
        const terminoBusqueda = inputBusqueda ? inputBusqueda.value.toLowerCase().trim() : '';
        const fechaFiltro = inputFecha ? inputFecha.value : '';
        let visibles = 0;

        filas.forEach(fila => {
            const textoFila = fila.textContent.toLowerCase();
            const estadoFila = fila.dataset.estado;
            const fechaFila = fila.dataset.fecha;

            const coincideEstado = (filtroEstado === 'todos') || (estadoFila === filtroEstado);
            const coincideBusqueda = !terminoBusqueda || textoFila.includes(terminoBusqueda);
            const coincideFecha = !fechaFiltro || (fechaFila === fechaFiltro);

            if (coincideEstado && coincideBusqueda && coincideFecha) {
                fila.classList.remove('fila-oculta');
                visibles++;
            } else {
                fila.classList.add('fila-oculta');
            }
        });

        if (footerContador) {
            footerContador.textContent = visibles;
        }
    }

    // Event Listeners para filtros
    if (tabs.length) {
        tabs.forEach(tab => {
            tab.addEventListener('click', () => {
                tabs.forEach(t => t.classList.remove('activo'));
                tab.classList.add('activo');
                aplicarFiltros();
            });
        });
    }

    if (inputBusqueda) inputBusqueda.addEventListener('input', aplicarFiltros);
    if (inputFecha) inputFecha.addEventListener('change', aplicarFiltros);

    // =====================================================================
    // 2. Lógica del Modal (Abrir y Cerrar)
    // =====================================================================
    document.querySelectorAll('.btn-autorizar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            accionActual = 'autorizar';
            filaActual = e.target.closest('tr');
            abrirModal();
        });
    });

    document.querySelectorAll('.btn-rechazar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            accionActual = 'rechazar';
            filaActual = e.target.closest('tr');
            abrirModal();
        });
    });

    function abrirModal() {
        if (!modal) return;

        if (accionActual === 'autorizar') {
            modalTitulo.textContent = '¿Autorizar esta transacción?';
            modalMensaje.textContent = 'La reserva del cliente se marcará como confirmada automáticamente. Esta acción no se puede deshacer.';
            modalTextarea.classList.add('hidden');
            btnConfirmar.textContent = 'Autorizar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-autorizar';
        } else {
            modalTitulo.textContent = '¿Rechazar esta transacción?';
            modalMensaje.textContent = 'Indica el motivo del rechazo. Este mensaje será visible para el cliente.';
            modalTextarea.classList.remove('hidden');
            modalTextarea.value = '';
            btnConfirmar.textContent = 'Rechazar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-rechazar';
            setTimeout(() => modalTextarea.focus(), 100);
        }

        modal.showModal();
    }

    if (btnCancelar && modal) {
        btnCancelar.addEventListener('click', () => {
            modal.close();
            resetEstado();
        });
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            const fuera = e.clientX < rect.left || e.clientX > rect.right || e.clientY < rect.top || e.clientY > rect.bottom;
            if (fuera) {
                modal.close();
                resetEstado();
            }
        });
    }

    // =====================================================================
    // 3. Confirmar Acción (Fetch al Backend)
    // =====================================================================
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', () => {
            if (accionActual === 'rechazar' && (!modalTextarea.value.trim())) {
                modalTextarea.style.borderColor = 'var(--color-danger)';
                modalTextarea.focus();
                return;
            }

            if (!filaActual) return;

            // Obtenemos la URL real desde el botón original (data-url)
            const urlBase = accionActual === 'autorizar' 
                ? filaActual.querySelector('.btn-autorizar').dataset.url 
                : filaActual.querySelector('.btn-rechazar').dataset.url;
                
            const token = document.querySelector('meta[name="csrf-token"]').content;

            btnConfirmar.textContent = 'Procesando...';
            btnConfirmar.disabled = true;

            fetch(urlBase, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ motivo: modalTextarea.value })
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload(); // Recarga para actualizar contadores
                } else {
                    alert('Ocurrió un error al procesar la transacción.');
                    btnConfirmar.textContent = accionActual === 'autorizar' ? 'Autorizar pago' : 'Rechazar pago';
                    btnConfirmar.disabled = false;
                }
            })
            .catch(() => {
                alert('Error de conexión.');
                btnConfirmar.disabled = false;
            });
        });
    }

    function resetEstado() {
        accionActual = '';
        filaActual = null;
        if (modalTextarea) {
            modalTextarea.value = '';
            modalTextarea.style.borderColor = '';
        }
        if (btnConfirmar) {
            btnConfirmar.disabled = false;
        }
    }
});