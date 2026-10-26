const Swal = window.Swal;

document.addEventListener('DOMContentLoaded', () => {
    const hoy = new Date();
    let mesActual = hoy.getMonth();
    let añoActual = hoy.getFullYear();
    let fechaSeleccionada = null;
    let horaSeleccionada = null;

    const moduloReserva = document.getElementById('modulo-reserva');
    if (!moduloReserva) return;

    const serviciosData = JSON.parse(moduloReserva.dataset.especialistas || '{}');
    const successMessage = moduloReserva.dataset.success;

    if (successMessage && successMessage.trim() !== '' && Swal) {
        Swal.fire({
            icon: 'success',
            title: '¡Operación Exitosa!',
            text: successMessage,
            confirmButtonColor: '#5A6B5D',
            timer: 4500,
            showConfirmButton: true,
        });
    }

    const MESES = ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Septiembre', 'Octubre', 'Noviembre', 'Diciembre'];
    const MESES_CORTOS = ['ENE', 'FEB', 'MAR', 'ABR', 'MAY', 'JUN', 'JUL', 'AGO', 'SEP', 'OCT', 'NOV', 'DIC'];
    const HORAS_DISPONIBLES = ['9:00 AM', '10:30 AM', '12:00 PM', '2:30 PM', '4:00 PM', '5:30 PM'];

    const calendarioTitulo = document.getElementById('calendario-mes');
    const calendarioDias   = document.getElementById('calendario-dias');
    const btnPrevMes       = document.getElementById('prev-mes');
    const btnNextMes       = document.getElementById('next-mes');
    const fechaTexto       = document.getElementById('fecha-texto');
    const listaHoras       = document.getElementById('lista-horas');
    const inputFecha       = document.getElementById('input-fecha');
    const inputHora        = document.getElementById('input-hora');
    const resumenServicio  = document.getElementById('resumen-servicio');
    const resumenDetalle   = document.getElementById('resumen-detalle');
    const resumenPrecio    = document.getElementById('resumen-precio');
    const contenedorEspecialistas = document.getElementById('contenedor-especialistas');

    function renderCalendario() {
        if (!calendarioTitulo || !calendarioDias) return;
        calendarioTitulo.textContent = `${MESES[mesActual]} ${añoActual}`;
        calendarioDias.innerHTML = '';
        const primerDia = new Date(añoActual, mesActual, 1);
        const ultimoDia = new Date(añoActual, mesActual + 1, 0);
        const diasEnMes = ultimoDia.getDate();
        let diaSemanaInicio = primerDia.getDay();
        diaSemanaInicio = diaSemanaInicio === 0 ? 6 : diaSemanaInicio - 1;

        for (let i = 0; i < diaSemanaInicio; i++) {
            const vacio = document.createElement('button');
            vacio.className = 'dia-calendario vacio'; vacio.disabled = true; vacio.type = 'button';
            calendarioDias.appendChild(vacio);
        }

        for (let dia = 1; dia <= diasEnMes; dia++) {
            const btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'dia-calendario'; btn.textContent = dia;
            const fechaBtn = new Date(añoActual, mesActual, dia);
            const hoySinHora = new Date(hoy.getFullYear(), hoy.getMonth(), hoy.getDate());

            if (fechaBtn < hoySinHora) { btn.disabled = true; btn.classList.add('pasado'); }
            if (fechaBtn.getTime() === hoySinHora.getTime()) { btn.classList.add('hoy'); }
            if (fechaSeleccionada && fechaSeleccionada.dia === dia && fechaSeleccionada.mes === mesActual && fechaSeleccionada.año === añoActual) {
                btn.classList.add('seleccionado');
            }

            btn.addEventListener('click', () => {
                fechaSeleccionada = { dia, mes: mesActual, año: añoActual };
                renderCalendario(); actualizarFechaTexto(); renderHoras(); sincronizarInputs();
            });
            calendarioDias.appendChild(btn);
        }
    }

    btnPrevMes?.addEventListener('click', () => { mesActual--; if (mesActual < 0) { mesActual = 11; añoActual--; } renderCalendario(); });
    btnNextMes?.addEventListener('click', () => { mesActual++; if (mesActual > 11) { mesActual = 0; añoActual++; } renderCalendario(); });

    function renderHoras() {
        if (!listaHoras) return;
        listaHoras.innerHTML = '';
        if (!fechaSeleccionada) {
            listaHoras.innerHTML = '<p style="grid-column: span 2; text-align:center; color: var(--text-muted); font-size:0.85rem; padding: 1rem;">Selecciona una fecha primero</p>';
            return;
        }
        HORAS_DISPONIBLES.forEach(hora => {
            const btn = document.createElement('button');
            btn.type = 'button'; btn.className = 'hora-item'; btn.textContent = hora;
            if (horaSeleccionada === hora) btn.classList.add('seleccionada');
            btn.addEventListener('click', () => { horaSeleccionada = hora; renderHoras(); sincronizarInputs(); });
            listaHoras.appendChild(btn);
        });
    }

    function actualizarFechaTexto() {
        if (!fechaTexto) return;
        if (!fechaSeleccionada) { fechaTexto.textContent = 'Selecciona una fecha'; return; }
        const { dia, mes } = fechaSeleccionada;
        const diasSemana = ['Domingo', 'Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado'];
        const fecha = new Date(fechaSeleccionada.año, mes, dia);
        fechaTexto.textContent = `${diasSemana[fecha.getDay()]}, ${dia} de ${MESES[mes].toLowerCase()}`;
    }

    // =====================================================================
    // LÓGICA DE SERVICIOS MÚLTIPLES Y SELECTORES POR SERVICIO
    // =====================================================================
    const servicioItems = document.querySelectorAll('.servicio-item');
    
    servicioItems.forEach(item => {
        item.addEventListener('click', (e) => {
            if (e.target.tagName !== 'INPUT') {
                const checkbox = item.querySelector('input[type="checkbox"]');
                checkbox.checked = !checkbox.checked;
            }
            actualizarSeleccionServicios();
            renderSelectoresEspecialistas();
        });
    });

    function actualizarSeleccionServicios() {
        let totalPrecio = 0;
        let totalDuracion = 0;
        let nombres = [];

        servicioItems.forEach(item => {
            const checkbox = item.querySelector('input[type="checkbox"]');
            if (checkbox.checked) {
                item.classList.add('activo');
                totalPrecio += parseFloat(item.dataset.precio);
                totalDuracion += parseInt(item.dataset.duracion);
                nombres.push(item.querySelector('.servicio-info strong').textContent);
            } else {
                item.classList.remove('activo');
            }
        });

        if (resumenServicio) resumenServicio.textContent = nombres.length > 0 ? nombres.join(' + ') : 'Ningún servicio';
        if (resumenPrecio) resumenPrecio.textContent = `$${totalPrecio}`;

        actualizarResumenDetalle(totalDuracion);
    }

    function renderSelectoresEspecialistas() {
        if (!contenedorEspecialistas) return;

        const serviciosSeleccionados = [];
        document.querySelectorAll('input[name="servicios[]"]:checked').forEach(cb => {
            serviciosSeleccionados.push(parseInt(cb.value));
        });

        // Limpiar contenedor, dejando solo el título "Especialistas"
        contenedorEspecialistas.innerHTML = '<small style="font-family: var(--font-ui); font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.1em; color: var(--text-muted);">Especialistas</small>';

        if (serviciosSeleccionados.length === 0) {
            contenedorEspecialistas.innerHTML += '<p style="font-size: 0.85rem; color: var(--text-muted); margin: 0;">Selecciona un servicio para asignar especialistas.</p>';
            return;
        }

        // Crear un select por cada servicio
        serviciosSeleccionados.forEach(idServicio => {
            const servicio = serviciosData[idServicio];
            if (!servicio) return;

            const div = document.createElement('div');
            div.style.marginTop = '8px';

            const label = document.createElement('small');
            label.style.display = 'block';
            label.style.marginBottom = '4px';
            label.style.fontSize = '0.75rem';
            label.style.color = 'var(--text-soft)';
            label.textContent = `¿Quién hará ${servicio.nombre}?`;
            
            const select = document.createElement('select');
            // Enviamos un arreglo con el ID del servicio y el ID del especialista
            select.name = `trabajadores_por_servicio[${servicio.id}]`;
            select.className = 'select-especialista';
            select.required = true;
            
            select.innerHTML = '<option value="">Elige especialista...</option>';
            
            if (servicio.especialistas && servicio.especialistas.length > 0) {
                servicio.especialistas.forEach(esp => {
                    const option = document.createElement('option');
                    option.value = esp.id;
                    option.textContent = esp.nombre;
                    select.appendChild(option);
                });
            } else {
                select.innerHTML = '<option value="">No hay especialistas para este servicio</option>';
                select.disabled = true;
            }

            div.appendChild(label);
            div.appendChild(select);
            contenedorEspecialistas.appendChild(div);
        });
    }

    function sincronizarInputs() {
        if (fechaSeleccionada && inputFecha) {
            const { dia, mes, año } = fechaSeleccionada;
            inputFecha.value = `${año}-${String(mes + 1).padStart(2, '0')}-${String(dia).padStart(2, '0')}`;
        }
        if (horaSeleccionada && inputHora) {
            const match = horaSeleccionada.match(/(\d+):(\d+)\s*(AM|PM)/);
            if (match) {
                let h = parseInt(match[1]);
                const ampm = match[3];
                if (ampm === 'PM' && h !== 12) h += 12;
                if (ampm === 'AM' && h === 12) h = 0;
                inputHora.value = `${String(h).padStart(2, '0')}:${match[2]}`;
            }
        }
        actualizarSeleccionServicios();
    }

    function actualizarResumenDetalle(duracionTotal = 0) {
        if (!resumenDetalle) return;
        if (!fechaSeleccionada || !horaSeleccionada) {
            resumenDetalle.textContent = 'Selecciona fecha y hora';
            return;
        }
        const { dia, mes } = fechaSeleccionada;
        resumenDetalle.textContent = `${dia} ${MESES_CORTOS[mes]} · ${horaSeleccionada} · ${duracionTotal} min`;
    }

    renderCalendario();
    renderHoras();
    actualizarSeleccionServicios();
    renderSelectoresEspecialistas();

    const form = document.getElementById('form-reserva');
    if (form) {
        form.addEventListener('submit', (e) => {
            if (!inputFecha?.value) { e.preventDefault(); alert('⚠️ Por favor selecciona una fecha.'); return false; }
            if (!inputHora?.value) { e.preventDefault(); alert('⚠️ Por favor selecciona una hora.'); return false; }
            
            const serviciosSeleccionados = document.querySelectorAll('input[name="servicios[]"]:checked');
            if (serviciosSeleccionados.length === 0) {
                e.preventDefault(); alert('⚠️ Por favor selecciona al menos un servicio.'); return false;
            }

            // Validar que se haya elegido especialista para cada servicio
            const selects = contenedorEspecialistas.querySelectorAll('select');
            for (let sel of selects) {
                if (!sel.disabled && !sel.value) {
                    e.preventDefault(); 
                    alert('⚠️ Por favor selecciona un especialista para cada servicio.'); 
                    return false;
                }
            }
        });
    }
});