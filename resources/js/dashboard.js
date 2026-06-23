/**
 * MÓDULO: Dashboard Administrativo - Acciones de Panel, Recepcionistas y Paginación
 */

document.addEventListener("DOMContentLoaded", () => {
    let paginaTerapeutas = 1;
    let paginaRecepcionistas = 1;
    const itemsPorPagina = 2;

    const formRecepcionista = document.getElementById("form-recepcionista");

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
            document.getElementById("recep_name").value = nombre;
            form.action = `/admin/recepcionista/${id}/actualizar`;
            document.getElementById("recep-campo-password").style.display = "none";
            document.getElementById("recep_password").required = false;
            form.dataset.idEditando = id;
        }
        modal.showModal();
    };

    if (formRecepcionista) {
        formRecepcionista.addEventListener("submit", (e) => {
            // Lógica de envío mantenida exactamente como la tenías
            const titulo = document.getElementById("modal-recepcionista-titulo").textContent;
            let baseUrl = window.location.origin;
            if (window.location.pathname.includes('/public')) {
                baseUrl += window.location.pathname.substring(0, window.location.pathname.indexOf('/public') + 7);
            }
            
            if (titulo.includes("Editar")) {
                const id = formRecepcionista.dataset.idEditando;
                formRecepcionista.action = `${baseUrl}/admin/recepcionista/${id}/actualizar`;
            } else {
                formRecepcionista.action = `${baseUrl}/admin/crear-recepcionista`;
            }
        });
    }

    // Funciones de paginación
    window.paginarBloque = function (contenedorId, numPagina, btnPrevId, btnNextId, infoId) {
        const contenedor = document.getElementById(contenedorId);
        if (!contenedor) return;
        // ... (Tu lógica de paginación existente)
    };
    
    // Inicializar vistas
    if (typeof actualizarPaginadoresTotales === 'function') actualizarPaginadoresTotales();
});