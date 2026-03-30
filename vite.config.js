import { defineConfig } from 'vite'
import laravel from 'laravel-vite-plugin'
import { createVuePlugin } from 'vite-plugin-vue2'

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/js/app.js'],
            refresh: true,
        }),
        createVuePlugin(),
    ],
    server: {
    host: '0.0.0.0',
    port: 5173,
    hmr: {
        host: 'localhost',
        port: 5173,
    },
    watch: {
        usePolling: true,
        interval: 500,
    },
}
})