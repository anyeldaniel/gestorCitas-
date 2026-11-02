"use strict";

// 👇 Cambia este número cada vez que modifiques este archivo para forzar la actualización en los navegadores
const CACHE_KEY = "syncrostyle-cache-v2"; 
const IGNORED_PATHS = ['/api/', '/livewire/', '/_inertia/'];
const OFFLINE_URL = "/offline.html";

// ==========================================
// 1. INSTALACIÓN Y ACTIVACIÓN (CACHÉ)
// ==========================================
self.addEventListener("install", (event) => {
    event.waitUntil(
        caches
            .open(CACHE_KEY)
            .then((cache) => cache.add(OFFLINE_URL).catch(() => { }))
            .then(() => self.skipWaiting()),
    );
});

self.addEventListener("activate", (event) => {
    event.waitUntil(
        caches
            .keys()
            .then((keys) =>
                Promise.all(
                    keys
                        .filter((key) => key !== CACHE_KEY)
                        .map((key) => caches.delete(key)),
                ),
            )
            .then(() => self.clients.claim()),
    );
});

// ==========================================
// 2. ESTRATEGIAS DE RED (FETCH)
// ==========================================
function isCacheable(response) {
    if (!response || response.status !== 200 || response.type === "opaque") {
        return false;
    }
    const cc = response.headers.get("cache-control") ?? "";
    if (cc.includes("no-cache") || cc.includes("no-store") || cc.includes("private")) {
        return false;
    }
    return true;
}

self.addEventListener("fetch", (event) => {
    if (event.request.method !== "GET") return;

    const requestPath = new URL(event.request.url).pathname;
    if (IGNORED_PATHS.some((prefix) => requestPath.startsWith(prefix))) return;

    if (event.request.headers.get("Range") || 
        event.request.headers.get("X-Inertia") || 
        event.request.headers.get("X-Livewire") || 
        event.request.headers.get("X-Livewire-Navigate") || 
        event.request.headers.get("HX-Request") || 
        event.request.headers.get("X-Requested-With")) {
        event.respondWith(fetch(event.request));
        return;
    }

    // Network-first para navegación (páginas HTML)
    if (event.request.mode === "navigate") {
        event.respondWith(
            fetch(event.request).catch(() => caches.match(OFFLINE_URL)),
        );
        return;
    }

    // Cache-first para estáticos (CSS, JS, imágenes)
    event.respondWith(
        caches.match(event.request).then((cached) => {
            if (cached) return cached;

            return fetch(event.request)
                .then((response) => {
                    if (isCacheable(response)) {
                        const clone = response.clone();
                        caches.open(CACHE_KEY).then((cache) => cache.put(event.request, clone));
                    }
                    return response;
                })
                .catch(() => new Response("Service unavailable", { status: 503, statusText: "Service Unavailable" }));
        }),
    );
});

// ==========================================
// 3. LÓGICA DE WEB PUSH (NOTIFICACIONES)
// ==========================================
self.addEventListener('push', function(event) {
    let data = {};
    
    if (event.data) {
        try {
            data = event.data.json();
        } catch (e) {
            // Si no es JSON, intentamos leerlo como texto
            data = { body: event.data.text() };
        }
    }

    // Si el paquete envía los datos dentro de una propiedad "notification", los extraemos
    const payload = data.notification || data;

    const title = payload.title || 'Syncrostyle';
    const options = {
        body: payload.body || 'Tienes una nueva notificación.',
        icon: payload.icon || '/icons/icon-192x192.png',
        badge: '/icons/icon-192x192.png',
        data: payload.data || { url: '/' }
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});