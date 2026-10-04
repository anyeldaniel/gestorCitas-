/**
 * MÓDULO: Gestión de Terapeutas - Vista Pública y Dashboard
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log('✅ Módulo de terapeutas cargado');

    const form = document.getElementById('form-terapeuta');
    if (form) {
        form.addEventListener('submit', function(e) {
            sincronizarEspecialidades();
            if (!validarContrasenas()) {
                e.preventDefault();
                return false;
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
});

// ============================================
// FUNCIONES GLOBALES - TERAPEUTAS
// ============================================

window.verTerapeutaDetalle = function(id) {
    let tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    
    if (!tarjeta) {
        const elemento = document.querySelector(`[data-id="${id}"]`);
        if (elemento) {
            tarjeta = elemento.closest('.tarjeta-terapeuta');
        }
    }
    
    if (!tarjeta) {
        alert('Terapeuta no encontrado');
        return;
    }
    
    const nombre = tarjeta.querySelector('.terapeuta-nombre')?.textContent.trim() || 'Sin nombre';
    const especialidades = tarjeta.querySelector('.contenedor-tags-especialidades .tag-especialidad')?.textContent.trim() || 'Sin especialidad';
    const foto = tarjeta.querySelector('img')?.src || null;
    const telefono = tarjeta.querySelector('.terapeuta-telefono')?.textContent.trim() || 'No disponible';
    const email = tarjeta.querySelector('.terapeuta-email')?.textContent.trim() || 'No disponible';
    const descripcion = tarjeta.querySelector('.terapeuta-descripcion')?.textContent.trim() || 'Sin descripción';
    
    const modal = document.getElementById('modal-ver-terapeuta');
    if (modal) {
        document.getElementById('view-terapeuta-nombre').textContent = nombre;
        document.getElementById('view-terapeuta-telefono').textContent = telefono;
        document.getElementById('view-terapeuta-email').textContent = email;
        document.getElementById('view-terapeuta-descripcion').textContent = descripcion;
        
        const espContainer = document.getElementById('view-terapeuta-especialidades');
        if (especialidades && especialidades !== 'Sin especialidad') {
            const espArray = especialidades.split(',').map(e => e.trim());
            espContainer.innerHTML = espArray.map(esp => 
                `<span class="badge">${esp}</span>`
            ).join('');
        } else {
            espContainer.innerHTML = '<span style="color: var(--gris-texto); font-size: 0.85rem;">Sin especialidades registradas</span>';
        }
        
        const fotoContainer = document.getElementById('view-terapeuta-foto');
        if (foto) {
            fotoContainer.innerHTML = `<img src="${foto}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
            fotoContainer.style.background = 'transparent';
        } else {
            const iniciales = nombre.split(' ').map(n => n[0]).join('').toUpperCase().substring(0, 2);
            fotoContainer.innerHTML = iniciales;
            fotoContainer.style.background = '#e8e0d6';
            fotoContainer.style.display = 'flex';
            fotoContainer.style.alignItems = 'center';
            fotoContainer.style.justifyContent = 'center';
            fotoContainer.style.fontSize = '1.8rem';
            fotoContainer.style.fontWeight = '600';
            fotoContainer.style.color = 'var(--color-marron-oscuro)';
        }
        
        modal.showModal();
    } else {
        alert(`Perfil de: ${nombre}\nEspecialidad: ${especialidades}\nTeléfono: ${telefono}\nEmail: ${email}\nDescripción: ${descripcion}`);
    }
};

/**
 * Abrir modal para agregar terapeuta
 * RUTA: POST /admin/crear-trabajador
 */
window.abrirModalAgregar = function() {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    
    if (!modal || !form) {
        console.error('❌ No se encontró el modal de terapeuta');
        return;
    }
    
    form.reset();
    form.action = '/admin/crear-trabajador';
    
    const methodInput = document.getElementById('terapeuta_method');
    if (methodInput) {
        methodInput.value = 'POST';
    }
    
    const metodoPut = document.getElementById('metodo-put-laravel');
    if (metodoPut) metodoPut.remove();
    
    document.getElementById('modal-titulo').textContent = 'Registrar Nuevo Especialista';
    
    const avatar = document.getElementById('previsualizacion-avatar-terapeuta');
    if (avatar) {
        avatar.innerHTML = 'TF';
        avatar.style.background = '#e8e0d6';
        avatar.style.display = 'flex';
        avatar.style.alignItems = 'center';
        avatar.style.justifyContent = 'center';
        avatar.style.fontSize = '1.3rem';
        avatar.style.fontWeight = '600';
        avatar.style.color = 'var(--color-marron-oscuro)';
    }
    
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if (wrapper) {
        wrapper.innerHTML = `
            <div class="fila-especialidad">
                <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required>
                <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
            </div>
        `;
    }
    
    const campoPassword = document.getElementById('campo-password');
    if (campoPassword) campoPassword.style.display = 'block';
    
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    if (passwordInput) {
        passwordInput.required = true;
        passwordInput.placeholder = 'Mínimo 8 caracteres';
        passwordInput.value = '';
    }
    if (confirmInput) {
        confirmInput.required = true;
        confirmInput.placeholder = 'Repita la contraseña';
        confirmInput.value = '';
    }
    
    modal.showModal();
};

/**
 * Editar terapeuta
 * RUTA: PUT /admin/trabajador/{id}
 */
window.editarTerapeuta = function(id) {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    
    if (!modal || !form) {
        console.error('❌ Modal o formulario no encontrados');
        return;
    }

    let tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    
    if (!tarjeta) {
        const fila = document.querySelector(`.tarjeta-fila[data-id="${id}"]`);
        if (fila) {
            tarjeta = fila;
        } else {
            const btn = document.querySelector(`button[onclick*="editarTerapeuta(${id})"]`) ||
                        document.querySelector(`button[onclick*="editEsp(${id})"]`);
            if (btn) {
                tarjeta = btn.closest('.tarjeta-fila') || btn.closest('.tarjeta-terapeuta');
            }
        }
    }

    form.action = `/admin/trabajador/${id}`;
    
    const methodInput = document.getElementById('terapeuta_method');
    if (methodInput) {
        methodInput.value = 'PUT';
    }
    
    document.getElementById('modal-titulo').textContent = 'Editar Información del Especialista';
    
    const campoPassword = document.getElementById('campo-password');
    if (campoPassword) campoPassword.style.display = 'none';
    
    const passwordInput = document.getElementById('password');
    const confirmInput = document.getElementById('password_confirmation');
    if (passwordInput) {
        passwordInput.required = false;
        passwordInput.placeholder = 'Dejar vacío para mantener';
        passwordInput.value = '';
    }
    if (confirmInput) {
        confirmInput.required = false;
        confirmInput.placeholder = 'Dejar vacío para mantener';
        confirmInput.value = '';
    }
    
    let nombre = '';
    let email = '';
    let telefono = '';
    let descripcion = '';
    let especialidades = '';

    if (tarjeta) {
        nombre = tarjeta.querySelector('.terapeuta-nombre')?.textContent.trim() || 
                  tarjeta.querySelector('h2')?.textContent.trim() || '';
        email = tarjeta.querySelector('.terapeuta-email')?.textContent.trim() || '';
        telefono = tarjeta.querySelector('.terapeuta-telefono')?.textContent.replace('Teléfono:', '').trim() || '';
        descripcion = tarjeta.querySelector('.terapeuta-descripcion')?.textContent.trim() || '';
        especialidades = tarjeta.dataset.especialidades || '';
        
        if (!nombre) {
            const infoUsuario = tarjeta.querySelector('.info-usuario');
            if (infoUsuario) {
                nombre = infoUsuario.querySelector('h4')?.textContent.trim() || '';
                const badges = infoUsuario.querySelectorAll('.badge');
                if (badges.length > 0) {
                    especialidades = Array.from(badges).map(b => b.textContent.trim()).join(',');
                }
                email = tarjeta.querySelector('.terapeuta-email')?.textContent.trim() || '';
                telefono = tarjeta.querySelector('.terapeuta-telefono')?.textContent.trim() || '';
                descripcion = tarjeta.querySelector('.terapeuta-descripcion')?.textContent.trim() || '';
            }
        }
    }

    if (!nombre) {
        const nombreElement = document.querySelector(`.tarjeta-fila[data-id="${id}"] .info-usuario h4`);
        if (nombreElement) {
            nombre = nombreElement.textContent.trim();
        } else {
            nombre = `Especialista #${id}`;
        }
    }

    document.getElementById('nombre').value = nombre;
    document.getElementById('email').value = email || '';
    document.getElementById('telefono').value = telefono || '';
    document.getElementById('descripcion').value = descripcion || '';

    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if (wrapper) {
        wrapper.innerHTML = '';
        const espData = especialidades ? especialidades.split(',') : [];
        
        if (espData.length === 0 || (espData.length === 1 && espData[0] === '')) {
            wrapper.innerHTML = `
                <div class="fila-especialidad">
                    <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required>
                    <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
                </div>
            `;
        } else {
            espData.forEach((esp, index) => {
                const div = document.createElement('div');
                div.className = 'fila-especialidad';
                if (index === 0) {
                    div.innerHTML = `
                        <input type="text" class="input-especialidad-item" value="${esp.trim()}" required>
                        <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
                    `;
                } else {
                    div.innerHTML = `
                        <input type="text" class="input-especialidad-item" value="${esp.trim()}" required>
                        <button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">×</button>
                    `;
                }
                wrapper.appendChild(div);
            });
        }
    }

    modal.showModal();
};

/**
 * Eliminar terapeuta
 * RUTA: DELETE /admin/usuario/{id}
 * 
 * ✅ ALERTA UNIFICADA CON EL ESTILO DEL CATÁLOGO
 */
window.eliminarTerapeuta = function(id) {
    const modal = document.getElementById('modal-confirmacion-custom');
    
    if (!modal) {
        if (confirm('¿Estás seguro de eliminar este especialista?')) {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `/admin/usuario/${id}`;
            form.innerHTML = `
                <input type="hidden" name="_token" value="${token}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }

    // Buscar el nombre del terapeuta para personalizar el mensaje
    let nombreTerapeuta = '';
    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"], .tarjeta-fila[data-id="${id}"]`);
    if (tarjeta) {
        nombreTerapeuta = tarjeta.querySelector('.terapeuta-nombre')?.textContent.trim() || 
                          tarjeta.querySelector('.info-usuario h4')?.textContent.trim() || '';
    }
    
    const nombreTexto = nombreTerapeuta ? `"${nombreTerapeuta.toUpperCase()}"` : 'este especialista';
    
    document.getElementById('confirm-alerta-titulo').textContent = '¿Remover especialista del equipo?';
    document.getElementById('confirm-alerta-mensaje').textContent = `¿Estás seguro de eliminar permanentemente a ${nombreTexto} del sistema? Esta operación no se puede deshacer.`;
    
    modal.showModal();

    const btnAceptar = document.getElementById('btn-confirm-aceptar');
    const nuevoAceptar = btnAceptar.cloneNode(true);
    btnAceptar.parentNode.replaceChild(nuevoAceptar, btnAceptar);

    nuevoAceptar.onclick = function() {
        modal.close();
        const token = document.querySelector('input[name="_token"]')?.value || '';
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/admin/usuario/${id}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${token}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    document.getElementById('btn-confirm-cancelar').onclick = function() {
        modal.close();
    };
};

// ============================================
// FUNCIONES DE UTILIDAD (sin cambios)
// ============================================

window.agregarCampoEspecialidad = function(valor = '') {
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if (!wrapper) return;
    
    const filas = wrapper.querySelectorAll('.fila-especialidad');
    const ultimaFila = filas[filas.length - 1];
    
    if (ultimaFila) {
        const input = ultimaFila.querySelector('input');
        if (input && input.value.trim() === '') {
            input.focus();
            return;
        }
    }
    
    const div = document.createElement('div');
    div.className = 'fila-especialidad';
    div.innerHTML = `
        <input type="text" class="input-especialidad-item" value="${valor}" placeholder="Ej. Rejuvenecimiento Facial" required>
        <button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">×</button>
    `;
    wrapper.appendChild(div);
};

window.sincronizarEspecialidades = function() {
    const inputs = document.querySelectorAll('.input-especialidad-item');
    const lista = [];
    inputs.forEach(input => {
        const val = input.value.trim();
        if (val) lista.push(val);
    });
    const hidden = document.getElementById('especialidades_hidden');
    if (hidden) hidden.value = lista.join(',');
};

window.validarContrasenas = function() {
    const pass = document.getElementById('password');
    const confirm = document.getElementById('password_confirmation');
    
    if (!pass || !confirm) return true;
    
    if (pass.value === '' && confirm.value === '') return true;
    
    if (pass.value !== confirm.value) {
        alert('❌ Las contraseñas no coinciden.');
        pass.focus();
        return false;
    }
    
    if (pass.value.length > 0 && pass.value.length < 8) {
        alert('❌ La contraseña debe tener al menos 8 caracteres.');
        pass.focus();
        return false;
    }
    
    return true;
};

window.alternarVisibilidad = function(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    
    const svg = btn.querySelector('.svg-ojo');
    if (!svg) return;
    
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
        `;
    } else {
        input.type = 'password';
        svg.innerHTML = `
            <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
            <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
        `;
    }
};

window.previsualizarImagen = function(input, previewId) {
    const preview = document.getElementById(previewId);
    if (!preview || !input.files || !input.files[0]) return;
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.innerHTML = `<img src="${e.target.result}" style="width:100%;height:100%;border-radius:50%;object-fit:cover;">`;
        preview.style.background = 'transparent';
        preview.style.display = 'flex';
        preview.style.alignItems = 'center';
        preview.style.justifyContent = 'center';
    };
    reader.readAsDataURL(input.files[0]);
};

window.cerrarModal = function() {
    document.querySelectorAll('dialog[open]').forEach(d => d.close());
};

// ============================================
// SYNCRO - COMPATIBILIDAD CON DASHBOARD
// ============================================

if (typeof window.Syncro === 'undefined') {
    window.Syncro = {};
}

window.Syncro.openEsp = function() {
    if (typeof window.abrirModalAgregar === 'function') {
        window.abrirModalAgregar();
    }
};

window.Syncro.editEsp = function(id) {
    if (typeof window.editarTerapeuta === 'function') {
        window.editarTerapeuta(id);
    }
};

window.Syncro.delEsp = function(id) {
    if (typeof window.eliminarTerapeuta === 'function') {
        window.eliminarTerapeuta(id);
    }
};

console.log('✅ Funciones de terapeutas cargadas correctamente');