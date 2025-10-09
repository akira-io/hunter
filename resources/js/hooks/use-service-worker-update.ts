import { useEffect, useState } from 'react';

export function useServiceWorkerUpdate() {
    const [updateAvailable, setUpdateAvailable] = useState(false);
    const [waitingWorker, setWaitingWorker] = useState<ServiceWorker | null>(null);

    useEffect(() => {
        if (!('serviceWorker' in navigator) || !import.meta.env.PROD) {
            return;
        }

        const handleUpdate = () => {
            navigator.serviceWorker
                .register('/sw.js')
                .then((registration) => {
                    console.log('[PWA] Service Worker registered:', registration.scope);

                    // Check for updates periodically
                    const intervalId = setInterval(() => {
                        registration.update();
                    }, 60000); // Check every minute

                    // Handle service worker updates
                    registration.addEventListener('updatefound', () => {
                        const newWorker = registration.installing;
                        if (newWorker) {
                            newWorker.addEventListener('statechange', () => {
                                if (newWorker.state === 'installed' && navigator.serviceWorker.controller) {
                                    // New service worker available
                                    setWaitingWorker(newWorker);
                                    setUpdateAvailable(true);
                                }
                            });
                        }
                    });

                    return () => clearInterval(intervalId);
                })
                .catch((error) => {
                    console.error('[PWA] Service Worker registration failed:', error);
                });

            // Handle service worker controller change
            navigator.serviceWorker.addEventListener('controllerchange', () => {
                window.location.reload();
            });
        };

        if (document.readyState === 'complete') {
            handleUpdate();
        } else {
            window.addEventListener('load', handleUpdate);
            return () => window.removeEventListener('load', handleUpdate);
        }
    }, []);

    const applyUpdate = () => {
        if (waitingWorker) {
            waitingWorker.postMessage({ type: 'SKIP_WAITING' });
            window.location.reload();
        }
    };

    const dismissUpdate = () => {
        setUpdateAvailable(false);
    };

    return {
        updateAvailable,
        applyUpdate,
        dismissUpdate,
    };
}
