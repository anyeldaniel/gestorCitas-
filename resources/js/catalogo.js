/**
 * MÓDULO: Catálogo Zen - Gestión Dinámica de Servicios, Modales y Alertas de Baja
 */

document.addEventListener("DOMContentLoaded", () => {
    const formServicio = document.getElementById("form-servicio");
    const confirmModal = document.getElementById("modal-confirmacion-custom");
    const inputFoto = document.getElementById("servicio_foto");

    // ==========================================================================
    // 1. REUTILIZACIÓN DE ALERTA DE CONFIRMACIÓN CENTRALIZADA EN PANTALLA
    // ==========================================================================
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
    // 2. PREVISUALIZACIÓN DINÁMICA DE LA FOTO EN EL AVATAR CIRUCLAR
    // ==========================================================================
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
    // 3. LOGICA CONTROLADORA: MODALES (REGISTRO / EDICIÓN / CIERRE)
    // ==========================================================================
    window.abrirModalAgregarServicio = function() {
        const modal = document.getElementById("modal-servicio");
        if (!modal || !formServicio) return;

        document.getElementById("modal-servicio-titulo").textContent = "Registrar Nuevo Servicio";
        formServicio.reset();
        document.getElementById("previsualizacion-avatar-servicio").innerHTML = "SZ";
        document.getElementById("previsualizacion-avatar-servicio").style.borderStyle = 'dashed';
        
        const checkboxes = formServicio.querySelectorAll('input[name="especialistas[]"]');
        checkboxes.forEach(cb => cb.checked = true);

        formServicio.action = "/admin/servicios/guardar";
        modal.showModal();
    };

    window.cerrarModalServicio = function() {
        const modal = document.getElementById("modal-servicio");
        if (modal) modal.close();
    };

    window.editarServicio = function(id) {
        const modal = document.getElementById("modal-servicio");
        if (!modal || !formServicio) return;

        document.getElementById("modal-servicio-titulo").textContent = "Editar Servicio del Catálogo";
        formServicio.reset();

        const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
        if (tarjeta) {
            // Rellenar controles del formulario con los data-attributes de la tarjeta
            document.getElementById("input_nombre").value = tarjeta.dataset.nombre || '';
            document.getElementById("input_precio").value = tarjeta.dataset.precio || '';
            document.getElementById("input_porcentaje").value = tarjeta.dataset.porcentaje || '';
            document.getElementById("input_tiempo").value = tarjeta.dataset.tiempo || '60 min - 90 min';
            document.getElementById("input_descripcion").value = tarjeta.dataset.descripcion || '';
            
            // Gestión de Checkboxes de especialistas asignados
            const especialistasIds = JSON.parse(tarjeta.dataset.especialistasIds || '[]');
            const checkboxes = formServicio.querySelectorAll('input[name="especialistas[]"]');
            
            checkboxes.forEach(cb => {
                cb.checked = especialistasIds.includes(parseInt(cb.value)) || especialistasIds.includes(cb.value);
            });

            // Replicar foto miniatura en el avatar redondo del modal
            const fotoSrc = tarjeta.querySelector('.foto-servicio-portada')?.src;
            const preview = document.getElementById("previsualizacion-avatar-servicio");
            if (fotoSrc && !fotoSrc.includes('default.jpg')) {
                preview.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                preview.style.borderStyle = 'solid';
            } else {
                preview.innerHTML = "SZ";
                preview.style.borderStyle = 'dashed';
            }

            formServicio.action = `/admin/servicios/${id}/actualizar`;
            formServicio.dataset.idEditando = id;
            modal.showModal();
        }
    };

    // ==========================================================================
    // 4. INTERCEPCIÓN SUBMIT: ACTUALIZACIÓN INTERACTIVA DE VISTAS (EN CALIENTE)
    // ==========================================================================
    if (formServicio) {
        formServicio.addEventListener("submit", (e) => {
            const titulo = document.getElementById("modal-servicio-titulo").textContent;
            const checkboxesSeleccionados = formServicio.querySelectorAll('input[name="especialistas[]"]:checked');
            const listaNombres = [];
            const listaIds = [];
            
            checkboxesSeleccionados.forEach(cb => {
                listaIds.push(cb.value);
                listaNombres.push(cb.parentNode.textContent.trim());
            });

            if (titulo.includes("Editar")) {
                e.preventDefault(); // Previene recarga para simular persistencia frontend en caliente
                const id = formServicio.dataset.idEditando;
                const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
                
                if (tarjeta) {
                    const nombre = document.getElementById("input_nombre").value;
                    const precio = document.getElementById("input_precio").value;
                    const porcentaje = document.getElementById("input_porcentaje").value;
                    const tiempo = document.getElementById("input_tiempo").value;
                    const descripcion = document.getElementById("input_descripcion").value;
                    const fotoSrc = document.getElementById("previsualizacion-avatar-servicio").querySelector('img')?.src;

                    // Mutar dataset de la tarjeta modificada
                    tarjeta.dataset.nombre = nombre;
                    tarjeta.dataset.precio = precio;
                    tarjeta.dataset.porcentaje = porcentaje;
                    tarjeta.dataset.tiempo = tiempo;
                    tarjeta.dataset.descripcion = descripcion;
                    tarjeta.dataset.especialistasNombres = JSON.stringify(listaNombres);
                    tarjeta.dataset.especialistasIds = JSON.stringify(listaIds);

                    // Actualizar UI visual de la tarjeta
                    tarjeta.querySelector('h3').textContent = nombre;
                    tarjeta.querySelector('.tag-rol.especialista').textContent = tiempo;
                    tarjeta.querySelector('.price-tag').textContent = `$${precio}`;
                    if (fotoSrc) {
                        tarjeta.querySelector('.foto-servicio-portada').src = fotoSrc;
                    }
                }
                alert("Cambios guardados en el servicio con éxito.");
                document.getElementById("modal-servicio").close();
            } else {
                e.preventDefault();
                const nombre = document.getElementById("input_nombre").value;
                const precio = document.getElementById("input_precio").value;
                const porcentaje = document.getElementById("input_porcentaje").value;
                const tiempo = document.getElementById("input_tiempo").value;
                const descripcion = document.getElementById("input_descripcion").value;
                const fotoSrc = document.getElementById("previsualizacion-avatar-servicio").querySelector('img')?.src || '/img/default.jpg';
                const nuevaId = Date.now();

                const nuevoHtml = `
                    <article class="tarjeta-componente tarjeta-servicio-item" 
                             data-id="${nuevaId}" 
                             data-nombre="${nombre}" 
                             data-precio="${precio}" 
                             data-porcentaje="${porcentaje}" 
                             data-tiempo="${tiempo}" 
                             data-descripcion="${descripcion}" 
                             data-especialistas-nombres='${JSON.stringify(listaNombres)}'
                             data-especialistas-ids='${JSON.stringify(listaIds)}'
                             style="display: flex; flex-direction: column; justify-content: space-between; background: #ffffff; border: 1px solid #e2e8f0; border-radius: 12px; padding: 1rem; max-width: 310px; width: 100%; box-sizing: border-box; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <div>
                            <img src="${fotoSrc}" alt="${nombre}" class="img-portada foto-servicio-portada" style="width: 100%; height: 170px; object-fit: cover; border-radius: 8px; margin-bottom: 0.75rem; display: block;">
                            <header>
                                <h3 class="text-lg font-semibold capitalize" style="margin: 0 0 0.25rem 0; font-size: 1.15rem; color: #1e293b; line-height: 1.3;">${nombre}</h3>
                                <p class="text-sm text-gray-500" style="margin: 0 0 0.4rem 0; font-size: 0.85rem; color: #64748b;">Tratamiento exclusivo de nuestro spa.</p>
                                <small class="texto-atenuado" style="display: block; margin-bottom: 0.75rem; font-size: 0.8rem; color: #94a3b8;">
                                    ⏱️ Rango: <span class="tag-rol especialista">${tiempo}</span>
                                </small>
                            </header>
                        </div>
                        <footer class="flex justify-between items-center mt-2" style="display: flex; justify-content: space-between; align-items: center; width: 100%; gap: 0.4rem; border-top: 1px dashed #e2e8f0; padding-top: 0.75rem; margin-top: auto;">
                            <span class="precio-tag price-tag" style="font-weight: 700; font-size: 1.1rem; color: #1e293b;">$${precio}</span>
                            <div class="acciones-catalogo-wrapper" style="display: flex; gap: 0.35rem; align-items: center;">
                                <button type="button" class="btn-secundario" style="padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; cursor: pointer;" onclick="verServicioDetalle('${nuevaId}')">Consultar</button>
                                <button type="button" class="btn-secundario" style="background-color: #e2e8f0; color: #334155; padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; border: none; cursor: pointer;" onclick="editarServicio('${nuevaId}')">Editar</button>
                                <button type="button" class="btn-baja" style="background: rgba(255, 90, 125, 0.08); color: #ff5a7d; border: 1px solid rgba(255, 90, 125, 0.3); padding: 0.4rem 0.65rem; font-size: 0.8rem; border-radius: 6px; cursor: pointer;" onclick="eliminarServicio('${nuevaId}')">Eliminar</button>
                            </div>
                        </footer>
                    </article>
                `;
                document.getElementById("contenedor-servicios-catalogo").insertAdjacentHTML('beforeend', nuevoHtml);
                alert("Servicio de bienestar agregado con éxito.");
                document.getElementById("modal-servicio").close();
            }
        });
    }

    // ==========================================================================
    // 5. CONSULTA DETALLADA: CONSTRUCCIÓN DINÁMICA DE LA FICHA TÉCNICA
    // ==========================================================================
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
    // 6. ADVERTENCIA DE BAJA BAJO VENTANA CRÍTICA DIÁLOGO (ELIMINAR)
    // ==========================================================================
    window.eliminarServicio = function(id) {
        const tarjeta = document.querySelector(`.tarjeta-servicio-item[data-id="${id}"]`);
        const nombreServicio = tarjeta ? tarjeta.dataset.nombre : "este servicio";
        
        window.mostrarAlertaConfirmacion(
            "¿Remover tratamiento del catálogo?",
            `¿Estás seguro de eliminar permanentemente el servicio "${nombreServicio.toUpperCase()}"? Esta operación no se puede deshacer de los registros del spa.`,
            () => {
                if (tarjeta) {
                    tarjeta.remove();
                }
            }
        );
    };

    // ==========================================================================
    // 7. MÁSCARA OSCURA: CIERRE CON CLICK EN BACKDROP OUTSIDE
    // ==========================================================================
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