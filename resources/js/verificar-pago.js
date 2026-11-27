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

    // Referencias del Modal de Confirmación
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
    const camposReembolso = document.getElementById('campos-reembolso-pago');
    const listaReembolsos = document.getElementById('lista-reembolsos-pago');
    const campoMontoReembolso = document.getElementById('campo-monto-reembolso');
    const inputMontoReembolso = document.getElementById('monto-reembolso');

    // Estado de la aplicación
    let accionActual = '';
    let filaActual = null;
    let pagosAprobadosActuales = [];

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

    // Aplicar filtro inicial al cargar la página
    aplicarFiltros();
    // =====================================================================
    // 2. Lógica del Modal de Confirmación (Abrir y Cerrar)
    // =====================================================================
    document.querySelectorAll('.btn-autorizar:not(.btn-definir-reembolso)').forEach(boton => {
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

    document.querySelectorAll('.btn-definir-reembolso').forEach(boton => {
        boton.addEventListener('click', (e) => {
            accionActual = 'definir_reembolso';
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
            modalTextarea.required = false;
        }
        camposReembolso.classList.add('hidden');
        listaReembolsos.replaceChildren();
        campoMontoReembolso.classList.add('hidden');
        inputMontoReembolso.required = false;
        inputMontoReembolso.value = '';

        if (accionActual === 'autorizar') {
            if (modalKicker) modalKicker.textContent = 'CONFIRMAR TRANSACCIÓN';
            modalTitulo.textContent = '¿Quieres autorizar pago?';
            modalMensaje.textContent = 'La reserva del cliente quedará confirmada al aprobar este pago.';
            btnConfirmar.textContent = 'Autorizar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-autorizar';
        } else if (accionActual === 'rechazar') {
            if (modalKicker) modalKicker.textContent = 'GESTIONAR PAGO';
            modalTitulo.textContent = '¿Quieres rechazar pago?';
            modalMensaje.textContent = 'El pago será rechazado y la cita se cancelará. Escribe el motivo.';
            btnConfirmar.textContent = 'Rechazar pago';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-rechazar';

            modalTextarea.classList.remove('hidden');
            modalTextarea.required = true;
            pagosAprobadosActuales = JSON.parse(filaActual.dataset.pagosAprobados || '[]');
            if (pagosAprobadosActuales.length) {
                listaReembolsos.innerHTML = pagosAprobadosActuales.map(pago => `
                    <label class="campo-reembolso-pago">
                        Pago #${pago.id} (máximo $${Number(pago.monto).toFixed(2)})
                        <input type="number" name="reembolsos[${pago.id}]" min="0.01"
                               max="${Number(pago.monto).toFixed(2)}" step="0.01" required>
                    </label>
                `).join('');
                camposReembolso.classList.remove('hidden');
            }
        } else {
            if (modalKicker) modalKicker.textContent = 'GESTIONAR REEMBOLSO';
            modalTitulo.textContent = 'Define el importe del reembolso';
            modalMensaje.textContent = 'El trabajador canceló la cita. Indica cuánto se reembolsará al cliente.';
            btnConfirmar.textContent = 'Guardar importe';
            btnConfirmar.className = 'btn-confirmar-accion btn-accion-autorizar';

            inputMontoReembolso.max = Number(
                filaActual.querySelector('.btn-definir-reembolso').dataset.max
            ).toFixed(2);
            campoMontoReembolso.classList.remove('hidden');
            inputMontoReembolso.required = true;
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
        btnConfirmar.addEventListener('click', async () => {
            if (!filaActual) return;

            const urlBase = accionActual === 'autorizar'
                ? filaActual.querySelector('.btn-autorizar').dataset.url
                : accionActual === 'rechazar'
                    ? filaActual.querySelector('.btn-rechazar').dataset.url
                    : filaActual.querySelector('.btn-definir-reembolso').dataset.url;

            const token = document.querySelector('meta[name="csrf-token"]').content;
            const body = {};

            if (accionActual === 'rechazar') {
                if (!modalTextarea.reportValidity()) return;
                body.motivo = modalTextarea.value.trim();
                body.reembolsos = {};
                listaReembolsos.querySelectorAll('input[name^="reembolsos["]').forEach(input => {
                    const id = input.name.match(/^reembolsos\[(\d+)\]$/)[1];
                    body.reembolsos[id] = input.value;
                });
            } else if (accionActual === 'definir_reembolso') {
                if (!inputMontoReembolso.reportValidity()) return;
                body.monto_reembolso = inputMontoReembolso.value;
            }

            btnConfirmar.textContent = 'Procesando...';
            btnConfirmar.disabled = true;

            try {
                const res = await fetch(urlBase, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify(body)
                });

                if (res.ok) {
                    window.location.reload();
                    return;
                }

                const data = await res.json();
                const error = data.message
                    || Object.values(data.errors || {}).flat()[0]
                    || 'Ocurrió un error al procesar la transacción.';
                alert(error);
                btnConfirmar.textContent = accionActual === 'autorizar'
                    ? 'Autorizar pago'
                    : accionActual === 'rechazar' ? 'Rechazar pago' : 'Guardar importe';
            } catch (error) {
                alert('Error de conexión.');
            } finally {
                btnConfirmar.disabled = false;
            }
        });
    }

    function resetEstado() {
        accionActual = '';
        filaActual = null;
        if (modalTextarea) {
            modalTextarea.value = '';
            modalTextarea.style.borderColor = '';
        }
        pagosAprobadosActuales = [];
        camposReembolso.classList.add('hidden');
        campoMontoReembolso.classList.add('hidden');
        if (btnConfirmar) {
            btnConfirmar.disabled = false;
        }
    }

    // =====================================================================
    // 4. Lógica del Modal de Detalle (Ver Detalle)
    // =====================================================================
    const modalDetallePago = document.getElementById('modal-detalle-pago');
    const botonesDetalle = document.querySelectorAll('.btn-ver-detalle');
    const btnCerrarDetalleX = document.getElementById('btn-detalle-cerrar-x');
    const btnCerrarDetalleFooter = document.getElementById('btn-detalle-cerrar');

    if (botonesDetalle.length) {
        botonesDetalle.forEach(boton => {
            boton.addEventListener('click', function() {
                // Extraemos los datos del botón usando los atributos data-*
                const cliente = this.dataset.cliente;
                const correo = this.dataset.correo;
                const iniciales = this.dataset.iniciales;
                const metodo = this.dataset.metodo;
                const banco = this.dataset.banco;
                const referencia = this.dataset.referencia;
                const monto = this.dataset.monto;
                const fecha = this.dataset.fecha;
                const estado = this.dataset.estado;
                const servicio = this.dataset.servicio;
                const duracion = this.dataset.duracion;
                const fechaCita = this.dataset.fechaCita;
                const horario = this.dataset.horario;
                const especialista = this.dataset.especialista;
                const contacto = this.dataset.contacto;

                // Rellenamos el HTML del modal de detalle
                document.getElementById('detalle-titulo').textContent = `Cita de ${cliente}`;
                document.getElementById('detalle-fecha').textContent = fecha;
                document.getElementById('detalle-monto').textContent = monto;
                
                // Método y banco (concatenamos si hay banco)
                const metodoCompleto = banco ? `${metodo} · ${banco}` : metodo;
                document.getElementById('detalle-metodo').textContent = metodoCompleto;
                document.getElementById('detalle-referencia').textContent = `#${referencia}`;
                document.getElementById('detalle-resultado').textContent = estado;

                // Detalles de la cita
                document.getElementById('detalle-servicio').textContent = servicio;
                document.getElementById('detalle-duracion').textContent = duracion;
                document.getElementById('detalle-fecha-cita').textContent = fechaCita;
                document.getElementById('detalle-horario').textContent = horario;
                document.getElementById('detalle-especialista').textContent = especialista;
                document.getElementById('detalle-contacto').textContent = contacto;

                // Datos del cliente final
                document.getElementById('detalle-cliente-iniciales').textContent = iniciales;
                document.getElementById('detalle-cliente-nombre').textContent = cliente;
                document.getElementById('detalle-cliente-correo').textContent = correo;

                // Lógica para los colores del badge de estado
                const badgeEstado = document.getElementById('detalle-badge-estado');
                badgeEstado.className = 'estado-autorizado'; // Clase base
                
                const estadoLower = estado.toLowerCase();
                if (estadoLower.includes('rechazado')) {
                    badgeEstado.className = 'estado-rechazado';
                } else if (estadoLower.includes('pendiente') || estadoLower.includes('reembolso')) {
                    badgeEstado.className = 'estado-pendiente';
                } else {
                    badgeEstado.className = 'estado-autorizado';
                }

                // Abrimos el modal
                if (modalDetallePago) {
                    modalDetallePago.showModal();
                }
            });
        });
    }

    // Lógica para cerrar el modal de detalle
    if (btnCerrarDetalleX) {
        btnCerrarDetalleX.addEventListener('click', () => {
            if (modalDetallePago) modalDetallePago.close();
        });
    }

    if (btnCerrarDetalleFooter) {
        btnCerrarDetalleFooter.addEventListener('click', () => {
            if (modalDetallePago) modalDetallePago.close();
        });
    }

    if (modalDetallePago) {
        modalDetallePago.addEventListener('click', (e) => {
            if (e.target === modalDetallePago) {
                modalDetallePago.close();
            }
        });
    }
});