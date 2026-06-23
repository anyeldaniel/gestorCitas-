/**
 * MÓDULO: Gestión de Terapeutas e Interacciones de Interfaz Nativas
 */

document.addEventListener("DOMContentLoaded", () => {
    const formTerapeutaModal = document.getElementById("form-terapeuta");

    if (formTerapeutaModal) {
        formTerapeutaModal.addEventListener("submit", (e) => {
            // Sincronizar antes de enviar
            sincronizarEspecialidades();

            if (!validarContrasenas()) {
                e.preventDefault();
                return;
            }
        });
    }
});

// --- Funciones de Gestión de Datos ---

window.sincronizarEspecialidades = function() {
    const inputsEsp = document.querySelectorAll(".input-especialidad-item");
    const listaEspecialidades = [];
    inputsEsp.forEach(input => {
        if(input.value.trim() !== "") listaEspecialidades.push(input.value.trim());
    });
    const inputOculto = document.getElementById("especialidades_hidden");
    if(inputOculto) inputOculto.value = listaEspecialidades.join(",");
};

window.abrirModalAgregar = function() {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    
    // Limpiar form
    form.reset();
    form.action = `${window.location.origin}/admin/crear-trabajador`;
    
    // Eliminar método PUT si existe
    const put = document.getElementById('metodo-put-laravel');
    if (put) put.remove();

    document.getElementById('modal-titulo').textContent = "Registrar Nuevo Especialista";
    document.getElementById('previsualizacion-avatar-terapeuta').innerHTML = "TF";
    
    // Resetear especialidades
    document.getElementById('wrapper-especialidades-lista').innerHTML = `
        <div class="fila-especialidad">
            <input type="text" class="input-especialidad-item" placeholder="Ej. Rejuvenecimiento Facial" required>
            <button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button>
        </div>
    `;

    modal.showModal();
};

window.editarTerapeuta = function(id) {
    const modal = document.getElementById('modal-terapeuta');
    const form = document.getElementById('form-terapeuta');
    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    
    if (!tarjeta) return;

    // Configurar acción del form
    form.action = `${window.location.origin}/admin/trabajador/${id}`;
    if (!document.getElementById('metodo-put-laravel')) {
        form.insertAdjacentHTML('beforeend', '<input type="hidden" name="_method" value="PUT" id="metodo-put-laravel">');
    }

    document.getElementById('modal-titulo').textContent = "Editar Información del Especialista";
    
    // Cargar datos en inputs
    document.getElementById('nombre').value = tarjeta.querySelector('h2')?.textContent.trim() || '';
    document.getElementById('email').value = tarjeta.querySelector('.terapeuta-email')?.textContent.trim() || '';
    document.getElementById('descripcion').value = tarjeta.querySelector('.terapeuta-descripcion')?.textContent.trim() || '';
    document.getElementById('telefono').value = tarjeta.querySelector('.terapeuta-telefono')?.textContent.replace('Teléfono:', '').trim() || '';

    // Cargar Especialidades
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    wrapper.innerHTML = '';
    const espData = tarjeta.dataset.especialidades ? tarjeta.dataset.especialidades.split(',') : [];
    
    espData.forEach((esp, i) => {
        if(i === 0) {
            wrapper.innerHTML = `<div class="fila-especialidad"><input type="text" class="input-especialidad-item" value="${esp.trim()}" required><button type="button" class="btn-añadir-especialidad" onclick="agregarCampoEspecialidad('')">+</button></div>`;
        } else {
            agregarCampoEspecialidad(esp.trim());
        }
    });

    modal.showModal();
};

window.verTerapeutaDetalle = function(id) {
    const modal = document.getElementById('modal-ver-terapeuta');
    const tarjeta = document.querySelector(`.tarjeta-terapeuta[data-id="${id}"]`);
    if (!modal || !tarjeta) return;

    // Poblar vista
    document.getElementById('view-terapeuta-nombre').textContent = tarjeta.querySelector('h2').textContent;
    document.getElementById('view-terapeuta-telefono').textContent = tarjeta.querySelector('.terapeuta-telefono').textContent;
    document.getElementById('view-terapeuta-descripcion').textContent = tarjeta.querySelector('.terapeuta-descripcion').textContent;
    
    // Acciones Admin en el modal
    const acciones = document.getElementById('view-acciones-admin');
    acciones.innerHTML = `
        <button type="button" class="btn-zen" onclick="document.getElementById('modal-ver-terapeuta').close(); editarTerapeuta(${id});">Editar</button>
        <button type="button" class="btn-zen btn-baja" onclick="document.getElementById('modal-ver-terapeuta').close(); eliminarTerapeuta(${id});">Dar de Baja</button>
    `;

    modal.showModal();
};

// --- Alerta de Eliminación Personalizada ---
window.eliminarTerapeuta = function(id) {
    const modal = document.getElementById('modal-confirmacion-custom');
    document.getElementById('confirm-alerta-titulo').textContent = "¿Estás seguro?";
    document.getElementById('confirm-alerta-mensaje').textContent = "Esta acción eliminará permanentemente al especialista del sistema.";

    modal.showModal();

    // Reemplazar botones para evitar duplicidad de eventos
    const btnAceptar = document.getElementById('btn-confirm-aceptar');
    const nuevoAceptar = btnAceptar.cloneNode(true);
    btnAceptar.parentNode.replaceChild(nuevoAceptar, btnAceptar);

    nuevoAceptar.onclick = () => {
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `${window.location.origin}/admin/usuario/${id}`;
        form.innerHTML = `
            <input type="hidden" name="_token" value="${document.querySelector('input[name="_token"]').value}">
            <input type="hidden" name="_method" value="DELETE">
        `;
        document.body.appendChild(form);
        form.submit();
    };

    document.getElementById('btn-confirm-cancelar').onclick = () => modal.close();
};

// --- Utilidades ---
window.agregarCampoEspecialidad = function(valor = '') {
    const wrapper = document.getElementById('wrapper-especialidades-lista');
    const div = document.createElement('div');
    div.className = 'fila-especialidad';
    div.innerHTML = `<input type="text" class="input-especialidad-item" value="${valor}" required><button type="button" class="btn-remover-especialidad" onclick="this.parentElement.remove()">X</button>`;
    wrapper.appendChild(div);
};

window.validarContrasenas = function() {
    const p = document.getElementById('password');
    const cp = document.getElementById('password_confirmation');
    if (p.value !== cp.value) {
        alert("🚨 Las contraseñas no coinciden.");
        return false;
    }
    return true;
};