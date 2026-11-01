/**
 * MÓDULO: Gestión de Terapeutas - Vista Pública y Dashboard
 */

document.addEventListener("DOMContentLoaded", function() {
    console.log('✅ Módulo de terapeutas cargado');

    // --- LECTURA DE MENSAJES DE ÉXITO (SweetAlert) ---
    // Esto atrapa el mensaje que Laravel envía al guardar o editar un especialista
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

    const form = document.getElementById('form-terapeuta');
    if (form) {
        form.addEventListener('submit', function(e) {
            sincronizarEspecialidades();
            
            // ✅ FORZAR EL ORIGEN JUSTO ANTES DE ENVIAR
            const origenInput = document.getElementById('form-origen');
            if (origenInput) {
                // Si existe el buscador, estamos en la vista de terapeutas
                if (document.getElementById('buscador-terapeutas')) {
                    origenInput.value = 'terapeutas';
                } else {
                    // Si no, estamos en el dashboard
                    origenInput.value = 'dashboard';
                }
            }

            if (!validarContrasenas()) {
                e.preventDefault();
                return false;
            }
        });
    }

    document.querySelectorAll('.modal-zen, .modal-contenedor-alerta-critica').forEach(modal => {
        modal.addEventListener('click', function(e) {
            if (e.target === this) {
                this.close();
            }
        });
    });

    renderizarEstrellas();
    initBuscadorTerapeutas();
});

function getBaseUrl() {
    let baseUrl = window.location.origin;
    if (window.location.pathname.includes('/public')) {
        baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
    }
    return baseUrl;
}

function initBuscadorTerapeutas() {
    const input       = document.getElementById('buscador-terapeutas');
    const limpiarBtn  = document.getElementById('buscador-limpiar');
    const resultados  = document.getElementById('buscador-resultados');
    const sinResult   = document.getElementById('buscador-sin-resultados');
    const grid        = document.querySelector('.grid-terapeutas');

    if (!input || !grid) return;

    const tarjetas = Array.from(grid.querySelectorAll('.tarjeta-terapeuta'));
    if (tarjetas.length === 0) return;

    function normalizar(texto) {
        return (texto || '')
            .toString()
            .toLowerCase()
            .normalize('NFD')
            .replace(/[\u0300-\u036f]/g, '')
            .trim();
    }

    const datosTarjetas = tarjetas.map(tarjeta => {
        const nombre = tarjeta.querySelector('.terapeuta-nombre')?.textContent || '';
        const especialidades = tarjeta.dataset.especialidades || '';
        const buscar = normalizar(`${nombre} ${especialidades}`);
        return { tarjeta, buscar, nombre, especialidades };
    });

    function aplicarFiltro(termino) {
        const q = normalizar(termino);
        let visibles = 0;

        datosTarjetas.forEach(({ tarjeta, buscar }) => {
            const coincide = q === '' || buscar.includes(q);
            if (coincide) {
                tarjeta.classList.remove('is-hidden');
                visibles++;
            } else {
                tarjeta.classList.add('is-hidden');
            }
        });

        if (limpiarBtn) {
            limpiarBtn.hidden = (q === '');
        }

        if (resultados) {
            if (q === '') {
                resultados.hidden = true;
                resultados.innerHTML = '';
            } else {
                resultados.hidden = false;
                const total = datosTarjetas.length;
                if (visibles === 0) {
                    resultados.innerHTML = `Sin resultados para <strong>"${escaparHTML(termino)}"</strong>`;
                } else {
                    resultados.innerHTML = `Mostrando <strong>${visibles}</strong> de <strong>${total}</strong> especialistas`;
                }
            }
        }

        if (sinResult) {
            sinResult.hidden = (visibles !== 0);
        }
    }

    function escaparHTML(str) {
        const div = document.createElement('div');
        div.textContent = str;
        return div.innerHTML;
    }

    input.addEventListener('input', (e) => {
        aplicarFiltro(e.target.value);
    });

    input.addEventListener('keydown', (e) => {
        if (e.key === 'Escape') {
            input.value = '';
            aplicarFiltro('');
            input.blur();
        }
    });

    if (limpiarBtn) {
        limpiarBtn.addEventListener('click', () => {
            input.value = '';
            aplicarFiltro('');
            input.focus();
        });
    }

    aplicarFiltro('');
}

function renderizarEstrellas() {
    document.querySelectorAll('.contenedor-estrellas').forEach(contenedor => {
        const promedio = parseFloat(contenedor.dataset.promedio || 0);
        const total = parseInt(contenedor.dataset.total || 0);

        const estrellasExistentes = contenedor.querySelectorAll('.estrellas span, .estrellas');
        if (estrellasExistentes.length > 0) return;

        const estrellasLlenas = Math.floor(promedio);
        const tieneMedia = (promedio - estrellasLlenas) >= 0.5;

        const spanEstrellas = document.createElement('span');
        spanEstrellas.className = 'estrellas';

        for (let i = 1; i <= 5; i++) {
            const star = document.createElement('span');
            if (i <= estrellasLlenas) {
                star.textContent = '★';
                star.style.color = 'var(--spa-gold)';
            } else if (i === estrellasLlenas + 1 && tieneMedia) {
                star.textContent = '★';
                star.style.color = 'var(--spa-gold)';
                star.style.opacity = '0.55';
            } else {
                star.textContent = '★';
                star.style.color = '#d1c7bd';
            }
            spanEstrellas.appendChild(star);
        }

        contenedor.innerHTML = '';
        contenedor.appendChild(spanEstrellas);

        const valor = document.createElement('span');
        valor.className = 'rating-valor';
        valor.textContent = promedio.toFixed(1);
        contenedor.appendChild(valor);

        const cantidad = document.createElement('span');
        cantidad.className = 'rating-cantidad';
        cantidad.textContent = `(${total})`;
        contenedor.appendChild(cantidad);
    });
}

window.verTerapeutaDetalle = function(id) {
    let tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (!tarjeta) {
        const elemento = document.querySelector(`[data-id="${id}"]`);
        if (elemento) tarjeta = elemento.closest('.tarjeta-terapeuta');
    }
    if (!tarjeta) { alert('Terapeuta no encontrado'); return; }

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
            espContainer.innerHTML = espArray.map(esp => `<span class="badge">${esp}</span>`).join('');
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

        const accionesAdmin = document.getElementById('view-acciones-admin');
        if (accionesAdmin) {
            accionesAdmin.innerHTML = `
                <button type="button" class="btn-zen" style="background:white; border:1px solid #cbd5e1; color:#475569;" onclick="document.getElementById('modal-ver-terapeuta').close(); editarTerapeuta(${id});">Editar</button>
                <button type="button" class="btn-zen btn-baja" onclick="document.getElementById('modal-ver-terapeuta').close(); eliminarTerapeuta(${id});">Dar de Baja</button>
            `;
        }
        modal.showModal();
    }
};

window.abrirModalAgregar = function() {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    if (!modal || !form) return;

    form.reset();
    form.action = `${getBaseUrl()}/admin/crear-trabajador`;
    
    document.getElementById('terapeuta_method').value = 'POST';

    const origenInput = document.getElementById('form-origen');
    if (origenInput) {
        origenInput.value = document.getElementById('buscador-terapeutas') ? 'terapeutas' : 'dashboard';
    }

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

    document.getElementById('campo-password').style.display = 'block';
    document.getElementById('password').required = true;
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').required = true;
    document.getElementById('password_confirmation').value = '';

    modal.showModal();
};

window.editarTerapeuta = function(id) {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    if (!modal || !form) return;

    let tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (!tarjeta) tarjeta = document.querySelector(`.tarjeta-fila[data-id="${id}"]`);

    form.action = `${getBaseUrl()}/admin/trabajador/${id}`;
    document.getElementById('terapeuta_method').value = 'PUT';

    const origenInput = document.getElementById('form-origen');
    if (origenInput) {
        origenInput.value = 'terapeutas';
    }

    document.getElementById('modal-titulo').textContent = 'Editar Información del Especialista';
    document.getElementById('campo-password').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').required = false;
    document.getElementById('password_confirmation').value = '';

    let nombre = '', email = '', telefono = '', descripcion = '', especialidades = '';

    if (tarjeta) {
        nombre = tarjeta.querySelector('.terapeuta-nombre')?.textContent.trim() || '';
        email = tarjeta.querySelector('.terapeuta-email')?.textContent.trim() || '';
        telefono = tarjeta.querySelector('.terapeuta-telefono')?.textContent.replace('Teléfono:', '').trim() || '';
        descripcion = tarjeta.querySelector('.terapeuta-descripcion')?.textContent.trim() || '';
        especialidades = tarjeta.dataset.especialidades || '';
    }

    document.getElementById('nombre').value = nombre;
    document.getElementById('email').value = email;
    document.getElementById('telefono').value = telefono;
    document.getElementById('descripcion').value = descripcion;

    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if (wrapper) {
        wrapper.innerHTML = '';
        const espData = especialidades ? especialidades.split(',') : [];
        if (espData.length === 0 || (espData.length === 1 && espData[0] === '')) {
            wrapper.innerHTML = `<div class="fila-especialidad"><input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required><button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button></div>`;
        } else {
            espData.forEach((esp, index) => {
                const div = document.createElement('div');
                div.className = 'fila-especialidad';
                div.innerHTML = `
                    <input type="text" class="input-especialidad-item" value="${esp.trim()}" required>
                    ${index === 0 ? `<button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>` : `<button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">×</button>`}
                `;
                wrapper.appendChild(div);
            });
        }
    }
    modal.showModal();
};

window.editarTerapeutaDesdeDashboard = function(id, nombre, email, telefono, especialidades, descripcion) {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    if (!modal || !form) return;

    form.action = `${getBaseUrl()}/admin/trabajador/${id}`;
    document.getElementById('terapeuta_method').value = 'PUT';
    document.getElementById('modal-titulo').textContent = 'Editar Información del Especialista';

    const origenInput = document.getElementById('form-origen');
    if (origenInput) origenInput.value = 'dashboard';

    document.getElementById('campo-password').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('password').value = '';
    document.getElementById('password_confirmation').required = false;
    document.getElementById('password_confirmation').value = '';

    document.getElementById('nombre').value = nombre || '';
    document.getElementById('email').value = email || '';
    document.getElementById('telefono').value = telefono || '';
    document.getElementById('descripcion').value = descripcion || '';

    const wrapper = document.getElementById('wrapper-especialidades-lista');
    wrapper.innerHTML = '';
    const espData = especialidades ? especialidades.split(',') : [];
    if (espData.length === 0 || (espData.length === 1 && espData[0] === '')) {
        wrapper.innerHTML = `<div class="fila-especialidad"><input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required><button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button></div>`;
    } else {
        espData.forEach((esp, index) => {
            const div = document.createElement('div');
            div.className = 'fila-especialidad';
            div.innerHTML = `
                <input type="text" class="input-especialidad-item" value="${esp.trim()}" required>
                ${index === 0 ? `<button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>` : `<button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">×</button>`}
            `;
            wrapper.appendChild(div);
        });
    }
    modal.showModal();
};

/**
 * Eliminar especialista
 * Usa fetch para mostrar SweetAlert y luego recargar la página.
 */
window.eliminarTerapeuta = function(id) {
    const modal = document.getElementById('modal-confirmacion-custom');
    
    // Fallback por si el modal de confirmación no existe en la vista actual
    if (!modal) {
        if (confirm('¿Estás seguro de eliminar este especialista?')) {
            const token = document.querySelector('input[name="_token"]')?.value || '';
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = `${getBaseUrl()}/admin/usuario/${id}`;
            form.innerHTML = `<input type="hidden" name="_token" value="${token}"><input type="hidden" name="_method" value="DELETE">`;
            document.body.appendChild(form);
            form.submit();
        }
        return;
    }

    let nombreTerapeuta = '';
    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"], .tarjeta-fila[data-id="${id}"], .dash-user-row[data-id="${id}"]`);
    if (tarjeta) {
        nombreTerapeuta = tarjeta.querySelector('.terapeuta-nombre')?.textContent.trim() || 
                          tarjeta.querySelector('.info-usuario h4')?.textContent.trim() || 
                          tarjeta.querySelector('.dash-user-name h4')?.textContent.trim() || '';
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
        const formData = new FormData();
        formData.append('_token', token);
        formData.append('_method', 'DELETE');

        // Enviamos la petición con fetch
        fetch(`${getBaseUrl()}/admin/usuario/${id}`, {
            method: 'POST', // POST simulando DELETE por el _method
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
            // Mostramos el SweetAlert de éxito
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Excelente!',
                    text: 'Especialista eliminado correctamente del sistema.',
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
                    text: 'No se pudo eliminar al especialista. Intenta de nuevo.',
                    confirmButtonColor: '#c53030'
                });
            } else {
                alert('No se pudo eliminar al especialista.');
            }
        });
    };

    document.getElementById('btn-confirm-cancelar').onclick = function() { modal.close(); };
};

window.agregarCampoEspecialidad = function(valor = '') {
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if (!wrapper) return;
    const div = document.createElement('div');
    div.className = 'fila-especialidad';
    div.innerHTML = `<input type="text" class="input-especialidad-item" value="${valor}" placeholder="Ej. Rejuvenecimiento Facial" required><button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">×</button>`;
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
    if (pass.value !== confirm.value) { alert('❌ Las contraseñas no coinciden.'); pass.focus(); return false; }
    if (pass.value.length > 0 && pass.value.length < 8) { alert('❌ La contraseña debe tener al menos 8 caracteres.'); pass.focus(); return false; }
    return true;
};

window.alternarVisibilidad = function(inputId, btn) {
    const input = document.getElementById(inputId);
    if (!input) return;
    const svg = btn.querySelector('.svg-ojo');
    if (!svg) return;
    if (input.type === 'password') {
        input.type = 'text';
        svg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />`;
    } else {
        input.type = 'password';
        svg.innerHTML = `<path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />`;
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

if (typeof window.Syncro === 'undefined') window.Syncro = {};

window.Syncro.openEsp = function() { if (typeof window.abrirModalAgregar === 'function') window.abrirModalAgregar(); };
window.Syncro.editEsp = function(id, nombre, email, telefono, especialidades, descripcion) {
    if (nombre !== undefined) {
        if (typeof window.editarTerapeutaDesdeDashboard === 'function') window.editarTerapeutaDesdeDashboard(id, nombre, email, telefono, especialidades, descripcion);
    } else {
        if (typeof window.editarTerapeuta === 'function') window.editarTerapeuta(id);
    }
};
window.Syncro.delEsp = function(id) { if (typeof window.eliminarTerapeuta === 'function') window.eliminarTerapeuta(id); };
// ============================================
// VISOR DE FOTO COMPLETA (LIGHTBOX)
// ============================================
window.abrirVisorFoto = function(url, nombre) {
    const visor = document.getElementById('visor-foto-terapeuta');
    const imagen = document.getElementById('visor-foto-imagen');
    const texto = document.getElementById('visor-foto-nombre');
    
    if (visor && imagen) {
        imagen.src = url;
        texto.textContent = nombre;
        visor.showModal();
    }
};

// Permitir cerrar con la tecla Escape
document.addEventListener('DOMContentLoaded', () => {
    const visor = document.getElementById('visor-foto-terapeuta');
    if (visor) {
        visor.addEventListener('cancel', (e) => {
            e.preventDefault();
            visor.close();
        });
    }
});

// ============================================
// MODAL DE RESEÑAS - CONTROLADOR LIMPIO
// ============================================
window.verResenasTerapeuta = function(btn) {
    if (typeof event !== 'undefined' && event) {
        event.stopPropagation();
        event.preventDefault();
    }

    const modal = document.getElementById('modal-resenas');
    const titulo = document.getElementById('resenas-titulo-terapeuta');
    const contenedor = document.getElementById('lista-resenas');
    
    if (!modal) return;

    const nombre = btn.dataset.nombre || 'Especialista';
    let resenas = [];
    try {
        resenas = JSON.parse(btn.dataset.resenas || '[]');
    } catch (e) {
        console.error("Error al parsear las reseñas", e);
    }
    
    titulo.textContent = `Reseñas de ${nombre}`;
    contenedor.innerHTML = '';

    if (resenas.length === 0) {
        contenedor.innerHTML = `
            <div style="text-align: center; padding: 2.5rem 1rem; color: #8c7a6b;">
                <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.2rem; margin: 0;">Este especialista aún no cuenta con opiniones registradas.</p>
                <small style="font-size: 0.8rem; color: #a89a8c; display: block; margin-top: 0.4rem;">¡Sé el primero en calificar su servicio!</small>
            </div>
        `;
    } else {
        resenas.forEach(resena => {
            let estrellasHTML = '';
            const puntuacion = parseInt(resena.puntuacion || 0);
            for (let i = 1; i <= 5; i++) {
                estrellasHTML += i <= puntuacion ? '★' : '<span style="color: #d1c7bd;">★</span>';
            }

            const htmlResena = `
                <div style="border-bottom: 1px solid #efe9e1; padding-bottom: 1.25rem;">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 0.4rem;">
                        <div style="color: #f5b301; font-size: 0.95rem; letter-spacing: 2px;">${estrellasHTML}</div>
                        <span style="font-family: 'Inter', sans-serif; font-size: 0.72rem; color: #a89a8c; font-weight: 500;">${resena.fecha}</span>
                    </div>
                    <p style="font-family: 'Cormorant Garamond', serif; font-size: 1.15rem; font-style: italic; color: #3d2c1e; margin: 0 0 0.5rem 0; line-height: 1.4;">
                        "${resena.comentario}"
                    </p>
                    <span style="font-family: 'Inter', sans-serif; font-size: 0.75rem; font-weight: 700; color: #8b5e3c; text-transform: uppercase; letter-spacing: 0.5px;">
                        ${resena.cliente}
                    </span>
                </div>
            `;
            contenedor.insertAdjacentHTML('beforeend', htmlResena);
        });
    }

    // Forzar visualización centrada con flex
    modal.style.setProperty('display', 'flex', 'important');
};

window.cerrarModalResenas = function() {
    const modal = document.getElementById('modal-resenas');
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');
    }
};

// Cerrar al hacer clic fuera de la caja blanca o presionando Escape
document.addEventListener('DOMContentLoaded', () => {
    const modal = document.getElementById('modal-resenas');
    if (modal) {
        modal.style.setProperty('display', 'none', 'important');

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.style.setProperty('display', 'none', 'important');
            }
        });

        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                modal.style.setProperty('display', 'none', 'important');
            }
        });
    }
});