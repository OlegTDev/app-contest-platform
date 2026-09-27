import { Button } from "@/components/ui/button";
import { create } from "@/routes/contest";
import { router } from '@inertiajs/react';

export default function Index(): React.JSX.Element {
  return (<>
    <Button type="button" onClick={() => router.get(create().url)}>Добавить</Button>
  </>);
}

Index.layout = {
    breadcrumbs: [
        {
            title: 'Конкурсы',
            href: create().url,
        },
    ],
};
