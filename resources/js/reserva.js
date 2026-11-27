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
    const inputAlternativo = document.getElementById('input-alternativo');
    const resumenServicio  = document.getElementById('resumen-servicio');
    const resumenDetalle   = document.getElementById('resumen-detalle');
    const resumenPrecio    = document.getElementById('resumen-precio');
    const contenedorEspecialistas = document.getElementById('contenedor-especialistas');
    const btnConfirmar     = document.getElementById('btn-confirmar');
    const form             = document.getElementById('form-reserva');

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
            vacio.className = 'dia-calendario vacio'; 
            vacio.disabled = true; 
            vacio.type = 'button';
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
                if(inputAlternativo) inputAlternativo.value = ''; 
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
            btn.addEventListener('click', () => { 
                horaSeleccionada = hora; 
                renderHoras(); 
                sincronizarInputs();
                if(inputAlternativo) inputAlternativo.value = ''; 
            });
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

    const servicioItems = document.querySelectorAll('.servicio-item');
    servicioItems.forEach(item => {
        const checkbox = item.querySelector('input[type="checkbox"]');
        checkbox.addEventListener('change', () => {
            actualizarSeleccionServicios();
            renderSelectoresEspecialistas();
            if(inputAlternativo) inputAlternativo.value = ''; 
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

        contenedorEspecialistas.innerHTML = '<small class="label-titulo">Especialistas</small>';

        if (serviciosSeleccionados.length === 0) {
            contenedorEspecialistas.innerHTML += '<p class="msg-vacio">Selecciona un servicio para asignar especialistas.</p>';
            return;
        }

        serviciosSeleccionados.forEach(idServicio => {
            const servicio = serviciosData[idServicio];
            if (!servicio) return;

            const div = document.createElement('div');
            div.className = 'grupo-especialista';

            const label = document.createElement('label');
            label.textContent = `¿Quién hará ${servicio.nombre}?`;
            
            const select = document.createElement('select');
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

    // MODAL DE ERRORES Y ALTERNATIVAS
    function mostrarError(mensaje, alternativas = null) {
        let htmlContent = `<p style="font-size:0.95rem; color:#4a4640; margin-bottom:10px;">${mensaje}</p>`;
        
        if (alternativas && alternativas.length > 0) {
            htmlContent += `<p style="font-size:0.85rem; font-weight:600; margin-top:15px; margin-bottom:10px; color:var(--primary-brown);">Selecciona un especialista disponible:</p>`;
            htmlContent += `<div class="alternativas-swal-container">`;
            alternativas.forEach(alt => {
                htmlContent += `
                    <label class="alternativa-swal-label">
                        <input type="radio" name="swal_alternativa" value="${alt.id}">
                        <span>${alt.nombre}</span>
                    </label>
                `;
            });
            htmlContent += `</div>`;
        }

        Swal.fire({
            icon: 'warning',
            title: 'Horario no disponible',
            html: htmlContent,
            showCancelButton: (alternativas && alternativas.length > 0),
            confirmButtonText: (alternativas && alternativas.length > 0) ? 'Reasignar y Confirmar' : 'Entendido',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4a3728',
            cancelButtonColor: '#8a8580',
            allowOutsideClick: false,
            preConfirm: () => {
                if (alternativas && alternativas.length > 0) {
                    const selected = document.querySelector('input[name="swal_alternativa"]:checked');
                    if (!selected) {
                        Swal.showValidationMessage('Por favor selecciona un especialista para continuar');
                        return false;
                    }
                    return selected.value;
                }
                return true;
            }
        }).then((result) => {
            if (result.isConfirmed && result.value && result.value !== true) {
                if (inputAlternativo) inputAlternativo.value = result.value;
                btnConfirmar.classList.add('cargando');
                btnConfirmar.querySelector('span').textContent = 'Procesando...';
                form.dispatchEvent(new Event('submit', { cancelable: true, bubbles: true }));
            }
        });
    }

    renderCalendario();
    renderHoras();
    actualizarSeleccionServicios();
    renderSelectoresEspecialistas();

    // =====================================================================
    // ENVÍO DEL FORMULARIO POR AJAX
    // =====================================================================
    if (form) {
        form.addEventListener('submit', async (e) => {
            e.preventDefault();

            if (!inputFecha?.value) return mostrarError('Por favor selecciona una fecha.');
            if (!inputHora?.value) return mostrarError('Por favor selecciona una hora.');
            
            const serviciosSeleccionados = document.querySelectorAll('input[name="servicios[]"]:checked');
            if (serviciosSeleccionados.length === 0) return mostrarError('Por favor selecciona al menos un servicio.');

            const selects = contenedorEspecialistas.querySelectorAll('select');
            for (let sel of selects) {
                if (!sel.disabled && !sel.value) {
                    return mostrarError('Por favor selecciona un especialista para cada servicio.');
                }
            }

            btnConfirmar.classList.add('cargando');
            btnConfirmar.querySelector('span').textContent = 'Procesando...';

            try {
                const formData = new FormData(form);
                const response = await fetch(form.action, {
                    method: 'POST',
                    body: formData,
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest',
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (!response.ok) {
                    // Manejo del error 422 (Choque de horario con alternativas)
                    if (data.alternativas) {
                        mostrarError(data.message || 'El especialista seleccionado ya tiene una reserva en este horario.', data.alternativas);
                    } else if (data.errors && data.errors.hora) {
                        mostrarError(data.errors.hora[0]);
                    } else if (data.message) {
                        mostrarError(data.message);
                    } else {
                        mostrarError('Ocurrió un error inesperado. Intenta de nuevo.');
                    }
                    
                    btnConfirmar.classList.remove('cargando');
                    btnConfirmar.querySelector('span').textContent = 'Confirmar cita';
                    return;
                }

                // ÉXITO: Redirigir a la pasarela de pago
                Swal.fire({
                    icon: 'success',
                    title: '¡Reserva Confirmada!',
                    text: data.message || 'Tu cita ha sido agendada con éxito.',
                    confirmButtonColor: '#5A6B5D',
                    confirmButtonText: 'Ir al pago'
                }).then(() => {
                    window.location.href = data.redirect_url;
                });

            } catch (error) {
                console.error(error);
                mostrarError('Error de conexión. Revisa tu internet e intenta de nuevo.');
                btnConfirmar.classList.remove('cargando');
                btnConfirmar.querySelector('span').textContent = 'Confirmar cita';
            }
        });
    }
});