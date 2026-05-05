import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import react from '@vitejs/plugin-react';

const vitePort = Number(process.env.VITE_PORT || 5173);
const hmrPort = Number(process.env.VITE_HMR_PORT || vitePort);

export default defineConfig({
    server: {
        host: process.env.VITE_HOST || '127.0.0.1',
        port: vitePort,
        strictPort: true,
        hmr: {
            host: process.env.VITE_HMR_HOST || 'localhost',
            port: hmrPort,
        },
    },
    plugins: [
        laravel({
            input: 'resources/js/app.jsx',
            refresh: true,
        }),
        react(),
    ],
});
