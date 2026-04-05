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
                manualChunks: {
                    // Vue core and Inertia (changes rarely)
                    'vue-vendor': ['vue', '@inertiajs/vue3'],
                    // Chart libraries (large, changes rarely)
                    'charts-vendor': ['chart.js', 'vue-chartjs'],
                    // Utilities and composables
                    'utils-vendor': [
                        '@vueuse/core',
                        'date-fns',
                        'clsx',
                        'class-variance-authority',
                        'tailwind-merge',
                    ],
                    // State management and animations
                    'vendor': ['pinia', 'motion-v'],
                },
            },
        },
    },
});
