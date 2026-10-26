document.addEventListener('DOMContentLoaded', function() {
    // --- 1. LÓGICA PARA CAMBIAR ENTRE LOGIN Y REGISTRO ---
    const contenedor = document.getElementById('contenedor-principal');
    const btnPanelRegistro = document.getElementById('btn-panel-registro');
    const btnPanelLogin = document.getElementById('btn-panel-login');
    const btnIrRegistro = document.getElementById('btn-ir-registro');
    const btnIrLogin = document.getElementById('btn-ir-login');

    if (btnPanelRegistro) btnPanelRegistro.addEventListener('click', () => contenedor.classList.add('activo'));
    if (btnIrRegistro) btnIrRegistro.addEventListener('click', () => contenedor.classList.add('activo'));
    
    if (btnPanelLogin) btnPanelLogin.addEventListener('click', () => contenedor.classList.remove('activo'));
    if (btnIrLogin) btnIrLogin.addEventListener('click', () => contenedor.classList.remove('activo'));


    // --- 2. LÓGICA DEL OJITO PARA VER CONTRASEÑA ---
    const botonesVerClave = document.querySelectorAll('.btn-ver-clave');
    botonesVerClave.forEach(boton => {
        boton.addEventListener('click', function() {
            const input = this.previousElementSibling;
            if (input.type === 'password') {
                input.type = 'text';
                this.style.filter = 'grayscale(0) opacity(1)';
            } else {
                input.type = 'password';
                this.style.filter = 'grayscale(100%) opacity(0.5)';
            }
        });
    });


    // --- 3. VALIDACIÓN DE CONTRASEÑA Y CORREO (Tu código original) ---
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('correo-electronico'); // Ajustado al ID de tu login
    const registerForm = document.getElementById('form-registro'); 

    let isPasswordValid = false;

    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const pwd = passwordInput.value;
            
            // Si tienes los elementos de feedback en el HTML, esta parte los actualizará
            const reqLength = document.getElementById('length-req');
            const reqAlpha = document.getElementById('alpha-req');
            const reqSpecial = document.getElementById('special-req');
            const strengthText = document.getElementById('strength-text');
            const feedbackBox = document.getElementById('password-feedback');

            if(feedbackBox) feedbackBox.style.display = pwd.length > 0 ? 'block' : 'none';

            const hasLetters = /[a-zA-Z]/.test(pwd);
            const hasNumbers = /[0-9]/.test(pwd);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
            const isLongEnough = pwd.length >= 8;

            const updateRequirement = (element, isValid, text) => {
                if(!element) return;
                element.style.color = isValid ? '#198754' : '#dc3545';
                element.innerHTML = isValid ? `✓ ${text}` : `✗ ${text}`;
            };

            updateRequirement(reqLength, isLongEnough, 'Mínimo 8 caracteres');
            updateRequirement(reqAlpha, (hasLetters && hasNumbers), 'Alfanumérica (letras y números)');
            updateRequirement(reqSpecial, hasSpecial, 'Un carácter especial (@, $, !, %, etc.)');

            if (isLongEnough && hasLetters && hasNumbers && hasSpecial) {
                isPasswordValid = true; 
                if(strengthText) {
                    strengthText.textContent = 'Estado: Contraseña Muy Segura';
                    strengthText.style.color = '#198754';
                }
            } else {
                isPasswordValid = false; 
                if(strengthText) {
                    strengthText.textContent = 'Estado: Contraseña Poco Segura';
                    strengthText.style.color = '#dc3545';
                }
            }
        });
    }

    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            const emailValue = emailInput ? emailInput.value.trim() : '';
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 

            if (emailValue === '' || !emailRegex.test(emailValue)) {
                event.preventDefault(); 
                if(emailInput) {
                    emailInput.style.border = '2px solid #dc3545';
                    emailInput.focus();
                }
                return; 
            } else {
                if(emailInput) emailInput.style.border = ''; 
            }

            if (!isPasswordValid) {
                event.preventDefault(); 
                if(passwordInput) passwordInput.focus();
            }
        });
    }

    // --- 4. ALERTAS DE SWEETALERT2 (Lógica movida aquí) ---
    if (typeof Swal !== 'undefined') {
        // Alerta de carga
        if (registerForm) {
            registerForm.addEventListener('submit', function() {
                // Solo mostrar el loading si la validación previa pasó (no está prevenida)
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

        // Alerta de éxito
        const successDiv = document.getElementById('session-success');
        if (successDiv) {
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
    }
});