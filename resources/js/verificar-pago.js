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
    const modalKicker = document.getElementById('modal-kicker');
    const modalTitulo = document.getElementById('modal-titulo');
    const modalMensaje = document.getElementById('modal-mensaje');
    const modalTextarea = document.getElementById('motivo-rechazo');
    const modalCliente = document.getElementById('modal-pago-cliente');
    const modalDetalle = document.getElementById('modal-pago-detalle');
    const modalMonto = document.getElementById('modal-pago-monto');
    const modalFecha = document.getElementById('modal-pago-fecha');
    const btnConfirmar = document.getElementById('btn-modal-confirmar');
    const btnCancelar = document.getElementById('btn-modal-cancelar');
    const btnCerrarX = document.getElementById('btn-modal-cerrar-x');

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

    function poblarResumenFila() {
        if (!filaActual) return;
        const cliente = filaActual.querySelector('.cliente-info strong')?.textContent.trim() || 'Cliente';
        const banco = filaActual.querySelector('.celda-banco strong')?.textContent.trim() || '';
        const ref = filaActual.querySelector('.celda-referencia code')?.textContent.trim() || '';
        const monto = filaActual.querySelector('.celda-monto')?.textContent.trim() || '$0.00';
        const fecha = filaActual.querySelector('.celda-fecha strong')?.textContent.trim() || '';
        const hora = filaActual.querySelector('.celda-fecha span')?.textContent.trim() || '';

        if (modalCliente) modalCliente.textContent = cliente;
        if (modalDetalle) modalDetalle.textContent = [banco, ref].filter(Boolean).join(' · ');
        if (modalMonto) modalMonto.textContent = monto;
        if (modalFecha) modalFecha.textContent = [fecha, hora].filter(Boolean).join(' · ');
    }

    function abrirModal() {
        if (!modal) return;
        poblarResumenFila();

        if (modalTextarea) {
            modalTextarea.classList.add('hidden');
            modalTextarea.value = '';
        }

        if (accionActual === 'autorizar') {
            if (modalKicker) modalKicker.textContent = 'CONFIRMAR TRANSACCIÓN';
            modalTitulo.textContent = '¿Quieres autorizar pago?';
            modalMensaje.textContent = 'La reserva del cliente quedará confirmada al aprobar este pago.';
            btnConfirmar.textContent = 'Autorizar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-autorizar';
        } else {
            if (modalKicker) modalKicker.textContent = 'GESTIONAR PAGO';
            modalTitulo.textContent = '¿Quieres rechazar pago?';
            modalMensaje.textContent = 'El pago se marcará como rechazado y no se validará la reserva.';
            btnConfirmar.textContent = 'Rechazar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-rechazar';
        }

        modal.showModal();
    }

    function cerrarModalPago() {
        if (!modal) return;
        modal.close();
        resetEstado();
    }

    if (btnCancelar) {
        btnCancelar.addEventListener('click', cerrarModalPago);
    }

    if (btnCerrarX) {
        btnCerrarX.addEventListener('click', cerrarModalPago);
    }

    if (modal) {
        modal.addEventListener('click', (e) => {
            if (e.target === modal) {
                cerrarModalPago();
            }
        });
    }

    // =====================================================================
    // 3. Confirmar Acción (Fetch al Backend)
    // =====================================================================
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', () => {
            if (!filaActual) return;

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
                body: JSON.stringify({})
            })
            .then(res => {
                if (res.ok) {
                    window.location.reload();
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