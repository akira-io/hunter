const CACHE_VERSION = 'v4';
const STATIC_CACHE = `devhunter-static-${CACHE_VERSION}`;
const DYNAMIC_CACHE = `devhunter-dynamic-${CACHE_VERSION}`;
const IMAGE_CACHE = `devhunter-images-${CACHE_VERSION}`;

// Assets to cache on install (only truly static assets, no HTML pages)
const STATIC_ASSETS = [
    '/manifest.json',
    '/logo.svg',
];

// Install event - cache static assets
self.addEventListener('install', (event) => {
    console.log('[SW] Installing service worker...');
    event.waitUntil(
        caches.open(STATIC_CACHE).then((cache) => {
            console.log('[SW] Caching static assets');
            return cache.addAll(STATIC_ASSETS.map(url => new Request(url, { cache: 'reload' })));
        }).catch((error) => {
            console.error('[SW] Failed to cache static assets:', error);
        })
    );
    self.skipWaiting();
});

// Activate event - clean up old caches
self.addEventListener('activate', (event) => {
    console.log('[SW] Activating service worker...');
    event.waitUntil(
        caches.keys().then((cacheNames) => {
            return Promise.all(
                cacheNames.map((cacheName) => {
                    if (cacheName !== STATIC_CACHE && cacheName !== DYNAMIC_CACHE && cacheName !== IMAGE_CACHE) {
                        console.log('[SW] Deleting old cache:', cacheName);
                        return caches.delete(cacheName);
                    }
                })
            );
        })
    );
    return self.clients.claim();
});

// Fetch event - serve from cache, fallback to network
self.addEventListener('fetch', (event) => {
    const { request } = event;
    const url = new URL(request.url);

    // Skip non-GET requests
    if (request.method !== 'GET') {
        return;
    }

    // Skip chrome extension requests
    if (url.protocol === 'chrome-extension:') {
        return;
    }

    // Skip auth routes (OAuth, login, register, etc) - let them pass through
    if (url.pathname.startsWith('/auth/') ||
        url.pathname.startsWith('/login') ||
        url.pathname.startsWith('/register') ||
        url.pathname.startsWith('/logout')) {
        return;
    }

    // Skip Inertia requests (infinite scroll, form submissions, etc) - always use network
    if (request.headers.get('X-Inertia') ||
        request.headers.get('X-Inertia-Partial-Component') ||
        request.headers.get('X-Inertia-Partial-Data')) {
        return;
    }

    // Handle different types of requests
    if (request.destination === 'image') {
        event.respondWith(handleImageRequest(request));
    } else if (url.pathname.startsWith('/api/')) {
        // Don't cache API requests - always fetch from network
        event.respondWith(fetch(request));
    } else if (request.destination === 'document' || request.headers.get('accept')?.includes('text/html')) {
        // HTML pages - always fetch from network first (Network First strategy)
        event.respondWith(handleHTMLRequest(request));
    } else if (request.destination === 'script' || request.destination === 'style' || url.pathname.match(/\.(js|css)$/)) {
        // JS/CSS - cache with network update in background (Stale While Revalidate)
        event.respondWith(handleAssetRequest(request));
    } else {
        // Other resources - network first
        event.respondWith(handleRequest(request));
    }
});

// Handle HTML requests - Network First (always try network first, fallback to cache)
async function handleHTMLRequest(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            // Don't cache HTML pages to always get fresh content
            return response;
        }
        throw new Error('Network response was not ok');
    } catch (error) {
        console.error('[SW] HTML fetch failed, trying cache:', error);
        const cache = await caches.open(DYNAMIC_CACHE);
        const cached = await cache.match(request);

        if (cached) {
            return cached;
        }

        // Return offline message
        return new Response('Offline - Please check your connection', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: new Headers({
                'Content-Type': 'text/html',
            }),
        });
    }
}

// Handle asset requests (JS/CSS) - Stale While Revalidate
async function handleAssetRequest(request) {
    const cache = await caches.open(DYNAMIC_CACHE);
    const cached = await cache.match(request);

    // Return cached version immediately and update in background
    if (cached) {
        // Update cache in background
        fetch(request).then(response => {
            if (response.ok) {
                cache.put(request, response.clone());
            }
        }).catch(() => {});
        return cached;
    }

    // No cache, fetch from network
    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Asset fetch failed:', error);
        return new Response('', { status: 404, statusText: 'Not Found' });
    }
}

// Handle image requests with caching - Cache First
async function handleImageRequest(request) {
    const cache = await caches.open(IMAGE_CACHE);
    const cached = await cache.match(request);

    if (cached) {
        return cached;
    }

    try {
        const response = await fetch(request);
        if (response.ok) {
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Image fetch failed:', error);
        // Return a fallback image or empty response
        return new Response('', { status: 404, statusText: 'Not Found' });
    }
}

// Handle general requests - Network First
async function handleRequest(request) {
    try {
        const response = await fetch(request);
        if (response.ok) {
            const cache = await caches.open(DYNAMIC_CACHE);
            cache.put(request, response.clone());
        }
        return response;
    } catch (error) {
        console.error('[SW] Fetch failed:', error);

        // Try dynamic cache
        const cache = await caches.open(DYNAMIC_CACHE);
        const cached = await cache.match(request);

        if (cached) {
            return cached;
        }

        // Try static cache
        const staticCache = await caches.open(STATIC_CACHE);
        const staticCached = await staticCache.match(request);

        if (staticCached) {
            return staticCached;
        }

        // Return offline page or error
        return new Response('Offline', {
            status: 503,
            statusText: 'Service Unavailable',
            headers: new Headers({
                'Content-Type': 'text/plain',
            }),
        });
    }
}

// Listen for messages from the client
self.addEventListener('message', (event) => {
    if (event.data && event.data.type === 'SKIP_WAITING') {
        self.skipWaiting();
    }

    if (event.data && event.data.type === 'CLEAR_CACHE') {
        event.waitUntil(
            caches.keys().then((cacheNames) => {
                return Promise.all(
                    cacheNames.map((cacheName) => caches.delete(cacheName))
                );
            })
        );
    }
});

// Background sync for offline actions
self.addEventListener('sync', (event) => {
    if (event.tag === 'sync-data') {
        event.waitUntil(syncData());
    }
});

async function syncData() {
    console.log('[SW] Syncing data in background...');
    // Implement your background sync logic here
}

// Push notification handler
self.addEventListener('push', (event) => {
    const data = event.data ? event.data.json() : {};
    const title = data.title || 'DevHunter';
    const options = {
        body: data.body || 'New notification',
        icon: '/icons/icon-192x192.png',
        badge: '/icons/icon-72x72.png',
        data: data.data || {},
        actions: data.actions || [],
    };

    event.waitUntil(
        self.registration.showNotification(title, options)
    );
});

// Notification click handler
self.addEventListener('notificationclick', (event) => {
    event.notification.close();

    event.waitUntil(
        clients.matchAll({ type: 'window', includeUncontrolled: true }).then((clientList) => {
            // Check if there's already a window open
            for (const client of clientList) {
                if (client.url === event.notification.data.url && 'focus' in client) {
                    return client.focus();
                }
            }
            // Open new window if none found
            if (clients.openWindow) {
                return clients.openWindow(event.notification.data.url || '/');
            }
        })
    );
});
