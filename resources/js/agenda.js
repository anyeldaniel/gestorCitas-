document.addEventListener('DOMContentLoaded', () => {
    const formFiltros = document.getElementById('form-filtros-agenda');
    
    // Si usas el ID en el formulario, asegúrate de añadirlo en el HTML
    // En el HTML que te pasé, el formulario no tiene ID, así que lo seleccionamos por clase
    const form = document.querySelector('.agenda-filtros');

    if (form) {
        // Escuchamos cambios en los selects e inputs para enviar el formulario automáticamente
        const inputs = form.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.addEventListener('change', () => {
                form.submit();
            });
        });
    }
});

/**
 * Función global para abrir el modal de edición
 * @param {number} citaId 
 */
window.abrirModalEditar = function(citaId) {
    console.log("Abriendo modal para la cita:", citaId);
    
    const modal = document.getElementById('modal-agenda');
    if (modal) {
        modal.style.display = 'block';
    }
};

/**
 * Función global para cerrar modales
 */
window.cerrarModal = function() {
    const modal = document.getElementById('modal-agenda');
    if (modal) modal.style.display = 'none';
};