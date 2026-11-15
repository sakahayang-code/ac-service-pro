// AC SERVICE PRO - Service Worker v1.0
const CACHE_NAME = 'ac-service-pro-v1';
const RELATIVE_ASSETS = [
    '',
    'dashboard',
    'assets/css/style.css',
    'assets/js/app.js',
    'assets/js/lucide.min.js',
    'assets/js/chart.min.js',
    'assets/js/offline-db.js',
    'assets/icons/icon-192.png',
    'assets/icons/icon-512.png',
    'assets/icons/favicon.png',
    'manifest.json'
];

self.addEventListener('install', (event) => {
    const scope = self.registration.scope;
    const staticAssets = RELATIVE_ASSETS.map(asset => new URL(asset, scope).href);
    event.waitUntil(
        caches.open(CACHE_NAME).then((cache) => {
            return cache.addAll(staticAssets).catch((err) => {
                console.warn('Pre-cache partial warning:', err);
            });
        }).then(() => self.skipWaiting())
    );
});

self.addEventListener('activate', (event) => {
    event.waitUntil(
        caches.keys().then((keys) => {
            return Promise.all(
                keys.map((key) => {
                    if (key !== CACHE_NAME) {
                        return caches.delete(key);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});

self.addEventListener('fetch', (event) => {
    const request = event.request;
    const url = new URL(request.url);

    // Only handle GET requests for cache
    if (request.method !== 'GET') {
        return;
    }

    // Don't cache API POST/PUT or dynamic sync routes
    if (url.pathname.includes('/api/sync')) {
        return;
    }

    // Static assets: Cache First, fallback to Network
    if (url.pathname.includes('/assets/')) {
        event.respondWith(
            caches.match(request).then((cachedResponse) => {
                if (cachedResponse) {
                    return cachedResponse;
                }
                return fetch(request).then((networkResponse) => {
                    if (networkResponse && networkResponse.status === 200) {
                        const responseClone = networkResponse.clone();
                        caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                    }
                    return networkResponse;
                });
            })
        );
        return;
    }

    // HTML Pages: Network First, fallback to Cache, then Offline Fallback
    event.respondWith(
        fetch(request)
            .then((networkResponse) => {
                if (networkResponse && networkResponse.status === 200) {
                    const responseClone = networkResponse.clone();
                    caches.open(CACHE_NAME).then((cache) => cache.put(request, responseClone));
                }
                return networkResponse;
            })
            .catch(() => {
                return caches.match(request).then((cached) => {
                    if (cached) {
                        return cached;
                    }
                    // Return basic offline fallback page
                    return new Response(
                        `<!DOCTYPE html>
                        <html lang="id">
                        <head>
                            <meta charset="UTF-8">
                            <meta name="viewport" content="width=device-width, initial-scale=1.0">
                            <title>Offline - AC Service Pro</title>
                            <link rel="stylesheet" href="/assets/css/style.css">
                        </head>
                        <body class="bg-slate-50 flex items-center justify-center min-h-screen p-4 text-center">
                            <div class="card max-w-md p-6">
                                <div class="w-16 h-16 mx-auto mb-4 bg-amber-100 text-amber-600 rounded-full flex items-center justify-center">
                                    <svg width="32" height="32" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M1 1l22 22M16.72 11.06A10.94 10.94 0 0 1 19 12.55M5 12.55a10.94 10.94 0 0 1 5.17-2.39M10.71 5.05A16 16 0 0 1 22.58 9M1.42 9a15.91 15.91 0 0 1 4.7-2.88M8.53 16.11a6 6 0 0 1 6.95 0M12 20h.01"/></svg>
                                </div>
                                <h1 class="text-xl font-bold text-slate-800 mb-2">Anda Sedang Offline</h1>
                                <p class="text-slate-600 text-sm mb-6">Koneksi internet terputus. Data pekerjaan yang sudah tersimpan di perangkat teknisi tetap dapat diakses dan diisi. Perubahan akan otomatis disinkronkan saat online kembali.</p>
                                <a href="/technician/tasks" class="btn btn-primary w-full">Buka Tugas Tersimpan</a>
                            </div>
                        </body>
                        </html>`,
                        { headers: { 'Content-Type': 'text/html; charset=utf-8' } }
                    );
                });
            })
    );
});
