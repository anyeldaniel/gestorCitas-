import '../css/notificaciones.css';

const csrf = document.querySelector('meta[name="csrf-token"]')?.content ?? '';
let panelAbierto = null;

// Leemos las URLs desde los data-attributes del <body>
const bodyData = document.body.dataset;
const URL_LISTA = bodyData.notifLista;
const URL_LEER = bodyData.notifLeer;
const URL_LEER_TODAS = bodyData.notifLeerTodas;

async function cargarWidget(suffix) {
    const badge = document.getElementById(`notif-badge${suffix}`);
    const lista = document.getElementById(`notif-lista${suffix}`);
    if (!badge || !lista) return;

    try {
        const res = await fetch(URL_LISTA, {
            headers: { 'Accept': 'application/json' }
        });
        if (!res.ok) {
            console.error("Error en fetch:", res.status, URL_LISTA);
            return;
        }

        const data = await res.json();

        // Actualizar la campanita visual en la web
        badge.hidden = data.count === 0;
        badge.textContent = data.count > 99 ? '99+' : data.count;

        // 👇 NUEVO: Actualizar el Badge del ícono de la PWA (Sistema Operativo) 👇
        if ('setAppBadge' in navigator) {
            if (data.count > 0) {
                navigator.setAppBadge(data.count).catch(err => console.log('Error al poner badge:', err));
            } else {
                navigator.clearAppBadge().catch(err => console.log('Error al limpiar badge:', err));
            }
        }
        // 👆 FIN DEL NUEVO CÓDIGO 👆

        if (!data.items.length) {
            const vacio = document.createElement('li');
            vacio.className = 'notif-vacio';
            vacio.textContent = 'Sin notificaciones por ahora';
            lista.textContent = '';
            lista.appendChild(vacio);
            return;
        }

        lista.textContent = '';
        data.items.forEach(n => {
            const item = document.createElement('li');
            item.className = `notif-item ${n.leida ? 'leida' : 'nueva'}`;
            item.dataset.id = n.id;

            const iconoWrapper = document.createElement('div');
            iconoWrapper.className = `notif-icono-wrapper ${n.color || 'gris'}`;
            const icono = document.createElement('i');
            icono.setAttribute('data-lucide', n.icono);
            iconoWrapper.appendChild(icono);

            const contenido = document.createElement('div');
            contenido.className = 'notif-contenido';
            const cabecera = document.createElement('div');
            cabecera.className = 'notif-cabecera';

            const titulo = document.createElement('strong');
            titulo.textContent = n.titulo;
            const tiempo = document.createElement('span');
            tiempo.className = 'notif-tiempo';
            tiempo.textContent = n.hace;
            cabecera.appendChild(titulo);
            cabecera.appendChild(tiempo);

            const mensaje = document.createElement('p');
            mensaje.className = 'notif-mensaje';
            mensaje.textContent = n.mensaje;
            contenido.appendChild(cabecera);
            contenido.appendChild(mensaje);
            item.appendChild(iconoWrapper);
            item.appendChild(contenido);
            lista.appendChild(item);
        });

        if (window.lucide) window.lucide.createIcons();
    } catch (e) {
        console.error("Error cargando notificaciones:", e);
    }
}

async function marcarLeida(id) {
    const url = URL_LEER.replace(':id', id);
    await fetch(url, {
        method: 'POST',
        headers: { 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }
    });
    cargarTodo();
}

async function marcarTodas() {
    await fetch(URL_LEER_TODAS, {
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
    if (typeof lucide !== 'undefined') lucide.createIcons();

    // Registrar SW
    if ('serviceWorker' in navigator) {
        window.addEventListener('load', () => {
            navigator.serviceWorker.register('/sw.js')
                .then(registration => console.log('SW registrado con éxito:', registration.scope))
                .catch(error => console.log('Error al registrar el SW:', error));
        });
    }

    // Menú móvil
    const hamburgerBtn = document.getElementById('hamburger-btn');
    const mobileMenu = document.getElementById('mobile-menu');
    if (hamburgerBtn && mobileMenu) {
        hamburgerBtn.addEventListener('click', () => mobileMenu.classList.add('active'));
    }

    // Lógica de la campanita
    ['', '-mobile'].forEach(suffix => {
        const widget = document.getElementById(`notif-widget${suffix}`);
        if (!widget) return;

        const btn      = document.getElementById(`notif-btn${suffix}`);
        const panel    = document.getElementById(`notif-panel${suffix}`);
        const lista    = document.getElementById(`notif-lista${suffix}`);
        const btnTodas = document.getElementById(`notif-marcar-todas${suffix}`);

        btn.addEventListener('click', (e) => {
            e.stopPropagation();
            if (panelAbierto && panelAbierto !== panel) panelAbierto.hidden = true;
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

    // ==========================================
    // WEB PUSH (NOTIFICACIONES DEL SISTEMA)
    // ==========================================
    const isAuthenticated = bodyData.auth === 'true';

    if (isAuthenticated && 'serviceWorker' in navigator && 'PushManager' in window) {
        const urlBase64ToUint8Array = (base64String) => {
            const padding = '='.repeat((4 - (base64String.length % 4)) % 4);
            const base64 = (base64String + padding).replace(/-/g, '+').replace(/_/g, '/');
            const rawData = atob(base64);
            return Uint8Array.from([...rawData].map(c => c.charCodeAt(0)));
        };

        const subscribeToPush = async () => {
            const vapidKey = bodyData.vapidKey;
            
            if (!vapidKey || vapidKey === '') {
                console.warn('⚠️ La clave VAPID está vacía. Revisa tu .env y config/pwa.php');
                return;
            }

            const permission = await Notification.requestPermission();
            if (permission !== 'granted') return;

            try {
                const registration = await navigator.serviceWorker.ready;
                let subscription = await registration.pushManager.getSubscription();
                
                if (!subscription) {
                    subscription = await registration.pushManager.subscribe({
                        userVisibleOnly: true,
                        applicationServerKey: urlBase64ToUint8Array(vapidKey)
                    });
                }

                const subJSON = subscription.toJSON();
                const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

                await fetch(bodyData.routePush, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({
                        endpoint: subJSON.endpoint,
                        keys: subJSON.keys
                    })
                });

                console.log('¡Suscripción Push exitosa!');
            } catch (error) {
                console.error('Error al suscribirse al Push:', error);
            }
        };

        setTimeout(subscribeToPush, 3000);
    }
});