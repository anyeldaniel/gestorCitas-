/**
 * MÓDULO: Gestión de Terapeutas e Interacciones de Interfaz Nativas del Dashboard Administrativo 
 */

document.addEventListener("DOMContentLoaded", () => {
    const formTerapeutaModal = document.getElementById("form-terapeuta");

    if (formTerapeutaModal) {
        formTerapeutaModal.addEventListener("submit", (e) => { 
            if (!validarContrasenas()) {
                e.preventDefault();
                return;
            }

            // Consolidar especialidades en un string separado por comas para Anyel
            const inputsEsp = formTerapeutaModal.querySelectorAll(".input-especialidad-item");
            const listaEspecialidades = [];
            inputsEsp.forEach(input => {
                if(input.value.trim() !== "") listaEspecialidades.push(input.value.trim());
            });
            document.getElementById("especialidades_hidden").value = listaEspecialidades.join(", ");

            const title = document.getElementById('modal-titulo').textContent;
            if (title.includes("Editar")) {
                e.preventDefault();
                
                const actionUrl = formTerapeutaModal.action;
                
                // === MODIFICADO LIGERAMENTE PARA EL BACKEND: Ajustamos su búsqueda para la ruta de Laravel ===
                const match = actionUrl.match(/\/admin\/trabajador\/(\d+)/);
                // ==============================================================================================
                
                if (match) {
                    const idEditando = match[1];
                    const tarjetas = document.querySelectorAll(`.tarjeta-terapeuta[data-id="${idEditando}"]`);
                    
                    // Extraer los datos editados para refrescar la vista en caliente
                    const nombre = document.getElementById('nombre').value;
                    const telefono = document.getElementById('telefono').value;
                    const email = document.getElementById('email').value;
                    const descripcion = document.getElementById('descripcion').value;
                    const fotoPreview = document.getElementById('previsualizacion-avatar-terapeuta').innerHTML;
                    const fotoSrc = document.getElementById('previsualizacion-avatar-terapeuta').querySelector('img')?.src || null;

                    tarjetas.forEach(tarjeta => {
                        if (tarjeta.querySelector('h2')) tarjeta.querySelector('h2').textContent = nombre;
                        if (tarjeta.querySelector('h3')) tarjeta.querySelector('h3').textContent = nombre;
                        
                        // Actualizar Teléfono, Email y Descripción encubiertos o visibles
                        const txtTel = tarjeta.querySelector('.terapeuta-telefono');
                        if (txtTel) txtTel.textContent = `Teléfono: ${telefono}`;
                        
                        const txtEmail = tarjeta.querySelector('.terapeuta-email');
                        if (txtEmail) txtEmail.textContent = email;

                        const txtDesc = tarjeta.querySelector('.terapeuta-descripcion');
                        if (txtDesc) txtDesc.textContent = descripcion;

                        // Actualizar foto de perfil en el contenedor de avatar simulado
                        const contFoto = tarjeta.querySelector('.foto-avatar-simulado') || tarjeta.querySelector('.avatar-preview');
                        if (contFoto) {
                            if (fotoSrc) {
                                contFoto.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                            } else {
                                contFoto.textContent = nombre.substring(0, 2).toUpperCase();
                            }
                        }

                        // Actualizar Tags de Especialidades visuales
                        let wrapperTags = tarjeta.querySelector('.contenedor-tags-especialidades');
                        if(!wrapperTags) {
                            wrapperTags = document.createElement('div');
                            wrapperTags.className = 'contenedor-tags-especialidades';
                            tarjeta.querySelector('.terapeuta-info')?.appendChild(wrapperTags);
                        }
                        wrapperTags.innerHTML = '';
                        listaEspecialidades.forEach(esp => {
                            wrapperTags.innerHTML += `<span class="tag-especialidad">${esp}</span>`;
                        });
                        
                        // Guardar los datos en el dataset de la tarjeta para futuras consultas dinámicas del botón Ver
                        tarjeta.dataset.especialidades = listaEspecialidades.join(", ");
                        if(fotoSrc) tarjeta.dataset.fotoSrc = fotoSrc;
                        else delete tarjeta.dataset.fotoSrc;
                    });
                }
                alert("Información del especialista actualizada con éxito.");
                cerrarModal();

                // === AGREGADO PARA EL BACKEND: Fuerzo el envío oculto a la base de datos después de su animación ===
                formTerapeutaModal.submit();
                // ====================================================================================================
            } else {
                alert("Procesando registro en el servidor...");
            }
        });
    }
});

// Exposición global para el flujo del Frontend
window.previsualizarImagen = function(input, targetId) {
    const contenedor = document.getElementById(targetId);
    if (input.files && input.files[0]) {
        const reader = new FileReader();
        reader.onload = function(e) {
            contenedor.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        };
        reader.readAsDataURL(input.files[0]);
    }
}

window.agregarCampoEspecialidad = function(valor = '') {
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    if(!wrapper) return;

    const div = document.createElement('div');
    div.className = 'fila-especialidad';
    div.style.marginTop = '0.35rem';
    div.innerHTML = `
        <input type="text" class="input-especialidad-item" placeholder="Otra especialidad" value="${valor}" required>
        <button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">X</button>
    `;
    wrapper.appendChild(div);
}

window.abrirModalAgregar = function() {
    const modal = document.getElementById('modal-terapeuta');
    const formulario = document.getElementById('form-terapeuta');
    const titulo = document.getElementById('modal-titulo');
    
    if (!modal || !formulario) return;

    // === AGREGADO PARA EL BACKEND: Limpiamos el método PUT por si el usuario venía de editar ===
    const metodoPut = document.getElementById('metodo-put-laravel');
    if (metodoPut) metodoPut.remove();
    // ============================================================================================
    
    formulario.reset();
    document.getElementById('previsualizacion-avatar-terapeuta').innerHTML = "TF";
    document.getElementById('wrapper-especialidades-lista').innerHTML = `
        <div class="fila-especialidad">
            <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required>
            <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
        </div>
    `;
    
    // Calculamos la ruta base real
    let baseUrl = window.location.origin;
    if (window.location.pathname.includes('/public')) {
        baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
    }
    
    // Asignamos la ruta correcta para crear el trabajador
    formulario.action = `${baseUrl}/admin/crear-trabajador`;
    // ==============================================================================================

    titulo.textContent = "Registrar Nuevo Especialista";
    
    document.getElementById('campo-password').style.display = 'grid';
    document.getElementById('password').required = true;
    document.getElementById('password_confirmation').required = true;

    modal.showModal(); 
}

window.editarTerapeuta = function(id) {
    const modal = document.getElementById('modal-terapeuta');
    const formulario = document.getElementById('form-terapeuta');
    const titulo = document.getElementById('modal-titulo');
    
    if (!modal || !formulario) return;
    
    titulo.textContent = "Editar Información del Especialista";
    
    // Calculamos la ruta base real
    let baseUrl = window.location.origin;
    if (window.location.pathname.includes('/public')) {
        baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
    }
    
    // Aplicamos la ruta correcta de una sola vez
    formulario.action = `${baseUrl}/admin/trabajador/${id}`;
    if (!document.getElementById('metodo-put-laravel')) {
        formulario.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT" id="metodo-put-laravel">');
    }
    // ======================================================================================

    document.getElementById('campo-password').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').required = false;

    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (tarjeta) {
        const nombreInyectado = tarjeta.querySelector('h2')?.textContent || tarjeta.querySelector('h3')?.textContent || '';
        const emailInyectado = tarjeta.querySelector('.terapeuta-email')?.textContent || '';
        const descripcionInyectada = tarjeta.querySelector('.terapeuta-descripcion')?.textContent || '';
        const telefonoInyectado = tarjeta.querySelector('.terapeuta-telefono')?.textContent || '';
        const fotoImgSrc = tarjeta.querySelector('.foto-avatar-simulado img, .avatar-preview img')?.src || tarjeta.dataset.fotoSrc;

        document.getElementById('nombre').value = nombreInyectado.trim();
        document.getElementById('email').value = emailInyectado.trim();
        document.getElementById('descripcion').value = descripcionInyectada.trim();
        document.getElementById('telefono').value = telefonoInyectado.replace('Teléfono:', '').trim();

        // Cargar foto si posee
        const preview = document.getElementById('previsualizacion-avatar-terapeuta');
        if(fotoImgSrc) {
            preview.innerHTML = `<img src="${fotoImgSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
        } else {
            preview.innerHTML = nombreInyectado.substring(0,2).toUpperCase();
        }

        // Cargar Especialidades en Campos Dinámicos
        const wrapper = document.getElementById('wrapper-especialidades-lista');
        wrapper.innerHTML = '';
        
        let especialidadesArray = [];
        if(tarjeta.dataset.especialidades) {
            especialidadesArray = tarjeta.dataset.especialidades.split(',').map(s => s.trim());
        } else {
            tarjeta.querySelectorAll('.tag-especialidad').forEach(t => especialidadesArray.push(t.textContent.trim()));
        }

        if(especialidadesArray.length === 0) especialidadesArray.push('');

        especialidadesArray.forEach((esp, index) => {
            if(index === 0) {
                wrapper.innerHTML = `
                    <div class="fila-especialidad">
                        <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" value="${esp}" required>
                        <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
                    </div>
                `;
            } else {
                agregarCampoEspecialidad(esp);
            }
        });
    }

    modal.showModal();
}

window.verTerapeutaDetalle = function(id) {
    const modal = document.getElementById('modal-ver-terapeuta');
    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (!modal || !tarjeta) return;

    const nombre = (tarjeta.querySelector('h2')?.textContent || tarjeta.querySelector('h3')?.textContent || '').trim();
    const telefono = (tarjeta.querySelector('.terapeuta-telefono')?.textContent || 'S/N').replace('Teléfono:', '').trim();
    const email = (tarjeta.querySelector('.terapeuta-email')?.textContent || 'sin-correo@thebeautyroom.com').trim();
    const descripcion = (tarjeta.querySelector('.terapeuta-descripcion')?.textContent || 'Sin descripción').trim();
    const fotoSrc = tarjeta.querySelector('.foto-avatar-simulado img, .avatar-preview img')?.src || tarjeta.dataset.fotoSrc;

    // Poblar campos
    document.getElementById('view-terapeuta-nombre').textContent = nombre;
    document.getElementById('view-terapeuta-telefono').textContent = telefono;
    document.getElementById('view-terapeuta-email').textContent = email;
    document.getElementById('view-terapeuta-descripcion').textContent = descripcion;

    const viewFoto = document.getElementById('view-terapeuta-foto');
    if(fotoSrc) {
        viewFoto.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
    } else {
        viewFoto.innerHTML = nombre.substring(0,2).toUpperCase();
    }

    // Especialidades Tags
    const tagsDestino = document.getElementById('view-terapeuta-especialidades');
    tagsDestino.innerHTML = '';
    let especialidadesArray = [];
    if(tarjeta.dataset.especialidades) {
        especialidadesArray = tarjeta.dataset.especialidades.split(',');
    } else {
        tarjeta.querySelectorAll('.tag-especialidad').forEach(t => especialidadesArray.push(t.textContent));
    }
    
    especialidadesArray.forEach(esp => {
        if(esp.trim() !== '') tagsDestino.innerHTML += `<span class="tag-especialidad">${esp.trim()}</span>`;
    });

    // Validar si el rol es Admin (inyectar botones de control)
    const accionesAdmin = document.getElementById('view-acciones-admin');
    accionesAdmin.innerHTML = `
        <button type="button" class="btn-zen" style="background:white; border:1px solid #cbd5e1; color:#475569;" onclick="document.getElementById('modal-ver-terapeuta').close(); editarTerapeuta(${id});">Editar</button>
        <button type="button" class="btn-zen btn-baja" onclick="document.getElementById('modal-ver-terapeuta').close(); eliminarTerapeuta(${id});">Dar de Baja</button>
    `;

    modal.showModal();
}

// Alerta customizada centrada en pantalla para la eliminación fluida de trabajadores
window.mostrarAlertaConfirmacion = function(titulo, mensaje, onConfirmar) {
    const modalConfirm = document.getElementById('modal-confirmacion-custom');
    document.getElementById('confirm-alerta-titulo').textContent = titulo;
    document.getElementById('confirm-alerta-text-mensaje') ? document.getElementById('confirm-alerta-text-mensaje').textContent = mensaje : document.getElementById('confirm-alerta-mensaje').textContent = mensaje;
    
    const btnAceptar = document.getElementById('btn-confirm-aceptar');
    const btnCancelar = document.getElementById('btn-confirm-cancelar');

    modalConfirm.showModal();

    const limpiarEventos = () => {
        btnAceptar.replaceWith(btnAceptar.cloneNode(true));
        btnCancelar.replaceWith(btnCancelar.cloneNode(true));
    };

    document.getElementById('btn-confirm-aceptar').addEventListener('click', () => {
        onConfirmar();
        modalConfirm.close();
        limpiarEventos();
    });

    document.getElementById('btn-confirm-cancelar').addEventListener('click', () => {
        modalConfirm.close();
        limpiarEventos();
    });
}

window.eliminarTerapeuta = function(id) {
    mostrarAlertaConfirmacion(
        "¿Estás seguro de eliminar los datos?",
        "¿Estás seguro de eliminar los datos del terapeuta?",
        () => {
            // Esto es lo único que necesitabas para el backend:
           // Esto es lo único que necesitabas para el backend:
            const form = document.createElement('form');
            form.method = 'POST';
            
            let baseUrl = window.location.origin;
            if (window.location.pathname.includes('/public')) {
                baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
            }
            form.action = `${baseUrl}/admin/usuario/${id}`;
            
            form.innerHTML = `
                <input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]').value}">
                <input type="hidden" name="_method" value="DELETE">
            `;
            document.body.appendChild(form);
            form.submit();
        }
    );
}

window.cerrarModal = function() {
    const modal = document.getElementById('modal-terapeuta');
    if (modal) modal.close();
}

window.alternarVisibilidad = function(inputId, boton) {
    const input = document.getElementById(inputId);
    if (!input) return;
    if (input.type === "password") {
        input.type = "text";
        boton.innerHTML = `<svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" /></svg>`;
    } else {
        input.type = "password";
        boton.innerHTML = `<svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" /><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" /></svg>`;
    }
}

window.validarContrasenas = function() {
    const password = document.getElementById('password');
    const confirmacion = document.getElementById('password_confirmation');
    const tituloActual = document.getElementById('modal-titulo').textContent;
    if (!tituloActual.includes("Editar") && password.value !== confirmacion.value) {
        alert("🚨 Las contraseñas no coinciden.");
        confirmacion.focus();
        return false;
    }
    return true;
}

// Función para sincronizar las especialidades dinámicas con el input oculto antes de enviar el formulario.
window.sincronizarEspecialidades = function() {
    // Buscamos los inputs dinámicos que creó tu compañero
    const inputsEsp = document.querySelectorAll(".input-especialidad-item");
    const listaEspecialidades = [];
    
    // Extraemos el valor de cada uno
    inputsEsp.forEach(input => {
        if(input.value.trim() !== "") {
            listaEspecialidades.push(input.value.trim());
        }
    });
    
    // Lo metemos en tu input oculto
    const inputOculto = document.getElementById("especialidades_hidden");
    if(inputOculto) {
        inputOculto.value = listaEspecialidades.join(",");
        console.log("Especialidades sincronizadas:", inputOculto.value);
    }
};