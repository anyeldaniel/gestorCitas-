document.addEventListener('DOMContentLoaded', () => {
    if (window.lucide) window.lucide.createIcons();

    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content
                    || document.querySelector('input[name="_token"]')?.value;

    /* ============================================================
       1. AUTO-SUBMIT DE FILTROS
       ============================================================ */
    const formFiltros = document.querySelector('.agenda-filtros');
    if (formFiltros) {
        formFiltros.querySelectorAll('select, input[type="date"]').forEach((el) => {
            el.addEventListener('change', () => formFiltros.submit());
        });

        const buscador = formFiltros.querySelector('input[name="search"]');
        if (buscador) {
            let ultimo = buscador.value;
            const enviar = () => {
                if (buscador.value.trim() !== ultimo.trim()) {
                    ultimo = buscador.value;
                    formFiltros.submit();
                }
            };
            buscador.addEventListener('blur', enviar);
            buscador.addEventListener('keydown', (e) => {
                if (e.key === 'Enter') { e.preventDefault(); enviar(); }
            });
        }
    }

    /* ============================================================
       2. HELPERS DE MODALES
       ============================================================ */
    const cerrar = (el) => el?.closest('dialog')?.close();

    document.querySelectorAll('[data-close-modal]').forEach((btn) => {
        btn.addEventListener('click', () => cerrar(btn));
    });

    // Cerrar al hacer click fuera de la tarjeta
    document.querySelectorAll('dialog.modal-agenda, dialog.modal-alerta-centro').forEach((dlg) => {
        dlg.addEventListener('click', (e) => {
            const rect = dlg.querySelector('article')?.getBoundingClientRect();
            if (!rect) return;
            const dentro =
                e.clientX >= rect.left && e.clientX <= rect.right &&
                e.clientY >= rect.top  && e.clientY <= rect.bottom;
            if (!dentro) dlg.close();
        });
    });

    /* ============================================================
       3. ALERTA CENTRAL
       ============================================================ */
    const alertaModal   = document.getElementById('modal-alerta');
    const alertaIcono   = document.getElementById('alerta-icono');
    const alertaTitulo  = document.getElementById('alerta-titulo');
    const alertaMensaje = document.getElementById('alerta-mensaje');

    const mostrarAlerta = (titulo, mensaje, tipo = 'success') => {
        alertaTitulo.textContent  = titulo;
        alertaMensaje.textContent = mensaje;

        alertaIcono.className = 'alerta-icono alerta-' + tipo;
        alertaIcono.innerHTML = tipo === 'success'
            ? '<i data-lucide="check-circle-2"></i>'
            : tipo === 'error'
                ? '<i data-lucide="alert-triangle"></i>'
                : '<i data-lucide="info"></i>';

        if (window.lucide) window.lucide.createIcons();
        alertaModal.showModal();
    };

    /* ============================================================
       4. MODAL CANCELAR
       ============================================================ */
    const modalCancelar   = document.getElementById('modal-cancelar-cita');
    const formCancelar    = document.getElementById('form-cancelar-cita');
    const cancelCliente   = document.getElementById('cancel-cliente');
    const cancelServicio  = document.getElementById('cancel-servicio');
    const cancelFecha     = document.getElementById('cancel-fecha');
    const cancelHora      = document.getElementById('cancel-hora');
    const cancelAvatar    = document.getElementById('cancel-avatar');

    document.querySelectorAll('[data-declinar]').forEach((btn) => {
        btn.addEventListener('click', () => {
            const citaId   = btn.dataset.cita;
            const cliente  = btn.dataset.cliente;
            const servicio = btn.dataset.servicio;
            const fecha    = btn.dataset.fecha;
            const hora     = btn.dataset.hora;

            formCancelar.action = `/agenda/cita/${citaId}/cancelar`;
            cancelCliente.textContent  = cliente;
            cancelServicio.textContent = servicio;
            cancelFecha.textContent    = fecha;
            cancelHora.textContent     = hora;
            cancelAvatar.textContent   = iniciales(cliente);

            formCancelar.querySelector('textarea[name="motivo"]').value = '';
            modalCancelar.showModal();
        });
    });

    formCancelar?.addEventListener('submit', async (e) => {
        e.preventDefault();
        const submit = formCancelar.querySelector('button[type="submit"]');
        submit.disabled = true;

        try {
            const res = await fetch(formCancelar.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
                body: new FormData(formCancelar),
            });
            const data = await res.json();

            if (!res.ok || !data.ok) {
                mostrarAlerta('No se pudo cancelar', data.mensaje || 'Revisa el motivo.', 'error');
                return;
            }

            modalCancelar.close();
            mostrarAlerta('Cita cancelada', data.mensaje, 'success');
            setTimeout(() => location.reload(), 900);
        } catch (err) {
            mostrarAlerta('Error de red', 'Revisa tu conexión e inténtalo de nuevo.', 'error');
        } finally {
            submit.disabled = false;
        }
    });

    /* ============================================================
       5. MODAL CAMBIAR ESTADO
       ============================================================ */
    const modalEstado = document.getElementById('modal-cambiar-estado');
    let citaEstadoId  = null;

    document.querySelectorAll('[data-cambiar-estado]').forEach((btn) => {
        btn.addEventListener('click', () => {
            citaEstadoId = btn.dataset.cita;
            modalEstado.showModal();
        });
    });

    document.querySelectorAll('.btn-estado-opcion').forEach((btn) => {
        btn.addEventListener('click', async () => {
            if (!citaEstadoId) return;
            const nuevoEstado = btn.dataset.estado;

            try {
                const res = await fetch(`/agenda/cita/${citaEstadoId}/estado`, {
                    method: 'PATCH',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                    },
                    body: JSON.stringify({ estado: nuevoEstado }),
                });
                const data = await res.json();

                if (!res.ok || !data.ok) {
                    mostrarAlerta('No se pudo actualizar', data.mensaje || 'Inténtalo de nuevo.', 'error');
                    return;
                }

                modalEstado.close();

                const tarjeta = document.querySelector(`[data-cita-id="${citaEstadoId}"]`);
                if (tarjeta) {
                    tarjeta.className = 'tarjeta-cita ' + data.clase;
                    const badge = tarjeta.querySelector('[data-badge]');
                    if (badge) {
                        badge.className = 'badge-estado ' + data.clase;
                        const txt = badge.querySelector('[data-badge-texto]');
                        if (txt) txt.textContent = data.etiqueta;
                    }
                }

                mostrarAlerta('Estado actualizado', data.mensaje, 'success');
            } catch (err) {
                mostrarAlerta('Error de red', 'No pudimos contactar al servidor.', 'error');
            }
        });
    });

    /* ============================================================
       6. HELPER: INICIALES
       ============================================================ */
    function iniciales(nombre) {
        if (!nombre) return '—';
        return nombre.trim().split(/\s+/).slice(0, 2)
            .map(p => p.charAt(0).toUpperCase()).join('');
    }
});