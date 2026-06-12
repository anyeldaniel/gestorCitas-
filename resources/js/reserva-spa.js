document.addEventListener('DOMContentLoaded', function () {
    const formulario = document.getElementById('form-reserva-spa');
    const contenedorErrores = document.getElementById('contenedor-errores-js');

    formulario.addEventListener('submit', function (event) {
        let mensajesError = [];
        contenedorErrores.style.display = 'none';
        contenedorErrores.innerHTML = '';

        const fechaSeleccionada = document.getElementById('fecha').value;
        const horaSeleccionada = document.getElementById('hora').value;

        //Acá añado esto para validar que no elijan una fecha anterior a hoy
        const hoy = new Date().toISOString().split('T')[0];
        if (fechaSeleccionada < hoy) {
            mensajesError.push('• No puede reservar una sesión en una fecha que ya pasó.');
        }

        // Valido el rango de atención comercial (08:00 AM a 07:00 PM)
        if (horaSeleccionada) {
            const [hora, minutos] = horaSeleccionada.split(':').map(Number);
            if (hora < 8 || hora >= 19) {
                mensajesError.push('• Nuestro horario de atención es de 08:00 AM a 07:00 PM.');
            }
        }

        // Si se encontraron errores, frenamos el envío del formulario
        if (mensajesError.length > 0) {
            event.preventDefault();
            contenedorErrores.innerHTML = mensajesError.join('<br>');
            contenedorErrores.style.display = 'block';
        }
    });
});