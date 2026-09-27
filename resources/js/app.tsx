import { createInertiaApp } from "@inertiajs/react";
import { ConfigProvider } from 'antd';
import ruRU from "antd/locale/ru_RU";
import MainLayout from "./layouts/main/Layout";

const appName = import.meta.env.VITE_APP_NAME || "";

void createInertiaApp({
  title: (title) => (title ? `${title} - ${appName}` : appName),
  layout: (name) => {
    switch (true) {
      // case name === "welcome":
      //   return null;
      case name.startsWith("auth/"):
        return null;
      // case name.startsWith("settings/"):
      //   return [AppLayout, SettingsLayout];
      default:
        return MainLayout;
    }
  },
  strictMode: true,
  withApp(app) {
    return (
      // <TooltipProvider delayDuration={0}>
      //   {app}
      //   <Toaster />
      // </TooltipProvider>
      <ConfigProvider locale={ruRU}>
        {app}
      </ConfigProvider>
    );
  },
  progress: {
    color: "#4B5563",
  },
});

