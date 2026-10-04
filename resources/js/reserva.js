/* =========================================================================
   RESERVA - Interactividad del calendario, servicios y horas
   CON DEBUG para detectar problemas de sincronización
   ========================================================================= */

document.addEventListener('DOMContentLoaded', () => {
    console.log('✅ Reserva - JS cargado');

    // =====================================================================
    // ESTADO
    // =====================================================================
    const hoy = new Date();
    let mesActual = hoy.getMonth();
    let añoActual = hoy.getFullYear();
    let fechaSeleccionada = null;
    let horaSeleccionada = null;

    const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio',
                   'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const MESES_CORTOS = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN',
                          'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];

    const HORAS_DISPONIBLES = [
        '9:00 AM', '10:30 AM', '12:00 PM', '2:30 PM', '4:00 PM', '5:30 PM'
    ];

    // =====================================================================
    // REFERENCIAS
    // =====================================================================
    const calendarioTitulo = document.getElementById('calendario-mes');
    const calendarioDias   = document.getElementById('calendario-dias');
    const btnPrevMes       = document.getElementById('prev-mes');
    const btnNextMes       = document.getElementById('next-mes');
    const fechaTexto       = document.getElementById('fecha-texto');
    const listaHoras       = document.getElementById('lista-horas');
    const inputServicio    = document.getElementById('input-servicio');
    const inputFecha       = document.getElementById('input-fecha');
    const inputHora        = document.getElementById('input-hora');
    const resumenServicio  = document.getElementById('resumen-servicio');
    const resumenDetalle   = document.getElementById('resumen-detalle');
    const resumenPrecio    = document.getElementById('resumen-precio');

    // DEBUG
    console.log('🔍 Referencias del DOM:');
    console.log('   - calendarioTitulo:', !!calendarioTitulo);
    console.log('   - calendarioDias:', !!calendarioDias);
    console.log('   - inputFecha:', !!inputFecha);
    console.log('   - inputHora:', !!inputHora);
    console.log('   - inputServicio:', !!inputServicio);

    // =====================================================================
    // 1. CALENDARIO
    // =====================================================================
    function renderCalendario() {
        if (!calendarioTitulo || !calendarioDias) {
            console.error('❌ Faltan elementos del calendario');
            return;
        }

        calendarioTitulo.textContent = `${MESES[mesActual]} ${añoActual}`;
        calendarioDias.innerHTML = '';

        const primerDia = new Date(añoActual, mesActual, 1);
        const ultimoDia = new Date(añoActual, mesActual + 1, 0);
        const diasEnMes = ultimoDia.getDate();

        let diaSemanaInicio = primerDia.getDay();
        diaSemanaInicio = diaSemanaInicio === 0 ? 6 : diaSemanaInicio - 1;

        // Espacios vacíos
        for (let i = 0; i < diaSemanaInicio; i++) {
            const vacio = document.createElement('button');
            vacio.className = 'dia-calendario vacio';
            vacio.disabled = true;
            vacio.type = 'button';
            calendarioDias.appendChild(vacio);
        }

        // Días del mes
        for (let dia = 1; dia <= diasEnMes; dia++) {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'dia-calendario';
            btn.textContent = dia;

            const fechaBtn = new Date(añoActual, mesActual, dia);
            const hoySinHora = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

            if (fechaBtn < hoySinHora) {
                btn.disabled = true;
                btn.classList.add('pasado');
            }

            if (fechaBtn.getTime() === hoySinHora.getTime()) {
                btn.classList.add('hoy');
            }

            if (fechaSeleccionada &&
                fechaSeleccionada.dia === dia &&
                fechaSeleccionada.mes === mesActual &&
                fechaSeleccionada.año === añoActual) {
                btn.classList.add('seleccionado');
            }

            btn.addEventListener('click', () => {
                console.log('🖱️ Click en día:', dia);
                fechaSeleccionada = { dia, mes: mesActual, año: añoActual };
                console.log('📅 fechaSeleccionada:', fechaSeleccionada);
                renderCalendario();
                actualizarFechaTexto();
                renderHoras();
                sincronizarInputs();
            });

            calendarioDias.appendChild(btn);
        }
    }

    // =====================================================================
    // 2. NAVEGACIÓN
    // =====================================================================
    btnPrevMes?.addEventListener('click', () => {
        mesActual--;
        if (mesActual < 0) { mesActual = 11; añoActual--; }
        renderCalendario();
    });

    btnNextMes?.addEventListener('click', () => {
        mesActual++;
        if (mesActual > 11) { mesActual = 0; añoActual++; }
        renderCalendario();
    });

    // =====================================================================
    // 3. HORAS
    // =====================================================================
    function renderHoras() {
        if (!listaHoras) return;
        listaHoras.innerHTML = '';

        if (!fechaSeleccionada) {
            listaHoras.innerHTML = '<p style="grid-column: span 2; text-align:center; color: var(--text-muted); font-size:0.85rem; padding: 1rem;">Selecciona una fecha primero</p>';
            return;
        }

        HORAS_DISPONIBLES.forEach(hora => {
            const btn = document.createElement('button');
            btn.type = 'button';
            btn.className = 'hora-item';
            btn.textContent = hora;

            if (horaSeleccionada === hora) {
                btn.classList.add('seleccionada');
            }

            btn.addEventListener('click', () => {
                console.log('🖱️ Click en hora:', hora);
                horaSeleccionada = hora;
                console.log('⏰ horaSeleccionada:', horaSeleccionada);
                renderHoras();
                sincronizarInputs();
            });

            listaHoras.appendChild(btn);
        });
    }

    // =====================================================================
    // 4. TEXTO DE FECHA
    // =====================================================================
    function actualizarFechaTexto() {
        if (!fechaTexto) return;
        if (!fechaSeleccionada) {
            fechaTexto.textContent = 'Selecciona una fecha';
            return;
        }
        const { dia, mes } = fechaSeleccionada;
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const fecha = new Date(fechaSeleccionada.año, mes, dia);
        const diaSemana = diasSemana[fecha.getDay()];
        fechaTexto.textContent = `${diaSemana}, ${dia} de ${MESES[mes].toLowerCase()}`;
    }

    // =====================================================================
    // 5. SERVICIOS
    // =====================================================================
    const servicioItems = document.querySelectorAll('.servicio-item');
    console.log('🎯 Servicios encontrados:', servicioItems.length);

    servicioItems.forEach(item => {
        item.addEventListener('click', () => {
            servicioItems.forEach(s => s.classList.remove('activo'));
            item.classList.add('activo');

            const radio = item.querySelector('input[type="radio"]');
            if (radio) radio.checked = true;

            if (inputServicio) inputServicio.value = item.dataset.servicioId;

            const nombre = item.querySelector('.servicio-info strong')?.textContent || '';
            const precio = item.dataset.precio || '';
            if (resumenServicio) resumenServicio.textContent = nombre;
            if (resumenPrecio) resumenPrecio.textContent = `$${precio}`;

            console.log('🛎️ Servicio seleccionado:', item.dataset.servicioId);
            actualizarResumenDetalle();
        });
    });

    // =====================================================================
    // 6. SINCRONIZAR INPUTS — ¡CON DEBUG!
    // =====================================================================
    function sincronizarInputs() {
        console.log('🔄 Sincronizando inputs...');
        console.log('   - fechaSeleccionada:', fechaSeleccionada);
        console.log('   - horaSeleccionada:', horaSeleccionada);

        // Fecha
        if (fechaSeleccionada && inputFecha) {
            const { dia, mes, año } = fechaSeleccionada;
            const mm = String(mes + 1).padStart(2, '0');
            const dd = String(dia).padStart(2, '0');
            inputFecha.value = `${año}-${mm}-${dd}`;
            console.log('   ✅ input-fecha.value =', inputFecha.value);
        } else {
            console.warn('   ⚠️ No se pudo setear input-fecha. fechaSeleccionada:', fechaSeleccionada, '| inputFecha existe:', !!inputFecha);
        }

        // Hora
        if (horaSeleccionada && inputHora) {
            const match = horaSeleccionada.match(/(\d+):(\d+)\s*(AM|PM)/);
            if (match) {
                let h = parseInt(match[1]);
                const m = match[2];
                const ampm = match[3];
                if (ampm === 'PM' && h !== 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
                inputHora.value = `${String(h).padStart(2, '0')}:${m}`;
                console.log('   ✅ input-hora.value =', inputHora.value);
            }
        } else {
            console.warn('   ⚠️ No se pudo setear input-hora. horaSeleccionada:', horaSeleccionada, '| inputHora existe:', !!inputHora);
        }

        actualizarResumenDetalle();
    }

    // =====================================================================
    // 7. RESUMEN
    // =====================================================================
    function actualizarResumenDetalle() {
        if (!resumenDetalle) return;
        if (!fechaSeleccionada || !horaSeleccionada) {
            resumenDetalle.textContent = 'Selecciona fecha y hora';
            return;
        }
        const { dia, mes } = fechaSeleccionada;
        const servicioActivo = document.querySelector('.servicio-item.activo');
        const duracion = servicioActivo?.dataset.duracion || '';
        resumenDetalle.textContent = `${dia} ${MESES_CORTOS[mes]} · ${horaSeleccionada} · ${duracion} min`;
    }

    // =====================================================================
    // 8. INICIALIZAR
    // =====================================================================
    renderCalendario();
    renderHoras();

    const servicioActivo = document.querySelector('.servicio-item.activo');
    if (servicioActivo) {
        const nombre = servicioActivo.querySelector('.servicio-info strong')?.textContent || '';
        const precio = servicioActivo.dataset.precio || '';
        if (resumenServicio) resumenServicio.textContent = nombre;
        if (resumenPrecio) resumenPrecio.textContent = `$${precio}`;
        if (inputServicio) inputServicio.value = servicioActivo.dataset.servicioId;
    }

    // =====================================================================
    // 9. VALIDACIÓN ANTES DE ENVIAR (por si acaso)
    // =====================================================================
    const form = document.getElementById('form-reserva');
    if (form) {
        form.addEventListener('submit', (e) => {
            console.log('📤 Enviando formulario...');
            console.log('   - servicio_id:', inputServicio?.value);
            console.log('   - fecha:', inputFecha?.value);
            console.log('   - hora:', inputHora?.value);

            if (!inputFecha?.value) {
                e.preventDefault();
                alert('⚠️ Por favor selecciona una fecha en el calendario.');
                return false;
            }
            if (!inputHora?.value) {
                e.preventDefault();
                alert('⚠️ Por favor selecciona una hora.');
                return false;
            }
        });
    }

    console.log('✅ Calendario y servicios listos');
});