document.addEventListener('DOMContentLoaded', function() {
    // ========================================================
    // 1. MODAL DE RESEÑAS
    // ========================================================
    const overlay = document.getElementById('modal-resena-overlay');
    if (overlay) {
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
    }

    // ========================================================
    // 2. FILTROS, ORDENAMIENTO Y PAGINACIÓN DE PRÓXIMAS CITAS
    // ========================================================
    const contenedorProximas = document.getElementById('lista-proximas-citas');
    if (!contenedorProximas) return;

    const fichasOriginales = Array.from(contenedorProximas.querySelectorAll('.cita-layout-grid'));
    if (fichasOriginales.length === 0) return;

    const botonesEstado = document.querySelectorAll('.filtro-estado-btn');
    const selectOrden = document.getElementById('orden-proximas-citas');
    const selectPorPagina = document.getElementById('por-pagina-proximas');
    const sinResultados = document.getElementById('proximas-sin-resultados');

    const pagContainer = document.getElementById('proximas-pagination-container');
    const pagInfo = document.getElementById('proximas-pagination-info');
    const pagNumeros = document.getElementById('proximas-pagination-numeros');
    const btnPrev = document.getElementById('btn-proximas-prev');
    const btnNext = document.getElementById('btn-proximas-next');

    let estadoActivo = 'todos';
    let paginaActual = 1;

    function obtenerFichasOrdenadasYFiltradas() {
        const criterio = selectOrden ? selectOrden.value : 'fecha-asc';

        // 1. Filtrar por estado de pago (todos | pendiente | pagado)
        const filtradas = fichasOriginales.filter(ficha => {
            if (estadoActivo === 'todos') return true;
            return ficha.dataset.pago === estadoActivo;
        });

        // 2. Ordenar
        filtradas.sort((a, b) => {
            const fechaA = `${a.dataset.fecha} ${a.dataset.hora}`;
            const fechaB = `${b.dataset.fecha} ${b.dataset.hora}`;
            const horaA = a.dataset.hora || '';
            const horaB = b.dataset.hora || '';
            const nombreA = a.dataset.nombre || '';
            const nombreB = b.dataset.nombre || '';

            switch (criterio) {
                case 'fecha-desc':
                    return fechaB.localeCompare(fechaA);
                case 'hora-asc':
                    return horaA.localeCompare(horaB) || fechaA.localeCompare(fechaB);
                case 'hora-desc':
                    return horaB.localeCompare(horaA) || fechaA.localeCompare(fechaB);
                case 'nombre-asc':
                    return nombreA.localeCompare(nombreB, 'es', { sensitivity: 'base' });
                case 'nombre-desc':
                    return nombreB.localeCompare(nombreA, 'es', { sensitivity: 'base' });
                case 'fecha-asc':
                default:
                    return fechaA.localeCompare(fechaB);
            }
        });

        return filtradas;
    }

    function renderizarNumerosPagina(totalPaginas) {
        if (!pagNumeros) return;
        pagNumeros.innerHTML = '';

        for (let i = 1; i <= totalPaginas; i++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'btn-paginacion-num' + (i === paginaActual ? ' is-active' : '');
            btn.textContent = i;
            btn.setAttribute('aria-label', `Ir a la reserva ${i}`);
            btn.addEventListener('click', () => {
                paginaActual = i;
                actualizarProximasCitas(false);
                document.querySelector('.proxima-cita-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            pagNumeros.appendChild(btn);
        }
    }

    function actualizarProximasCitas(reiniciarPagina = true) {
        if (reiniciarPagina) paginaActual = 1;

        const porPagina = selectPorPagina ? parseInt(selectPorPagina.value, 10) : 1;
        const lista = obtenerFichasOrdenadasYFiltradas();
        const total = lista.length;

        // Ocultar todas inicialmente y limpiar animación previa
        fichasOriginales.forEach(ficha => {
            ficha.style.display = 'none';
            ficha.classList.remove('ficha-fade-in');
        });

        // Reordenar nodos en el DOM según filtro/orden
        lista.forEach(ficha => {
            contenedorProximas.appendChild(ficha);
        });

        if (total === 0) {
            if (sinResultados) sinResultados.style.display = 'block';
            if (pagContainer) pagContainer.style.display = 'none';
            return;
        }

        if (sinResultados) sinResultados.style.display = 'none';

        const totalPaginas = Math.max(1, Math.ceil(total / porPagina));
        if (paginaActual > totalPaginas) paginaActual = totalPaginas;
        if (paginaActual < 1) paginaActual = 1;

        const inicio = (paginaActual - 1) * porPagina;
        const fin = inicio + porPagina;

        lista.slice(inicio, fin).forEach(ficha => {
            ficha.style.display = '';
            void ficha.offsetWidth;
            ficha.classList.add('ficha-fade-in');
        });

        if (pagContainer) {
            pagContainer.style.display = totalPaginas > 1 ? 'flex' : 'none';
            if (pagInfo) {
                const desde = inicio + 1;
                const hasta = Math.min(fin, total);
                pagInfo.textContent = porPagina === 1
                    ? `Mostrando reserva ${desde} de ${total}`
                    : `Mostrando del ${desde} al ${hasta} de ${total} reservas`;
            }
            if (btnPrev) btnPrev.disabled = paginaActual <= 1;
            if (btnNext) btnNext.disabled = paginaActual >= totalPaginas;
            renderizarNumerosPagina(totalPaginas);
        }
    }

    // Eventos de filtro por estado
    botonesEstado.forEach(btn => {
        btn.addEventListener('click', function() {
            botonesEstado.forEach(b => b.classList.remove('is-active'));
            this.classList.add('is-active');
            estadoActivo = this.dataset.filtroEstado || 'todos';
            actualizarProximasCitas(true);
        });
    });

    // Eventos de ordenamiento y cantidad por página
    if (selectOrden) {
        selectOrden.addEventListener('change', () => actualizarProximasCitas(true));
    }
    if (selectPorPagina) {
        selectPorPagina.addEventListener('change', () => actualizarProximasCitas(true));
    }

    // Botones Anterior / Siguiente
    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            if (paginaActual > 1) {
                paginaActual--;
                actualizarProximasCitas(false);
                document.querySelector('.proxima-cita-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }
    if (btnNext) {
        btnNext.addEventListener('click', () => {
            const porPagina = selectPorPagina ? parseInt(selectPorPagina.value, 10) : 1;
            const totalPaginas = Math.ceil(obtenerFichasOrdenadasYFiltradas().length / porPagina);
            if (paginaActual < totalPaginas) {
                paginaActual++;
                actualizarProximasCitas(false);
                document.querySelector('.proxima-cita-section')?.scrollIntoView({ behavior: 'smooth', block: 'start' });
            }
        });
    }

    actualizarProximasCitas(true);
});