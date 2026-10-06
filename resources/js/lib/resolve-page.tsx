import { type ResolvedComponent } from "@inertiajs/react";
import MainLayout from '@/layouts/main/Layout';

export async function resolvePage(name: string): Promise<any> {
  const pages = import.meta.glob<ResolvedComponent>([
    '.././pages/**/*.tsx',
    '.././pages/**/index.tsx'
  ], { eager: true });

  let page = pages[`./pages/${name}.tsx`];
  console.log(page, `.././pages/${name}.tsx`);
  if (!page) {
    page = pages[`.././pages/${name}/index.tsx`];
  }

  if (!page) {
    throw new Error(`Inertia page component "./pages/${name}.tsx" or "./pages/${name}/index.tsx" not found.`);
  }

  const pageComponent: any = (page as any).default || page;
  if (pageComponent.layout !== null) {
    pageComponent.layout = (page: React.ReactNode) => <MainLayout>{page}</MainLayout>;
  }

  return pageComponent;
}
