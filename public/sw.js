const CACHE_NAME = 'pos-cache-v1';
const URLS_TO_CACHE = [
    '/pos', // Halaman utama POS
    // Tambahkan aset statis yang dibutuhkan kasir di sini
    'https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap',
    'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css',
    'https://cdn.jsdelivr.net/npm/localforage@1.10.0/dist/localforage.min.js'
];

self.addEventListener('install', (event) => {
    self.skipWaiting();
    event.waitUntil(
        caches.open(CACHE_NAME)
            .then((cache) => {
                return cache.addAll(URLS_TO_CACHE);
            })
    );
});

self.addEventListener('fetch', (event) => {
    // Hanya proses request GET
    if (event.request.method !== 'GET') return;

    // Untuk halaman POS atau aset, usahakan ambil dari jaringan dulu (Network First),
    // jika gagal (offline), ambil dari cache.
    event.respondWith(
        fetch(event.request)
            .then((response) => {
                // Jika server merespon dengan error 50x (misal 500 DB down, 503)
                // anggap server sedang bermasalah dan fallback ke cache offline
                if (response && response.status >= 500) {
                    throw new Error('Server error, fallback to cache');
                }

                // Simpan ke cache jika sukses
                if (response && response.status === 200 && response.type === 'basic') {
                    const responseToCache = response.clone();
                    caches.open(CACHE_NAME).then((cache) => {
                        cache.put(event.request, responseToCache);
                    });
                }
                return response;
            })
            .catch(() => {
                return caches.match(event.request, { ignoreSearch: true }).then((cachedResponse) => {
                    if (cachedResponse) {
                        return cachedResponse;
                    }
                    // Jika yang direquest adalah halaman web (navigasi) dan gagal, kembalikan halaman utama POS dari cache
                    if (event.request.mode === 'navigate') {
                        return caches.match('/pos', { ignoreSearch: true });
                    }
                    return null;
                });
            })
    );
});

self.addEventListener('activate', (event) => {
    const cacheWhitelist = [CACHE_NAME];
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheWhitelist.indexOf(cacheName) === -1) {
                        return caches.delete(cacheName);
                    }
                })
            );
        }).then(() => self.clients.claim())
    );
});
