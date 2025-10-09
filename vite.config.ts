import tailwindcss from '@tailwindcss/vite';
import react from '@vitejs/plugin-react';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import fs from 'fs';

// Plugin to auto-update Service Worker cache version
function serviceWorkerVersionPlugin() {
    return {
        name: 'sw-version',
        writeBundle() {
            const swPath = 'public/sw.js';
            if (fs.existsSync(swPath)) {
                let content = fs.readFileSync(swPath, 'utf-8');
                const version = `v${Date.now()}`;
                content = content.replace(/const CACHE_VERSION = ['"]v\d+['"]/g, `const CACHE_VERSION = '${version}'`);
                fs.writeFileSync(swPath, content);
                console.log(`✓ Service Worker cache version updated to ${version}`);
            }
        },
    };
}

export default defineConfig({
    plugins: [
        wayfinder(),
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        serviceWorkerVersionPlugin(),
    ],
    esbuild: {
        jsx: 'automatic',
    },
});
