import './bootstrap';
import '../css/app.css';

import { createApp, h } from 'vue';
import { createInertiaApp } from '@inertiajs/vue3';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

// Импортируем твои основные компоненты
import AppHeader from './Pages/Header.vue';
import AppFooter from './Pages/Footer.vue';

createInertiaApp({
  title: (title) => `${title} - ${window.appName}`, // <-- Используем window.appName
  resolve: (name) => resolvePageComponent(`./Pages/${name}.vue`, import.meta.glob('./Pages/**/*.vue')),
    setup({ el, App, props, plugin }) {
        return createApp({ render: () => h(App, props) })
            .use(plugin)
            // Регистрируем глобально, если ты хочешь использовать их напрямую в других компонентах
            // .component('AppHeader', AppHeader)
            // .component('AppFooter', AppFooter)
            .mount(el);
    },
    progress: {
        color: '#ffc0cb', // Цвет для полосы прогресса Inertia
    },
});