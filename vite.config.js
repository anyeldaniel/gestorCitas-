    import { defineConfig } from 'vite';
    import laravel from 'laravel-vite-plugin';
    import tailwindcss from '@tailwindcss/vite';

    export default defineConfig({
        plugins: [
            laravel({
                input: ['resources/css/app.css', 
                        'resources/js/app.js',
                        'resources/js/dashboard.js', // Acá añado las rutas de los archivos js
                        'resources/js/reportes.js',  
                        'resources/js/servicios.js', 
                        'resources/js/agenda.js', //acá añado la ruta del nuevo archivo js para el módulo de agenda
                        'resources/css/terapeutas.css', // Acá añado la ruta del nuevo archivo css para el módulo de terapeutas
                        'resources/js/terapeutas.js' //Mi nuevo archivo js para el módulo de terapeutas
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
