import { PageProps as InertiaPageProps } from '@inertiajs/core';
import { Auth } from './auth';

export interface PageProps extends InertiaPageProps {
  name: string;
  auth: Auth;
  sidebarOpen: boolean;
  app: {
    max_upload_size: number;
  };
  flash: {
    success: string | null;
    error: string | null;
  };
  csrf_token: string;
  [key: string]: unknown;
}
