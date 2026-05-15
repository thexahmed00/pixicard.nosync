import { defineConfig, loadEnv } from 'vite';
import vue from '@vitejs/plugin-vue';
import fs from 'fs';
import { fileURLToPath, URL } from 'node:url';

export default defineConfig(({ mode }) => {
    const env = loadEnv(mode, process.cwd(), '');

    return {
        build: {
            OutDir: 'dist',
        },

        // This is for `npm run dev`
        server: {
            host: env.VITE_HOST || '0.0.0.0',
            port: Number(env.VITE_PORT) || 5000,
            https: env.SSL_ENABLED === 'true' ? {
                key: fs.readFileSync(env.SSL_KEY_PATH),
                cert: fs.readFileSync(env.SSL_CERT_PATH),
            } : false,
        },

        // --- THIS IS THE NEW, REQUIRED SECTION ---
        // This is for `npm run preview` which PM2 uses
        preview: {
            host: env.VITE_HOST || '0.0.0.0',
            port: Number(env.VITE_PORT) || 5000,
            https: env.SSL_ENABLED === 'true' ? {
                key: fs.readFileSync(env.SSL_KEY_PATH),
                cert: fs.readFileSync(env.SSL_CERT_PATH),
            } : false,
            // This line specifically fixes your "Blocked request" error
            allowedHosts: [
                'beta.pixicard.com'
            ]
        },
        // --- END OF NEW SECTION ---

        plugins: [vue()],
        
        resolve: {
            alias: {
                '@src': fileURLToPath(new URL('./src', import.meta.url)),
                '@components': fileURLToPath(new URL('./src/components', import.meta.url)),
                '@skeletons': fileURLToPath(new URL('./src/components/skeletons', import.meta.url)),
            },
        },
    };
});