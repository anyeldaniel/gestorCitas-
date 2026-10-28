/**
 * auth.js — The Beauty Room
 * Toda la lógica de la vista de autenticación (login + registro).
 * El Blade no ejecuta JS: solo carga este archivo y pasa datos por data-attributes.
 */

// =============================================
// FUNCIÓN GLOBAL: ojito para ver/ocultar contraseña
// (el Blade la llama por onclick="togglePassword('id', this)")
// =============================================
function togglePassword(inputId, iconElement) {
    const input = document.getElementById(inputId);
    if (!input) return;

    if (input.type === 'password') {
        input.type = 'text';
        // Cambiamos el ícono a ojo tachado y aplicamos el color oscuro
        if (iconElement) {
            iconElement.innerHTML = '<i data-lucide="eye-off"></i>';
            iconElement.style.filter = 'grayscale(0) opacity(1)';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    } else {
        input.type = 'password';
        // Volvemos al ícono de ojo normal y lo atenuamos
        if (iconElement) {
            iconElement.innerHTML = '<i data-lucide="eye"></i>';
            iconElement.style.filter = 'grayscale(100%) opacity(0.5)';
            if (typeof lucide !== 'undefined') lucide.createIcons();
        }
    }
}

document.addEventListener('DOMContentLoaded', function () {

    // =============================================
    // 0. INICIALIZAR ÍCONOS LUCIDE
    // =============================================
    if (typeof lucide !== 'undefined') {
        lucide.createIcons();
    }

    // =============================================
    // 1. TOGGLE ENTRE PANEL LOGIN / REGISTRO
    // =============================================
    const contenedor = document.getElementById('contenedor-principal');
    const btnPanelRegistro = document.getElementById('btn-panel-registro');
    const btnPanelLogin = document.getElementById('btn-panel-login');
    const btnIrRegistro = document.getElementById('btn-ir-registro');
    const btnIrLogin = document.getElementById('btn-ir-login');

    // Botones móviles agregados
    const btnMovilARegistro = document.getElementById('btn-movil-a-registro');
    const btnMovilALogin = document.getElementById('btn-movil-a-login');

    if (btnPanelRegistro) btnPanelRegistro.addEventListener('click', () => contenedor.classList.add('activo'));
    if (btnIrRegistro) btnIrRegistro.addEventListener('click', () => contenedor.classList.add('activo'));
    if (btnMovilARegistro) {
        btnMovilARegistro.addEventListener('click', (e) => {
            e.preventDefault();
            contenedor.classList.add('activo');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    if (btnPanelLogin) btnPanelLogin.addEventListener('click', () => contenedor.classList.remove('activo'));
    if (btnIrLogin) btnIrLogin.addEventListener('click', () => contenedor.classList.remove('activo'));
    if (btnMovilALogin) {
        btnMovilALogin.addEventListener('click', (e) => {
            e.preventDefault();
            contenedor.classList.remove('activo');
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }


    // =============================================
    // 2. OJITO (listener adicional, por si algún botón no usa onclick)
    // =============================================
    document.querySelectorAll('.btn-ver-clave').forEach(boton => {
        boton.addEventListener('click', function () {
            const input = this.previousElementSibling;
            if (!input) return;

            if (input.type === 'password') {
                input.type = 'text';
                this.innerHTML = '<i data-lucide="eye-off"></i>';
                this.style.filter = 'grayscale(0) opacity(1)';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            } else {
                input.type = 'password';
                this.innerHTML = '<i data-lucide="eye"></i>';
                this.style.filter = 'grayscale(100%) opacity(0.5)';
                if (typeof lucide !== 'undefined') lucide.createIcons();
            }
        });
    });


    // =============================================
    // 3. VALIDACIÓN DE CONTRASEÑA EN VIVO
    // =============================================
    const passwordInput = document.getElementById('password_reg');
    const emailInput = document.getElementById('email_reg');
    const registerForm = document.getElementById('form-registro');

    let isPasswordValid = false;

    if (passwordInput) {
        passwordInput.addEventListener('input', function () {
            const pwd = passwordInput.value;

            const reqLength = document.getElementById('length-req');
            const reqAlpha = document.getElementById('alpha-req');
            const reqSpecial = document.getElementById('special-req');
            const strengthText = document.getElementById('strength-text');
            const feedbackBox = document.getElementById('password-feedback');

            if (feedbackBox) feedbackBox.style.display = pwd.length > 0 ? 'block' : 'none';

            const hasLetters = /[a-zA-Z]/.test(pwd);
            const hasNumbers = /[0-9]/.test(pwd);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
            const isLongEnough = pwd.length >= 8;

            const updateRequirement = (element, isValid, text) => {
                if (!element) return;
                element.style.color = isValid ? '#198754' : '#dc3545';
                element.innerHTML = isValid ? `✓ ${text}` : `✗ ${text}`;
            };

            updateRequirement(reqLength, isLongEnough, 'Mínimo 8 caracteres');
            updateRequirement(reqAlpha, (hasLetters && hasNumbers), 'Alfanumérica (letras y números)');
            updateRequirement(reqSpecial, hasSpecial, 'Un carácter especial (@, $, !, %, etc.)');

            if (isLongEnough && hasLetters && hasNumbers && hasSpecial) {
                isPasswordValid = true;
                if (strengthText) {
                    strengthText.textContent = 'Estado: Contraseña Muy Segura';
                    strengthText.style.color = '#198754';
                }
            } else {
                isPasswordValid = false;
                if (strengthText) {
                    strengthText.textContent = 'Estado: Contraseña Poco Segura';
                    strengthText.style.color = '#dc3545';
                }
            }
        });
    }

    // --- 4. VALIDACIÓN AL ENVIAR EL REGISTRO ---
    if (registerForm) {
        registerForm.addEventListener('submit', function (event) {
            const emailValue = emailInput ? emailInput.value.trim() : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

            if (emailValue === '' || !emailRegex.test(emailValue)) {
                event.preventDefault();
                if (emailInput) {
                    emailInput.style.border = '2px solid #dc3545';
                    emailInput.focus();
                }
                return;
            } else if (emailInput) {
                emailInput.style.border = '';
            }

            if (!isPasswordValid) {
                event.preventDefault();
                if (passwordInput) passwordInput.focus();
                return; // <-- IMPORTANTE: Evita que el SweetAlert de "Creando cuenta" salga si hay error
            }
        });
    }
    // =============================================
    // 5. SWEETALERT2
    // =============================================
    if (typeof Swal !== 'undefined') {

        // 5a. Loading al enviar el registro
        if (registerForm) {
            registerForm.addEventListener('submit', function () {
                setTimeout(() => {
                    Swal.fire({
                        title: 'Creando tu cuenta...',
                        text: 'Estamos registrando tu perfil en The Beauty Room.',
                        allowOutsideClick: false,
                        showConfirmButton: false,
                        didOpen: () => { Swal.showLoading(); }
                    });
                }, 10);
            });
        }

        // 5b. Datos que vienen del Blade (sin lógica en el Blade)
        const config = document.getElementById('auth-config');

        if (config) {
            // Alerta de éxito tras registro
            if (config.dataset.success === '1') {
                Swal.fire({
                    icon: 'success',
                    title: '¡Cuenta creada con éxito!',
                    text: 'Ya eres parte de The Beauty Room. Por favor, inicia sesión con tus datos para comenzar a agendar tus citas.',
                    confirmButtonText: 'Iniciar Sesión',
                    confirmButtonColor: '#5A6B5D',
                    background: '#FFFFFF',
                    color: '#2C352D'
                });
            }

            // Mantener el panel en Registro si hay errores de validación
            if (config.dataset.forceRegister === '1' && contenedor) {
                contenedor.classList.add('activo');
            }
        }
    }
});