import { Head } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import { type BreadcrumbItem } from '@/types';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de tema',
        href: '/settings/appearance',
    },
];

export default function Appearance() {
    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Definições de tema" />
            <SettingsLayout>
                <div className="space-y-4 sm:space-y-6">
                    <HeadingSmall
                        title="Definições de tema"
                        description="Atualize a aparéncia do seu painel"
                    />
                    <AppearanceTabs className="w-full sm:w-auto" />
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
