/* =========================================================================
   VERIFICACIÓN DE PAGOS - Controlador interactivo
   Con tabs de filtrado + modal unificado estilo catálogo
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Verificar pagos - JS iniciado');

    // =====================================================================
    // REFERENCIAS (con protección contra null)
    // =====================================================================
    const modal         = document.getElementById('modal-confirmacion');
    const modalTitulo   = document.getElementById('modal-titulo');
    const modalMensaje  = document.getElementById('modal-mensaje');
    const motivoRechazo = document.getElementById('motivo-rechazo');
    const btnConfirmar  = document.getElementById('btn-modal-confirmar');
    const btnCancelar   = document.getElementById('btn-modal-cancelar');

    // Tabs y buscador
    const tabs          = document.querySelectorAll('.tab-estado');
    const inputBusqueda = document.querySelector('.campo-busqueda input');
    const inputFecha    = document.querySelector('.campo-fecha input');
    const filas         = document.querySelectorAll('.tabla-transacciones tbody tr');

    // Debug
    console.log('🔍 Tabs:', tabs.length, '| Filas:', filas.length, '| Modal:', !!modal);

    // Estado local
    let accionActual = '';
    let filaActual = null;

    // =====================================================================
    // 1. TABS DE FILTRADO
    // =====================================================================
    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            console.log('🖱️ Tab clickeado:', tab.dataset.filtro);

            // Quitar "activo" de todos y ponerlo solo al clickeado
            tabs.forEach(t => t.classList.remove('activo'));
            tab.classList.add('activo');

            // Aplicar filtro
            aplicarFiltros(tab.dataset.filtro);
        });
    });

    // =====================================================================
    // 2. BUSCADOR + FECHA
    // =====================================================================
    if (inputBusqueda) {
        inputBusqueda.addEventListener('input', () => {
            const tabActivo = document.querySelector('.tab-estado.activo');
            aplicarFiltros(tabActivo ? tabActivo.dataset.filtro : 'todos');
        });
    }

    if (inputFecha) {
        inputFecha.addEventListener('change', () => {
            const tabActivo = document.querySelector('.tab-estado.activo');
            aplicarFiltros(tabActivo ? tabActivo.dataset.filtro : 'todos');
        });
    }

    // =====================================================================
    // 3. FUNCIÓN CENTRAL DE FILTRADO
    // =====================================================================
    function aplicarFiltros(estadoFiltro) {
        const termino = (inputBusqueda?.value || '').toLowerCase().trim();
        const fechaSeleccionada = inputFecha?.value || '';
        let visibles = 0;

        filas.forEach(fila => {
            const textoFila = fila.textContent.toLowerCase();
            const badge = fila.querySelector('.estado-pendiente, .estado-autorizado, .estado-rechazado');
            const estadoFila = badge ? obtenerEstadoClave(badge) : '';

            // Coincidencia por texto
            const coincideBusqueda = !termino || textoFila.includes(termino);

            // Coincidencia por estado
            const coincideEstado = estadoFiltro === 'todos' || estadoFila === estadoFiltro;

            // Coincidencia por fecha (si la fila tiene un data-fecha)
            let coincideFecha = true;
            if (fechaSeleccionada) {
                const dataFecha = fila.dataset.fecha || '';
                coincideFecha = !dataFecha || dataFecha === fechaSeleccionada;
            }

            // Mostrar u ocultar
            if (coincideBusqueda && coincideEstado && coincideFecha) {
                fila.style.display = '';
                visibles++;
            } else {
                fila.style.display = 'none';
            }
        });

        console.log(`📊 Mostrando ${visibles} de ${filas.length} (filtro: ${estadoFiltro})`);
    }

    function obtenerEstadoClave(badge) {
        if (!badge) return '';
        if (badge.classList.contains('estado-pendiente')) return 'pendiente';
        if (badge.classList.contains('estado-autorizado')) return 'autorizado';
        if (badge.classList.contains('estado-rechazado')) return 'rechazado';
        return '';
    }

    // =====================================================================
    // 4. ABRIR MODAL — AUTORIZAR
    // =====================================================================
    document.querySelectorAll('.btn-autorizar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            console.log('✅ Click autorizar');

            accionActual = 'autorizar';
            filaActual = e.target.closest('tr');

            if (!modal) return;

            modalTitulo.textContent = '¿Autorizar esta transacción?';
            modalMensaje.textContent = 'La reserva del cliente se marcará como confirmada automáticamente una vez que apruebes este pago móvil. Esta acción no se puede deshacer.';

            if (motivoRechazo) {
                motivoRechazo.classList.add('hidden');
                motivoRechazo.value = '';
                motivoRechazo.style.borderColor = '';
            }

            if (btnConfirmar) {
                btnConfirmar.classList.remove('accion-rechazar');
                btnConfirmar.classList.add('accion-autorizar');
                btnConfirmar.textContent = 'Autorizar pago';
            }

            modal.showModal();
        });
    });

    // =====================================================================
    // 5. ABRIR MODAL — RECHAZAR
    // =====================================================================
    document.querySelectorAll('.btn-rechazar').forEach(boton => {
        boton.addEventListener('click', (e) => {
            console.log('❌ Click rechazar');

            accionActual = 'rechazar';
            filaActual = e.target.closest('tr');

            if (!modal) return;

            modalTitulo.textContent = '¿Rechazar esta transacción?';
            modalMensaje.textContent = 'Indica el motivo del rechazo. Este mensaje será visible para el cliente y no se podrá modificar después.';

            if (motivoRechazo) {
                motivoRechazo.classList.remove('hidden');
                motivoRechazo.value = '';
                motivoRechazo.style.borderColor = '';
            }

            if (btnConfirmar) {
                btnConfirmar.classList.remove('accion-autorizar');
                btnConfirmar.classList.add('accion-rechazar');
                btnConfirmar.textContent = 'Rechazar pago';
            }

            modal.showModal();
            setTimeout(() => motivoRechazo?.focus(), 250);
        });
    });

    // =====================================================================
    // 6. CANCELAR
    // =====================================================================
    if (btnCancelar && modal) {
        btnCancelar.addEventListener('click', () => {
            modal.close();
            resetEstado();
        });
    }

    // =====================================================================
    // 7. CONFIRMAR ACCIÓN
    // =====================================================================
    if (btnConfirmar) {
        btnConfirmar.addEventListener('click', () => {
            // Validación: si es rechazo, el motivo es obligatorio
            if (accionActual === 'rechazar') {
                if (!motivoRechazo || motivoRechazo.value.trim() === '') {
                    if (motivoRechazo) {
                        motivoRechazo.style.borderColor = 'var(--color-danger)';
                        motivoRechazo.focus();
                    }
                    return;
                }
            }

            if (filaActual) {
                const badgeEstado = filaActual.querySelector('.estado-pendiente, .estado-autorizado, .estado-rechazado');

                if (badgeEstado) {
                    if (accionActual === 'autorizar') {
                        badgeEstado.textContent = 'Autorizado';
                        badgeEstado.className = 'estado-autorizado';
                    } else {
                        badgeEstado.textContent = 'Rechazado';
                        badgeEstado.className = 'estado-rechazado';
                        if (motivoRechazo) {
                            badgeEstado.setAttribute('title', 'Motivo: ' + motivoRechazo.value.trim());
                            badgeEstado.setAttribute('data-motivo', motivoRechazo.value.trim());
                        }
                    }
                }

                // Deshabilitar botones de esa fila
                filaActual.querySelectorAll('button').forEach(b => b.disabled = true);

                // Actualizar contadores de tabs
                actualizarContadores();

                // Re-aplicar filtros por si la fila ya no coincide con el tab actual
                const tabActivo = document.querySelector('.tab-estado.activo');
                aplicarFiltros(tabActivo ? tabActivo.dataset.filtro : 'todos');
            }

            if (modal) modal.close();
            resetEstado();
        });
    }

    // =====================================================================
    // 8. CERRAR POR BACKDROP
    // =====================================================================
    if (modal) {
        modal.addEventListener('click', (e) => {
            const rect = modal.getBoundingClientRect();
            const dentro =
                e.clientX >= rect.left &&
                e.clientX <= rect.right &&
                e.clientY >= rect.top &&
                e.clientY <= rect.bottom;

            if (!dentro) {
                modal.close();
                resetEstado();
            }
        });

        modal.addEventListener('close', resetEstado);
    }

    function resetEstado() {
        accionActual = '';
        filaActual = null;
        if (motivoRechazo) {
            motivoRechazo.value = '';
            motivoRechazo.style.borderColor = '';
            motivoRechazo.classList.add('hidden');
        }
        if (btnConfirmar) {
            btnConfirmar.classList.remove('accion-autorizar', 'accion-rechazar');
        }
    }

    // =====================================================================
    // 9. ACTUALIZAR CONTADORES DE LOS TABS
    // =====================================================================
    function actualizarContadores() {
        const contadores = { pendiente: 0, autorizado: 0, rechazado: 0, todos: 0 };

        document.querySelectorAll('.tabla-transacciones tbody tr').forEach(fila => {
            const badge = fila.querySelector('.estado-pendiente, .estado-autorizado, .estado-rechazado');
            if (!badge) return;

            if (badge.classList.contains('estado-pendiente')) contadores.pendiente++;
            if (badge.classList.contains('estado-autorizado')) contadores.autorizado++;
            if (badge.classList.contains('estado-rechazado')) contadores.rechazado++;
            contadores.todos++;
        });

        tabs.forEach(tab => {
            const filtro = tab.dataset.filtro;
            const contador = tab.querySelector('.tab-contador');
            if (contador && contadores[filtro] !== undefined) {
                contador.textContent = contadores[filtro];
            }
        });
    }

    // =====================================================================
    // 10. APLICAR FILTRO INICIAL AL CARGAR
    // =====================================================================
    actualizarContadores();

    const tabInicial = document.querySelector('.tab-estado.activo') || tabs[0];
    if (tabInicial) {
        aplicarFiltros(tabInicial.dataset.filtro);
    }

    console.log('✅ Verificar pagos - listo');
});