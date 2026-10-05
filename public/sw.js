const CACHE = 'drfis-static-v10';
const STATIC_ASSETS = [
    './css/drfis-theme.css',
    './css/drfis-dashboard.css',
    './vendor/choices/choices.min.css',
    './vendor/choices/choices.min.js',
    './js/searchable-select.js',
    './images/durian-smart-orchard-hero.webp',
    './js/app.js',
    './js/field-fill.js',
    './js/date-picker.js'
];

self.addEventListener('install', event => {
    event.waitUntil(caches.open(CACHE).then(cache => cache.addAll(STATIC_ASSETS)));
    self.skipWaiting();
});

self.addEventListener('activate', event => {
    event.waitUntil(caches.keys().then(keys => Promise.all(keys.filter(key => key !== CACHE).map(key => caches.delete(key)))));
    self.clients.claim();
});

self.addEventListener('fetch', event => {
    if (event.request.method !== 'GET' || event.request.mode === 'navigate') return;
    const url = new URL(event.request.url);
    if (url.origin !== self.location.origin) return;
    event.respondWith(caches.match(event.request).then(cached => cached || fetch(event.request)));
});
