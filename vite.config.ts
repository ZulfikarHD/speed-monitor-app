import inertia from '@inertiajs/vite';
import { wayfinder } from '@laravel/vite-plugin-wayfinder';
import tailwindcss from '@tailwindcss/vite';
import vue from '@vitejs/plugin-vue';
import laravel from 'laravel-vite-plugin';
import { defineConfig } from 'vite';

export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.ts'],
            refresh: true,
        }),
        inertia(),
        tailwindcss(),
        vue({
            template: {
                transformAssetUrls: {
                    base: null,
                    includeAbsolute: false,
                },
            },
        }),
        wayfinder({
            formVariants: true,
        }),
    ],
    build: {
        rollupOptions: {
            output: {
                manualChunks: (id) => {
                    // Vue core and Inertia (changes rarely)
                    if (id.includes('node_modules/vue/') || id.includes('node_modules/@inertiajs/vue3/')) {
                        return 'vue-vendor';
                    }
                    // Chart libraries (large, changes rarely)
                    if (id.includes('node_modules/chart.js/') || id.includes('node_modules/vue-chartjs/')) {
                        return 'charts-vendor';
                    }
                    // Utilities and composables
                    if (
                        id.includes('node_modules/@vueuse/core/') ||
                        id.includes('node_modules/date-fns/') ||
                        id.includes('node_modules/clsx/') ||
                        id.includes('node_modules/class-variance-authority/') ||
                        id.includes('node_modules/tailwind-merge/')
                    ) {
                        return 'utils-vendor';
                    }
                    // State management and animations
                    if (id.includes('node_modules/pinia/') || id.includes('node_modules/motion-v/')) {
                        return 'vendor';
                    }
                    // All other node_modules
                    if (id.includes('node_modules/')) {
                        return 'vendor';
                    }
                },
            },
        },
    },
});
