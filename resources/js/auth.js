document.addEventListener('DOMContentLoaded', function() {
    const passwordInput = document.getElementById('password');
    const emailInput = document.getElementById('email'); // 1. Capturamos el campo del correo
    const feedbackBox = document.getElementById('password-feedback');
    const registerForm = document.getElementById('registerForm'); 
    
    const reqLength = document.getElementById('length-req');
    const reqAlpha = document.getElementById('alpha-req');
    const reqSpecial = document.getElementById('special-req');
    const strengthText = document.getElementById('strength-text');

    let isPasswordValid = false;

    // --- Validación dinámica de la contraseña ---
    if (passwordInput) {
        passwordInput.addEventListener('input', function() {
            const pwd = passwordInput.value;
            
            feedbackBox.style.display = pwd.length > 0 ? 'block' : 'none';

            const hasLetters = /[a-zA-Z]/.test(pwd);
            const hasNumbers = /[0-9]/.test(pwd);
            const hasSpecial = /[!@#$%^&*(),.?":{}|<>]/.test(pwd);
            const isLongEnough = pwd.length >= 8;

            const updateRequirement = (element, isValid, text) => {
                element.style.color = isValid ? '#198754' : '#dc3545';
                element.innerHTML = isValid ? `✓ ${text}` : `✗ ${text}`;
            };

            updateRequirement(reqLength, isLongEnough, 'Mínimo 8 caracteres');
            updateRequirement(reqAlpha, (hasLetters && hasNumbers), 'Alfanumérica (letras y números)');
            updateRequirement(reqSpecial, hasSpecial, 'Un carácter especial (@, $, !, %, etc.)');

            if (isLongEnough && hasLetters && hasNumbers && hasSpecial) {
                isPasswordValid = true; 
                strengthText.textContent = 'Estado: Contraseña Muy Segura';
                strengthText.style.color = '#198754';
            } else {
                isPasswordValid = false; 
                strengthText.textContent = 'Estado: Contraseña Poco Segura';
                strengthText.style.color = '#dc3545';
            }
        });
    }

    // --- Muro de seguridad al intentar enviar el formulario ---
    if (registerForm) {
        registerForm.addEventListener('submit', function(event) {
            
            // 1. Validar el Correo Electrónico
            const emailValue = emailInput.value.trim();
            // Expresión regular para asegurar que tenga estructura de correo real
            const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/; 

            if (emailValue === '' || !emailRegex.test(emailValue)) {
                event.preventDefault(); // Detiene el envío al servidor
                emailInput.style.border = '2px solid #dc3545'; // Pinta el borde rojo
                emailInput.focus(); // Regresa el cursor al correo
                return; // Corta la ejecución aquí para que el usuario lo arregle primero
            } else {
                emailInput.style.border = ''; // Restaura el borde a su estado normal si está bien
            }

            // 2. Validar la Contraseña (si el correo está bien, pasa a revisar esto)
            if (!isPasswordValid) {
                event.preventDefault(); 
                feedbackBox.style.display = 'block';
                passwordInput.focus();
            }
        });
    }
});