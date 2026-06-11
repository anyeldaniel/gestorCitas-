// Lógica del Módulo de Agenda Global

document.addEventListener('DOMContentLoaded', () => {
    //Capturar los elementos de control y las filas
    const selectorTerapeuta = document.getElementById('filtro-terapeuta');
    const inputFecha = document.getElementById('filtro-fecha');
    const filasCitas = document.querySelectorAll('.fila-cita');

    //Acá se hace el filtrado de terapeutas en tiempo real 
    if (selectorTerapeuta) {
        selectorTerapeuta.addEventListener('change', (e) => {
            const seleccion = e.target.value.toLowerCase();
            console.log(`Filtrando agenda para el terapeuta: ${seleccion}`);

            filasCitas.forEach(fila => {
                //Capturamos el valor del atributo data-terapeuta de cada fila
                const terapeutaFila = fila.getAttribute('data-terapeuta').toLowerCase();

                if (seleccion === 'todos') {
                    fila.style.display = 'table-row'; // Muestra todas las filas
                } else if (terapeutaFila.includes(seleccion)) {
                    fila.style.display = 'table-row'; // Muestra las coincidencias
                } else {
                    fila.style.display = 'none';      // Oculta las demás
                }
            });
        });
    }

    // Escuchador para el cambio de fecha (Pendiente para el backend con Fetch/AJAX)
    if (inputFecha) {
        inputFecha.addEventListener('change', (e) => {
            console.log(`Cambiando fecha de control a: ${e.target.value}`);
            // Aquí es donde en el futuro anyel o wladi harán el fetch() al servidor para traer nuevas citas
        });
    }
});

//Exponer las funciones al objeto Window

window.modificarCita = function(id) {
    alert(`Abriendo ventana de reasignación para la cita #${id}`);
};

window.cancelarCita = function(id) {
    if (confirm(`¿Está seguro de que desea remover la cita #${id} de la agenda?`)) {
        console.log(`Solicitud de cancelación enviada para ID: ${id}`);
        // Aquí, anyel o wladi, meterán la lógica de eliminación asíncrona más adelante
    }
};