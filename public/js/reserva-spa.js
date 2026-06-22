document.addEventListener('DOMContentLoaded', function() {
    const selectServicio = document.getElementById('servicio_id');
    const selectTrabajador = document.getElementById('trabajador_id');

    // Función que va al servidor y actualiza la lista
    function actualizarEspecialistas() {
        const servicioId = selectServicio.value;

        if (!servicioId) return;

        // Le ponemos texto de "Cargando..." para que se vea profesional
        selectTrabajador.innerHTML = '<option value="aleatorio">Buscando especialistas...</option>';

        // Hacemos la petición AJAX a la ruta que creamos en Laravel
        fetch(baseUrl + '/especialistas-por-servicio/' + servicioId)
            .then(response => response.json())
            .then(data => {
                // Limpiamos el select
                selectTrabajador.innerHTML = '<option value="aleatorio">Cualquiera disponible</option>';

                // Llenamos con los nuevos datos que trajo la base de datos
                data.forEach(especialista => {
                    const option = document.createElement('option');
                    option.value = especialista.id;
                    option.textContent = especialista.nombre;
                    selectTrabajador.appendChild(option);
                });
            })
            .catch(error => {
                console.error("Error cargando especialistas:", error);
                selectTrabajador.innerHTML = '<option value="aleatorio">Cualquiera disponible</option>';
            });
    }

    // Escuchar cada vez que el cliente cambia el servicio en el selector
    selectServicio.addEventListener('change', actualizarEspecialistas);

    // Si ya viene un servicio seleccionado desde el catálogo, filtra inmediatamente
    if (selectServicio.value) {
        actualizarEspecialistas();
    }
});