document.addEventListener('DOMContentLoaded', function () {
    // panel deslizante
    const btnNuevoLogin = document.getElementById('btn-panel-login');
    const btnNuevoRegistro = document.getElementById('btn-panel-registro');
    const contenedorPrincipal = document.getElementById('contenedor-principal');

    if (btnNuevoRegistro && contenedorPrincipal) {
        // esro es para cuando se haga click en crear cuenta se mueva el apnel a la izquierda
        btnNuevoRegistro.addEventListener('click', function() {
            contenedorPrincipal.classList.add('activo');
        });
    }

    if (btnNuevoLogin && contenedorPrincipal) {
        // cuando se hace click en iniciar sesion el panel se mueve a la derecha
        btnNuevoLogin.addEventListener('click', function() {
            contenedorPrincipal.classList.remove('activo');
        });
    }
});

// el ojito para ver contraseña
// Esta función está fuera del DOMContentLoaded porque se llama directo desde el HTML (onclick)
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (input.type === 'password') {
        input.type = 'text'; // Muestra la clave
        iconElement.textContent = '🫣';
    } else {
        input.type = 'password'; // Oculta la clave
        iconElement.textContent = '👁️';
    }
}