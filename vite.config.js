import { defineConfig } from 'vite';
import vue from '@vitejs/plugin-vue'
import laravel from 'laravel-vite-plugin';
import tailwindcss from 'tailwindcss';
import { resolve } from 'path'

export default defineConfig({
    plugins: [
        tailwindcss(),
        vue(),
        laravel({
            input: [
                'resources/css/app.css',
                'resources/js/app.js',
                'resources/js/backoffice.js'
            ],
            refresh: true,
        }),
    ],
    resolve: {
      alias: {
        '@': resolve(__dirname, 'resources'),
      },
    },
    server: {
      open: true,
    },
});
