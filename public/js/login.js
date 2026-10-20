document.addEventListener('DOMContentLoaded', function () {
    // 1. LÓGICA DEL PANEL DESLIZANTE
    const btnNuevoLogin = document.getElementById('btn-panel-login');
    const btnNuevoRegistro = document.getElementById('btn-panel-registro');
    const contenedorPrincipal = document.getElementById('contenedor-principal');

    if (btnNuevoRegistro && contenedorPrincipal) {
        // Al hacer clic en "Crear Cuenta", movemos el panel a la izquierda
        btnNuevoRegistro.addEventListener('click', function() {
            contenedorPrincipal.classList.add('activo');
        });
    }

    if (btnNuevoLogin && contenedorPrincipal) {
        // Al hacer clic en "Iniciar Sesión", regresamos el panel a la derecha
        btnNuevoLogin.addEventListener('click', function() {
            contenedorPrincipal.classList.remove('activo');
        });
    }
});

// 2. FUNCIÓN PARA VER/OCULTAR CONTRASEÑA (El ojito)
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