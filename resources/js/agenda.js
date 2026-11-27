document.addEventListener('DOMContentLoaded', () => {
    
    /* =========================================================
       1. LÓGICA PARA DESPLEGAR Y OCULTAR LOS DETALLES DE LA CITA
       ========================================================= */
    const botonesExpandir = document.querySelectorAll('[data-expandir-cita]');

    botonesExpandir.forEach(boton => {
        boton.addEventListener('click', function(e) {
            e.preventDefault();
            const citaId = this.dataset.cita;
            const detalles = document.getElementById(`detalles-cita-${citaId}`);
            const estaExpandido = this.classList.contains('is-expanded');

            if (estaExpandido) {
                // Contraer
                detalles.setAttribute('hidden', '');
                this.classList.remove('is-expanded');
                this.setAttribute('aria-expanded', 'false');
            } else {
                // Expandir
                detalles.removeAttribute('hidden');
                this.classList.add('is-expanded');
                this.setAttribute('aria-expanded', 'true');
            }

            // --- CORRECCIÓN PARA EL ICONO ---
            // Buscamos el SVG que Lucide ya renderizó dentro del botón
            const iconoSVG = this.querySelector('svg');
            if (iconoSVG) {
                // Alternamos una clase de rotación (puedes usar tu clase 'is-expanded' o una personalizada)
                // Si usas Tailwind: iconoSVG.classList.toggle('rotate-180'); 
                // Si usas CSS puro, asegúrate de tener: .rotate-180 { transform: rotate(180deg); }
                iconoSVG.classList.toggle('rotate-180');
            }
        });
    });

    /* =========================================================
       2. LÓGICA GENERAL PARA CERRAR MODALES
       ========================================================= */
    const botonesCerrar = document.querySelectorAll('[data-close-modal]');
    
    botonesCerrar.forEach(boton => {
        boton.addEventListener('click', (e) => {
            e.preventDefault();
            const modal = boton.closest('dialog');
            if (modal) {
                modal.close();
            }
        });
    });

    /* =========================================================
       3. LÓGICA PARA EL MODAL DE DECLINAR / CANCELAR CITA
       ========================================================= */
    const botonesDeclinar = document.querySelectorAll('[data-declinar]');
    const modalCancelar = document.getElementById('modal-cancelar-cita');
    const formCancelar = document.getElementById('form-cancelar-cita');

    if (modalCancelar && formCancelar) {
        botonesDeclinar.forEach(boton => {
            boton.addEventListener('click', function(e) {
                e.preventDefault();
                
                // Obtener los datos inyectados desde Blade en el HTML
                const url = this.dataset.url;
                const cliente = this.dataset.cliente;
                const servicio = this.dataset.servicio;
                const fecha = this.dataset.fecha;
                const hora = this.dataset.hora;
                const puedeDefinirReembolso = this.dataset.puedeDefinirReembolso === '1';
                const pagosAprobados = JSON.parse(this.dataset.pagosAprobados || '[]');

                // Llenar los campos del modal
                formCancelar.action = url;
                document.getElementById('cancel-cliente').textContent = cliente;
                document.getElementById('cancel-servicio').textContent = servicio;
                document.getElementById('cancel-hora').textContent = hora;
                document.getElementById('cancel-fecha').textContent = fecha;

                // Generar iniciales para el avatar del modal
                const iniciales = cliente.split(' ').map(n => n[0]).join('').substring(0, 2).toUpperCase();
                document.getElementById('cancel-avatar').textContent = iniciales || '—';

                // Manejar la sección de reembolsos dinámicamente
                const camposReembolso = document.getElementById('campos-reembolso-cita');
                const listaReembolsos = document.getElementById('lista-reembolsos-cita');
                const avisoPendiente = document.getElementById('aviso-reembolso-pendiente');

                if (puedeDefinirReembolso && pagosAprobados.length > 0) {
                    camposReembolso.removeAttribute('hidden');
                    avisoPendiente.setAttribute('hidden', '');
                    listaReembolsos.innerHTML = ''; // Limpiar contenido anterior

                    pagosAprobados.forEach(pago => {
                        listaReembolsos.innerHTML += `
                            <div style="margin-bottom: 12px;">
                                <label style="font-size: 0.85rem; color: #555; display: block; margin-bottom: 4px;">Pago #${pago.id} (Anticipo: $${pago.monto})</label>
                                <input type="number" name="reembolsos[${pago.id}]" step="0.01" max="${pago.monto}" value="${pago.monto}" class="input-reembolso" style="width: 100%; padding: 10px; border: 1px solid #ccc; border-radius: 6px;">
                            </div>
                        `;
                    });
                } else if (!puedeDefinirReembolso) {
                    camposReembolso.setAttribute('hidden', '');
                    avisoPendiente.removeAttribute('hidden');
                } else {
                    camposReembolso.setAttribute('hidden', '');
                    avisoPendiente.setAttribute('hidden', '');
                }

                // Abrir el modal
                modalCancelar.showModal();
            });
        });
    }

    /* =========================================================
       4. LÓGICA PARA EL MODAL DE CAMBIAR ESTADO
       ========================================================= */
    const botonesCambiarEstado = document.querySelectorAll('[data-cambiar-estado]');
    const modalEstado = document.getElementById('modal-cambiar-estado');

    if (modalEstado) {
        let urlCambioEstado = '';

        // Abrir modal y guardar la URL de la cita seleccionada
        botonesCambiarEstado.forEach(boton => {
            boton.addEventListener('click', function(e) {
                e.preventDefault();
                urlCambioEstado = this.dataset.url;
                modalEstado.showModal();
            });
        });

        // Detectar clic en las opciones de estado dentro del modal
        const botonesOpcionEstado = modalEstado.querySelectorAll('[data-estado]');
        botonesOpcionEstado.forEach(botonOpcion => {
            botonOpcion.addEventListener('click', async function(e) {
                e.preventDefault();
                const nuevoEstado = this.dataset.estado;
                
                try {
                    // Obtener token CSRF de Laravel
                    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content');

                    // Petición al servidor
                    const response = await fetch(urlCambioEstado, {
                        method: 'POST', // Cambia a 'PUT' si tu ruta en Laravel lo requiere
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': csrfToken || '',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ estado: nuevoEstado })
                    });

                    if (response.ok) {
                        window.location.reload(); // Recargar para reflejar el nuevo estado
                    } else {
                        alert('Hubo un error al cambiar el estado de la cita.');
                    }
                } catch (error) {
                    console.error('Error:', error);
                    alert('Error de conexión al intentar cambiar el estado.');
                }
            });
        });
    }

});