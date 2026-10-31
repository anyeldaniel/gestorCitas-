import '../css/notificaciones.css';

// Ya no necesitamos el objeto COLORS aquí, porque los colores ahora 
// se aplican directamente a través de las clases CSS (rojo, verde, azul...).

const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let panelAbierto = null;

async function cargarWidget(suffix) {
    const badge = document.getElementById(`notif-badge${suffix}`);
    const lista = document.getElementById(`notif-lista${suffix}`);
    if (!badge || !lista) return;

    try {
        // Usamos la URL generada por Laravel
        const res = await fetch(window.NOTIF_URL_LISTA, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
            console.error("Error en fetch:", res.status);
            return;
        }

        const data = await res.json();

        badge.hidden = data.count === 0;
        badge.textContent = data.count > 99 ? '99+' : data.count;

        if (!data.items.length) {
            lista.innerHTML = '<li class="notif-vacio">Sin notificaciones por ahora</li>';
            return;
        }

        // Aquí está el cambio: nueva estructura y clases dinámicas para el icono
        lista.innerHTML = data.items.map(n => `
            <li class="notif-item ${n.leida ? 'leida' : 'nueva'}" data-id="${n.id}">
                <div class="notif-icono-wrapper ${n.color || 'gris'}">
                    <i data-lucide="${n.icono}"></i>
                </div>
                <div class="notif-contenido">
                    <div class="notif-cabecera">
                        <strong>${n.titulo}</strong>
                        <span class="notif-tiempo">${n.hace}</span>
                    </div>
                    <p class="notif-mensaje">${n.mensaje}</p>
                </div>
            </li>
        `).join('');

        if (window.lucide) window.lucide.createIcons();
    } catch (e) {
        console.error("Error cargando notificaciones:", e);
    }
}

async function marcarLeida(id) {
    // Reemplazamos el :id en la URL generada por Laravel
    const url = window.NOTIF_URL_LEER.replace(':id', id);
    await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    cargarTodo();
}

async function marcarTodas() {
    await fetch(window.NOTIF_URL_LEER_TODAS, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    cargarTodo();
}

function cargarTodo() {
    cargarWidget('');         // Escritorio
    cargarWidget('-mobile');  // Móvil
}

document.addEventListener('DOMContentLoaded', () => {
    ['', '-mobile'].forEach(suffix => {
        const widget = document.getElementById(`notif-widget${suffix}`);
        if (!widget) return;

        const btn      = document.getElementById(`notif-btn${suffix}`);
        const panel    = document.getElementById(`notif-panel${suffix}`);
        const lista    = document.getElementById(`notif-lista${suffix}`);
        const btnTodas = document.getElementById(`notif-marcar-todas${suffix}`);

        btn.addEventListener('click', (e) => {
            e.stopPropagation();

            if (panelAbierto && panelAbierto !== panel) {
                panelAbierto.hidden = true;
            }

            panel.hidden = !panel.hidden;
            panelAbierto = panel.hidden ? null : panel;

            if (!panel.hidden) cargarWidget(suffix);
        });

        lista.addEventListener('click', (e) => {
            const item = e.target.closest('.notif-item.nueva');
            if (item) marcarLeida(item.dataset.id);
        });

        btnTodas.addEventListener('click', marcarTodas);

        document.addEventListener('click', (e) => {
            if (!widget.contains(e.target)) panel.hidden = true;
        });
    });

    cargarTodo();
    setInterval(cargarTodo, 30000);
});