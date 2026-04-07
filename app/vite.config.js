import { defineConfig } from 'vite';
import laravel from 'laravel-vite-plugin';
import tailwindcss from '@tailwindcss/vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.js'],
            refresh: true,
        }),
        tailwindcss(),
    ],
    server: {
        // 1. Escuta em todas as interfaces de rede do container
        host: '0.0.0.0', 
        // 2. Garante que o navegador no Windows saiba onde conectar
        hmr: {
            host: 'localhost',
        },
        watch: {
            // 3. Essencial para o WSL 2 detectar mudanças de arquivos no disco do Windows
            usePolling: true, 
            ignored: ['**/storage/framework/views/**'],
        },
    },
});