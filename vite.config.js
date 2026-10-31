import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    base: './',   // ✅ hace que las rutas del CSS sean relativas
    plugins: [
        laravel({
            input: [
                // === Base ===
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/layout.js',
                'resources/js/bootstrap.js',
                

                // === Auth (login/registro/password) ===
                'resources/css/auth.css',
                'resources/js/auth.js',

                // === Dashboard / Admin ===
                'resources/js/dashboard.js',
                'resources/css/reportes.css',
                'resources/js/reportes.js',
                'resources/js/servicios.js',

                // === Agenda ===
                'resources/css/agenda.css',
                'resources/js/agenda.js',

                // === Terapeutas ===
                'resources/css/terapeutas.css',
                'resources/js/terapeutas.js',

                // === Catálogo ===
                'resources/css/modal.css',
                'resources/js/catalogo.js',

                // === Pagos ===
                'resources/css/pasarela-pago.css',
                'resources/js/pasarela-pago.js',
                'resources/css/pago-checkout.css',
                'resources/js/pago-checkout.js',
                'resources/css/verificar-pago.css',
                'resources/js/verificar-pago.js',

                // === Reserva ===
                'resources/css/reserva.css',
                'resources/js/reserva.js',
                'resources/css/mis-reservas.css',
                'resources/js/mis-reservas.js',
            ],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
});