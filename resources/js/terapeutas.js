// Lógica del Módulo de Gestión de Terapeutas - The Beauty Room

document.addEventListener('DOMContentLoaded', () => {
    console.log('Módulo de Terapeutas cargado e inicializado de manera independiente.');
});

/**
 * Simulación para abrir el flujo de creación de un nuevo terapeuta
 */
window.abrirModalAgregar = function() {
    // Por ahora usamos un prompt semántico para prototipar los datos rápidamente
    const nombre = prompt('Ingrese el nombre completo del especialista:');
    if (!nombre) return;

    const especialidad = prompt('Ingrese la especialidad (Ej: Cosmiatría, Dermatología):');
    if (!especialidad) return;

    alert(`¡Simulación exitosa!\nSe enviará al backend el registro de: ${nombre} (${especialidad}).`);
    // Aquí es donde Anyel o Wladi meterán más adelante el modal real de HTML o la petición fetch()
};

/**
 * Simulación para editar la información de un especialista existente
 * @param {number} id - ID único del terapeuta
 */
window.editarTerapeuta = function(id) {
    console.log(`Solicitando datos del terapeuta con ID: ${id} para edición.`);
    alert(`Abriendo panel de edición para el especialista con registro número: #${id}`);
};

/**
 * Simulación con confirmación nativa para dar de baja a un terapeuta
 * @param {number} id - ID único del terapeuta
 */
window.eliminarTerapeuta = function(id) {
    const tarjeta = document.querySelector(`article[data-id="${id}"]`);
    const nombreTerapeuta = tarjeta ? tarjeta.querySelector('h2').textContent : `ID #${id}`;

    if (confirm(`¿Está completamente seguro de que desea remover a ${nombreTerapeuta} del sistema de The Beauty Room?`)) {
        console.log(`Petición DELETE enviada al servidor para el ID: ${id}`);
        alert(`El especialista ${nombreTerapeuta} ha sido removido con éxito (Modo Simulación).`);
        
        // Efecto visual instantáneo en el frontend: ocultar la tarjeta
        if (tarjeta) {
            tarjeta.style.transition = 'all 0.3s ease';
            tarjeta.style.opacity = '0';
            tarjeta.style.transform = 'scale(0.9)';
            setTimeout(() => tarjeta.remove(), 300);
        }
    }
};