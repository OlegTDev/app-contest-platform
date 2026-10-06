import { createInertiaApp } from "@inertiajs/react";
import { ConfigProvider } from 'antd';
import ruRU from "antd/locale/ru_RU";
import MainLayout from "./layouts/main/Layout";
import { resolvePageComponent } from 'laravel-vite-plugin/inertia-helpers';

const appName = import.meta.env.VITE_APP_NAME || "";

void createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: (name) => {
    switch (true) {
      case name.startsWith("auth/"):
        return null;
      default:
        return MainLayout;
    }
  },
  strictMode: true,
  withApp(app) {
    return (
      <ConfigProvider locale={ruRU}>
        {app}
      </ConfigProvider>
    );
  },
  progress: {
    color: "#4B5563",
  },
});

