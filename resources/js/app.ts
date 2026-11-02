import { createInertiaApp } from '@inertiajs/vue3';
import AuthenticatedLayout from '@/shared/layouts/AuthenticatedLayout.vue';

const appName = import.meta.env.VITE_APP_NAME || 'Laravel';

createInertiaApp({
  pages: './app',
  title: (title) => (title ? `${title} - ${appName}` : appName),
  progress: {
    color: '#4B5563',
  },
  layout: () => AuthenticatedLayout,
});
