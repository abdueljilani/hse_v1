import React from 'react';
import { createRoot } from 'react-dom/client';
import { createInertiaApp } from '@inertiajs/react';
import './Layouts/app-shell.css';
import '../css/dashboard-react.css';
import '../css/auth-react.css';

const pages = import.meta.glob('./Pages/**/*.jsx');
const guestPages = new Set([
    'Auth/Login',
    'Auth/Register',
    'Auth/ForgotPassword',
    'Auth/ResetPassword',
]);

createInertiaApp({
    resolve: async (name) => {
        const loadPage = pages[`./Pages/${name}.jsx`];
        if (!loadPage) throw new Error(`Unknown Inertia page: ${name}`);

        const module = await loadPage();
        if (!guestPages.has(name) && name !== 'Reports/Print') {
            const { default: AppShell } = await import('./Layouts/AppShell.jsx');
            module.default.layout = (page) => React.createElement(AppShell, null, page);
        }

        return module;
    },
    setup({ el, App, props }) {
        createRoot(el).render(React.createElement(App, props));
    },
    progress: { color: '#ffb000', showSpinner: false },
});
