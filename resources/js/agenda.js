document.addEventListener('DOMContentLoaded', () => {
    const formFiltros = document.getElementById('form-filtros-agenda');
    
    // 1. Filtrado dinámico (cuando cambias el terapeuta o la fecha)
    if (formFiltros) {
        formFiltros.addEventListener('change', () => {
            const formData = new FormData(formFiltros);
            const params = new URLSearchParams(formData).toString();
            
            // Redirige manteniendo los filtros activos en la URL
            window.location.href = `${window.location.pathname}?${params}`;
        });
    }
});

/**
 * Función global para abrir el modal de edición
 * @param {number} citaId 
 */
window.abrirModalEditar = function(citaId) {
    console.log("Abriendo modal para la cita:", citaId);
    
    // Aquí es donde harías una llamada fetch al backend:
    // fetch(`/api/citas/${citaId}`)
    //   .then(res => res.json())
    //   .then(data => { /* Llenar campos del modal */ });
    
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