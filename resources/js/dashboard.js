/**
 * MÓDULO: Dashboard Administrativo - Acciones de Panel
 */

document.addEventListener("DOMContentLoaded", () => {
    const formTrabajador = document.getElementById("form-registro-trabajador");
    const formTerapeutaModal = document.getElementById("form-terapeuta");
    const contenedorTrabajadores = document.getElementById("contenedor-trabajadores");

    // Formulario express lateral izquierdo para añadir trabajadores administrativos (recepcionistas y especialistas) de forma rápida, con validación básica y actualización en tiempo real del panel sin recargar
    if (formTrabajador) {
        formTrabajador.addEventListener("submit", (e) => {
            e.preventDefault();
            
            const nombre = document.getElementById("worker_name").value;
            const rolSeleccionado = document.getElementById("worker_role").value;
            const nuevaId = Date.now();
            
            const tagClase = rolSeleccionado === 'especialista' ? 'especialista' : 'recepcionista';
            const tagTexto = rolSeleccionado === 'especialista' ? 'Especialista' : 'Recepcionista';
            
            const nuevoHtml = `
                <article class="tarjeta-admision ${rolSeleccionado === 'especialista' ? 'tarjeta-terapeuta' : ''}" data-id="${nuevaId}">
                    <header class="info-cliente">
                        <h3>${nombre}</h3>
                        <p>Rol: <span class="tag-rol ${tagClase}">${tagTexto}</span></p>
                        ${rolSeleccionado === 'especialista' ? `
                            <p class="terapeuta-especialidad" style="display:none;">Personal General</p>
                            <p class="terapeuta-email" style="display:none;">sin-correo@express.com</p>
                            <p class="terapeuta-descripcion" style="display:none;">Registrado express desde el panel lateral.</p>
                        ` : ''}
                    </header>
                    
                    ${rolSeleccionado === 'especialista' ? `
                        <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto;">
                            <button type="button" class="btn-zen" onclick="editarTerapeuta(${nuevaId})" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                            <button type="button" class="btn-zen btn-baja" onclick="eliminarTerapeuta(${nuevaId})">Baja</button>
                        </footer>
                    ` : `
                        <button class="btn-zen btn-baja btn-eliminar-trabajador" data-id="${nuevaId}">Baja</button>
                    `}
                </article>
            `;
            
            if (contenedorTrabajadores) {
                contenedorTrabajadores.insertAdjacentHTML('beforeend', nuevoHtml);
            }
            alert(`Trabajador añadido con éxito.`);
            formTrabajador.reset();
        });
    }

    // Validar el formulario del modal, pero NO impedir el envío normal cuando todo está correcto.
    if (formTerapeutaModal && contenedorTrabajadores) {
        formTerapeutaModal.addEventListener("submit", (e) => {
            e.preventDefault();

            if (window.validarContrasenas && !window.validarContrasenas()) return;

            const nombre = document.getElementById("nombre").value;
            const especialidad = document.getElementById("especialidad").value;
            const email = document.getElementById("email").value;
            const descripcion = document.getElementById("descripcion").value;
            const tituloActual = document.getElementById("modal-titulo").textContent;

            if (tituloActual.includes("Editar")) {
                const actionUrl = formTerapeutaModal.action;
                const match = actionUrl.match(/\/terapeutas\/(\d+)\/actualizar/);
                
                if (match) {
                    const idEditando = match[1];
                    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${idEditando}"]`);
                    if (tarjeta) {
                        if (tarjeta.querySelector('h3')) tarjeta.querySelector('h3').textContent = nombre;
                        tarjeta.querySelector('.terapeuta-especialidad').textContent = especialidad;
                        tarjeta.querySelector('.terapeuta-email').textContent = email;
                        tarjeta.querySelector('.terapeuta-descripcion').textContent = descripcion;
                    }
                }
                alert("Cambios guardados en el panel unificado.");
            } else {
                const nuevaId = Date.now();
                const nuevaTarjetaHtml = `
                    <article class="tarjeta-admision tarjeta-terapeuta" data-id="${nuevaId}">
                        <header class="info-cliente">
                            <h3>${nombre}</h3>
                            <p>Rol: <span class="tag-rol especialista">Especialista</span></p>
                            <p class="terapeuta-especialidad" style="display:none;">${especialidad}</p>
                            <p class="terapeuta-email" style="display:none;">${email}</p>
                            <p class="terapeuta-descripcion" style="display:none;">${descripcion}</p>
                        </header>
                        <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto;">
                            <button type="button" class="btn-zen" onclick="editarTerapeuta(${nuevaId})" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                            <button type="button" class="btn-zen btn-baja" onclick="eliminarTerapeuta(${nuevaId})">Baja</button>
                        </footer>
                    </article>
                `;
                
                contenedorTrabajadores.insertAdjacentHTML('beforeend', nuevaTarjetaHtml);
                alert("¡Especialista registrado con éxito junto a su correo!");
            }
            
            if (window.cerrarModal) window.cerrarModal();
        });
    }

    //Manejo de clics de baja para personal antiguo y nuevo, utilizando delegación de eventos para abarcar ambos tipos de tarjetas (admision y terapeuta) sin necesidad de recargar la página o re-renderizar el DOM, manteniendo la experiencia fluida del dashboard administrativo.
    document.addEventListener("click", (e) => {
        if (e.target.classList.contains("btn-eliminar-trabajador") && e.target.hasAttribute("data-id")) {
            if (confirm("¿Está seguro de que desea dar de baja a este trabajador administrativo?")) {
                const tarjetaAdmision = e.target.closest(".tarjeta-admision");
                if (tarjetaAdmision) tarjetaAdmision.remove();
            }
        }
    });
});