import { createInertiaApp } from '@inertiajs/vue3';
import { createPinia } from 'pinia';
import { createApp, h } from 'vue';
import type { DefineComponent } from 'vue';

import { serviceWorkerManager } from '@/services/serviceWorkerManager';
import { useAuthStore } from '@/stores/auth';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    progress: {
        color: '#4B5563',
    },
    resolve: (name) => {
        const pages = import.meta.glob<DefineComponent>('./pages/**/*.vue', {
            eager: true,
        });

        return pages[`./pages/${name}.vue`];
    },
    setup({ el, App, props, plugin }) {
        const pinia = createPinia();

        const app = createApp({ render: () => h(App, props) })
            .use(plugin)
            .use(pinia);

        // Initialize auth store from localStorage OR Inertia page props
        const authStore = useAuthStore();
        authStore.initializeAuth();

        // Sync user data from Inertia page props (session-based auth)
        // WHY: Backend uses session auth and shares user via Inertia middleware
        // Frontend needs this data in the auth store for navigation/role checks
        if (props.initialPage.props.auth?.user) {
            authStore.setUser(props.initialPage.props.auth.user);
        }

        // Register Service Worker for PWA offline support
        // Deferred to window.load to avoid blocking initial render
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                serviceWorkerManager
                    .register()
                    .catch((error) => {
                        console.error('[App] Service Worker registration failed:', error);
                    });
            });
        }

        app.mount(el);
    },
});
