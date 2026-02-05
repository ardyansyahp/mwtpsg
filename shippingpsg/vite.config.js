import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css'],
            refresh: true,
            buildDirectory: 'build',
        }),
        tailwindcss(),
    ],
    server: {
        host: 'localhost',
        port: 5173,
        strictPort: false,
        watch: {
            ignored: ['**/storage/framework/views/**'],
        },
    },
    build: {
        manifest: 'manifest.json',
        outDir: 'public/build',
        rollupOptions: {
            output: {
                manualChunks: undefined,
            }
        }
    }
});
