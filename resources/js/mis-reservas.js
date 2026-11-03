document.addEventListener('DOMContentLoaded', function() {
    const overlay = document.getElementById('modal-resena-overlay');
    if (!overlay) return;

    const estrellas = document.querySelectorAll('.estrella-clic');
    const textoPuntuacion = document.getElementById('texto-puntuacion');
    const btnEnviar = document.getElementById('btn-enviar-resena');
    const inputPuntuacion = document.getElementById('resena-puntuacion');
    let puntuacionSeleccionada = 0;

    const etiquetas = { 1: 'Muy malo 😞', 2: 'Malo 😕', 3: 'Regular 😐', 4: 'Bueno 😊', 5: '¡Excelente! 🤩' };

    window.abrirModalResena = function(citaId, nombreTerapeuta) {
        puntuacionSeleccionada = 0;
        estrellas.forEach(e => e.style.color = '#d1c7bd');
        textoPuntuacion.innerHTML = 'Selecciona una puntuación';
        btnEnviar.disabled = true;
        btnEnviar.style.opacity = '0.5';
        document.getElementById('comentario-resena').value = '';
        
        document.getElementById('resena-cita-id').value = citaId;
        document.getElementById('resena-nombre-terapeuta').textContent = nombreTerapeuta;
        inputPuntuacion.value = 0;
        
        overlay.style.display = 'flex'; 
    };

    window.cerrarModalResena = function() {
        overlay.style.display = 'none';
    };

    estrellas.forEach(estrella => {
        estrella.addEventListener('mouseenter', function() {
            const valor = parseInt(this.dataset.valor);
            estrellas.forEach(e => {
                e.style.color = parseInt(e.dataset.valor) <= valor ? '#f5b301' : '#d1c7bd';
            });
        });

        estrella.addEventListener('mouseleave', function() {
            estrellas.forEach(e => {
                e.style.color = parseInt(e.dataset.valor) <= puntuacionSeleccionada ? '#f5b301' : '#d1c7bd';
            });
        });

        estrella.addEventListener('click', function() {
            puntuacionSeleccionada = parseInt(this.dataset.valor);
            estrellas.forEach(e => {
                e.style.color = parseInt(e.dataset.valor) <= puntuacionSeleccionada ? '#f5b301' : '#d1c7bd';
            });
            textoPuntuacion.innerHTML = `Has seleccionado <strong>${puntuacionSeleccionada} de 5</strong> — ${etiquetas[puntuacionSeleccionada]}`;
            inputPuntuacion.value = puntuacionSeleccionada;
            btnEnviar.disabled = false;
            btnEnviar.style.opacity = '1';
        });
    });

    overlay.addEventListener('click', function(e) {
        if (e.target === overlay) cerrarModalResena();
    });
});