import { createInertiaApp } from '@inertiajs/react';
import { ConfigProvider } from 'antd';
import ruRU from 'antd/locale/ru_RU';
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';
import type { ComponentType } from 'react';
import MainLayout from './layouts/main/Layout';

const appName = import.meta.env.VITE_APP_NAME || '';

const pageModules = import.meta.glob<{ default: ComponentType }>(
    './pages/**/*.tsx',
);

const resolvePageName = (name: string): string => name.replace(/\./g, '/');

void createInertiaApp({
    title: (title) => (title ? `${title} - ${appName}` : appName),
    resolve: async (name) => {
        const module = await resolvePageComponent(
            `./pages/${resolvePageName(name)}.tsx`,
            pageModules,
        );

        return module.default;
    },
    layout: (name) => {
        switch (true) {
            case name.startsWith('auth/'):
                return null;
            default:
                return MainLayout;
        }
    },
    strictMode: true,
    withApp(app) {
        return <ConfigProvider locale={ruRU}>{app}</ConfigProvider>;
    },
    progress: {
        color: '#4B5563',
    },
});
