/**
 * MÓDULO: Dashboard Administrativo - Acciones de Panel, Recepcionistas y Paginación
 * CORREGIDO: Rutas dinámicas para funcionar con o sin /public/
 */

// ============================================
// FUNCIÓN AUXILIAR: DETECTAR URL BASE
// ============================================
function getBaseUrl() {
    let baseUrl = window.location.origin;
    if (window.location.pathname.includes('/public')) {
        baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
    }
    return baseUrl;
}

document.addEventListener("DOMContentLoaded", () => {
    console.log('✅ Dashboard.js cargado');
    console.log('🔗 Base URL detectada:', getBaseUrl());

    let paginaTerapeutas = 1;
    let paginaRecepcionistas = 1;
    const itemsPorPagina = 2;

    const formRecepcionista = document.getElementById("form-recepcionista");

    // --- LECTURA DE MENSAJES DE ÉXITO (SweetAlert) ---
    const mensajeExito = document.getElementById("dashboard-mensaje-exito");
    if (mensajeExito) {
        const texto = mensajeExito.dataset.mensaje;
        if (texto && typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '¡Excelente!',
                text: texto,
                confirmButtonColor: '#8b5e3c',
                confirmButtonText: 'Aceptar'
            });
        }
    }

    // --- VALIDACIÓN DEL FORMULARIO RECEPCIONISTA ---
    if (formRecepcionista) {
        formRecepcionista.addEventListener("submit", (e) => {
            const pass = document.getElementById('recep_password');
            const passConf = document.getElementById('recep_password_confirmation');

            if (pass && pass.value) {
                if (pass.value !== passConf.value) {
                    alert("🚨 Las contraseñas no coinciden.");
                    e.preventDefault();
                    return false;
                }
                if (pass.value.length < 8) {
                    alert("🚨 La contraseña debe tener al menos 8 caracteres.");
                    e.preventDefault();
                    return false;
                }
            }
        });
    }

    // Cerrar modales con click en backdrop
    document.querySelectorAll('.modal-zen, .modal-contenedor-alerta-critica').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.close();
            }
        });
    });

    // Inicializar Syncro si existe en el window
    if (typeof window.Syncro !== 'undefined' && window.Syncro.init) {
        console.log('✅ Inicializando Syncro desde dashboard.js');
        window.Syncro.init();
    } else {
        console.warn('⚠️ Syncro no está definido o no tiene init');
    }

    // Inicializar vistas
    if (typeof actualizarPaginadoresTotales === 'function') {
        actualizarPaginadoresTotales();
    }

    // Inicializar paginadores del dashboard
    initPaginadoresDashboard();
});

// ============================================
// PAGINADOR PROFESIONAL DEL DASHBOARD
// ============================================
/**
 * Inicializa los paginadores de las listas de recepcionistas y especialistas.
 * - 4 items por página por defecto (configurable).
 * - Oculta filas que no pertenecen a la página actual.
 * - Actualiza el texto "Página X de Y".
 * - Deshabilita los botones en los extremos.
 */
function initPaginadoresDashboard() {
    const ITEMS_POR_PAGINA = 4;

    /**
     * Configura un paginador individual.
     * @param {string} idContenedor  ID del div que contiene las filas
     * @param {string} idPaginador   ID del footer del paginador
     */
    function configurarPaginador(idContenedor, idPaginador) {
        const contenedor = document.getElementById(idContenedor);
        const paginador  = document.getElementById(idPaginador);
        if (!contenedor || !paginador) return;

        const filas = Array.from(contenedor.querySelectorAll('.dash-user-row'));
        const total = filas.length;

        // Actualiza el atributo data-total (para ocultar el paginador si no hay filas)
        paginador.dataset.total = total;

        if (total === 0) return;

        const totalPaginas = Math.ceil(total / ITEMS_POR_PAGINA);
        const btnPrev  = paginador.querySelector('[data-pagina="prev"]');
        const btnNext  = paginador.querySelector('[data-pagina="next"]');
        const info     = paginador.querySelector('.dash-paginador-info');

        let paginaActual = 1;

        function renderizar() {
            // Mostrar/ocultar filas
            filas.forEach((fila, index) => {
                const inicio = (paginaActual - 1) * ITEMS_POR_PAGINA;
                const fin    = inicio + ITEMS_POR_PAGINA;
                fila.style.display = (index >= inicio && index < fin) ? 'flex' : 'none';
            });

            // Actualizar texto
            if (info) {
                info.textContent = `Página ${paginaActual} de ${totalPaginas}`;
            }

            // Estado de botones
            if (btnPrev) btnPrev.disabled = (paginaActual <= 1);
            if (btnNext) btnNext.disabled = (paginaActual >= totalPaginas);
        }

        // Listeners (clonamos para evitar duplicados si la función se llama varias veces)
        if (btnPrev) {
            const nuevoPrev = btnPrev.cloneNode(true);
            btnPrev.parentNode.replaceChild(nuevoPrev, btnPrev);
            nuevoPrev.addEventListener('click', () => {
                if (paginaActual > 1) {
                    paginaActual--;
                    renderizar();
                }
            });
        }

        if (btnNext) {
            const nuevoNext = btnNext.cloneNode(true);
            btnNext.parentNode.replaceChild(nuevoNext, btnNext);
            nuevoNext.addEventListener('click', () => {
                if (paginaActual < totalPaginas) {
                    paginaActual++;
                    renderizar();
                }
            });
        }

        // Render inicial
        renderizar();
    }

    configurarPaginador('lista-recepcionistas', 'paginador-recepcionistas');
    configurarPaginador('lista-especialistas',  'paginador-especialistas');
}

// ============================================
// FUNCIONES GLOBALES - RECEPCIONISTAS
// ============================================

/**
 * Abrir modal para agregar recepcionista
 * RUTA: POST /admin/crear-recepcionista
 */
window.abrirModalRecepcionista = function() {
    const modal = document.getElementById('modal-recepcionista');
    const form = document.getElementById('form-recepcionista');

    if (!modal || !form) return;

    document.getElementById('modal-recepcionista-titulo').textContent = 'Agregar Nuevo Recepcionista';
    form.reset();

    // ✅ RUTA DINÁMICA
    form.action = `${getBaseUrl()}/admin/crear-recepcionista`;
    delete form.dataset.idEditando;

    const methodInput = document.getElementById('recep_method');
    if (methodInput) {
        methodInput.value = 'POST';
    }

    const campoPass = document.getElementById('recep-campo-password');
    if (campoPass) {
        campoPass.style.display = 'grid';
    }

    const passInput = document.getElementById('recep_password');
    const passConfInput = document.getElementById('recep_password_confirmation');
    if (passInput) {
        passInput.required = true;
        passInput.placeholder = 'Mínimo 8 caracteres';
        passInput.value = '';
    }
    if (passConfInput) {
        passConfInput.required = true;
        passConfInput.placeholder = 'Repita la contraseña';
        passConfInput.value = '';
    }

    const avatar = document.getElementById('previsualizacion-avatar-recepcionista');
    if (avatar) avatar.textContent = 'RE';

    modal.showModal();
};

/**
 * Editar recepcionista
 * RUTA: PUT /admin/recepcionista/{id}/actualizar
 */
window.editarRecepcionista = function(id, nombre, email, telefono) {
    const modal = document.getElementById('modal-recepcionista');
    const form = document.getElementById('form-recepcionista');

    if (!modal || !form) return;

    document.getElementById('modal-recepcionista-titulo').textContent = 'Editar Recepcionista';

    // ✅ RUTA DINÁMICA
    form.action = `${getBaseUrl()}/admin/recepcionista/${id}/actualizar`;
    form.dataset.idEditando = id;

    const methodInput = document.getElementById('recep_method');
    if (methodInput) {
        methodInput.value = 'PUT';
    }

    document.getElementById('recep_name').value = nombre || '';
    document.getElementById('recep_email').value = email || '';
    document.getElementById('recep_phone').value = telefono || '';

    const campoPass = document.getElementById('recep-campo-password');
    if (campoPass) {
        campoPass.style.display = 'none';
    }

    const passInput = document.getElementById('recep_password');
    const passConfInput = document.getElementById('recep_password_confirmation');
    if (passInput) {
        passInput.required = false;
        passInput.placeholder = 'Dejar vacío para mantener';
        passInput.value = '';
    }
    if (passConfInput) {
        passConfInput.required = false;
        passConfInput.placeholder = 'Dejar vacío para mantener';
        passConfInput.value = '';
    }

    modal.showModal();
};

/**
 * Eliminar recepcionista
 * RUTA: DELETE /admin/usuario/{id}
 * (Unificada con la ruta de eliminar usuario)
 */
window.eliminarRecepcionista = function(id) {
    const modal = document.getElementById('modal-confirmacion-custom');
    
    // Si por alguna razón el modal no existe, usamos el método clásico
    if (!modal) {
        if (confirm('¿Estás seguro de eliminar este recepcionista?')) {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${getBaseUrl()}/admin/usuario/${id}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }

    let nombreRecepcionista = '';
    const tarjeta = document.querySelector(`.dash-user-row[data-id="${id}"]`);
    if (tarjeta) {
        nombreRecepcionista = tarjeta.querySelector('.dash-user-name h4')?.textContent.trim() || '';
    }

    const nombreTexto = nombreRecepcionista ? `"${nombreRecepcionista.toUpperCase()}"` : 'este recepcionista';

    document.getElementById('confirm-alerta-titulo').textContent = '¿Remover recepcionista del equipo?';
    document.getElementById('confirm-alerta-mensaje').textContent = `¿Estás seguro de eliminar permanentemente a ${nombreTexto} del sistema? Esta operación no se puede deshacer.`;

    modal.showModal();

    const btnAceptar = document.getElementById('btn-confirm-aceptar');
    const btnCancelar = document.getElementById('btn-confirm-cancelar');

    const nuevoAceptar = btnAceptar.cloneNode(true);
    btnAceptar.parentNode.replaceChild(nuevoAceptar, btnAceptar);

    nuevoAceptar.onclick = function() {
        modal.close();
        
        const token = document.querySelector('input[name="_token"]')?.value || '';
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('_method', 'DELETE');

        // Enviamos la petición con fetch para poder mostrar el SweetAlert sin recargar de golpe
        fetch(`${getBaseUrl()}/admin/usuario/${id}`, {
            method: 'POST', // Usamos POST simulando DELETE
            headers: {
                'X-CSRF-TOKEN': token,
                'Accept': 'application/json'
            },
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Error al eliminar');
            return response.json().catch(() => ({}));
        })
        .then(() => {
            // Mostramos el SweetAlert verde
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Excelente!',
                    text: 'Recepcionista eliminado correctamente del sistema.',
                    confirmButtonColor: '#8b5e3c',
                    confirmButtonText: 'Aceptar',
                    allowOutsideClick: false
                }).then(() => {
                    window.location.reload();
                });
            } else {
                window.location.reload();
            }
        })
        .catch(error => {
            console.error(error);
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo eliminar al recepcionista. Intenta de nuevo.',
                    confirmButtonColor: '#c53030'
                });
            } else {
                alert('No se pudo eliminar al recepcionista.');
            }
        });
    };

    btnCancelar.onclick = function() {
        modal.close();
    };
};

// ============================================
// PAGINACIÓN (COMPATIBILIDAD LEGACY)
// ============================================

window.paginarBloque = function(contenedorId, numPagina, btnPrevId, btnNextId, infoId) {
    const contenedor = document.getElementById(contenedorId);
    if (!contenedor) return;

    const items = contenedor.querySelectorAll('.tarjeta-fila');
    const totalItems = items.length;
    const itemsPorPagina = 2;
    const totalPaginas = Math.ceil(totalItems / itemsPorPagina);

    if (numPagina < 1) numPagina = 1;
    if (numPagina > totalPaginas) numPagina = totalPaginas;

    items.forEach((item, index) => {
        const start = (numPagina - 1) * itemsPorPagina;
        const end = start + itemsPorPagina;
        if (index >= start && index < end) {
            item.style.display = 'flex';
        } else {
            item.style.display = 'none';
        }
    });

    const btnPrev = document.getElementById(btnPrevId);
    const btnNext = document.getElementById(btnNextId);
    const info = document.getElementById(infoId);

    if (btnPrev) btnPrev.disabled = numPagina <= 1;
    if (btnNext) btnNext.disabled = numPagina >= totalPaginas;
    if (info) info.textContent = `Página ${numPagina} de ${totalPaginas || 1}`;

    return numPagina;
};

// ============================================
// SYNCRO - OBJETO DE COMPATIBILIDAD
// ============================================

window.Syncro = {
    init: function() {
        console.log('✅ Syncro.init ejecutado');
        const btnAddRecep = document.getElementById('btn-add-recep');
        const btnAddEsp = document.getElementById('btn-add-esp');

        if (btnAddRecep) {
            const newBtn = btnAddRecep.cloneNode(true);
            btnAddRecep.parentNode.replaceChild(newBtn, btnAddRecep);
            newBtn.addEventListener('click', this.openRecep);
        }
        if (btnAddEsp) {
            const newBtn = btnAddEsp.cloneNode(true);
            btnAddEsp.parentNode.replaceChild(newBtn, btnAddEsp);
            newBtn.addEventListener('click', this.openEsp);
        }
    },

    openRecep: function() {
        if (typeof window.abrirModalRecepcionista === 'function') {
            window.abrirModalRecepcionista();
        } else {
            console.warn('abrirModalRecepcionista no está definida');
        }
    },

    editRecep: function(id, n, c, t) {
        if (typeof window.editarRecepcionista === 'function') {
            window.editarRecepcionista(id, n, c, t);
        } else {
            console.warn('editarRecepcionista no está definida');
        }
    },

    delRecep: function(id) {
        if (typeof window.eliminarRecepcionista === 'function') {
            window.eliminarRecepcionista(id);
        } else {
            console.warn('eliminarRecepcionista no está definida');
        }
    },

    openEsp: function() {
        console.log('✅ Syncro.openEsp ejecutado');
        if (typeof window.abrirModalAgregar === 'function') {
            window.abrirModalAgregar();
        } else {
            console.warn('abrirModalAgregar no está definida');
        }
    },

    editEsp: function(id) {
        if (typeof window.editarTerapeuta === 'function') {
            window.editarTerapeuta(id);
        } else {
            console.warn('editarTerapeuta no está definida');
        }
    },

    delEsp: function(id) {
        if (typeof window.eliminarTerapeuta === 'function') {
            window.eliminarTerapeuta(id);
        } else {
            console.warn('eliminarTerapeuta no está definida');
        }
    }
};

// ============================================
// RE-INICIALIZAR PAGINADOR TRAS CAMBIOS DINÁMICOS
// ============================================
/**
 * Expone la función globalmente para que otros módulos
 * (ej. terapeutas.js tras eliminar) puedan refrescar el paginador.
 */
window.refrescarPaginadoresDashboard = function() {
    initPaginadoresDashboard();
};

console.log('✅ Dashboard.js cargado completamente');