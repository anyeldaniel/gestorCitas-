/**
 * MÓDULO: Dashboard Administrativo - Acciones de Panel, Recepcionistas y Paginación en Frontend
 */

document.addEventListener("DOMContentLoaded", () => {
    // Configuración para simular paginaciones del lado del admin en los bloques de terapeutas y recepcionistas, con botones de navegación y actualización dinámica de vistas.
    let paginaTerapeutas = 1;
    let paginaRecepcionistas = 1;
    const itemsPorPagina = 2;

    const formRecepcionista = document.getElementById("form-recepcionista");

    // Lógica e inyección dinámica del Modal de Recepcionistas (Crear/Editar)
    window.abrirModalRecepcionista = function() {
        const modal = document.getElementById("modal-recepcionista");
        const form = document.getElementById("form-recepcionista");
        document.getElementById("modal-recepcionista-titulo").textContent = "Agregar Nuevo Recepcionista";
        form.reset();
        document.getElementById("previsualizacion-avatar-recepcionista").innerHTML = "RE";
        form.action = "/admin/recepcionista/guardar";
        document.getElementById("recep-campo-password").style.display = "grid";
        document.getElementById("recep_password").required = true;
        modal.showModal();
    };

    window.editarRecepcionista = function(id) {
        const modal = document.getElementById("modal-recepcionista");
        const form = document.getElementById("form-recepcionista");
        document.getElementById("modal-recepcionista-titulo").textContent = "Editar Recepcionista";
        form.reset();
        
        const tarjeta = document.querySelector(`.tarjeta-recepcionista-item[data-id="${id}"]`);
        if(tarjeta) {
            const nombre = tarjeta.querySelector('h3').textContent;
            const correo = tarjeta.dataset.correo || '';
            const telefono = tarjeta.dataset.telefono || '';
            const fotoSrc = tarjeta.querySelector('img')?.src;

            document.getElementById("recep_name").value = nombre;
            document.getElementById("recep_email").value = correo;
            document.getElementById("recep_phone").value = telefono;
            form.action = `/admin/recepcionista/${id}/actualizar`;
            document.getElementById("recep-campo-password").style.display = "none";
            document.getElementById("recep_password").required = false;

            const preview = document.getElementById("previsualizacion-avatar-recepcionista");
            if(fotoSrc) {
                preview.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
            } else {
                preview.innerHTML = nombre.substring(0,2).toUpperCase();
            }
            form.dataset.idEditando = id;
        }
        modal.showModal();
    };

    if(formRecepcionista) {
        formRecepcionista.addEventListener("submit", (e) => {
            const titulo = document.getElementById("modal-recepcionista-titulo").textContent;
            if(titulo.includes("Editar")) {
                e.preventDefault();
                const id = formRecepcionista.dataset.idEditando;
                const tarjeta = document.querySelector(`.tarjeta-recepcionista-item[data-id="${id}"]`);
                if(tarjeta) {
                    const nombre = document.getElementById("recep_name").value;
                    const correo = document.getElementById("recep_email").value;
                    const telefono = document.getElementById("recep_phone").value;
                    const fotoSrc = document.getElementById("previsualizacion-avatar-recepcionista").querySelector('img')?.src;

                    tarjeta.querySelector('h3').textContent = nombre;
                    tarjeta.dataset.correo = correo;
                    tarjeta.dataset.telefono = telefono;

                    const avatarBox = tarjeta.querySelector('.foto-avatar-simulado') || tarjeta.querySelector('.avatar-preview');
                    if(avatarBox) {
                        if(fotoSrc) {
                            avatarBox.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                        } else {
                            avatarBox.innerHTML = nombre.substring(0,2).toUpperCase();
                        }
                    }
                }
                alert("Cambios guardados en el recepcionista con éxito.");
                document.getElementById("modal-recepcionista").close();
            } else {
                // Flujo nativo de registro express hacia el controlador si fuera necesario,
                // pero lo interceptamos para pintarlo en caliente a petición del flujo Frontend
                e.preventDefault();
                const nombre = document.getElementById("recep_name").value;
                const correo = document.getElementById("recep_email").value;
                const telefono = document.getElementById("recep_phone").value;
                const fotoSrc = document.getElementById("previsualizacion-avatar-recepcionista").querySelector('img')?.src;
                const nuevaId = Date.now();

                const avatarRender = fotoSrc ? `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">` : nombre.substring(0,2).toUpperCase();

                const nuevoHtml = `
                    <article class="tarjeta-admision tarjeta-recepcionista-item" data-id="${nuevaId}" data-correo="${correo}" data-telefono="${telefono}">
                        <figure class="terapeuta-foto-contenedor" style="margin-bottom:0.5rem; display:flex; justify-content:center;">
                            <div class="foto-avatar-simulado" style="width:50px; height:50px; border-radius:50%; background:#cbd5e1; display:flex; align-items:center; justify-content:center; font-weight:bold; overflow:hidden;">${avatarRender}</div>
                        </figure>
                        <header class="info-cliente" style="text-align:center;">
                            <h3>${nombre}</h3>
                            <p>Rol: <span class="tag-rol recepcionista">Recepcionista</span></p>
                        </header>
                        <footer style="display: flex; gap: 0.5rem; padding: 0; background: none; border: none; margin-top: auto; justify-content:center;">
                            <button type="button" class="btn-zen" onclick="editarRecepcionista(${nuevaId})" style="background: #ffffff; color: #475569; border: 1px solid #cbd5e1; padding: 0.35rem 0.75rem; font-size: 0.85rem; border-radius: 0.375rem; font-weight: 600; cursor: pointer;">Editar</button>
                            <button type="button" class="btn-zen btn-baja" onclick="eliminarRecepcionista(${nuevaId})">Baja</button>
                        </footer>
                    </article>
                `;
                document.getElementById("contenedor-recepcionistas").insertAdjacentHTML('beforeend', nuevoHtml);
                alert("Recepcionista agregado con éxito.");
                document.getElementById("modal-recepcionista").close();
                actualizarPaginadoresTotales();
            }
        });
    }

    window.eliminarRecepcionista = function(id) {
        if(window.mostrarAlertaConfirmacion) {
            window.mostrarAlertaConfirmacion(
                "¿Estás seguro de eliminar los datos?",
                "¿Estás seguro de eliminar los datos del recepcionista?",
                () => {
                    const tarjeta = document.querySelector(`.tarjeta-recepcionista-item[data-id="${id}"]`);
                    if(tarjeta) tarjeta.remove();
                    actualizarPaginadoresTotales();
                }
            );
        }
    };

    // Motor de paginación del lado del admin para Terapeutas y Recepcionistas
    window.paginarBloque = function(contenedorId, numPagina, btnPrevId, btnNextId, infoId) {
        const contenedor = document.getElementById(contenedorId);
        if(!contenedor) return;
        const items = contenedor.children;
        const totalItems = items.length;
        const maxPaginas = Math.ceil(totalItems / itemsPorPagina) || 1;

        if (numPagina < 1) numPagina = 1;
        if (numPagina > maxPaginas) numPagina = maxPaginas;

        for (let i = 0; i < totalItems; i++) {
            if (i >= (numPagina - 1) * itemsPorPagina && i < numPagina * itemsPorPagina) {
                items[i].style.display = "flex";
            } else {
                items[i].style.display = "none";
            }
        }

        document.getElementById(btnPrevId).disabled = (numPagina === 1);
        document.getElementById(btnNextId).disabled = (numPagina === maxPaginas);
        document.getElementById(infoId).textContent = `Pág. ${numPagina} de ${maxPaginas}`;

        return numPagina;
    };

    function actualizarPaginadoresTotales() {
        paginaTerapeutas = paginarBloque("contenedor-trabajadores", paginaTerapeutas, "prev-terapeutas", "next-terapeutas", "info-terapeutas");
        paginaRecepcionistas = paginarBloque("contenedor-recepcionistas", paginaRecepcionistas, "prev-recepcionistas", "next-recepcionistas", "info-recepcionistas");
    }

    document.getElementById("prev-terapeutas")?.addEventListener("click", () => { paginaTerapeutas--; actualizarPaginadoresTotales(); });
    document.getElementById("next-terapeutas")?.addEventListener("click", () => { paginaTerapeutas++; actualizarPaginadoresTotales(); });
    document.getElementById("prev-recepcionistas")?.addEventListener("click", () => { paginaRecepcionistas--; actualizarPaginadoresTotales(); });
    document.getElementById("next-recepcionistas")?.addEventListener("click", () => { paginaRecepcionistas++; actualizarPaginadoresTotales(); });

    // Inicializar vistas de paginación
    actualizarPaginadoresTotales();
});