const CACHE_NAME = 'trace-app-v1';
const ASSETS_TO_CACHE = [
    '/',
    '/finishgood/in/create',
    '/assets/css/app.css',
    '/assets/js/app.js',
    'https://cdn.tailwindcss.com',
    'https://fonts.bunny.net/css?family=instrument-sans:400,500,600'
];

// Install Event
self.addEventListener('install', event => {
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then(cache => {
                console.log('SW: Pre-caching assets');
                return cache.addAll(ASSETS_TO_CACHE);
            })
    );
});

// Activate Event
self.addEventListener('activate', event => {
    event.waitUntil(
        caches.keys().then(keys => {
            return Promise.all(keys
                .filter(key => key !== CACHE_NAME)
                .map(key => caches.delete(key))
            );
        })
    );
});

// Fetch Event (Network First, then Cache)
self.addEventListener('fetch', event => {
    // We only want to cache GET requests
    if (event.request.method !== 'GET') return;

    event.respondWith(
        fetch(event.request)
            .then(networkResponse => {
                const responseClone = networkResponse.clone();
                caches.open(CACHE_NAME).then(cache => {
                    cache.put(event.request, responseClone);
                });
                return networkResponse;
            })
            .catch(() => {
                return caches.match(event.request);
            })
    );
});
// End of service worker
