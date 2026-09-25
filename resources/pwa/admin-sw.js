const CACHE = 'focusrent-admin-v2';

self.addEventListener('install', (event) => {
    event.waitUntil(self.skipWaiting());
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => Promise.all(
            keys.filter((key) => key.startsWith('focusrent-admin-') && key !== CACHE).map((key) => caches.delete(key))
        )).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    if (request.method !== 'GET') {
        return;
    }

    const url = new URL(request.url);
    if (url.origin !== self.location.origin || !url.pathname.startsWith('/admin')) {
        return;
    }

    if (url.pathname === '/admin/sw.js' || url.pathname === '/admin/manifest.webmanifest' || url.pathname === '/admin/login') {
        return;
    }

    if (request.mode === 'navigate') {
        event.respondWith(fetch(request).catch(() => offlinePage()));
        return;
    }

    event.respondWith(networkFirst(request));
});

async function networkFirst(request) {
    try {
        return await fetch(request);
    } catch (error) {
        const cached = await caches.match(request);
        if (cached) {
            return cached;
        }
        if (request.mode === 'navigate') {
            return offlinePage();
        }
        throw error;
    }
}

function offlinePage() {
    return new Response(
        '<!DOCTYPE html><html lang="fr"><meta charset="utf-8"><meta name="viewport" content="width=device-width,initial-scale=1"><title>Hors ligne</title><body style="margin:0;min-height:100vh;display:grid;place-items:center;background:#0A0413;color:#F9F9F9;font-family:system-ui,sans-serif;text-align:center;padding:2rem"><main><h1>Hors ligne</h1><p>Les visites et les messages se mettent à jour dès que le réseau revient.</p></main></body></html>',
        { headers: { 'Content-Type': 'text/html; charset=UTF-8' } }
    );
}
