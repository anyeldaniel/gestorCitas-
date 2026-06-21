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
    window.abrirModalRecepcionista = function () {
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

    window.editarRecepcionista = function (id) {
        const modal = document.getElementById("modal-recepcionista");
        const form = document.getElementById("form-recepcionista");
        document.getElementById("modal-recepcionista-titulo").textContent = "Editar Recepcionista";
        form.reset();

        const tarjeta = document.querySelector(`.tarjeta-recepcionista-item[data-id="${id}"]`);
        if (tarjeta) {
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
            if (fotoSrc) {
                preview.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
            } else {
                preview.innerHTML = nombre.substring(0, 2).toUpperCase();
            }
            form.dataset.idEditando = id;
        }
        modal.showModal();
    };

    if (formRecepcionista) {
        formRecepcionista.addEventListener("submit", (e) => {
            e.preventDefault(); // Sus compañeros detienen el envío para hacer la animación

            const titulo = document.getElementById("modal-recepcionista-titulo").textContent;

            let baseUrl = window.location.origin;
            if (window.location.pathname.includes('/public')) {
                baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
            }

            if (titulo.includes("Editar")) {
                const id = formRecepcionista.dataset.idEditando;
                const tarjeta = document.querySelector(`.tarjeta-recepcionista-item[data-id="${id}"]`);

                // === LÓGICA VISUAL DE TUS COMPAÑEROS (INTACTA) ===
                if (tarjeta) {
                    const nombre = document.getElementById("recep_name").value;
                    const correo = document.getElementById("recep_email").value;
                    const telefono = document.getElementById("recep_phone").value;
                    const fotoSrc = document.getElementById("previsualizacion-avatar-recepcionista").querySelector('img')?.src;

                    if (tarjeta.querySelector('h3')) tarjeta.querySelector('h3').textContent = nombre;
                    tarjeta.dataset.correo = correo;
                    tarjeta.dataset.telefono = telefono;

                    const avatarBox = tarjeta.querySelector('.foto-avatar-simulado') || tarjeta.querySelector('.avatar-preview');
                    if (avatarBox) {
                        if (fotoSrc) {
                            avatarBox.innerHTML = `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">`;
                        } else {
                            avatarBox.innerHTML = nombre.substring(0, 2).toUpperCase();
                        }
                    }
                }
                document.getElementById("modal-recepcionista").close();
                // =================================================

                // === EL FIX: ENVIAR LOS DATOS A LA BASE DE DATOS ===
                formRecepcionista.action = `${baseUrl}/admin/recepcionista/${id}/actualizar`;
                if (!document.getElementById('metodo-put-recep')) {
                    formRecepcionista.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT" id="metodo-put-recep">');
                }
                formRecepcionista.submit(); // ¡Esta línea faltaba!
                // ===================================================

            } else {
                // === LÓGICA VISUAL DE TUS COMPAÑEROS (INTACTA) ===
                const nombre = document.getElementById("recep_name").value;
                const correo = document.getElementById("recep_email").value;
                const telefono = document.getElementById("recep_phone").value;
                const fotoSrc = document.getElementById("previsualizacion-avatar-recepcionista").querySelector('img')?.src;
                const nuevaId = Date.now();

                const avatarRender = fotoSrc ? `<img src="${fotoSrc}" style="width:100%; height:100%; object-fit:cover; border-radius:50%;">` : nombre.substring(0, 2).toUpperCase();

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
                document.getElementById("modal-recepcionista").close();
                if (typeof actualizarPaginadoresTotales === 'function') actualizarPaginadoresTotales();
                // =================================================

                // === EL FIX: ENVIAR LOS DATOS A LA BASE DE DATOS ===
                const metodosOcultos = formRecepcionista.querySelectorAll('input[name="_method"]');
                metodosOcultos.forEach(input => input.remove());
                formRecepcionista.action = `${baseUrl}/admin/crear-recepcionista`;
                formRecepcionista.submit(); // ¡Esta línea faltaba!
                // ===================================================
            }
        });
    }

    window.eliminarRecepcionista = function (id) {
        if (confirm("¿Estás seguro de que quieres eliminar a este recepcionista permanentemente?")) {
            const form = document.createElement('form');
            form.method = 'POST';

            // Calculamos la ruta base para que funcione en cualquier servidor
            let baseUrl = window.location.origin;
            if (window.location.pathname.includes('/public')) {
                baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
            }

            // Esta es la ruta hacia tu AdminController@destroyUsuario
            form.action = `${baseUrl}/admin/usuario/${id}`;

            form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('meta[name="csrf-token"]').getAttribute('content')}">
            <input type="hidden" name="_method" value="DELETE">
        `;
            document.body.appendChild(form);
            form.submit();
        }
    }

    // Motor de paginación del lado del admin para Terapeutas y Recepcionistas
    window.paginarBloque = function (contenedorId, numPagina, btnPrevId, btnNextId, infoId) {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) return;
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