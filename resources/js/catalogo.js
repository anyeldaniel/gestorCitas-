/**
 * MÓDULO: Catálogo Zen - Gestión Dinámica de Servicios, Modales y Alertas de Baja
 */


// Esperamos a que el DOM esté completamente cargado para inicializar la lógica del catálogo.
document.addEventListener("DOMContentLoaded", () => {
    const formServicio = document.getElementById("form-servicio");
    const confirmModal = document.getElementById("modal-confirmacion-custom");
    const inputFoto = document.getElementById("servicio_foto");

    // ==========================================================================
    //     REUTILIZACIÓN DE ALERTA DE CONFIRMACIÓN CENTRALIZADA EN PANTALLA
    // ==========================================================================

    // Función global para mostrar una alerta de confirmación personalizada.
    window.mostrarAlertaConfirmacion = function(titulo, mensaje, callbackAceptar) {
        if (!confirmModal) return;
        document.getElementById("confirm-alerta-titulo").textContent = titulo;
        document.getElementById("confirm-alerta-mensaje").textContent = mensaje;

        const btnAceptar = document.getElementById("btn-confirm-aceptar");
        const btnCancelar = document.getElementById("btn-confirm-cancelar");

        // Clonar nodo para limpiar event listeners previos acumulados
        const clonAceptar = btnAceptar.cloneNode(true);
        btnAceptar.parentNode.replaceChild(clonAceptar, btnAceptar);

        clonAceptar.addEventListener("click", () => {
            callbackAceptar();
            confirmModal.close();
        });

        btnCancelar.onclick = () => confirmModal.close();
        confirmModal.showModal();
    };

    // ==========================================================================
    //       PREVISUALIZACIÓN DINÁMICA DE LA FOTO EN EL AVATAR CIRUCLAR
    // ==========================================================================

    // Función global para ser llamada desde el onchange del input file.
    window.previsualizarImagenServicio = function(input) {
        const preview = document.getElementById("previsualizacion-avatar-servicio");
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                preview.innerHTML = `<img src="${e.target.result}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                preview.style.borderStyle = 'solid';
            }
            reader.readAsDataURL(input.files[0]);
        } else {
            preview.innerHTML = "SZ";
            preview.style.borderStyle = 'dashed';
        }
    };

    if (inputFoto) {
        inputFoto.addEventListener("change", function() {
            window.previsualizarImagenServicio(this);
        });
    }

    // ==========================================================================
    //       LOGICA CONTROLADORA: MODALES (REGISTRO / EDICIÓN / CIERRE)
    // ==========================================================================

    // Función para abrir el modal de registro con estado limpio y configuración para creación.
window.abrirModalAgregarServicio = function() {
        const modal = document.getElementById("modal-servicio");
        if (!modal || !formServicio) return;

        document.getElementById("modal-servicio-titulo").textContent = "Registrar Nuevo Servicio";
        formServicio.reset();
        document.getElementById("previsualizacion-avatar-servicio").innerHTML = "SZ";
        document.getElementById("previsualizacion-avatar-servicio").style.borderStyle = 'dashed';
        
        const checkboxes = formServicio.querySelectorAll('input[name="especialistas[]"]');
        checkboxes.forEach(cb => cb.checked = true);

        // ======================================================
        let baseUrl = window.location.origin;
        if (window.location.pathname.includes('/public')) {
            baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
        }
        formServicio.action = `${baseUrl}/admin/servicios/guardar`;
        // ======================================================

        const metodoOculto = document.getElementById('metodo-put-servicio');
        if (metodoOculto) metodoOculto.remove(); // Quitamos el PUT por si veníamos de editar
        
        modal.showModal();
    };

    // Función para cerrar cualquier modal de servicio (registro o edición).
    window.cerrarModalServicio = function() {
        const modal = document.getElementById("modal-servicio");
        if (modal) modal.close();
    };

    // Función para abrir el modal de edición con datos precargados del servicio seleccionado.
window.editarServicio = function(id) {
            const modal = document.getElementById("modal-servicio");
            if (!modal || !formServicio) return;

            document.getElementById("modal-servicio-titulo").textContent = "Editar Servicio del Catálogo";
            formServicio.reset();

            const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
            if (tarjeta) {

                // Rellenar controles del formulario con los data-attributes de la tarjeta.
                document.getElementById("input_nombre").value = tarjeta.dataset.nombre || '';
                document.getElementById("input_precio").value = tarjeta.dataset.precio || '';
                document.getElementById("input_porcentaje").value = tarjeta.dataset.porcentaje || '';
                document.getElementById("input_tiempo").value = tarjeta.dataset.tiempo || '60 min - 90 min';
                document.getElementById("input_descripcion").value = tarjeta.dataset.descripcion || '';
                
                // Gestión de Checkboxes de especialistas asignados.
                const especialistasIds = JSON.parse(tarjeta.dataset.especialistasIds || '[]');
                const checkboxes = formServicio.querySelectorAll('input[name="especialistas[]"]');
                
                checkboxes.forEach(cb => {
                    cb.checked = especialistasIds.includes(parseInt(cb.value)) || especialistasIds.includes(cb.value);
                });

                // Replicar foto miniatura en el avatar redondo del modal.
                const fotoSrc = tarjeta.querySelector('.foto-servicio-portada')?.src;
                const preview = document.getElementById("previsualizacion-avatar-servicio");
                if (fotoSrc && !fotoSrc.includes('default.jpg')) {
                    preview.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                    preview.style.borderStyle = 'solid';
                } else {
                    preview.innerHTML = "SZ";
                    preview.style.borderStyle = 'dashed';
                }

                // ======================================================
                let baseUrl = window.location.origin;
                if (window.location.pathname.includes('/public')) {
                    baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
                }
                formServicio.action = `${baseUrl}/admin/servicios/${id}/actualizar`;
                // ======================================================

                formServicio.dataset.idEditando = id;
                
                // Forzamos a laravel a usar el método PUT para la actualización.
                if (!document.getElementById('metodo-put-servicio')) {
                    formServicio.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT" id="metodo-put-servicio">');
                }

                modal.showModal();
            }
        };

    // ==========================================================================
    //       CONSULTA DETALLADA: CONSTRUCCIÓN DINÁMICA DE LA FICHA TÉCNICA
    // ==========================================================================

    // Función para mostrar el modal de consulta detallada con información extraída de los data-attributes de la tarjeta.
    window.verServicioDetalle = function(id) {
        const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
        if (tarjeta) {
            document.getElementById("view-servicio-nombre").textContent = tarjeta.dataset.nombre;
            document.getElementById("view-servicio-precio").textContent = `$${tarjeta.dataset.precio}`;
            document.getElementById("view-servicio-porcentaje").textContent = tarjeta.dataset.porcentaje;
            document.getElementById("view-servicio-tiempo").textContent = tarjeta.dataset.tiempo;
            document.getElementById("view-servicio-descripcion").textContent = tarjeta.dataset.descripcion;
            document.getElementById("view-servicio-foto").src = tarjeta.querySelector('.foto-servicio-portada').src;

            const contenedorTags = document.getElementById("view-servicio-especialistas");
            contenedorTags.innerHTML = '';
            
            const especialistas = JSON.parse(tarjeta.dataset.especialistasNombres || '[]');
            if (especialistas.length > 0) {
                especialistas.forEach(esp => {
                    const tag = document.createElement('span');
                    tag.className = 'tag-especialidad';
                    tag.style.cssText = "padding: 0.25rem 0.5rem; background: #f1f5f9; color: #334155; border-radius: 4px; font-size: 0.75rem; font-weight: 500;";
                    tag.textContent = esp;
                    contenedorTags.appendChild(tag);
                });
            } else {
                contenedorTags.innerHTML = '<span class="ayuda-input">Sin especialistas asignados</span>';
            }

            document.getElementById("modal-ver-servicio").showModal();
        }
    };

    // ==========================================================================
    //       ADVERTENCIA DE BAJA BAJO VENTANA CRÍTICA DIÁLOGO (ELIMINAR)
    // ==========================================================================

    // Función para mostrar una alerta de confirmación antes de eliminar un servicio, con información contextualizada del servicio a eliminar.
    window.eliminarServicio = function(id) {
        const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
        const nombreServicio = tarjeta ? tarjeta.dataset.nombre : "este servicio";
        
        window.mostrarAlertaConfirmacion(
            "¿Remover tratamiento del catálogo?",
            `¿Estás seguro de eliminar permanentemente "${nombreServicio.toUpperCase()}"?`,
            () => {
                // === EL FIX: CREAMOS UN FORMULARIO FANTASMA PARA ENVIAR EL DELETE ===
                let baseUrl = window.location.origin;
                if (window.location.pathname.includes('/public')) {
                    baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
                }

                const form = document.createElement('form');
                form.method = 'POST';
                form.action = `${baseUrl}/admin/servicios/${id}/eliminar`;
                form.innerHTML = `
                    <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]')?.content || ''}">
                    <input type="hidden" name="_method" value="DELETE">
                `;
                document.body.appendChild(form);
                form.submit();
            }
        );
    };

    // ==========================================================================
    //         MÁSCARA OSCURA: CIERRE CON CLICK EN BACKDROP OUTSIDE
    // ==========================================================================

    // Agregamos un listener global para cerrar cualquier modal al hacer click fuera del contenido (en el backdrop).
    document.querySelectorAll('dialog').forEach(modal => {
        modal.addEventListener('click', function(e) {
            const rect = modal.getBoundingClientRect();
            const isInDialog = (rect.top <= e.clientY && e.clientY <= rect.top + rect.height &&
                                rect.left <= e.clientX && e.clientX <= rect.left + rect.width);
            if (!isInDialog) {
                modal.close();
            }
        });
    });
});