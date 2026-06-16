/**
 * MÓDULO: Gestión de Terapeutas - Interacciones de Interfaz Nativas del Dashboard Administrativo
 */

document.addEventListener("DOMContentLoaded", () => {
    const formTerapeutaModal = document.getElementById("form-terapeuta");

    if (formTerapeutaModal && !document.getElementById("contenedor-trabajadores")) {
        formTerapeutaModal.addEventListener("submit", (e) => {
            e.preventDefault(); 

            if (!validarContrasenas()) return;

            const nombre = document.getElementById('nombre').value;
            const especialidad = document.getElementById('especialidad').value;
            const email = document.getElementById('email').value;
            const descripcion = document.getElementById('descripcion').value;
            const tituloActual = document.getElementById('modal-titulo').textContent;

            if (tituloActual.includes("Editar")) {
                const actionUrl = formTerapeutaModal.action;
                const match = actionUrl.match(/\/terapeutas\/(\d+)\/actualizar/);
                
                if (match) {
                    const idEditando = match[1];
                    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${idEditando}"]`);
                    if (tarjeta) {
                        if (tarjeta.querySelector('h2')) tarjeta.querySelector('h2').textContent = nombre;
                        tarjeta.querySelector('.terapeuta-especialidad').textContent = especialidad;
                        tarjeta.querySelector('.terapeuta-email').textContent = email;
                        tarjeta.querySelector('.terapeuta-descripcion').textContent = descripcion;
                    }
                }
                alert("Información del especialista actualizada con éxito.");
            } else {
                const gridTerapeutas = document.querySelector('.grid-terapeutas');
                const nuevaId = Date.now();

                const nuevaTarjetaHtml = `
                    <article class="tarjeta-terapeuta" data-id="${nuevaId}">
                        <figure class="terapeuta-foto-contenedor">
                            <div class="foto-avatar-simulado">${nombre.substring(0, 2).toUpperCase()}</div>
                            <span class="tag-disponibilidad">Disponible</span>
                        </figure>
                        <div class="terapeuta-info">
                            <h2>${nombre}</h2>
                            <p class="terapeuta-especialidad">${especialidad}</p>
                            <p class="terapeuta-email" style="font-size:0.85rem; color:#64748b; margin:0 0 0.5rem 0;">${email}</p>
                            <p class="terapeuta-descripcion">${descripcion}</p>
                        </div>
                        <footer class="terapeuta-acciones">
                            <button type="button" class="btn-terapeuta-editar" onclick="editarTerapeuta(${nuevaId})">Editar Info</button>
                            <button type="button" class="btn-terapeuta-eliminar" onclick="eliminarTerapeuta(${nuevaId})">Dar de Baja</button>
                        </footer>
                    </article>
                `;

                if (gridTerapeutas) {
                    const contenedorVacio = document.querySelector('.contenedor-vacio');
                    if (contenedorVacio) contenedorVacio.remove();

                    gridTerapeutas.insertAdjacentHTML('beforeend', nuevaTarjetaHtml);
                    alert("¡Nuevo especialista registrado con éxito!");
                }
            }
            cerrarModal();
        });
    }
});

// ==========================================================================
// EXPOSICIÓN GLOBAL AL OBJETO WINDOW para Funciones de Interacción con el Módulo de Terapeutas, permitiendo su invocación desde elementos HTML dinámicos sin necesidad de recargar la página, manteniendo la experiencia fluida del dashboard administrativo.
// ==========================================================================

window.alternarVisibilidad = function(inputId, boton) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (input.type === "password") {
        input.type = "text";
        boton.innerHTML = `
            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M3.98 8.223A10.477 10.477 0 0 0 1.934 12C3.226 16.338 7.244 19.5 12 19.5c.993 0 1.953-.138 2.863-.395M6.228 6.228A10.451 10.451 0 0 1 12 4.5c4.756 0 8.773 3.162 10.065 7.498a10.522 10.522 0 0 1-4.293 5.774M6.228 6.228 3 3m3.228 3.228 3.65 3.65m7.894 7.894L21 21m-3.228-3.228-3.65-3.65m0 0a3 3 0 1 0-4.243-4.243m4.242 4.242L9.88 9.88" />
            </svg>
        `;
    } else {
        input.type = "password";
        boton.innerHTML = `
            <svg class="svg-ojo" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" d="M2.036 12.322a1.012 1.012 0 0 1 0-.639C3.423 7.51 7.36 4.5 12 4.5c4.638 0 8.573 3.007 9.963 7.178.07.207.07.431 0 .639C20.577 16.49 16.64 19.5 12 19.5c-4.638 0-8.573-3.007-9.963-7.178Z" />
                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 1 1-6 0 3 3 0 0 1 6 0Z" />
            </svg>
        `;
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

window.abrirModalAgregar = function() {
    const modal = document.getElementById('modal-terapeuta');
    const formulario = document.getElementById('form-terapeuta');
    const titulo = document.getElementById('modal-titulo');
    
    if (!modal || !formulario) return;
    
    formulario.reset();
    formulario.action = "/admin/terapeutas/guardar"; 
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
    formulario.action = `/admin/terapeutas/${id}/actualizar`; 

    document.getElementById('campo-password').style.display = 'none';
    document.getElementById('password').required = false;
    document.getElementById('password_confirmation').required = false;

    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (tarjeta) {
        const nombreInyectado = tarjeta.querySelector('h2')?.textContent || tarjeta.querySelector('h3')?.textContent;
        const especialidadInyectada = tarjeta.querySelector('.terapeuta-especialidad')?.textContent;
        const emailInyectado = tarjeta.querySelector('.terapeuta-email')?.textContent;
        const descripcionInyectada = tarjeta.querySelector('.terapeuta-descripcion')?.textContent;

        if (nombreInyectado) document.getElementById('nombre').value = nombreInyectado.trim();
        if (especialidadInyectada) document.getElementById('especialidad').value = especialidadInyectada.trim();
        if (emailInyectado) document.getElementById('email').value = emailInyectado.trim();
        if (descripcionInyectada) document.getElementById('descripcion').value = descripcionInyectada.trim();
    }

    modal.showModal();
}

window.cerrarModal = function() {
    const modal = document.getElementById('modal-terapeuta');
    if (modal) modal.close();
}

window.eliminarTerapeuta = function(id) {
    if (confirm("¿Estás seguro de dar de baja a este especialista?")) {
        const elemento = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
        if (elemento) {
            elemento.remove();
        }
    }
}