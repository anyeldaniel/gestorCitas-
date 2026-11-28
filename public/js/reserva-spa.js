document.addEventListener('DOMContentLoaded', function() {
    const selectServicio = document.getElementById('servicio_id');
    const selectTrabajador = document.getElementById('trabajador_id');

    // esta es la funcion que va al servidor para actualizar la lista
    function actualizarEspecialistas() {
        const servicioId = selectServicio.value;

        if (!servicioId) return;

        // texto de cargando
        selectTrabajador.innerHTML = '<option value="aleatorio">Buscando especialistas...</option>';

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
r
    selectServicio.addEventListener('change', actualizarEspecialistas);

    if (selectServicio.value) {
        actualizarEspecialistas();
    }
});