/**
 * LÓGICA DE GESTIÓN DEL MENÚ Y COMPORTAMIENTO DEL HEADER
 */

// Elementos
const mobileMenu = document.getElementById('mobile-menu');
const hamburgerBtn = document.getElementById('hamburger-btn');
const header = document.getElementById('main-header');

// --- 1. Lógica del Menú Móvil ---

const closeMenu = () => {
    mobileMenu.classList.remove('active');
    document.body.style.overflow = '';
};

// Abrir menú
hamburgerBtn.addEventListener('click', (e) => {
    e.stopPropagation();
    mobileMenu.classList.add('active');
    document.body.style.overflow = 'hidden';
});

// Cerrar al hacer clic en enlaces dentro del menú
mobileMenu.querySelectorAll('a').forEach(link => {
    link.addEventListener('click', closeMenu);
});

// Cerrar al hacer clic fuera del menú
document.addEventListener('click', (e) => {
    if (mobileMenu.classList.contains('active') && 
        !mobileMenu.contains(e.target) && 
        e.target !== hamburgerBtn) {
        closeMenu();
    }
});

// Cerrar con la X
mobileMenu.querySelector('button').addEventListener('click', closeMenu);

// Respetar cambios de tamaño (cerrar en modo escritorio)
window.addEventListener('resize', () => {
    if (window.innerWidth > 768) closeMenu();
});

// --- 2. Lógica del Scroll (Ocultar/Mostrar Header) ---

let lastScrollTop = 0;

window.addEventListener('scroll', () => {
    // Solo permitimos ocultar el header si el menú móvil NO está abierto
    if (!mobileMenu.classList.contains('active')) {
        let scrollTop = window.pageYOffset || document.documentElement.scrollTop;
        
        // Evitamos que ocurra en el tope de la página (scrollTop > 100)
        if (scrollTop > lastScrollTop && scrollTop > 100) {
            // Bajando: ocultar
            header.classList.add('header-hidden');
        } else {
            // Subiendo: mostrar
            header.classList.remove('header-hidden');
        }
        
        lastScrollTop = scrollTop <= 0 ? 0 : scrollTop;
    }
}, { passive: true });