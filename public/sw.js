const CACHE_NAME = 'studio-crm-cache-v4'; // Bumped to v4 to destroy all old caches

const urlsToCache = [
    '/manifest.json'
];

// Install the service worker
self.addEventListener('install', event => {
    self.skipWaiting(); // Forces the new service worker to activate immediately
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                return cache.addAll(urlsToCache);
            })
    );
});

// Fetch resources (Network strictly first for HTML)
self.addEventListener('fetch', event => {
    
    // SECURITY FIX: Never intercept POST/PUT/DELETE
    if (event.request.method !== 'GET') {
        return;
    }

    // STRICT ANTI-419 FIX: Never serve cached HTML pages. Always fetch fresh from server
    // so Laravel generates a valid CSRF Token mapped to the active Session ID.
    if (event.request.mode === 'navigate' || event.request.headers.get('accept').includes('text/html')) {
        event.respondWith(fetch(event.request));
        return;
    }

    // For static assets like images or CSS, fallback to cache if offline
    event.respondWith(
        fetch(event.request).catch(() => {
            return caches.match(event.request);
        })
    );
});

// Update the service worker and completely wipe old corrupted caches
self.addEventListener('activate', event => {
    event.waitUntil(self.clients.claim()); 
    
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then(cacheNames => {
            return Promise.all(
                cacheNames.map(cacheName => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName); 
                    }
                })
            );
        })
    );
});