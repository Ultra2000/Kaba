/**
 * Service worker de KABA.
 *
 * Principe : la fraîcheur prime sur la vitesse pour tout ce qui touche aux
 * annonces (prix, disponibilité, panier). Le cache sert surtout à alléger
 * les connexions lentes et à garder le site consultable hors ligne.
 */
const VERSION = 'kaba-v1';
const SHELL_CACHE = `${VERSION}-shell`;
const ASSET_CACHE = `${VERSION}-assets`;
const IMAGE_CACHE = `${VERSION}-images`;
const MAX_IMAGES = 120;

const OFFLINE_URL = '/offline.html';
const SHELL = [OFFLINE_URL, '/icons/icon-192.png', '/manifest.webmanifest'];

self.addEventListener('install', (event) => {
    event.waitUntil(
        caches.open(SHELL_CACHE)
            .then((cache) => cache.addAll(SHELL))
            .then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys()
            .then((keys) => Promise.all(
                keys.filter((k) => !k.startsWith(VERSION)).map((k) => caches.delete(k))
            ))
            .then(() => self.clients.claim())
    );
});

/** Limite la taille d'un cache (évite de saturer le stockage du téléphone). */
async function trim(cacheName, max) {
    const cache = await caches.open(cacheName);
    const keys = await cache.keys();
    if (keys.length > max) {
        await Promise.all(keys.slice(0, keys.length - max).map((k) => cache.delete(k)));
    }
}

self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // On ne touche jamais aux écritures ni aux requêtes Inertia :
    // servir un panier ou une commande périmés serait pire que pas de cache.
    if (request.method !== 'GET') return;
    if (request.headers.get('X-Inertia')) return;
    if (url.pathname.startsWith('/admin')) return;

    // Assets compilés : nom haché donc immuable -> cache d'abord.
    if (url.origin === self.location.origin && url.pathname.startsWith('/build/')) {
        event.respondWith(
            caches.match(request).then((hit) => hit || fetch(request).then((res) => {
                const copy = res.clone();
                caches.open(ASSET_CACHE).then((c) => c.put(request, copy));
                return res;
            }))
        );
        return;
    }

    // Images (couvertures, photos d'annonces, icônes) : cache d'abord, plafonné.
    const isImage = request.destination === 'image'
        || /\.(png|jpe?g|webp|gif|svg)$/i.test(url.pathname);
    if (isImage) {
        event.respondWith(
            caches.match(request).then((hit) => hit || fetch(request).then((res) => {
                if (res.ok || res.type === 'opaque') {
                    const copy = res.clone();
                    caches.open(IMAGE_CACHE).then((c) => {
                        c.put(request, copy);
                        trim(IMAGE_CACHE, MAX_IMAGES);
                    });
                }
                return res;
            }).catch(() => caches.match('/icons/icon-192.png')))
        );
        return;
    }

    // Pages : réseau d'abord (données à jour), cache en secours hors ligne.
    if (request.mode === 'navigate') {
        event.respondWith(
            fetch(request)
                .then((res) => {
                    const copy = res.clone();
                    caches.open(SHELL_CACHE).then((c) => c.put(request, copy));
                    return res;
                })
                .catch(() => caches.match(request).then((hit) => hit || caches.match(OFFLINE_URL)))
        );
    }
});
