/**
 * MÓDULO: Dashboard Administrativo - Acciones de Panel, Recepcionistas y Paginación
 * RUTAS CORREGIDAS según web.php
 */

document.addEventListener("DOMContentLoaded", () => {
    let paginaTerapeutas = 1;
    let paginaRecepcionistas = 1;
    const itemsPorPagina = 2;

    const formRecepcionista = document.getElementById("form-recepcionista");

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
        window.Syncro.init();
    }
    
    // Inicializar vistas
    if (typeof actualizarPaginadoresTotales === 'function') {
        actualizarPaginadoresTotales();
    }
});

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
    
    form.action = '/admin/crear-recepcionista';
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
    
    form.action = `/admin/recepcionista/${id}/actualizar`;
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
 * RUTA: DELETE /admin/eliminar-recepcionista/{id}
 * 
 * ✅ ALERTA UNIFICADA CON EL ESTILO DEL CATÁLOGO
 */
window.eliminarRecepcionista = function(id) {
    const modal = document.getElementById('modal-confirmacion-custom');
    if (!modal) {
        if (confirm('¿Estás seguro de eliminar este recepcionista?')) {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/eliminar-recepcionista/${id}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }

    // Buscar el nombre del recepcionista para personalizar el mensaje
    let nombreRecepcionista = '';
    const tarjeta = document.querySelector(`.tarjeta-fila[data-id="${id}"]`);
    if (tarjeta) {
        nombreRecepcionista = tarjeta.querySelector('.info-usuario h4')?.textContent.trim() || '';
    }
    
    const nombreTexto = nombreRecepcionista ? `"${nombreRecepcionista.toUpperCase()}"` : 'este recepcionista';
    
    document.getElementById('confirm-alerta-titulo').textContent = '¿Remover recepcionista del equipo?';
    document.getElementById('confirm-alerta-mensaje').textContent = `¿Estás seguro de eliminar permanentemente a ${nombreTexto} del sistema? Esta operación no se puede deshacer.`;

    modal.showModal();

    const btnAceptar = document.getElementById('btn-confirm-aceptar');
    const btnCancelar = document.getElementById('btn-confirm-cancelar');

    // Clonar para limpiar listeners previos
    const nuevoAceptar = btnAceptar.cloneNode(true);
    btnAceptar.parentNode.replaceChild(nuevoAceptar, btnAceptar);

    nuevoAceptar.onclick = function() {
        modal.close();
        const token = document.querySelector('input[name="_token"]')?.value || '';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/eliminar-recepcionista/${id}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    btnCancelar.onclick = function() {
        modal.close();
    };
};

// ============================================
// PAGINACIÓN
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