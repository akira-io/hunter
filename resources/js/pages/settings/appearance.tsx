import { Head, router } from '@inertiajs/react';

import AppearanceTabs from '@/components/appearance-tabs';
import HeadingSmall from '@/components/heading-small';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { type BreadcrumbItem } from '@/types';
import { BookOpen } from 'lucide-react';

import AppLayout from '@/layouts/app-layout';
import SettingsLayout from '@/layouts/settings/layout';
import OnboardingController from '@/actions/App/Http/Controllers/OnboardingController';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Definições de tema',
        href: '/settings/appearance',
    },
];

export default function Appearance() {
    const handleReplayTutorial = () => {
        router.post(
            OnboardingController.deleteMethod.url(),
            {},
            {
                preserveScroll: true,
                onSuccess: () => {
                    window.location.reload();
                },
            },
        );
    };

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title="Definições de tema" />
            <SettingsLayout>
                <div className="space-y-6">
                    <HeadingSmall title="Definições de tema" description="Atualize a aparéncia do seu painel" />
                    <AppearanceTabs />

                    {/* Replay Tutorial Section */}
                    <Card className="gradient">
                        <CardHeader>
                            <CardTitle className="flex items-center gap-2">
                                <BookOpen className="h-5 w-5" />
                                Tutorial de Boas-Vindas
                            </CardTitle>
                            <CardDescription>Reveja o tutorial para relembrar as principais funcionalidades do DevHunter</CardDescription>
                        </CardHeader>
                        <CardContent>
                            <Button onClick={handleReplayTutorial} variant="outline">
                                <BookOpen className="mr-2 h-4 w-4" />
                                Repetir Tutorial
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </SettingsLayout>
        </AppLayout>
    );
}
