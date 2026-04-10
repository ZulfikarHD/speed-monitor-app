import { createInertiaApp, router } from '@inertiajs/vue3';
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
        if (props.initialPage.props.auth?.user) {
            authStore.setUser(props.initialPage.props.auth.user);
        }

        // Keep auth store in sync on every Inertia navigation
        // WHY: Without this, role-based navigation (BottomNav/TopNav) can show
        // wrong items if localStorage has stale data from a previous session
        router.on('navigate', (event) => {
            const user = event.detail.page.props.auth?.user;

            if (user) {
                authStore.setUser(user);
            }
        });

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
